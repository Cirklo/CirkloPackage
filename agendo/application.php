<?php
require_once("commonCode.php");

if(isset($_POST['makeUser']) && $_POST['makeUser'] == true){
	makeUser();
	exit;
}

if(isset($_GET['code']) && $_GET['code'] != ''){
	// check if code exists
	$sql = "select * from pending where pending_code = :0";
	$prep = dbHelp::query($sql, array($_GET['code']));
	if(dbHelp::numberOfRows($prep) == 0){
		throw new Exception('This operation has expired');
	}
	
	// a check is made in ajaxdpt.php to make sure the login and email dont exist already in the database
	$login = generate_login($_GET['first'], $_GET['last'], $_GET['mail']);
	$pass = generate_password();
	insert_user(array($login, cryptPassword($pass), $_GET['first'], $_GET['last'], $_GET['dep'], $_GET['phone'], $_GET['ext'], $_GET['mobile'], $_GET['mail']));
	
	// get the user id
	$sql = "select user_id from ".dbHelp::getSchemaName().".user where user_login = :0";
	$prep = dbHelp::query($sql, array($login));
	$row = dbHelp::fetchRowByIndex($prep);
	$userId = $row[0];
	
	// get the resource name
	$sql = "select resource_name, user_email, user_firstname, user_lastname from resource join ".dbHelp::getSchemaName().".user on user_id = resource_resp where resource_id = :0 and resource_status not in (0, 2)";
	$prep = dbHelp::query($sql, array($_GET['res']));
	$row = dbHelp::fetchRowByIndex($prep);
	$resName = $row[0];
	$managerMail = $row[1];
	$managerName = $row[2]." ".$row[2];
	
	// give user access to the requested resource
	$sql = "insert into permissions values(NULL, ".$userId.", :0, 1, 0, NULL)";
	$prep = dbHelp::query($sql, array($_GET['res']));
	
	// remove code from db
	$sql = "delete from pending where pending_code = :0";
	$prep = dbHelp::query($sql, array($_GET['code']));
	
	// mail user with is login data
	$subject = "Agendo: You have been added";
	$message = "You have been added to agendo and have permission to use the resource '".$resName."'";
	$message .= "\nYour login data is:";
	$message .= "\nlogin: ".$login;
	$message .= "\npassword: ".$pass;
	$mail = getMailObject($subject, $_GET['mail'], $message, $replyToPerson, $managerMail);
	sendMailObject($mail);
	
	echo "User has been added, access to the resource has been given and an email has been sent to the user with the login data";
	exit;
}

function insert_user($dataArray){
	$sql = "
		insert into ".dbHelp::getSchemaName().".user
			(user_login, user_passwd, user_firstname, user_lastname, user_dep, user_phone, user_phonext, user_mobile, user_email, user_alert, user_level)
		values
			(:0, :1, :2, :3, :4, :5, :6, :7, :8, '1', '2')
	";
	dbHelp::query($sql, $dataArray);
}

function makeUser(){
	try{
		// $dataArray = $_POST['dataArray'];
		// $sql = "
			// insert into ".dbHelp::getSchemaName().".user
				// (user_login, user_passwd, user_firstname, user_lastname, user_dep, user_phone, user_phonext, user_mobile, user_email, user_alert, user_level)
			// values
				// (:0, :1, :2, :3, :4, :5, :6, :7, :8, '1', '2')
		// ";
		// dbHelp::query($sql, $dataArray);
		insert_user($_POST['dataArray']);
		$json->success = true;
		$json->message = "User inserted";
	}
	catch(Exception $e){
		$json->success = false;
		$json->message = $e->getMessage();
	}
	// wtf($json->success."--".$json->message);
	echo json_encode($json);
}

function generate_login($firstName, $lastName, $mail){
	$sql = "select user_login from ".dbHelp::getSchemaName().".user where user_login = :0";
	
	// check if mail can be used to generate the login
	$login = strtolower(strtok($mail,"@"));
	$prep = dbHelp::query($sql, array($login));
	if(dbHelp::numberOfRows($prep) == 0){
		return $login;
	}
	
	// $login = strtolower(substr($firstName, 0, 1).$lastName);
	// $prep = dbHelp::query($sql, array($login));
	// if(dbHelp::numberOfRows($prep) == 0){
	// 	return $login;
	
	$length = sizeOf($firstName);
	for($i = 0; $i < $length; $i++){
		$login = strtolower(substr($firstName, 0, $i).$lastName);
		$prep = dbHelp::query($sql, array($login));
		if(dbHelp::numberOfRows($prep) == 0){
			return $login;
		}
	}
	
	// this will get a new login fo sure, even if it takes decades
	$i = 0;
	$login = strtolower(substr($firstName, 0, 1).$lastName);
	while(true){
		$i++;
		$loginTemp = $login.$i;
		$prep = dbHelp::query($sql, array($loginTemp));
		if(dbHelp::numberOfRows($prep) == 0){
			return $loginTemp;
		}
	}
	
	// this will never happen
	return false;
}
	

?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/admin.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/checkfields.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>

<script type="text/javascript">
function getValue(id,target){
    var val = document.getElementById(id).value;
    if(val == '0'){
        document.getElementById('GEDepartment').removeAttribute('readonly');
        document.getElementById(target).removeAttribute('readonly');

    } else {
        document.getElementById('GEDepartment').setAttribute('readonly','readonly');
        document.getElementById('GEDepartment').value = '';
        document.getElementById(target).setAttribute('readonly','readonly');
    }
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    url="ajaxdpt.php?val=" + val;
    xmlhttp.open("GET", url, false);
    xmlhttp.send(null);

    var str = xmlhttp.responseText;
    document.getElementById(target).value = str;
}

function checktrain(id){
    var str = id.substring(0, id.length-6);
    document.getElementById(str).checked = document.getElementById(id).checked;
}

userLogin = '';
userPass = '';
var userInfoArray = new Array();
function setValues(){
	try{
		userLogin = opener.document.getElementById('user_idm').value;
		userPass = opener.document.getElementById('user_passwd').value;
		
		userInfoArray = opener.getUserInfo();
		// if(userInfoArray.length > 0){
		if(userInfoArray != ''){
			// document.getElementById('Email').value = userInfoArray['email'];
			document.getElementById('Email').value = userInfoArray;
			document.getElementById('Email').setAttribute('readonly','readonly');
		}
	}
	catch(error){
		alert(error);
	}
}

function makeNewUser(){
	// alert('making user');
	var dataArray = new Array();
	dataArray[0] = userLogin; // undefined?
	dataArray[1] = userPass; // undefined?
	dataArray[2] = document.getElementById('First name').value;
	dataArray[3] = document.getElementById('Last name').value;
	departments = document.getElementById('Department');
	dataArray[4] = departments.options[departments.selectedIndex].value;
	dataArray[5] = document.getElementById('Phone').value;
	dataArray[6] = document.getElementById('Phone extension').value;
	dataArray[7] = document.getElementById('Mobile').value;
	dataArray[8] = document.getElementById('Email').value;
	notEmpty = true;
	for(i=0;i<dataArray.length;i++){
		if(dataArray[i] == ''){
			notEmpty = false;
			break;
		}
	}
	if(notEmpty){
		$.post('application.php', {	makeUser:true, 'dataArray[]': dataArray},
			function(serverData){
				// alert(serverData.message+"----"+serverData.success);
				showMessage(serverData.message, !serverData.success);
				opener.document.getElementById('user_idm').value = '';
				opener.document.getElementById('user_passwd').value = '';
			},
			'json')
			.error(
				function(error) {
					showMessage("Error: " + error.responseText, true);
				}
			)
		;
	}
	else{
		showMessage("Please fill all fields", true);
	}
}

window.onload = function()
{
	setValues();
}
</script>

<?php
// require_once(".htconnect.php");
// require_once("__dbHelp.php");
// require_once("errorHandler.php");
// $error = new errorHandler;
importJs();

// echo " <input type=text name='asd' id='asd' value='asdqwe'/> ";

$newUser = isset($_GET['makeUser']);
$action = "makeNewUser();";
echo "<form method=post name='application' id='application' style='width: 600px;'>";
	echo "<table>";
		echo "<tr>";
			echo "<td colspan=2><font size=5px>Personal information</font></td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td colspan=2><font size=2px>All fields are mandatory</font></td>";
		echo "</tr>";

		echo "<tr><td colspan=2><br></td></tr>";
		echo "<tr>";
			echo "<td width=100px>First name</td>";
			echo "<td><input type=text name='First name' id='First name'></td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>Last name</td>";
			echo "<td><input type=text name='Last name' id='Last name'></td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>Department</td>";
			echo "<td>";
				echo "<select name='Department' id='Department' onchange=\"javascript:getValue(this.id,'Institute');\">";
				echo "<option value='0'>--- Select / Other ---</option>";
				$sql = "SELECT department_id, department_name FROM department ORDER BY department_name";
				$res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
				while($row = dbHelp::fetchRowByIndex($res)){
					echo "<option value='".$row[0]."'>".$row[1]."</option>";
				}
				echo "</select>";
				if($newUser){
					$extra = "type='hidden'";
				}
				else{
					$extra = "type='text'";
					echo " Other ";
				}
				echo "<input ".$extra." name='GEDepartment' id='GEDepartment' value='' />";
			echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>Institute</td>";
			echo "<td><input type=text name=Institute id=Institute size=35></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>Work phone</td>";
			echo "<td><input type=text name=Phone id='Phone'></td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>Phone extension</td>";
			echo "<td><input type=text name='Phone extension' id='Phone extension'></td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>Mobile</td>";
			echo "<td><input type=text name='Mobile' id='Mobile'></td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>Email</td>";
			echo "<td><input type=text name='Email' id='Email'></td>";
		echo "</tr>";
	echo "</table>";

	echo "<br>";
	if(!$newUser){
		$action = "validate_form();";
		echo "<table><tr><td colspan=2><font size=5px>Select the resource you want to use</font></td></tr>";
			echo "<tr>";
				echo "<td colspan=2><font size=2px>If you don't know how to use the equipment ask for assistance</font></td>";
			echo "</tr>";

			echo "<tr>";
				echo "<td colspan=2><br></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td width=100px>Resource Type</td>";
				echo "<td>";
					echo "<select name=Type id=Type onChange=\"ajaxEquiDD(this,'Resource')\">";
					// $sql = "SELECT resourcetype_id, resourcetype_name FROM resourcetype";
					$sql = "SELECT distinct resourcetype_id, resourcetype_name FROM resource, resourcetype where resource_type = resourcetype_id AND resource_status not in (0, 2) order by resourcetype_name";
					echo "<option id=0>Select Resource...</option>";
					$res = dbHelp::query($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
					while($row = dbHelp::fetchRowByIndex($res)){
						echo "<option value='".$row[0]."'>".$row[1]."</option>";
					}
					echo "</select>";
				echo "</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>Resource</td>";
				echo "<td><select name=Resource id=Resource></select></td>";
			echo "</tr>";
		echo "</table>";

		echo "<br>";
		// echo "<table border=0>";
			// echo "<tr><td><input type=button value=Submit onclick=\"javascript:validate_form();\"></td></tr>";
		// echo "</table>";
	}

	echo "<table border=0>";
		echo "<tr><td><input type=button value=Submit onclick='".$action."'></td></tr>";
	echo "</table>";
echo "</form>";

?>