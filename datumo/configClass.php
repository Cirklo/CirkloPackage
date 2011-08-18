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
	
	public function checkPlugins($level){
		//set search path to main database
		$this->pdo->dbConn();
		if($level!=3){	//do not allow external users to view internal plugins
			$sql=$this->pdo->prepare("SELECT * FROM plugin");
			$sql->execute();
			for($i=0;$row=$sql->fetch();$i++){
				$arr[]=$row[1];
			}
			$this->plugin=$arr;
			//call method to find submenus
			$this->checkSubmenus();
		}
		if($level==0 or $level==3){	//allow admins and external users to view extra plugins
			$this->checkExtras();
			
		}
	}
	
	/**
	 * @author João lagarto
	 * @abstract Query the database for submenus and data display
	 */
	
	public function checkSubmenus(){
		//set search path to main database
		$this->pdo->dbConn();
		for($i=0;$i<sizeof($this->plugin);$i++){
			echo "<h3>".$this->plugin[$i]."</h3>";
			$sql=$this->pdo->prepare("SELECT menu_name, menu_description, menu_url FROM ".$this->pdo->getDatabase().".menu WHERE menu_plugin IN (SELECT plugin_id FROM ".$this->pdo->getDatabase().".plugin WHERE plugin_name='".$this->plugin[$i]."') ORDER BY menu_id");
			$sql->execute();
			for($j=0;$row=$sql->fetch();$j++){
				echo "<a href=$row[2] title='$row[1]'>$row[0]</a><br>";
			}
		}
	}
	
	public function checkExtras(){
		$query="SELECT extra_name, extra_description, extra_url FROM extra";
		try{
			$sql=$this->pdo->query($query);
			if($sql->rowCount()>0)	echo "<h3>External plugins</h3>";
			for($i=0;$row=$sql->fetch();$i++){
				echo "<a href=$row[2] target=_blank title='$row[1]'>$row[0]</a><br>";
			}
		} catch (Exception $e){
			echo "External plugins not available";
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