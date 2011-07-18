<?php

/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract Class to handle reports
 */

require_once ("errorClass.php");

class reportClass{
	private $pdo;
	private $admin;
	private $mainQuery;
	private $arr=array();
	private $fk=array();
	private $numrows;
	private $params=array();
	private $datatypes=array();
	private $refs=array();
	private $reportName;
	private $reportDesc;
	
	public function __construct(){
    	$this->pdo = new dbConnection();
    	$this->perm = new restrictClass();
   		$this->query = new queryClass();
   		$this->error = new errorClass(); 	
	}
    
	public function setQuery($arg){ $this->mainQuery = $arg;}
	
	public function getNumrows(){ return $this->numrows;}
	public function getQuery(){	return $this->mainQuery;}
	public function getReportName(){	return $this->reportName;}
	public function getReportDesc(){	return $this->reportDesc;}
	
	public function testQuery(){
		//set search path to main database
		$this->pdo->dbConn();
		$sql=$this->pdo->prepare($this->mainQuery);
		try{
			$sql->execute();
		} catch (Exception $e){
			$this->error->errorDisplay($this->mainQuery,$objName,$e->getMessage(),"Could not execute query. <b>If the problem persists please contact the administrator!</b> <a href=javascript:window.close()>Return to main menu</a>");
			
			/*echo "<script type='text/javascript'>";
			echo "alert('Could not execute query! Please view the help file to build an appropriate query');";
			echo "window.close();";
			echo "</script>";*/
		}
	}
	
/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @abstract method to display the list of available tables
 */
	
   	public function reportCreator($user_id){
   		//set search path to main database
   		$this->pdo->dbConn();
   		$tables = $this->perm->tableAccess($user_id);
		echo "<table border=0>";
		echo "<tr><td colspan=2>Select tables (max. 3)</td></tr>";
		for($i=0;$i<sizeof($tables);$i++){
			echo "<tr>";
			echo "<td><input type=checkbox id=$tables[$i] name=$tables[$i] class=tables></td>";
			echo "<td> $tables[$i]</td>";
			echo "</tr>";
		}
		echo "</table>";
   		
   	}
   	
   /**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @abstract method to display the table header
 */
   	
   	public function displayHeader($fields){
   		$this->arr = explode(",",$fields);
   		if(strpos($this->arr[0],"DISTINCT")) $this->arr[0] = substr($this->arr[0],10,strlen($this->arr[0])-10);
   		echo "<tr class=headers>";
   		for($i=0;$i<sizeof($this->arr);$i++){
   			$this->arr[$i] = rtrim($this->arr[$i]);
   			echo "<td class=headers>".$this->arr[$i]."</td>";
   		}
   		echo "</tr>";
   	}
   	
/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @abstract method to find referenced tables
 */ 
   	
   	public function FKfield(){
   		//change database to information schema
		$this->pdo->dbInfo();
		for($j=0;$j<sizeof($this->arr);$j++){
			//construct array for input parameters.
			$array = array($this->pdo->getDatabase(),$this->arr[$j],'',''); //table and database
			for($i = 0;$i<sizeof($array);$i++){
				$this->query->__set($i, $array[$i]);	
			}
			//select engine (mysql or pgsql)
			$this->query->engineHandler($this->pdo->getEngine());
			//query number 6 -> necessary in order to select specific query from vault
			$sql = $this->pdo->prepare($this->query->getSQL(6)); 
			try{
				$sql->execute();
				$row = $sql->fetch();
				$this->fk[$this->arr[$j]] = $row[0];			
			} catch(Exception $e){
				//report error
				$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
			}
		} 		
   	}
   	
   	
/**
 * @author João Lagarto / Nuno Moreno
 * @copyright João Lagarto 2010
 * @abstract method to display the results
 */  	
	
   	public function displayResults(){
   		$this->FKfield();
   		//set search path to main database
   		$this->pdo->dbConn();
   		$sql=$this->pdo->prepare($this->mainQuery);
   		//echo $sql->queryString;
   		try{
   			$sql->execute();
   		} catch (Exception $e){
   			$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
   			//echo "Could not execute query!";
   		}
   		for($j=0;$row=$sql->fetch();$j++){
	   		echo "<tr>";
   			for($i=0;$i<sizeof($this->arr);$i++){
	   			echo "<td style='padding-left:5px'>";
	   			$len = strlen($this->fk[$this->arr[$i]]);
	   			if($this->fk[$this->arr[$i]]!="" and $this->fk[$this->arr[$i]]!=substr($this->arr[$i],0,$len)){ //is it a foreign key
	   				echo $this->FKvalue($this->fk[$this->arr[$i]], $row[$i]);
	   			} else {
	   				echo $row[$i];
	   			}
	   			
	   			echo "</td>";
	   		}
	   		echo "</tr>";
   		}
   		   		
   	}
   	
   		/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract method to get the total number of rows
 * 
  */

   	public function maxRows($query){
   		//set search path to main database
   		$this->pdo->dbConn();
   		$sql = $this->pdo->prepare($query);
   		try {
   			$sql->execute();
   		} catch (Exception $e){
   			//echo "Could not execute query.";
   			$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
   		}
   		return $sql->rowCount();
   	}
   	
	/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract gets the second attribute in a table from the referenced id
 */
	
	public function FKvalue($objName, $value){
		//set path to main database
		$this->pdo->dbConn();
		//query for second attribute of the referenced table
		$sql = $this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".$objName WHERE ".$objName."_id='$value'");
		$sql->execute();
		$row = $sql->fetch();
		return $row[1];
	}
	
	public function loadReports($user_id, $target){
		//set search path to main database
		$this->pdo->dbConn();
		echo "<table>";
		echo "<tr><td><b>List of available reports</b></td></tr>";
		$sql = $this->pdo->prepare("SELECT report_id, report_name, report_description FROM ".$this->pdo->getDatabase().".report WHERE report_id NOT IN (SELECT param_report FROM param) AND report_conf=1 OR (report_user=$user_id AND report_conf=2) ORDER BY report_name");
		$sql->execute();
		for($i=0;$row=$sql->fetch();$i++){
			echo "<tr><td><a href=javascript:void(0) onclick=window.open('".$this->pdo->getFolder()."/report.php?report=$row[0]','_blank','height=550px,width=720px,scrollbars=yes'); title='$row[2]'>".$row[1]."</a></td></tr>";
		}
		echo "</table>";
	}
	
	public function loadQuery($report_id){
		//set search path to main database
		$this->pdo->dbConn();
		$sql = $this->pdo->prepare("SELECT report_query FROM ".$this->pdo->getDatabase().".report WHERE report_id=$report_id");
		$sql->execute();
		try{
			$row=$sql->fetch();
			return $row[0];
		} catch (Exception $e){
			$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
		}		
	}
	
	
		/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract method to display dynamic reports
 */
	
	public function dynamicReports($user_id){
		//set search path to main database
		$this->pdo->dbConn();
		$sql = $this->pdo->prepare("SELECT report_id, report_name, report_description FROM ".$this->pdo->getDatabase().".report WHERE report_id IN (SELECT param_report FROM param) AND ( report_conf=1 OR (report_conf=2 AND report_user=$user_id)) ORDER BY report_name");
		$sql->execute();
		if($sql->rowCount()>0){
			for($i=0;$row=$sql->fetch();$i++){
				echo "<form method=post name=report$i class=reportsForm>";
				echo "<table border=0 width=250px>";
				echo "<tr><td colspan=2><b>$row[1]</b></td></tr>";
				echo "<tr><td colspan=2><hr></td></tr>";
				echo "<tr><td colspan=2>$row[2]</td></tr>";			
				echo "<tr><td colspan=2><hr></td></tr>";
				$this->findParam($row[0]);
				for($j=0;$j<sizeof($this->params);$j++){
					echo "<tr><td>".$this->params[$j]."</td>";
					echo "<td style='text-align:right'><input type=text id='".$this->refs[$this->params[$j]]."_id' name='".$this->refs[$this->params[$j]]."_id' ";
					if($this->datatypes[$this->params[$j]]=="date"){
						echo " onfocus=showCalendarControl(this) readonly=readonly ";
					}
					if($this->refs[$this->params[$j]]!=""){
						echo " class=fk lang=__fk ";
					}
					else echo " class=reg ";
					echo "></td></tr>";
				}
				echo "<tr><td colspan=2 style='text-align:right'><input type=button value=Execute onclick=dynReport('report$i','$row[0]')></td></tr>";
				echo "</table>";	
				echo "</form>";
			}
		} else echo "<table><tr><td>No reports available</td></tr></table>";
	}

	
	
/**
 * @author João Lagarto	/ Nuno Moreno
 * @abstract method find parameters for the requested report
 */
	
	public function findParam($report_id){
		//search for input parameters related with this report
		$sql = $this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".param WHERE param_report=$report_id");
		try{
			$sql->execute();
			unset($this->params);
			unset($this->datatypes);
			unset($this->refs);
			for($i=0;$row=$sql->fetch();$i++){
				$this->params[] = $row['param_name'];
				$this->datatypes[$row['param_name']] = $row['param_datatype'];
				$this->refs[$row['param_name']] = $row['param_reference'];
			}
		} catch (Exception $e){
			$this->error->errorDisplay($sql->queryString,$objName,$e->getMessage());
		}
		
	}
	
	public function reportInfo($report_id){
		$query="SELECT report_name, report_description FROM report WHERE report_id=$report_id";
		$sql=$this->pdo->query($query);
		$row=$sql->fetch();
		$this->reportName=$row[0];
		$this->reportDesc=$row[1];
		
		
	}
}

	

?>