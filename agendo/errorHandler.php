<?php

require_once("alert/class.phpmailer.php");

/**
  * @author João Lagarto
  * @copyright 2010 João Lagarto
  * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
  * @version 1.0
  * @abstract Class to handle mysql errors
 */

//generate warning (alert) when the query could not be completed
//save query and error number and type
//send this information to the software administrator by email
//return to the main page without making any changes in the database

//generate warning (send to javascript) and return to the main page

class errorHandler extends PHPMailer{
    
    function __construct() {
    
        // $sql = "SELECT mainconfig_host, mainconfig_port, mainconfig_password, mainconfig_email, mainconfig_smtpsecure, mainconfig_smtpauth FROM mainconfig WHERE mainconfig_id = 1";
        // $res = dbHelp::query($sql) or die ($sql);
        // $row = dbHelp::fetchRowByIndex($res);
		$sql = "SELECT configParams_name, configParams_value from configParams where configParams_name='host' or configParams_name='port' or configParams_name='password' or configParams_name='email' or configParams_name='smtpsecure' or configParams_name='smtpauth'";
		$res = dbHelp::query($sql);
		$configArray = array();
		for($i=0;$arr=dbHelp::fetchRowByIndex($res);$i++){
			$configArray[$arr[0]] = $arr[1];
		}
		$this->SMTPAuth   = $configArray['smtpauth'];
		$this->SMTPSecure = $configArray['smtpsecure'];
		$this->Port       = $configArray['port'];
		$this->Host       = $configArray['host'];
		$this->Username   = $configArray['email'];
		$this->Password   = $configArray['password'];
		$this->IsSMTP(); // telling the class to use SMTP
		$this->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
		$this->SetFrom($configArray['email'], "Calendar administration");
		$this->AddReplyTo($configArray['email'],"Calendar administration");   
		// for($i=0; $arr = dbHelp::fetchRowByIndex($res); $i++){
			// $row[$i] = $arr[1];
		// }
        // $this->IsSMTP(); // telling the class to use SMTP
        // $this->SMTPDebug  = 1;           // enables SMTP debug information (for testing)
        // $this->Host       = $row[0];     // sets GMAIL as the SMTP server
        // $this->Port       = $row[1];     // set the SMTP port for the GMAIL server   
        // $this->Password   = $row[2];     // GMAIL password
        // $this->Username   = $row[3]; 	 // GMAIL username
        // $this->SMTPSecure = $row[4];     // sets the prefix to the servier
        // $this->SMTPAuth   = $row[5];     // enable SMTP authentication
        // $this->SetFrom($row[3], "Calendar Admin");
        // $this->AddReplyTo($row[3],"Calendar Admin");
   }
    
 /*
	function sqlError($type, $num, $query, $table, $user_id){
        // echo $query."-".$type;
        //Which user has generated the error?
        $login = $this->getUser($user_id);
        $usertype = $this->getAdmin($user_id);
        $alert = $this->error($num);
        
        //Is the user an administrator?
        if($table != ''){
            if($usertype != 0) { 
                $url = "manager.php?table=$table&nrows=20&order=ASC";
            } else { //Regular user
                $url = "index.php";
            }
        } else {
            $url = "calendar.igc.gulbenkian.pt";
        }
        //build email message (body) --> send mail to the administrator reporting the error number, type and the sql query
        $date = date('F j, Y, g:i a');
        $msg = "Error occurred on $date\n\n";
        $msg .= "Number: $num\n";
        $msg .= "Description: $type\n";
        $msg .= "Query: $query\n";
        $msg .= "User: ".$login;
        
        $this->Subject = "Calendar administration error";
        $this->Body = $msg;
        $this->AddAddress('jlagarto@igc.gulbenkian.pt', "");

        //send mail to the administrator reporting the error number, type and the sql query
        if($alert == ''){
            if(!$this->Send()) {
                //mail error
                $alert .= "Critical error! Please contact the administration for further details.";
            } else {
                //mail OK
                $alert .= "Critical error! Click OK to return to the administration area!";
            }      
        }
        
        //warning script display
        echo "<script type='text/javascript'>";
        echo "alert('$alert');";
        echo "location='$url';";
        echo "</script>";
    }
*/    

    function error($num){
        switch($num){
            case '1451':
                $alert = "Invalid operation! The records you are trying to delete are being used.";
                break;
            case '1452':
                $alert = "Invalid operation! Wrong data!";
                break;
            case '1062':
                $alert = "Invalid operation! The record you are trying to add already exists.";
                break;
            default:
                $alert = "";
        }
        
        return $alert;
    }
    
    function getUser($id){ //get user login
        $sql = "SELECT user_login from ".dbHelp::getSchemaName().".user WHERE user_id =".$id;
        $res = dbHelp::query($sql);
        $row = dbHelp::fetchRowByIndex($res);
        return $row[0];
    }
    
    function getAdmin($id){ //Search for any administration entry // Doesnt give problems in postgrsql
        $sql = "SELECT admin_table FROM admin WHERE admin_user = '".$id."' GROUP BY admin_table ORDER BY admin_table ASC";
        $result = dbHelp::query($sql);
        $num_rows = dbHelp::numberOfRows($result);
        return $num_rows;
    }
}

 /* MYSQL ERRORS
    Error number - description
    1451 - On delete: Cannot delete because PK is already being used as FK 
    1062 - On insert: duplicated entries (unique columns)
    1452 - On insert/update: cannot proceed with the action because FK constraint fails
    1054 - Unknown Column - Query error (CRITICAL ERROR)
*/

?>