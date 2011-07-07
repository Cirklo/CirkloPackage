<?php 

require_once "__dbConnect.php";
require_once "mailClass.php";
require_once "genObjClass.php";
require_once "dispClass.php";

//call database
$conn=new dbConnection();
$mail=new mailClass();
$display=new dispClass();
$genObj=new genObjClass();

//phpinfo();
 
$sql=$conn->query("SELECT user_id, user_login, user_email FROM user");
//echo $sql->queryString;
//loop through all registered users
for($j=0;$row=$sql->fetch();$j++){
    $vowels="aeiyou";
    $consonants="bcdfghjklmnpqrstvwxz";
    $pwd='';
    for ($i = 0; $i < 8; $i++) {
        if ($i%2==0) {
            $pwd.=$consonants[rand(0,strlen($consonants)-1)];
        } else {
            $pwd.=$vowels[rand(0,strlen($vowels)-1)];
        }
    }
    //echo $pwd;
    $newpwd=$genObj->cryptPass($pwd);
    try{
    	$sql_upd=$conn->query("UPDATE user SET user_passwd='$newpwd' WHERE user_id=$row[0]");
    	$subject="(BUGFIX) Agendo reservation system: New password";
	    $msg="Login: $row[1]\n";
	    $msg.="Password: $pwd\n";
	    $msg.="\n\nThis is a randomly generated password. You can enter your profile and change this password.";
	    $msg.="\n\n For any information contact support@cirklo.org";
	    $to=$row[2];
	    $from="info@cirklo.org";
	   // $mail->sendMail($subject, $to, $from, $msg);
	   //	$mail->ClearAddresses();
    } catch (Exception $e){
    	echo "Unable to send email!";
    }
}
?>