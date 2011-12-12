<?php

// require_once(".htconnect.php");
// require_once("__dbHelp.php");
require_once("commonCode.php");
require_once("errorHandler.php");

$error = new errorHandler;

if(isset($_GET['val'])){ //new user form -> ajax response
    $id = $_GET['val'];    
    if($id != 0){
        // $sql = "SELECT institute_name FROM institute, department WHERE institute_id = department_inst AND department_id = ".$id;
        // $res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
        $sql = "SELECT institute_name FROM institute, department WHERE institute_id = department_inst AND department_id = :0";
        $res = dbHelp::query($sql, array($id)) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
        $row = dbHelp::fetchRowByIndex($res);
        echo $row[0];
    } 
	// else {
        //do nothing
    // }
}

if(isset($_GET['user'])){
    $login = $_GET['user'];
    $login = strtolower(strtok($login,"@"));
    $firstname = strtolower($_GET['fn']);
    $lastname = strtolower($_GET['ln']);
    // $sql = "SELECT * from ".dbHelp::getSchemaName().".user WHERE lower(user_firstname)=lower('".$firstname."') AND lower(user_lastname)=lower('".$lastname."') AND lower(user_login)=lower('".$login."')";
    // $res = dbHelp::query($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    $sql = "SELECT * from ".dbHelp::getSchemaName().".user WHERE lower(user_firstname)= :0 AND lower(user_lastname)= :1 AND lower(user_login)= :2";
    $res = dbHelp::query($sql, array($firstname, $lastname, $login)) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    $nrows = dbHelp::numberOfRows($res);
    if($nrows == 0){ //not yet registered
        echo "OK";
    }
	// else { //already registered
    // }
}

?>