<?php 

/**
 * @author João Lagarto
 * @copyright João Lagarto 2010
 * @license EUPL
 * @version Datumo 2.0
 * @abstract Class handle to handle project management
 */

class projectClass{
	private $pdo;
	private $project_list=array();
	private $StartDate;
	private $EndDate;
	private $IniBudget;
	private $CurBudget;
	private $Department;
	private $AccountNo;
	private $ProjectName;
	private $GraphSize;
	
	public function __construct(){
		$this->pdo = new dbConnection();
	}
	
	
	function setStartDate($arg){$this->StartDate=$arg;}
	function setEndDate($arg){$this->EndDate=$arg;}
	function setAccountNo($arg){$this->AccountNo=$arg;}
	function setIniBudget($arg){$this->IniBudget=$arg;}
	function setCurBudget($arg){$this->CurBudget=$arg;}
	function setProjectName($arg){$this->ProjectName=$arg;}
	function setGraphSize($arg){$this->GraphSize=$arg;}
	function setDepartment($arg){
		$sql=$this->pdo->prepare("SELECT department_name FROM department where department_id=" . $arg);
		$sql->execute();
		$this->Department=$sql->fetch();
		
	}
	
	function getStartDate(){return $this->StartDate;}
	function getEndDate(){return $this->EndDate;}
	function getAccountNo(){return $this->AccountNo;}
	function getIniBudget(){return $this->IniBudget;}
	function getCurBudget(){return $this->CurBudget;}
	function getProjectName(){return $this->ProjectName;}
	function getGraphSize(){return  $this->GraphSize;}
	function getDepartment(){return $this->Department;}
	
	/**
	 * 
	 * Method to write a list of projects from this user's group into an array
	 */
	
	public function projects($user_id){
		$sql=$this->pdo->prepare("SELECT account_id, account_number, account_project, account_inibudget, account_budget, account_start, account_end FROM account WHERE account_dep IN (SELECT user_dep FROM ".$this->pdo->getDatabase().".user WHERE user_id=$user_id) UNION SELECT  account_id, account_number, account_project, account_inibudget, account_budget, account_start, account_end  FROM account, accountperm WHERE accountperm_account=account_id AND account_start<NOW() AND account_end>NOW() AND account_id<>0 AND accountperm_user=$user_id ORDER BY account_number");
		$sql->execute();
		echo "<ul class=list>";
		for($i=0;$row=$sql->fetch();$i++){
			echo "<li>";
			//is the project overdue?
			if(date("Y-m-d")>$row[6]){ //project finished
				echo "<font color=#FF0000>Finished</font>";
			} else { //project on course
				echo "<font color=#00FF00>Running</font>";
			}
			$arr[]=$row[2];
			
			echo " <a href=javascript:void(0) style='text-decoration:none' onclick=projectInfo($row[0])>$row[2] ($row[1])</a>";
			echo "</li>";
		}
		echo "</ul>";
		$this->project_list=$arr;
	}
	
	function setAccountInfo($AccountId){
		$sql=$this->pdo->prepare("SELECT * FROM account WHERE account_id=". $AccountId);
		$sql->execute();
		$row = $sql->fetch();
		$this->StartDate=$row['account_start'];
		$this->EndDate=$row['account_end'];
		$this->IniBudget=$row['account_inibudget'];
		$this->ProjectName=$row['account_project'];
		$this->AccountNo=$row['account_number'];
		$this->CurBudget=$row['account_budget'];
		$this->setDepartment($row['account_dep']);
	}
	
}

?>