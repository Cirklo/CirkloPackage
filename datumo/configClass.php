<?php 

/**
 * @author João Lagarto
 * @version Datumo 2.0
 * @abstract Datumo configuration. Inhere we check for any plugins available
 * @license EUPL
 */

class configClass{
	private $pdo;
	private $plugin=array();
	private $menu=array();
	
	public function __construct(){
		$this->pdo = new dbConnection();
	}
	
	public function getPlugins(){	return $this->plugin;}
	public function getMenu(){	return $this->menu;}
	
	
	/**
	 * @author João lagarto
	 * @abstract Query the database for plugins
	 */
	
	public function checkPlugins(){
		//set search path to main database
		$this->pdo->dbConn();
		$sql=$this->pdo->prepare("SELECT * FROM ".$this->pdo->getDatabase().".plugin");
		$sql->execute();
		for($i=0;$row=$sql->fetch();$i++){
			$arr[]=$row[1];
		}
		$this->plugin=$arr;
		//call method to find submenus
		$this->checkSubmenus();
	}
	
	/**
	 * @author João lagarto
	 * @abstract Query the database for submenus and data display
	 */
	
	public function checkSubmenus(){
		//set search path to main database
		$this->pdo->dbConn();
		for($i=0;$i<sizeof($this->plugin);$i++){
			echo "<tr><td colspan=2><hr></td></tr>";
			echo "<tr><td>".$this->plugin[$i]."</td></tr>";
			$sql=$this->pdo->prepare("SELECT menu_name, menu_description, menu_url FROM ".$this->pdo->getDatabase().".menu WHERE menu_plugin IN (SELECT plugin_id FROM ".$this->pdo->getDatabase().".plugin WHERE plugin_name='".$this->plugin[$i]."') ORDER BY menu_id");
			$sql->execute();
			for($j=0;$row=$sql->fetch();$j++){
				echo "<tr><td><a href=".$this->pdo->getFolder()."/$row[2] title='$row[1]'>$row[0]</a></td></tr>";
			}
		}
	}
	
	public function compat(){
		$pow="Powered by <a href='http://www.cirklo.org' target=_blank>Cirklo</a>";
		$var="Optimized for <a href=http://www.mozilla.com target='_blank'>Mozilla Firefox 4.0</a>";
		echo "<tr><td colspan=2><hr></td></tr>";
		echo "<tr><td colspan=2 style='text-align:center;font-size:9px'>$pow</td></tr>";
		
		
	}
	
}















?>