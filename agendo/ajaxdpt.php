<?php

// require_once(".htconnect.php");
// require_once("__dbHelp.php");
require_once("commonCode.php");
require_once("errorHandler.php");

$error = new errorHandler;

if(isset($_GET['val'])){ //new user form -> ajax response
    $id = $_GET['val'];    
    if($id != 0){
        $sql = "SELECT institute_name FROM institute, department WHERE institute_id = department_inst AND department_id = $id";
        $res = dbHelp::mysql_query2($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
        $row = dbHelp::mysql_fetch_row2($res);
        echo $row[0];
    } else {
        //do nothing
    }
}

if(isset($_GET['user'])){
    $login = $_GET['user'];
    $login = strtok($login,"@");
    $firstname = $_GET['fn'];
    $lastname = $_GET['ln'];
    $sql = "SELECT * from ".dbHelp::getSchemaName().".user WHERE lower(user_firstname)=lower('$firstname') AND lower(user_lastname)=lower('$lastname') AND lower(user_login)=lower('$login')";
    $res = dbHelp::mysql_query2($sql) or die ($sql); //$error->sqlError(mysql_error(), mysql_errno(), $sql, '', ''));
    $nrows = dbHelp::mysql_numrows2($res);
    if($nrows == 0){ //not yet registered
        echo "OK";
    } else { //already registered
    }
}

?>