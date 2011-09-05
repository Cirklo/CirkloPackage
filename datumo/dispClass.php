<?php 


/**
 * @author João Lagarto
 * @abstract Class to handle table displays (results and insert forms)
 */

//include list to avoid code repetition
require_once ("queryClass.php");
require_once ("resClass.php");
require_once ("searchClass.php");
require_once ("reportClass.php");
require_once ("errorClass.php");

class dispClass{	
	private $table_bool=false;
	private $view_bool=false;
	private $pdo;
	private $tableType=array();
	private $tableComment=array();
	private $tableName;
	private $fullheader=array();
	private $header=array();
	private $datatype=array();
	private $null=array();
	private $default=array();
	private $comment=array();
	private $length=array();
	private $FKtable=array();
	private $FKeys=array();
	private $arrMasks=array();
	private $arrMaskPic=array();
	private $query;
	private $vars=array();
	private $mainQuery;
	private $FKid; //variable that stores foreign key id
	private $FKvalue; //variable that stores foreign key second attribute
	private $perm;
	private $search;
	private $report;
	private $msg;
	private $types=array();
	private $error;
	
	public function __construct(){
		$this->pdo = new dbConnection();
		$this->query = new queryClass();
		$this->perm = new restrictClass();
		$this->search = new searchClass();
		$this->report = new reportClass();
		$this->error = new errorClass();
		
	}
	
	public function __set($var, $val){
        $this->vars[$var] = $val;
    } 
    public function __get($var){
        if(isset($this->vars[$var])){
            return $this->vars[$var];
        } else {
            throw new Exception("Property ‘$var’ does not exist");
        }
        
    }
    
    //sets
	function setTableType($arg){ $this->tableType=$arg;}
	function setTableComment($arg){ $this->tableComment=$arg;}
	function setMainQuery($arg){ $this->mainQuery=$arg;}
	function setQuerySession($arg){ $_SESSION['sql'] = $arg;}
	function setFullHeader($arg){	$this->fullheader = $arg;}
	function setHeader($arg){	$this->header = $arg;}
	function setNullable($arg){	$this->null = $arg;}
	function setDatatype($arg){	$this->datatype = $arg;}
	function setComment($arg){	$this->comment = $arg;}
	function setLength($arg){	$this->length = $arg;}
	function setDefault($arg){	$this->default = $arg;}
	function setFKeys($arg){	$this->FKeys = $arg;}
	function setFKtable($arg){	$this->FKtable = $arg;}
	function setMasks($arg){	$this->arrMasks=$arg;}
	
	//gets
	function getArrayTableTypes(){ return $this->types;}
	function getTableType(){ return $this->tableType;}
	function getTableComment(){ return $this->tableComment;}
	function getFullHeader(){ return $this->fullheader;}
	function getHeader(){ return $this->header;}
	function getNullable(){ return $this->null;}
	function getDatatype(){ return $this->datatype;}
	function getComment(){ return $this->comment;}
	function getLength(){ return $this->length;}
	function getFKtable(){ return $this->FKtable;}
	function getFKeys(){ return $this->FKeys;}
	function getMainQuery(){ return $this->mainQuery;}
	function getFKatt(){ return $this->FKvalue;}
	function getDefault(){ return $this->default;}
	function getMasks(){	return $this->arrMasks;}
	function getMaskPic(){	return $this->arrMaskPic;}
	
	
/**
 * @author João Lagarto	
 * @abstract method to display attributes from table $objName
 */
	
	public function fields($objName,$j, $origin, $order, $colOrder,$page){		
		$this->tableHeaders($objName);
		//change database to information schema
		$this->pdo->dbInfo();
		$sql = $this->pdo->prepare("SELECT column_name FROM columns WHERE table_schema='".$this->pdo->getDatabase()."' AND table_name='$objName'");
		echo "<table border=0>";
		echo "<form name=table$j method=post>";
		$sql->execute();
		//print_r($_POST);
		for($i=0;$row=$sql->fetch();$i++){ 
			echo "<tr><td>".$this->header[$i]."</td>";
			echo "<td><input type=text name=$row[0]_f_ id=$row[0]_f_";
			//is it a foreign key?
			if($this->FKtable[$i]!='' and $this->FKtable[$i]!=$objName) {
				if(trim($_POST[$row[0]."_f_"]," ") != null){
					$this->getFKvalue($_POST[$row[0]."_f_"], $i);
					$value=$this->FKvalue;
				} else {
					$value="";
				}
				echo " class=fk lang=__fk "; //set this as a FK input
			} else {
				if($_POST[$row[0]."_f_"] != ''){
					$value = $_POST[$row[0]."_f_"];
				} else {
					$value="";
				}
				echo " class=reg ";
				if($this->datatype[$this->fullheader[$i]]=="date" or $this->datatype[$this->fullheader[$i]]=="datetime")
					echo " onfocus=showCalendarControl(this) readonly=readonly";
			}
			echo " value='$value'>";
			if($this->FKtable[$i]!='' and $this->FKtable[$i]!=$objName) {
				echo "<a href=javascript:void(0) onclick=redirect('".$this->FKtable[$i]."') style='text-decoration:none' title='click to view all the available keys'><img src=pics/alert.png width=16px height=16px border=0></a>"; 
			}
			echo "</td></tr>";
		}
		
		echo "<tr><td></td><td><input type=button name=$objName.$j id=$objName.$j value='Apply filter' onclick=filter('table$j','$objName','$j','$order','$colOrder',1);></td></tr>";
		echo "</form>";
		echo "<tr><td></td><td><input type=button onclick=cleanForm('table$j') value='Clean all fields'></td></tr>";
		echo "<tr><td></td><td>Results per page <input type=text name=nrows$j id=nrows$j value=20 size=1></td></tr>";
		echo "<tr><td colspan=2 align=right><a href=javascript:void(0) onclick=showhide('".$objName."_div');>Close</a></td></tr>";
		echo "</table>";
	}
	
/**
 * @author João Lagarto	
 * @abstract method to handle table description
 */
	
	
	public function tableDescription ($objName){
		//change database to information schema
		$this->pdo->dbInfo();
		//construct array for input parameters.
		$arr = array($objName,$this->pdo->getDatabase(),'',''); //table and database
		for($i = 0;$i<sizeof($arr);$i++){
			$this->query->__set($i, $arr[$i]);	
		}
		//select engine (mysql or pgsql)
		$this->query->engineHandler($this->pdo->getEngine());
		//query number 1 -> necessary in order to select specific query from vault
		$sql = $this->pdo->prepare($this->query->getSQL(1)); 
		try{
			$sql->execute();
		} catch (Exception $e){ 
			//do nothing
		}
		$row = $sql->fetch();
		//set variables
		$this->tableType=$row[0];
		$this->tableComment=$row[1];
		//change to original database
		$this->pdo->dbConn();
	}

/**
 * @author João Lagarto	
 * @abstract method to calculate the number of tables and views that this user is allowed to view
 * @param $tables Array with the allowed tables and views
 */	
	
	public function tableview($tables){
		//set search path to main database
		$this->pdo->dbInfo();
		//initialize array
		$typearr=array();
		$tableDesc=array();
		//loop through all tables and write the result to an empty array
	
		foreach($tables as $objName){
			//echo $objName."<br>";
			//construct array for input parameters.
			$arr = array($objName,$this->pdo->getDatabase(),'',''); //table and database
			for($i = 0;$i<sizeof($arr);$i++){
				$this->query->__set($i, $arr[$i]);	
			}
			//select engine (mysql or pgsql)
			$this->query->engineHandler($this->pdo->getEngine());
			//query number 1 -> necessary in order to select specific query from vault
			$sql = $this->pdo->prepare($this->query->getSQL(1)); 
			try{
				$sql->execute();
			} catch (Exception $e){
				//echo $e->getMessage();
			}
			$row=$sql->fetch();
			$typearr[]=$row[0];	
			$tableDesc[]=$row[1];
			//build table mask array
		}
		$this->types = $typearr;
		return array($typearr, $tableDesc);
	}
	
/**
 * @author João Lagarto	
 * @abstract method to get table headers
 */
	
	public function tableHeaders ($objName){
		//change database to information_schema
		$this->pdo->dbInfo();
		//construct array for input parameters.
		$arr = array($objName,$this->pdo->getDatabase(),'','');
		for($i = 0;$i<sizeof($arr);$i++){
			$this->query->__set($i, $arr[$i]);	
		}
		//select engine (mysql or pgsql)
		$this->query->engineHandler($this->pdo->getEngine());
		$sql = $this->pdo->prepare($this->query->getSQL(2)); 
		$sql->execute();		
		for($i=0;$row = $sql->fetch();$i++){
			$fullheader[]=$row[0];
			$header[]=substr($row[0],strlen($objName)+1,strlen($row[0])-strlen($objName));
//			$null[$row[0]] = $row[1];
			$datatype[$row[0]] = $row[1];
			$comment[$row[0]] = $row[2];
			$length[$row[0]] = $row[3];
			$default[$row[0]] = $row[4];
		}
		//set variables
		$this->setFullHeader($fullheader);
		$this->setHeader($header);
//		$this->setNullable($null);
		$this->setDatatype($datatype);
		$this->setComment($comment);
		$this->setLength($length);
		$this->setDefault($default);
		//find if attribute is foreign key
		$this->findFK();
		
	}
	
	/**
 * @author João Lagarto	
 * @abstract method that searches for FK and referenced tables
 */
	
	private function findFK(){
		for($i=0;$i<sizeof($this->fullheader);$i++){
			//construct array for input parameters.
			$arr = array($this->fullheader[$i],$this->pdo->getDatabase(),'','');
			for($j = 0;$j<sizeof($arr);$j++){
				$this->query->__set($j, $arr[$j]);	
			}
			//select engine (mysql or pgsql)
			$this->query->engineHandler($this->pdo->getEngine());
			//query number 1 -> necessary in order to select specific query from vault
			$sql = $this->pdo->prepare($this->query->getSQL(3)); 
			$sql->execute();
			$row = $sql->fetch();
			$FKeys[$this->fullheader[$i]] = $row[0];	
			$FKtable[]=$row[0];
		}
		
		//set variables
		$this->setFKeys($FKeys);//FKeys = $FKeys;
		$this->setFKtable($FKtable);//FKtable=$FKtable;
		//change the database to the original
		$this->pdo->dbConn();
	}
	
	/**
 * @author João Lagarto	
 * @abstract method to build dynamic queries responsible for displaying the results in manager.php
 */
	
	public function queryBuilder($user_id, $objName, $nrows, $filter, $offset, $setOrder, $colOrder, $stype){
		$arr = array();
		//change the database to the original
		$this->pdo->dbConn();
		//initiate query
		$sql = "SELECT * FROM ".$this->pdo->getDatabase().".$objName";
		$where = " WHERE ";
		//is the ordering column a foreign key?
		if ($this->FKeys[$colOrder]) {
			$arr = $this->queryFK($objName, $this->FKeys[$colOrder], $colOrder);
			$sql = $arr[0];
			$where .= $arr[1];
			$colOrder = $arr[2];
		}
		if($colOrder!="" and $setOrder!="")
			$order = " ORDER BY $colOrder $setOrder"; //set order to display the results
		$limit = " LIMIT $nrows OFFSET $offset"; //set limits for pagination
		//Was it called by advanced filter??
		if(!$filter){
			foreach($this->vars as $key=>$value){
				$value=trim($value," ");	//hack for Google Chrome
				//it comes from filter
				if(substr($key,strlen($key)-3,strlen($key))=="_f_")
					$key=substr($key,0,strlen($key)-3);
				switch ($this->datatype[$key]){//search for attribute type
					case "varchar": //mysql string
						$op = " regexp ";
						break;
					case "character varying": //pgsql string
						$op = " ~* ";
						break;
					default: //integer, double, date and datetime
						$op = "=";
				}
				//building the where clause			
				$where .= " LOWER($key)".$op."LOWER('$value') AND ";
			}
		} else {  //from advanced filter
			//unserialize advanced filter array
			$arr=unserialize($_GET['arr']);
			//how many rows does the filter have
			$noRows=sizeof($arr);
			foreach ($arr as $row){
				switch($row[1][value]){
					case 0:
					if($this->pdo->getEngine() == "mysql")	$op .= " regexp ";
					if($this->pdo->getEngine() == "pgsql")	$op .= " ~* ";
					break;
					case 1:
						$op .= "=";
						break;
					case 2:
						$op .= "<";
						break;
					case 3:
						$op .= ">";
						break;
					case 4:
						$op .= "=";
						break;	
				}
				$where.=$row[0][value].$op."'".$row[2][value]."' AND ";
				//clear operator
				unset($op);
			} 
		}
		//check for restraining clauses
		$having = $this->perm->restrictAttribute($user_id, $objName);
		if($having!="")	$where.=$having." AND";
		//remove last 'AND ' from query
		if(sizeof($this->vars)!=0 or sizeof($arr)!=0 or $having!="") $where = substr($where,0,strlen($where)-4);
		else $where = "";
		$this->mainQuery = $sql.$where.$order.$limit;
		$this->setQuerySession($sql.$where.$order);
		//echo $this->mainQuery;
	}
	
	
	/**
 * @author João Lagarto	
 * @abstract method to order dynamic queries by a foreign key attribute
 *
 **/
	
	public function queryFK($objName, $FKobjName, $colOrder){
		//set search path to information schema
		$this->pdo->dbInfo();
		$arr = array();
		$sql = "SELECT ";
		foreach($this->fullheader as $value){
			$sql .= " $value,";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= " FROM ".$this->pdo->getDatabase().".$objName,".$this->pdo->getDatabase().".$FKobjName";
		$where = " $colOrder=".$FKobjName."_id AND ";
		$order = $this->pdo->prepare("SELECT column_name FROM columns WHERE table_schema='".$this->pdo->getDatabase()."' AND table_name='$FKobjName' AND ordinal_position=2");
		$order->execute();
		$row = $order->fetch();
		$arr[0] = $sql;
		$arr[1] = $where;
		$arr[2] = $row[0];
		//set search path to main database
		$this->pdo->dbConn();
		//print_r($arr);
		//exit();
		return $arr;
	}
	
	/**
 * @author João Lagarto	
 * @abstract Display results in manager.php
 */
	
	
	public function results ($objName, $r){
		//get user id variable stored in the session variable
		$user_id=$_SESSION['user_id'];
		//set path to main database
		$this->pdo->dbConn();
		$sql = $this->pdo->prepare($this->mainQuery);
		//echo $sql->queryString;
		try{
			$sql->execute();
		} catch (Exception $e){
			//$this->error->errorDisplay($this->mainQuery,$objName,$e->getMessage(),"Could not execute query. <b>If the problem persists please contact the administrator! <a href=admin.php>Return to main menu</a></b>");
		}
		$nrows = $sql->rowCount();
		for($i=0;$row=$sql->fetch();$i++){
			//write line
			$highlight_color="#CCC";
			if(is_odd($i))	$background="#DDDDEE";
			else $background="#EEEEFF";
			echo "<tr style='background-color:$background;' onmouseover=$(this).css('background-color','$highlight_color') onmouseout=$(this).css('background-color','$background')>";
			echo "<td  style='text-align:center'>";
			if($r) echo "<a href=javascript:void(0) onclick=copy($i) title='copy row'><img src=pics/copy.png width=16px height=16px border=0></a>";
			echo "</td>";
			//check if this user has permissions to make requisitions
			$this->perm->tablePermissions($objName, $user_id);
			if($this->perm->getRequest()){
				echo "<td class=cart nowrap=nowrap height=20px>";
				echo "<a href=javascript:void(0) style='text-decoration:none;font-size:12px;' class=exp onclick=updQtt('sum',$i)>+</a>&nbsp;&nbsp;";
				echo "<a href=javascript:void(0) style='text-decoration:none;font-size:12px;' class=exp onclick=updQtt('sub',$i)>-</a>&nbsp;&nbsp;";
				echo "<input type=text id=quantity_$i name=quantity_$i value=1 size=1>&nbsp;&nbsp;";
				echo "<a href=javascript:void(0) onclick=\$(document).addToCart({objName:'$objName',row:'$i'})><img src=pics/store.png border=0 width=16px height=16px></a>";
				echo "</td>";
			} else {
				echo "<td></td>";
			}
			//check if there is any permission for this table and user
			//does this user have permissions to update or delete this table? if so apply checkboxes. If not, set fields as readonly
			if($r)  {	echo "<td style='text-align:center'><input type=checkbox id=cb$i name=cb$i)></td>";}
			echo "<form name=tableman$i method=post>";
			for($j=0;$j<$sql->columnCount();$j++){
				$size = $this->writeProperties($j);
				//first cell cannot be changed as it is a primary key
				if($r){
					if($j==0) {$readonly=" readonly ";}
					else {$readonly="";}
				} else {
					$readonly=" readonly ";
				}
				//is it a foreign key?
				if($this->FKtable[$j] and $j!=0){
					//get foreign key values
					$this->getFKvalue($row[$j],$j);
					echo "<td nowrap=nowrap valign=top class=results><input type=text class=fk id=".$this->fullheader[$j]." name=".$this->fullheader[$j]." value='".$this->FKvalue."' lang=__fk onchange=selectRow('$i') $size $readonly>";
					//div that enclosures this FK details
					echo "<a href=javascript:void(0) title='Click for details' onclick=getdetails('details_".$this->fullheader[$j].$i."','".$this->FKtable[$j]."','$row[$j]')><img src=pics/details.gif border=0></a>";
					echo "<div id='details_".$this->fullheader[$j].$i."' class=details>";
					echo "</div>";
					echo "</td>";
				} else { 
					if($this->datatype[$this->fullheader[$j]]=="text")
						echo "<td valign=top class=results><textarea rows=3 cols=40 class=reg id=".$this->fullheader[$j]." name=".$this->fullheader[$j].">".strip_tags($row[$j])."</textarea></td>";
					else
						echo "<td valign=top class=results><input type=text class=reg id=".$this->fullheader[$j]." name=".$this->fullheader[$j]."  value='$row[$j]' onchange=selectRow('$i') $size $readonly";
					//set field to open link in a new window if it starts with http://
					if(substr($row[$j],0,7)=="http://")
						echo " ondblclick=window.open('".$row[$j]."')";
					//set calendar control for date or datetime fields
					if($this->datatype[$this->fullheader[$j]]=="date" or $this->datatype[$this->fullheader[$j]]=="datetime")
						echo " onfocus=showCalendarControl(this) readonly=readonly";
					if($this->datatype[$this->fullheader[$j]]!="text")echo "></td>";
				}
			}
			echo "</form>";
			echo "</tr>";
		}
	}
	
	/**
 * @author João Lagarto	
 * @abstract gets the second attribute in a table from the referenced id
 */
	
	public function getFKvalue($value, $j){
		//set path to main database
		$this->pdo->dbConn();
		//query for second attribute of the referenced table
		$sql = $this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".".$this->FKtable[$j]." WHERE ".$this->FKtable[$j]."_id='$value'");
		//echo $sql->queryString;
		$sql->execute();
		$row = $sql->fetch();
		$this->FKvalue = $row[1];
		$this->FKid = $value;
	}
	
/**
 * @author João Lagarto	
 * @abstract method to display table headers
 * @param boolean variable is a hack to avoid div 'showhide' bug
 */
	
	public function headers($bool,$stype,$table,$nrows,$order,$page){
		for($i=0;$i<sizeof($this->header);$i++){
			echo "<td valign=top class=headers>";
			if($this->FKtable[$i]!="" and $i!=0 and !$bool){ //is this a foreign key?
				//need to define class in order not to trigger somekind of stupid bug related with jquery
				echo "<a class=exp href=javascript:void(0) onclick=window.open('list.php?table=".$this->FKtable[$i]."','_blank','width=350,height=400,scrollbars=yes') style='text-decoration:none' title='click to view all the available keys'><b>".strtoupper($this->header[$i])."</b></a>";
			} else {
				echo "<a class=exp><b>".strtoupper($this->header[$i])."</b></a>";				
//				echo strtoupper($this->header[$i]);
			}
			//display column comments
			if($this->comment[$this->fullheader[$i]]) { //clause to display header comments
				echo "<div lang=exp class=columnComments style='display:none;font-size:9px'>";
				echo $this->comment[$this->fullheader[$i]];
				echo "</div>";
			}
			//display ascending/descending arrows
			if(!$bool){
				echo "<br>";
				echo "<a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'ASC','".$this->fullheader[$i]."',$page) title='Sort by ".$this->header[$i]." ascending order'><img src=pics/asc.gif border=0></a>";
				echo "&nbsp;&nbsp;";
				echo "<a href=javascript:void(0) class=exp onclick=submit('$stype','$table',$nrows,'DESC','".$this->fullheader[$i]."',$page) title='Sort by ".$this->header[$i]." ascending order'><img src=pics/desc.gif border=0></a>";
			}
			echo "</td>";
		}
		
	}
		
/**
 * @author João Lagarto
 * @abstract method to display teh insert form
 */
	
	public function insert($objName,$stype,$nrows,$order){
		//write table headers
//		echo "<table class=main>";
//		echo "<tr class=headers>";
//		echo "<td colspan=3></td>";
//		$this->headers(TRUE, $stype,$objName,$nrows,$order,1); //call method to display table headers
//		echo "</tr>";
		echo "<tr class=headers>";
		echo "<td colspan=".(sizeof($this->header)+3)."><hr style='border:0px'></td>";
		echo "</tr>";
		
		echo "<tr style='background-color:#DDDDEE'>";
		echo "<form method=post name=tableman id=tableman>";
		//path to add or remove multiple insert forms
		echo "<td width=40px><a href=javascript:void(0) style='text-decoration:none' class=cloneMe onclick=checkMultiple('sum',this) title='clone row'><img src=pics/add.png border=0 width=32px height=32px></a></td>";
    	echo "<td width=40px><a href=javascript:void(0) style='text-decoration:none' class=deleteMe onclick=checkMultiple('subtract',this) title='cancel row'><img src=pics/remove.png border=0 width=32px height=32px></a></td>";
		echo "<td><a href=javascript:void(0) style='text-decoration:none' class=cloneMe id=insert name=insert title='insert data'><img src=pics/submit.png border=0 width=32px height=32px></a></td>";
		//insert form to be cloned
		for($i=0;$i<sizeof($this->header);$i++){
			if($i==0) {$readonly=" disabled ";}
			else {$readonly="";}
			$size = $this->writeProperties($i);
			if($this->FKtable[$i] and $i!=0){ //is it a foreign key?
				//get default value for this attribure
				if($this->default[$this->fullheader[$i]]!=null){
					$this->getFKvalue($this->default[$this->fullheader[$i]], $i);
					$val = $this->FKvalue;
				} else $val="";
				echo "<td id=td_$i nowrap=nowrap class=results><input type=text class=fk id=".$this->fullheader[$i]." name=".$this->fullheader[$i]." value='$val' lang=__fk $size></td>";
			} else { // no foreign key
				if($i!=0){
					$val = $this->default[$this->fullheader[$i]];
				}
				if($this->datatype[$this->fullheader[$i]]=="text")
					echo "<td id=td_$i class=results><textarea rows=3 cols=40 class=reg id=".$this->fullheader[$i]." name=".$this->fullheader[$i].">".strip_tags($val)."</textarea></td>";
				else
					echo "<td id=td_$i class=results><input type=text class=reg id=".$this->fullheader[$i]." name=".$this->fullheader[$i]." value='$val' $size $readonly lang='".$this->datatype[$this->fullheader[$i]]."' alt='".$this->null[$this->fullheader[$i]]."'";
				if($this->datatype[$this->fullheader[$i]]=="date" or $this->datatype[$this->fullheader[$i]]=="datetime")
					echo " onfocus=showCalendarControl(this) readonly=readonly";
				if($this->datatype[$this->fullheader[$i]]!="text") echo "></td>";	
				
			}
		}
		echo "</form>";
		echo "</tr>";
		echo "</table>";
		
	}
		
	/**
 * @author João Lagarto	
 * @abstract method to to write field's properties
 */
	
	public function writeProperties($j){
		//$this->fullheader
		switch($this->datatype[$this->fullheader[$j]]){
			case "varchar":
				$size = $this->length[$this->fullheader[$j]];
				$mlength = $this->length[$this->fullheader[$j]];
				break;
			case "character varying":
				$size = $this->length[$this->fullheader[$j]];
				$mlength = $this->length[$this->fullheader[$j]];
				break;
			case "datetime":
				$size=35;
				$mlength=35;
				break;
			case "date":
				$size=17;
				$mlength=17;
				break;
			default:
				if($this->FKtable[$j]){//Is this a foreign key?
					//change to information schema
					$this->pdo->dbInfo();
					$sql=$this->pdo->query("SELECT character_maximum_length FROM columns WHERE table_schema='".$this->pdo->getDatabase()."' AND table_name='".$this->FKtable[$j]."' AND ordinal_position=2");
					$row=$sql->fetch();
					$size=$row[0];
					$mlength=$row[0];
					//change to main schema 
					$this->pdo->dbConn();
					//need to find the second attribute of the referenced table and look for its length
				} else { //regular field (integers, doubles...)
					$size = 10;	
					$mlength = 10;
				}
		}	
		$scale=0.5;
		$size=$size*$scale;
		return " size=$size maxlength=$mlength ";
	}
	
/**
 * @author João Lagarto	
 * @abstract method to calculate the number of results in the queried table
 */	

	
	public function maxRows($objName, $filter, $user_id){
		$where=" WHERE ";
		if(!$filter){
			if($this->vars){
				foreach($this->vars as $key=>$value){
					//it comes from filter
					if(substr($key,strlen($key)-3,strlen($key))=="_f_")
						$key=substr($key,0,strlen($key)-3);
					switch ($this->datatype[$key]){//search for attribute type
						case "varchar": //mysql string
							$op = " regexp ";
							break;
						case "character varying": //pgsql string
							$op = " ~* ";
							break;
						default: //integer, double, date and datetime
							$op = "=";
					}
					//building the where clause			
					$where .= " LOWER($key)".$op."LOWER('$value') AND ";
				}
			}
		} else {
			//unserialize advanced filter array
			$arr=unserialize($_GET['arr']);
			//how many rows does the filter have
			$noRows=sizeof($arr);
			foreach ($arr as $row){
				switch($row[1][value]){
					case 0:
					if($this->pdo->getEngine() == "mysql")	$op .= " regexp ";
					if($this->pdo->getEngine() == "pgsql")	$op .= " ~* ";
					break;
					case 1:
						$op .= "=";
						break;
					case 2:
						$op .= "<";
						break;
					case 3:
						$op .= ">";
						break;
					case 4:
						$op .= "=";
						break;	
				}
				$where.=$row[0][value].$op."'".$row[2][value]."' AND ";
				//clear operator
				unset($op);
			} 	
		}
		
		//check for restraining clauses
		$having = $this->perm->restrictAttribute($user_id, $objName);
		if($having!="")	$where.=$having." AND";
		//remove last 'AND ' from query
		if($where!="" and $where!=" WHERE ") $where = substr($where,0,strlen($where)-4);
		else $where = "";
		//set search path to main database
		$sql = $this->pdo->prepare("SELECT COUNT(".$objName."_id) FROM ".$this->pdo->getDatabase().".$objName $where");
		//echo $sql->queryString;
		try{
			$sql->execute();
		} catch (Exception $e){
			//echo $this->mainQuery;
			//Display error in the screen
			//$this->error->errorDisplay($this->mainQuery,$objName,$e->getMessage(),"Could not execute query. <b>If the problem persists please contact the administrator! <a href=admin.php>Return to main menu</a></b>");
		}
		$row = $sql->fetch();
		return $row[0];
	}
	
	/**
 * @author João Lagarto	
 * @abstract method to construct the quick search query
 */	
	
	public function qSearchQueryBuilder($objName, $nrows){
		//get posted variable (search string)
		$var = $_POST['qsearch'.$objName];
		//get search query from the database
		$sql=$this->search->quickSearch($objName);
		$this->mainQuery = "$sql '".strtolower($var)."' LIMIT $nrows";
	}
	
	public function information($objName, $nrows, $order, $column){
		echo "<table border=0 class=informations>";
		echo "<tr><td><b>Engine</b></td><td width=250px>".$this->pdo->getEngine()."</td></tr>";
		echo "<tr><td><b>Database</b></td><td>".$this->pdo->getDatabase()."</td></tr>";
		echo "<tr><td><b>Table</b></td><td>$objName</td></tr>";		
		echo "<tr><td><b>Rows</b></td><td>$nrows</td></tr>";
		echo "<tr><td><b>Query</b></td><td>".$this->mainQuery."</td></tr>";
		echo "</table>";
	}
	
/**
 * @author João Lagarto	
 * @abstract method to create the foreign key list displayed in a new window
 */	

	public function FKlist($objName, $offset, $user_id){
		$this->tableHeaders($objName);
		echo "<tr class=headers>";
		for($i=0;$i<2;$i++){ //only display two attributes
			echo "<td class=list>".$this->header[$i]."</td>";
		}
		echo "</tr>";
		//check for restraining clauses
		$where="";
		$having = $this->perm->restrictAttribute($user_id, $objName);
		if($having!="") $where= " WHERE $having ";
		$sql = $this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".$objName $where LIMIT 20 OFFSET $offset");
		$sql->execute();
		for($i=0;$row=$sql->fetch();$i++){
			echo "<tr><td class=list><b>".$row[0]."</b></td><td style='text-align:left;' class=list>".$row[1]."</td></tr>";
		}
		if($having!="") return $sql->rowCount();
	}

/**
 * @author João Lagarto	
 * @abstract method display user options
 */	
	
	public function userOptions($bool, $user_id){
		$this->perm->userInfo($user_id);
		echo "<tr><td><hr></td></tr>";
		echo "<tr><td>You are logged in as <b>".$this->perm->getUserLogin()."</b></td></tr>";
		echo "<tr><td><br></td></tr>";
		//boolean variable to know which code is to be displayed (display on admin.php)
		if($bool){
			/*
			//code to write new messages
			echo "<tr><td>";
			echo "<a href=javascript:void(0) onclick=showhide('newMsg') title='Send message to a registered user'>Send message</a>";
			echo "<div id='newMsg' style='display:none;position:absolute;border-style:solid;border-width:1px;background-color:#C2DFFF;z-index:99;padding:3px;'>";
		   	$this->msg->genMsgForm($user_id);
			echo "</div>";
			echo "</td></tr>";
			//code to display received messages
			echo "<tr><td>";
			echo "<a href=javascript:void(0) onclick=showhide('readMsg') title='Read all messages'>Read messages</a>";
			echo "<div id='readMsg' style='display:none;position:absolute;border-style:solid;border-width:1px;background-color:#C2DFFF;z-index:99;padding:3px;'>";
		   	$this->msg->readMsgForm($user_id);
			echo "</div>";
			echo "</td></tr>";
			*/
			//Logout -> exit datumo
			echo "<tr><td><a href=".$this->pdo->getFolder()."/session.php?logout>Sign out</a></td></tr>";
		} else {
			//exit page
			echo "<tr><td><a href=javascript:void(0) onclick=window.close()>Exit</a></td></tr>";	
		}
		echo "<tr><td><a href=http://github.com/Cirklo/Datumo-2.0/wiki/_pages target='_blank' title='Need help?'>Help</a></td></tr>";
	}
	
/**
 * @author João Lagarto	
 * @abstract method to display report options
 */	
	
	public function reportOptions($bool, $user_id){
		echo "<tr><td>";
		echo "<a href=javascript:void(0)>My Reports</a>";
		echo "<div id='reportList' class=sidebar>";
		if($bool) $target="_blank";
		else $target="_self";
		//display list of available reports
		$this->report->loadReports($user_id, $target);
		echo "</div>"; 
		echo "</td></tr>";
		//display window to generate a new report
		echo "<tr><td><a href=javascript:void(0) onclick=window.open('".$this->pdo->getFolder()."/genReport.php','mywindow','height=500px,width=500px,scrollbars=yes')>Create report</a></td></tr>";
		
	}
	
/**
 * method to display page header according to input array
 * @param unknown_type $arr
 */
	
	public function options($arr){
		echo "<tr>";
		for($i=0;$i<sizeof($arr);$i++){
			echo "<td><h3>$arr[$i]</h3></td>";
		}
		echo "</tr>";
	}
	
/**
 * Method to display the contact form
 * @param unknown_type $contact
 */
	
	
	public function contactForm(){
		$contact = "Do you want to report a bug? Please submit the form.";
		echo "<div id=contactMe class='container'>";
        echo "<form class='contactForm' name='cform' method='post'>";
        echo "<p><b>$contact</b></p>";
        echo "<p><select name=contactType id=contactType>";
        echo "<option value=0 selected>Contact us...</option>";
        echo "<option value=bugs@cirklo.org>Bugs</option>";
        echo "<option value=support@cirklo.org>Support</option>";
        echo "<option value=info@cirklo.org>Informations</option>";
        echo "</select></p>";
        echo "<p><label for='name'>Name</label>";
        echo "<input id='name' type='text' value='' name='name' class='name'/></p>";
        echo "<p><label for='e-mail'>E-mail</label>";
        echo "<input id='email' type='text' value='' name='email' class='email'/></p>";
        echo "<p><label for='message'>Message</label>";
        echo "<textarea id='message' rows='' cols='' name='message' class='message'></textarea></p>";
        echo "<input type='button' name='submitbug' value='Submit Form' onclick=$(document).submitBug({form:'cform'});>";
        echo "</form>";
		echo "</div>";
		echo "<div id=errorNotify></div>";
		
	}
	
	
/**
 * Method to check if this is a foreign key field or not 
 */
	
	function FKfield($objName, $val){
		//change database to information schema
		$this->pdo->dbInfo();
		$sql=$this->pdo->query("SELECT b.referenced_table_name FROM columns a, key_column_usage b WHERE a.column_name=b.column_name and a.table_schema=b.table_schema AND b.table_name='$objName' AND b.table_schema='".$this->pdo->getDatabase()."' AND a.ordinal_position=2");
		if($sql->rowCount()==0)
			return $val;
		else {
			$row=$sql->fetch();
			//change to main schema
			$this->pdo->dbConn();
			$sql=$this->pdo->query("SELECT * FROM ".$this->pdo->getDatabase().".$row[0] WHERE $row[0]_id='$val'");
			$row=$sql->fetch();
			return $row[1];
		}
		
	}

	public function masks($objName){
		//set path to main schema
		$this->pdo->dbConn();
		//query the database for masks
		$sql=$this->pdo->query("SELECT DISTINCT mask_name, mask_pic FROM mask WHERE mask_table='$objName'");
		$row=$sql->fetch();
		//is there any match?
		if($sql->rowCount()!=0){
			$this->arrMaskPic[]=$row[1];
			$mask=$row[0];
		} else { //just write the table name
			$mask=$objName;
		}	
		//return to information schema
		$this->pdo->dbInfo();
		return $mask;
	}
	
	public function legend($objName,$user_id){
		$this->perm->tablePermissions($objName, $user_id);
		//table legend
		echo "<table>";
		echo "<tr><td colspan=2 style='text-align:center'><b>Press + for detailed information</b></td></tr>";
		if($this->perm->getInsert()){	//only display this legend if this user has insert permissions
			echo "<tr><td style='text-align:center'><img src=pics/add.png width=32px></td><td>Add row to multiple insert</td></tr>";
			echo "<tr><td style='text-align:center'><img src=pics/remove.png width=32px></td><td>Remove row from multiple insert</td></tr>";
			echo "<tr><td style='text-align:center'><img src=pics/submit.png width=32px></td><td>Insert rows</td></tr>";
			echo "<tr><td style='text-align:center'><img src=pics/copy.png width=16px></td><td>Copy row to insert</td></tr>";
		}
		if($this->perm->getRequest()){
			echo "<tr><td style='text-align:center'><img src=pics/store.png width=16px></td><td>Add to products to basket</td></tr>";
		}
		//always show this legend
		echo "<tr><td style='text-align:center'><img src=pics/details.gif></td><td>Value details</td></tr>";
		echo "<tr><td style='text-align:center'><img src=pics/help.png></td><td>Observations</td></tr>";
		echo "</table>";
		
	}
	
	/**
	 * 
	 * Method that verifies if a specific table is a view or a base table
	 */
	
	function checkTableType($objName){
		//search path to information schema
		$this->pdo->dbInfo();
		$query="SELECT table_type FROM tables WHERE table_name='$objName' AND table_schema='".$this->pdo->getDatabase()."'";
		$sql=$this->pdo->query($query);
		$row=$sql->fetch();
		if($row[0]=="VIEW"){
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Method to display announcements
	 * 
	 */
	
	function displayMessage(){
		$this->pdo->dbConn();
		try{
			$query = "SELECT DISTINCT object_id, object_name 
			FROM object, announcement 
			WHERE announcement_object=object_id 
			AND announcement_end_date > NOW() 
			ORDER BY object_id";
			$sql = $this->pdo->query($query);
		} catch (Exception $e){
			$query = "SELECT DISTINCT resource_id, resource_name 
			FROM resource, announcement 
			WHERE announcement_object=resource_id 
			AND announcement_end_date > NOW() 
			ORDER BY resource_id";
			$sql = $this->pdo->query($query);
		}
		for ($i=0;$row=$sql->fetch();$i++){
			echo "<h3>".$row[1]."</h3>";
			$query_="SELECT announcement_id, announcement_title, announcement_date 
			FROM announcement 
			WHERE announcement_object=$row[0] 
			AND announcement_end_date > NOW()
			ORDER BY announcement_date DESC";
			$sql_=$this->pdo->query($query_);
			echo "<ul class=list>";
			if($sql->rowCount()){
				for($j=0;$row_=$sql_->fetch();$j++){
					echo "<li><b>$row_[2]:</b> <a href=javascript:void(0) onclick=window.open('announcement.php?announcement_id=$row_[0]','_blank','height=350px,width=300px,scrollbars=yes');>$row_[1]</a></li>";
				}
			} else {
				echo "<li>No announcements to display at the time</li>";
			}
			echo "</ul>";
		}
		
		
		
	}
}

?>