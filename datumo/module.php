<?php 


class module{
	private $pdo;
	
	public function __construct($objName){
		$this->pdo=new dbConnection();
		//check if there are any modules available for this table
		$sql="SELECT module_name, module_desc FROM module WHERE module_table='$objName'";
		//loop through all results ... if any
		foreach ($this->pdo->query($sql) as $row){
			echo "<td>";
			echo "<input type=button name=$row[0] id=$row[0] value='$row[1]'>";
			//call function stored in the database
			if(method_exists($this, $row[0])){ //this function exists
				//create div to hold module contecnt
				echo "<div id='div_$row[0]' class=regular>";
				//call module function
				$func=$row[0];
				$this->$func(); //call method/function through a variable
			} else {	//this function does not exist
				//do nothing
				//when exists it is suppose to call a javascript
			}
			echo "</div>";
			echo "</td>";
		}
	}
	
/**
 * 
 * Module for admin table
 * Show every available table in the database
 */
	
	
	public function tables(){
		//set search path to information schema
		$this->pdo->dbInfo();
		$sql="SELECT table_name, table_comment FROM tables WHERE table_schema='".$this->pdo->getDatabase()."'";
		echo "<table>";
		$i=0; //initialize counter
		foreach ($this->pdo->query($sql) as $row){ //loop through all tables
			$i++; //increment counter to handle row display 
			//initialize row
			//change schema 
			$this->pdo->dbConn();
			
			$query = "SELECT mask_name FROM mask WHERE mask_table='$row[0]'";
			$sql = $this->pdo->query($query);
			$r = $sql->fetch();
			
			if($i==1)	echo "<tr>";
			echo "<td width=250px><label title='$row[1]'>$row[0] - <b>$r[0]</b></label></td>";
			//end row
			if($i==4){	echo "</tr>";$i=0;}
			$this->pdo->dbInfo();
		}
		echo "</table>";
	}

	
}







?>