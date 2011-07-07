<?php

class searchClass{
	private $pdo;
	private $admin;
	
	public function __construct(){
		$this->pdo = new dbConnection();
		$this->admin = new restrictClass();
	}
	
	public function qsearchFind($objName){
		$sql = $this->pdo->prepare("SELECT 1 FROM ".$this->pdo->getDatabase().".search WHERE search_table='$objName'");
		$sql->execute();
		if($sql->rowCount() == 0){ 
			return FALSE; //no quick search tool for this table
		} else {
			return TRUE;
		}
	}
	
	public function advancedFilter($user_id, $objName){
		$tables = $this->admin->tableAccess($user_id);
		echo "<table border=0>";
		echo "<tr><td>Select table to query</td><td>";
		echo "<select name=table id=table onChange=ajaxEquiDD(this,'field')>";
		echo "<option id=Table>-----------------</option>";
		if($objName!=''){ //comes from manager.php -> restrict options to the current table
			echo "<option id=$objName>$objName</option>";
		} else { //comes from admin.php
			for($i=0;$i<sizeof($tables);$i++){
				echo "<option id=$tables[$i]>$tables[$i]</option>";
			}
		}
		echo "</select>";
		echo "</tr>";
		echo "</table>";
		echo "<form name=advFilter method=post>";
		echo "<table>";
		echo "<tr>";
    	echo "<td id=clone><a href=javascript:void(0) style='text-decoration:none' class=cloneMe onclick=\"javascript:checknew('sum', this);\" title='clone row'>Add</a></td>";
    	echo "<td id=delete><a href=javascript:void(0) style='text-decoration:none' class=deleteMe onclick=\"javascript:checknew('subtract', this);\" title='cancel row'>Remove</a></td>";
   		echo "<td><select id=field name=field onchange=selOperator(this.id,event) ></select></td>";
   		echo "<td><select id=operator name=operator></select></td>";
   		echo "<td><input type=text id=val name=val></td>";
   		echo "<td><a href=javascript:void(0) class=cloneMe onclick=filterSubmit('advFilter')>Submit</a></td>";
   		echo "</tr>";
	//	echo "<tr><td colspan=6 style='text-align:right'><br><a href=javascript:void(0) onclick=showhide('advsearch')>Close</a></td></tr>";
		echo "</table>";
		echo "</form>";
		echo "<input type='hidden' name='multiple' id='multiple' value=1>";
	}
	
	public function quickSearch($objName){
		//set search path to main database
		$this->pdo->dbConn();
		//query the database
		$sql = $this->pdo->prepare("SELECT search_query FROM ".$this->pdo->getDatabase().".search WHERE search_table='$objName'");
		$sql->execute();
		$row = $sql->fetch();
		return $row[0];
	}
}

?>