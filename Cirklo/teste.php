<?php
	session_start();
	$_SESSION['path'] = "../Cirklo";
	require_once("C:/xampp/htdocs/agendo/commonCode.php");
	
	// echo "server REMOTE_HOST ->'".$_SERVER['REMOTE_HOST']."'";
	// echo "<br>";
	// echo "server REMOTE_ADDR ->'".$_SERVER['REMOTE_ADDR']."'";
	// echo "<br>";
	// echo "server HTTP_CLIENT_IP ->'".$_SERVER['HTTP_CLIENT_IP']."'";
	// echo "<br>";
	// echo "server HTTP_X_FORWARDED_FOR ->'".$_SERVER['HTTP_X_FORWARDED_FOR']."'";
	
	// $sqlFlag = "select allowedips_iprange from allowedips where allowedips_id = 1";
	// $resFlag=dbHelp::mysql_query2($sqlFlag);
	// $ip = $_SERVER['REMOTE_ADDR'];
	// $ip = substr($ip, 0, strripos($ip, '.'));
	// $arrFlag=dbHelp::mysql_fetch_row2($resFlag);
	// $text = "Ip in Database: -".$arrFlag[0]."- Gama Ip detected: -".$ip."-";
	// echo "<br>";
	// echo $text;
	$dbEngine = "mysql";
	$db = "databasename";
	$dbHost = "localhost";
	$dbUser = "root";
	$dbPass = "";
	// echo "treta";
	$sql = "insert into bool values (2,'treta')";
	// noHtconnectFetchRows($sql, $dbEngine, $db, $dbHost, $dbUser, $dbPass);
	$res=dbHelp::mysql_query2($sql);

	function noHtconnectFetchRows($sql, $engine, $db, $host, $user, $pass){
		$pdo = new PDO($engine.":".$db.";".$host, $user, $pass);
		$prepSql = $pdo->prepare($sql);
		$prepSql->execute();
		// if(!$prepSql->execute()){
			// throw new Exception("Error using the 'noHtconnectFetchRows' function when executing the sql query '".$sql."'.");
		// }
		return $prepSql->fetch(PDO::FETCH_NUM);
	}
?>