<?php

//script to be called through an ajax request

//includes
require_once "__dbConnect.php";
require_once "../session.php";
$user_id = startSession();

if(isset($_GET['type'])){	
	$call=$_GET['type'];
	switch ($call){
		case 0:
			createMating();
			break;
		case 1:
			createWeaning();
			break;
		case 2:
			break;
	}
}



function createMating(){
	//database connection 
	$conn=new dbConnection();
	
	//initialize local variables
	$origin=array();
	$error=false;
	$male=array();
	$female=array();
	
	//get url variables
	if(isset($_GET['ids'])){	$arr=$_GET['ids'];}
	
	foreach ($arr as $animal_id){
		$gender=checkGender($animal_id);
		if($gender=="Male"){
			$male[]=$animal_id;
		} elseif ($gender=="Female"){
			$female[]=$animal_id;
		}
	}
	
	//gender validation check
	if(sizeof($male)==0 or sizeof($male)>1){ //can only choose 1 male
		$error=true;	//trigger error
	}
	if(sizeof($female)==0 or sizeof($female)>2){ //can only choose 1 or 2 females
		$error=true;	//trigger error
	}
	if($error){ //output error. Stop script 
		echo "Unable to proceed. Choose 1 male and 1 or 2 females!";
		exit();
	}
		
	if($origin=checkOriginFromAnimal($arr)){
		$cagetype_id=$origin[0];
		$strain_id=$origin[1];
	} else {
		echo "Unable to proceed. Selected animals have different origin!";
		exit();
	}
	
	//Begin SQL transaction
	$conn->beginTransaction();
	//create new cage
	$query="INSERT INTO cage (cage_rack, cage_strain, cage_type, cage_animals) VALUES (0,$strain_id, $cagetype_id, ".sizeof($arr).")";
	try{
		$sql=$conn->query($query);
		$new_cage_id=$conn->lastInsertId();
		//create new mating
		if(!isset($female[1]))	$female[1]=0; //check if there is a second female
		$today=date("Y-m-d");
		$query="INSERT INTO mating VALUES ('',$new_cage_id,'$today',$male[0], $female[0], $female[1],'')";
		$sql=$conn->query($query);
		foreach($arr as $animal_id){
			$query="UPDATE animal SET animal_cage=$new_cage_id WHERE animal_id=$animal_id";
			$sql=$conn->query($query);
		}
		//commit changes
		$conn->commit();
		echo "New cage and mating created! Animal cages updated!";
	} catch (Exception $e){
		$conn->rollBack();
		echo $e->getMessage();
	}
}

function checkGender($animal_id){
	//database connection 
	$conn=new dbConnection();
	$sql=$conn->query("SELECT gender_name FROM animal, gender WHERE animal_gender=gender_id AND animal_id=$animal_id");
	$row=$sql->fetch();
	return $row[0];
}

function checkOriginFromAnimal($arr){
	//database connection 
	$conn=new dbConnection();
	//initialize array
	$origin=array();
	$cagetype=array();
	$strain=array();
	//loop through all selected animals
	foreach ($arr as $animal_id){
		$sql=$conn->query("SELECT cage_type, cage_strain FROM cage, animal WHERE animal_cage=cage_id AND animal_id=$animal_id");
		$row=$sql->fetch();
		$cagetype[]=$row[0];
		$strain[]=$row[1];
	}
	//how many strains and cagetypes are there?
	if(sizeof(array_count_values($cagetype))==1 and sizeof(array_count_values($strain))==1){	//one value means one strain and one cagetype
		$origin[0]=$cagetype[0];
		$origin[1]=$cagetype[1];
		return $origin;
	} else {	//more than one strain or cagetype in the selected animals
		return false;
	}
}

function createWeaning(){
	//database connection 
	$conn=new dbConnection();
	
	//local variables
	$noMales=0; 	//total number of males 
	$noFemales=0;	//total number of females
	$origin=array();
	$strain=array();
	$cagetype=array();
	
	//get url variables
	if(isset($_GET['ids'])){	$arr=$_GET['ids'];}
	
	if($origin=checkOriginFromLitter($arr)){
		$cagetype_id=$origin[0];
		$strain_id=$origin[1];
		print_r($origin);
	} else {
		echo "Unable to proceed. Selected animals have different origin!";
		exit();
	}
	
	
	//before proceeding with weaning must check for strain and cagetypes
	/* Animal attributes in need for checking
	 * 
	 * gender -> easy
	 * genotype -> get it from parents; if parents have different genotype set it as Undefined
	 * date of birth -> easy
	 * date of weaning -> easy
	 * generation -> generation after the parents (create new if it does not exist)
	 * origin -> cage of origin -> easy
	 * cage -> create new cage (a cage for males and another for females)
	 * state -> obviously alive
	 * account -> set it as 0
	 * type -> animal
	 * vat -> 0
	 * 
	 * Validation
	 * check cagetype
	 * check strain
	 * 
	 */
	
}

function checkOriginFromLitter($arr){
	//database connection 
	$conn=new dbConnection();
	//initialize array
	$origin=array();
	$cagetype=array();
	$strain=array();
	//loop through all selected animals
	foreach ($arr as $litter_id){
		$sql=$conn->query("SELECT cage_type, cage_strain FROM cage, litter WHERE litter_origin=cage_id AND litter_id=$litter_id");
		$row=$sql->fetch();
		$cagetype[]=$row[0];
		$strain[]=$row[1];
	}
	//how many strains and cagetypes are there?
	if(sizeof(array_count_values($cagetype))==1 and sizeof(array_count_values($strain))==1){	//one value means one strain and one cagetype
		$origin[0]=$cagetype[0];
		$origin[1]=$cagetype[1];
		return $origin;
	} else {	//more than one strain or cagetype in the selected animals
		return false;
	}
}
