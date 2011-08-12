<?php
require_once("session.php");
$user_id = startSession();

if(isset($_GET['type'])){
	require_once "__dbConnect.php";
	require_once "mailClass.php";
	$mail = new mailClass();
	//$refMail = "bugs@cirklo.org"; //where the mails go to (only bug reports)
	$name = $_POST['name'];
	$email = $_POST['email'];
	$target=$_POST['target'];
	$subject=$_POST['subject'];
	$message = nl2br($_POST['message']);
	//get todays date
	$todayis = date("l, F j, Y, g:i a") ;
	//set a title for the message
	$subject = "[datumo] $subject";
	$body = "From $name, \n\n";
	$body.=strip_tags($message);
	//put your email address here
	$str=$mail->sendMail($subject, $target, $email, $body);
	echo $str;
	exit;
}

?>

<!doctype html>  
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>

<!-- BEGIN Meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title>Datumo helpdesk</title>

<!-- BEGIN Navigation bar CSS - This is where the magic happens -->
<link rel="stylesheet" href="css/main.css">
<link rel="stylesheet" href="css/jquery.jnotify.css">
<!-- END Navigation bar CSS -->

<!-- BEGIN JavaScript -->
<script type="text/javascript" src="js/jquery-1.5.1.js"></script>
<script type="text/javascript" src="js/jquery.jnotify.js"></script>
<script type="text/javascript">

function helpdesk(){
	cform="cform";
	//call contact form
	var CurForm=eval("document."+cform);
	var contactType=$("#contactType").val();
	if(contactType==0){
		$.jnotify("Choose a contact type");
		return;
	}
	for(var i=0;i<CurForm.length;i++){
		if(CurForm[i].value==""){
			CurForm[i].focus();
			$.jnotify("Missing fields");
			return;
		}
	}
	var url = "helpdesk.php?type";
	//ajax request with post variables (NICE)
	$.post(url,{name:$('#name').val(),
		  target:$("#contactType").val(),
		  email:$('#email').val(),
		  message:$('#message').val(),
		  subject:$('#subject').val()},
			
	//retrieve that from ajax request
	function(data){
		$.jnotify(data);
	});
	//call method to clean form
	cleanForm(cform);
};

function cleanForm(form){
	$("form[name="+form+"]").find(":input").each(function(){
		switch(this.type){
			case "text":
			case "textarea":
				$(this).val("");
				break;
		}	
	});
}


</script>
<!-- END Javascript -->
<body>
<?php 

$contact = "Do you want to report a bug? Please submit the form.";
echo "<form name='cform' method='post'>";
echo "<p><b>$contact</b></p>";
echo "<p><select name=contactType id=contactType>";
echo "<option value=0 selected>Contact us...</option>";
echo "<option value=bugs@cirklo.org>Bugs</option>";
echo "<option value=support@cirklo.org>Support</option>";
echo "<option value=info@cirklo.org>Informations</option>";
echo "</select></p>";
echo "<p><label for='subject'>Subject</label><br>";
echo "<input id='subject' type='text' value='' name='subject' class='name' size=40></p>";
echo "<p><label for='name'>Name</label><br>";
echo "<input id='name' type='text' value='' name='name' class='name' size=30></p>";
echo "<p><label for='e-mail'>E-mail</label><br>";
echo "<input id='email' type='text' value='' name='email' class='email' size=30></p>";
echo "<p><label for='message'>Message</label><br>";
echo "<textarea id='message' rows='5' cols='40' name='message' class='message'></textarea></p>";
echo "<input type='button' id=sendMail name='sendMail' value='Submit Form' onclick=helpdesk()>";
echo "</form>";


?>
</body>
</html>