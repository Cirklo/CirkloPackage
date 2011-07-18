<?php

/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract Class handle messages between user -> *** New Feature ***
 */

class msgClass{
	private $pdo;
	private $query;
	
	public function __construct(){
		$this->pdo = new dbConnection();
		$this->query = new queryClass();
	}
	
/**
* @author João Lagarto / Nuno Moreno
* @abstract method to create new message Form
*/
	
	public function genMsgForm($user_id){
		//set search path to main database
		$this->pdo->dbConn();
		echo "<form name=msgForm>";
		echo "<table>";
		echo "<tr><td>To:</td>";
		echo "<td><select name=touser id=touser>";
		echo "<option id=0>---- All users ----</option>";
		$sql=$this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".user WHERE user_id<>$user_id");
		$sql->execute();
		for($i=0;$row=$sql->fetch();$i++){
			echo "<option id=$row[0]>$row[1]</option>";
		}
		echo "</select>";
		echo "</td></tr>";
		echo "<tr><td>Subject:</td><td><input type=text id=subject name=subject class=reg maxlength=20></td></tr>";
		echo "<tr><td colspan=2>Message (max. 200 chars)&nbsp;&nbsp;&nbsp;&nbsp;<input type=text class=reg id=noChars size=1></td></tr>";
		echo "<tr><td colspan=2><textarea id=msgArea name=msgArea class=reg rows=6 cols=21 onkeyup=countchars(this.id)></textarea></td></tr>";
		echo "<tr><td colspan=2><input type=button onclick=cleanAll('msgForm') value='Clean all'><input type=button value='Send Message' onclick=sendMsg($user_id)></td></tr>";
		echo "<tr><td style='text-align:right' colspan=2><a href=javascript:void(0) onclick=showhide('msgArea')>Close</a></td></tr>";
		echo "</table>";
		echo "</form>";
	}
	
	/**
* @author João Lagarto / Nuno Moreno
* @abstract method to display the messages received by this user
*/
	
	public function readMsgForm($user_id){
		//set search path to main database
		$this->pdo->dbConn();
		//set the value for the number of messages to be displayed
		if(isset($_GET['nmsg'])) $nmsg = $_GET['nmsg'];
		else $nmsg = 10;
		echo "<table cellspacing='8px'>";
		//construct array for input parameters.
		$array = array($this->pdo->getDatabase(),$user_id,$nmsg,''); //table and database
		for($i = 0;$i<sizeof($array);$i++){
			$this->query->__set($i, $array[$i]);	
		}
		//select engine (mysql or pgsql)
		$this->query->engineHandler($this->pdo->getEngine());
		//query number 6 -> necessary in order to select specific query from vault
		$sql = $this->pdo->prepare($this->query->getSQL(7)); 
		$sql->execute();
		//$sql = $this->pdo->prepare("SELECT user_login, message_title, message_text, date_trunc('second',message_date) FROM ".$this->pdo->getDatabase().".message, ".$this->pdo->getDatabase().".user WHERE user_id=message_from AND (message_to=$user_id OR message_to=message_from) ORDER BY message_date DESC LIMIT $nmsg");
		$sql->execute();
		if($sql->rowCount()>0){
			//array to display the headers
			$arr=array("From","Subject","Text","Date");
			echo "<tr><td colspan=2><b>Messages received</b></td></tr>";
			echo "<tr>";
			for($i=0;$i<sizeof($arr);$i++){
				echo "<td><b>$arr[$i]</b></td>";
			}
			echo "</tr>";
			for($i=0;$row=$sql->fetch();$i++){
				echo "<tr valign=top>";
				echo "<td>$row[0]</td><td>$row[1]</td><td width=200px>$row[2]</td><td>$row[3]</td>";
				echo "</tr>";
			}
			echo "<tr><td colspan=3>No. of messages <input type=text id=nmsg name=nmsg title='Number of messages to be displayed' size=1 value=$nmsg><input type=button title='Refresh page' value='Refresh' onclick=refresh()></a></td><td style='text-align:right'><a href=javascript:void(0) onclick=showhide('readMsg')>Close</a></td></tr>";	
		} else {
			echo "<tr><td><b>No messages received. I guess no one likes you :)</b></td></tr>";
		}
		echo "</table>";
		
		
		
		
		
		
	}
}
?>