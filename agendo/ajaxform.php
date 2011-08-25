<?php
require_once("commonCode.php");
importJs();
?>
<script type="text/javascript">
function checklogin(type){
    // alert("Invalid " + type + "! Please try again.");
    showMessage("Invalid " + type + "! Please try again.");
    window.location = "newperm.php";
}
</script>

<?php

// require_once(".htconnect.php");
require_once("errorHandler.php");
require_once("alert/class.phpmailer.php");

$error = new errorHandler;
$mail = new PHPMailer;

if(isset($_GET['val'])){ //new user form -> ajax response
    $id = $_GET['val'];    
    if($id != 0){
        $sql = "SELECT institute_name FROM institute, department WHERE institute_id = department_inst AND department_id = $id";
        $res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
        $row = dbHelp::fetchRowByIndex($res);
        echo $row[0];
    } else {
        //do nothing
    }
} else { //new permissions form
    $user_login = $_POST['user_login'];
    $pwd = $_POST['pwd'];
    $resource = $_POST['Resource'];
    $train = $_POST['assistance'];
    if($train == 'on') $train = "yes";
    else $train = "no";
    
    // $sql = "SELECT password('$pwd')";
    // $res = dbHelp::query($sql) or die($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    // $row = dbHelp::fetchRowByIndex($res);
    // $pwd = $row[0];
    // $sql = "SELECT user_passwd, CONCAT(user_firstname,' ',user_lastname),user_email from ".dbHelp::getSchemaName().".user WHERE user_login = '".$user_login."'";
    $pwd = cryptPassword($pwd);
    $sql = "SELECT user_passwd,user_firstname,user_lastname,user_email from ".dbHelp::getSchemaName().".user WHERE user_login = '".$user_login."'";
    $res = dbHelp::query($sql) or die($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    $row = dbHelp::fetchRowByIndex($res);
    $nrows = dbHelp::numberOfRows($res);
    // $user_name = $row[1];
    // $user_email = $row[2];
    $user_name = $row[1]." ".$row[2];
    $user_email = $row[3];
    
    if($nrows == 0){
        echo "<script type='text/javascript'>checklogin('user name');</script>";
        exit();
    }
    if($pwd != $row[0]){
        echo "<script type='text/javascript'>checklogin('password');</script>";
        exit();
    }
    
    $sql = "SELECT permissions_resource FROM permissions WHERE permissions_user IN (SELECT user_id from ".dbHelp::getSchemaName().".user WHERE user_login='$user_login') AND permissions_resource = $resource";
    $res = dbHelp::query($sql) or die($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    if(dbHelp::numberOfRows($res) != 0){
        echo "<script type='text/javascript'>";
        // echo "alert('You already have permissions to use this resource. Please contact the equipment administrator for more information!');";
        echo "showMessage('You already have permissions to use this resource. Please contact the equipment administrator for more information!');";
        echo "window.close();";
        echo "</script>";
        exit();
    }
    
    $sql = "SELECT user_email, resource_name from ".dbHelp::getSchemaName().".user, resource WHERE user_id = resource_resp AND resource_id = ".$resource;
    $res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    $row = dbHelp::fetchRowByIndex($res);
    
    $resp = $row[0];
    $resource = $row[1];
    
    //Send email both to the user and to the administrator
    // $sql = "SELECT mainconfig_host, mainconfig_port, mainconfig_password, mainconfig_email, mainconfig_smtpsecure, mainconfig_smtpauth FROM mainconfig WHERE mainconfig_id = 1";
    // $res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    // $row = dbHelp::fetchRowByIndex($res);
	$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'";
	$res = dbHelp::query($sql);
	$configArray = array();
	for($i=0;$arr=dbHelp::fetchRowByIndex($res);$i++){
		$configArray[$arr[0]] = $arr[1];
	}
	$mail->SMTPAuth   = $configArray['smtpauth'];
	$mail->SMTPSecure = $configArray['smtpsecure'];
	$mail->Port       = $configArray['port'];
	$mail->Host       = $configArray['host'];
	$mail->Username   = $configArray['email'];
	$mail->Password   = $configArray['password'];
    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
    $mail->SetFrom($configArray['email'], "Calendar administration");
    $mail->AddReplyTo($configArray['email'],"Calendar administration");   

	// for($i=0; $arr = dbHelp::fetchRowByIndex($res); $i++){
		// $row[$i] = $arr[1];
	// }
    
    // $mail->SMTPAuth   = $row[5];                  // enable SMTP authentication
    // $mail->SMTPSecure = $row[4];                 // sets the prefix to the servier
    // $mail->Port       = $row[1];                   // set the SMTP port for the GMAIL server   
    // $mail->Host       = $row[0];      // sets GMAIL as the SMTP server
    // $mail->Username   = $row[3];  // GMAIL username
    // $mail->Password   = $row[2];            // GMAIL password
    // $mail->SetFrom($row[3], "Calendar administration");
    // $mail->AddReplyTo($row[3],"Calendar administration");   
    
    $msg = "The user $user_name is requesting permission to use $resource.\n\n";
    $msg .= "login: $user_login\n";
    $msg .= "email: $user_email\n";
    $msg .= "resource: $resource\n";
    $msg .= "training request: $train\n\n";
    $msg .= "Please confirm the permission to the requesting user.\n\n";
    $msg .= "This is an automatic generated email. Do not reply!";
    
    $mail->Subject = "Calendar administration: Permission requested";
    $mail->Body = $msg;
    
    $mail->AddAddress($resp, "");
    // $mail->AddAddress("rpdias@fc.ul.pt", "");

    if(!$mail->Send()) {
        echo "<table border=0>";
        echo "<tr><td><br>Error on sending request!<br></td></tr>";
        echo "<tr><td>If the problem persists please contact the administration!<br><br></td></tr>";
        echo "<tr><td><a href=# class=new onclick=\"javascript:window.open('newperm.php','_blank','directories=no,status=no,menubar=yes,location=yes,resizable=no,scrollbars=no,width=350,height=275')\">New permission</a>";
        echo "&nbsp;&nbsp;&nbsp;";
        echo "<tr><td><a href=# onclick='window.close()'>Exit</a></td></tr>";
        echo "</table>";
        
    } else {
        echo "<table border=0>";
        echo "<tr><td>Your request has been successfully sent to the equipment administrator. Please wait for confirmation!<br><br></td></tr>";
        echo "<tr><td><a href=# onclick='window.close()'>Exit</a></td></tr>";
        echo "</table>";
    }
}
   
    

?>