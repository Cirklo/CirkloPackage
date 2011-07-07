<?php
require_once("session.php");
$user_id = startSession();

?>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<script type="text/javascript">
$(document).ready(function(){
	$("*").tipTip(); //tiptip initialization
	$("#browser").treeview({
		toggle: function() {
			console.log("%s was toggled.", $(this).find(">span").text());
		}
	});

	/**
	 * AutoSuggest Plugin
	 * 
	 * Input must have lang=__fk in order to work correctly
	 */
	
	$("input[lang=__fk]").focus(function(){
		$(this).simpleAutoComplete("autoSuggest.php?field="+this.id);
	});
	
	
});
</script>
<?php 
require_once ("__dbConnect.php");
require_once "dispClass.php";
require_once "resClass.php";
require_once ("treeClass.php");
require_once ("queryClass.php");

//call database class
$conn = new dbConnection();
$engine = $conn->getEngine();
$database = $conn->getDatabase();
//other classes
$tree = new treeClass();
$display = new dispClass();

//http variables
if(isset($_GET['id'])) 		$id = $_GET['id'];
if(isset($_GET['tree'])) 	$treeview_id= $_GET['tree'];
if(isset($_GET['table1'])) 	$table1 = $_GET['table1'];
if(isset($_GET['table2'])) 	$table2 = $_GET['table2'];
if(isset($_GET['conn'])) 	$field = $_GET['conn'];
if(isset($_GET['val'])) 	$val = $_GET['val'];
if(isset($_GET['type'])) 	$type = $_GET['type'];
if(isset($_GET['list']))	$list=$_GET['list'];
if(isset($_GET['page'])){
	$pageNum = $_GET['page'];
} else {
	$pageNum = 1;
}

//get user permission for this view
$tree->treeRestrictions($treeview_id, $user_id);
//clause to display last table inputs
if(isset($_GET["disp"])){
	if($tree->getDelete())		echo "<input type=button id=del name=del value=Delete onclick=actionTree('delete','$val','$field','$treeview_id')>";
	//do we have permissions to update or insert??
	//on button click verify with checkboxes from this item are checked (update checked entries)
	if($tree->getUpdate() || $tree->getAdd())  {
		if($tree->getUpdate()) 	echo "<input type=button id=upd name=upd value=Update onclick=actionTree('update','$val','$field','$treeview_id')>";
		//if($tree->getAdd()) 	echo "<input type=button id=add name=add value=Insert>";
		//get all tables that form this tree
		$arr=$tree->treeTables($treeview_id);
		
		//Index attribute between the last two tables
		$index=$tree->tableConn($arr[1], $arr[2]);
		//loop through all fields for each selected checkbox 
		$empty_array=array();
		$clean_array=array();
		$i=0;
		//setting the array
		$list=explode(",",$list);
		foreach($list as $row){
			$sql=$conn->prepare("SELECT * FROM $database.$arr[2] WHERE $arr[2]_id=$row");
			//echo $sql->queryString;
			$sql->execute();
			$res=$sql->fetch();
			//control array -> for the first loop
			if($i==0)$empty_array=$res;
			//loop through all attributes
			foreach($res as $key=>$value){
				if($empty_array[$key]==$value){
					$clean_array[$key]=$value;
				}else{
					$clean_array[$key]="";
				}
			}
			$empty_array=$clean_array;
			$i++;
		}
	//	print_r($clean_array);
		//get last table from the tree
		$objName=end($arr);
		//get all fields from this table
		$display->tableHeaders($objName);
		$header=$display->getHeader();
		$fullheader=$display->getFullHeader();
		$fk=$display->getFKeys();
		//loop through all fields
		echo "<form method=post name=CurForm_$field>";
		echo "<table>";
		for($i=0;$i<sizeof($header);$i++){
			//variable initialization
			$class="";
			$lang="";
			$val="";
			echo "<tr>";
			//write field label
			echo "<td><b>$header[$i]</b></td>";
			//is it foreign key?
			if($fk[$fullheader[$i]]!=""){
				$class="fk";
				$lang="__fk";
				if($fk[$fullheader[$i]]==$arr[1]){
					$val=$_GET['val'];
					//control query (number of rows)
					//get second attribute of this id
					$sql = $conn->prepare("SELECT * FROM ".$database.".".$arr[1]." WHERE ".$arr[1]."_id='$val'");
					$sql->execute();
					$row=$sql->fetch();
					$val=$row[1];
				} else {
					if($clean_array[$fullheader[$i]]!=""){
						$display->getFKvalue($clean_array[$fullheader[$i]],$i);
						$val=$display->getFKatt();
					}
				}
			} else { //regular field
				$class="reg";
				$val=$clean_array[$fullheader[$i]];
			}
			//display input (need to validate value)
			echo "<td><input type=text class=$class lang=$lang id='$fullheader[$i]' name='$fullheader[$i]' alt='' value='$val'></td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</form>";
	} else {
		//escape string (nothing else to be displayed)
		echo "No options available for this table";
	}
	//do not allow for the other code to run
	exit();
}

//initialize other variables 
$dblConn="";
//variables to handle a great number of results
$rowsPerPage=20;
$offset = ($pageNum-1)*$rowsPerPage;

//control query (number of rows)
$sql = $conn->prepare("SELECT * FROM ".$database.".".$table1." WHERE $field='$val' ORDER BY 2");
try{
	$sql->execute();
	$maxRows = $sql->rowCount();
	$maxPage = ceil($maxRows/$rowsPerPage);
} catch (Exception $e){
	//do nothing for now
}

//set search path to information schema
$conn->dbInfo();
$query="SELECT b.referenced_table_name 
	FROM columns as a, key_column_usage as b 
	WHERE a.table_schema='labcal' 
	AND a.table_name='$table1' 
	AND b.table_name=a.table_name 
	AND a.column_name=b.column_name 
	AND a.table_schema=b.table_schema 
	AND a.ordinal_position=2";

$sql=$conn->query($query);
$row=$sql->fetch();
if($row[0]){
	$refTable=$row[0];
}
//change back to main database
$conn->dbConn();
//display first table options
$sql = $conn->prepare("SELECT * FROM ".$database.".".$table1." WHERE $field='$val' ORDER BY 2 LIMIT 20 OFFSET $offset");
try{
	$sql->execute();
	/**********************PHP CONTAINER TO DISPLAY THE LINKS FOR THE LAST/NEXT 20 ROWS*******************************/
	if($sql->rowCount()>0){
		echo "<b>Showing page $pageNum of $maxPage</b>";
		if($pageNum>1) {
			$page = $pageNum-1;
			
			echo "&nbsp;&nbsp;<a href=javascript:void(0) onclick=dispTree('$id','$table1','$table2','$field','$val','$page',$type,true,$treeview_id)>Back</a>";
		} 
		if($pageNum<$maxPage){
			$page = $pageNum+1;
			echo "&nbsp;&nbsp;<a href=javascript:void(0) onclick=dispTree('$id','$table1','$table2','$field','$val','$page',$type,true,$treeview_id)>Next</a>";
		}
		if($type==2)	echo " <a href=javascript:void(0) onclick=checkit('details','$table1','$val',true,$treeview_id)>Select all</a>";
	}
	/*****************************************************************************************************************/
	
} catch (Exception $e){
	//do nothing for now
}
//do not allow to proceed if there are no results to display
if($sql->rowCount()==0) exit();
echo "<ul>";
$ref = $table1;
//Only allow treeview changes if we are in the last table
for($j=0;$row = $sql->fetch();$j++){
	if(isset($table2) and $table2!="") {
		$dblConn = $tree->tableConn($table1, $table2);
		$table1=$table2;
		$table2="";
	}
	echo "<li>";
	//display first table options
	if($type==1){
		echo "<span class='folder' onclick=dispTree('Tree_$row[0]','$table1','$table2','$dblConn','$row[0]',1,2,false,$treeview_id);>$row[1]</span>";
	}
	
	//if it is the last branch of the tree
	if($type==2) {
		if($tree->getUpdate() or $tree->getDelete()) echo "<input type=checkbox id=$row[0] onclick=dispInputTree('details','$table1','$val',true,$treeview_id);>";
		//echo "<span class=file onclick=getdetails('details','$table1','$row[0]',true,$treeview_id);>$row[1]</span>";
		if(isset($refTable)){
			$sql2=$conn->query("SELECT * FROM $refTable WHERE ".$refTable."_id=$row[1]");
			$row2=$sql2->fetch();
			$v=$row2[1];
		} else {
			$v=$row[1];
		}
		echo "<span class=file>$v</span>";
	}
	echo "<div id='Tree_$row[0]' class=c style='display:none'></div>";
	echo "</li>";
}
echo "</ul>";



?>