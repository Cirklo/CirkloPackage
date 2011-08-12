<?php


class menu{
	private $conn;
	private $reports=array();
	private $treeviews=array();
	private $plots=array();
	
	//use the constructor to get all reports, treeviews and plots available for this user
	public function __construct($user_id){
		try{
			$this->conn=new dbConnection();	//db connection
			//get all reports
			$query="SELECT report_id, report_name, report_description FROM report WHERE report_id NOT IN (SELECT param_report FROM param) AND report_conf=1 OR (report_user=$user_id AND report_conf=2) ORDER BY report_name";
			$sql = $this->conn->query($query);
			//loop through all reports
			for($i=0;$row=$sql->fetch();$i++){
				$reports[$row[0]]=$row[1];
			}		
			//set report variable
			$this->setReports($reports);
			
			//get all treeviews
			$query="SELECT treeview_id, treeview_name, treeview_description FROM treeview WHERE treeview_id IN (SELECT restree_name FROM restree WHERE restree_user=$user_id)";
			$sql = $this->conn->query($query);
			//loop through all reports
			for($i=0;$row=$sql->fetch();$i++){
				$treeview[$row[0]]=$row[1];
			}		
			//set report variable
			$this->setTreeviews($treeview);
			
			//get all plots
			$query="SELECT plot_id,plot_title FROM plot";
			$sql=$this->conn->query($query);
			
			//loop through all reports
			for($i=0;$row=$sql->fetch();$i++){
				$plots[$row[0]]=$row[1];
			}		
			//set report variable
			$this->setPlots($plots);
		
		} catch (Exception $e){
			echo $e->getMessage();
		}
	}	
	
	//setters
	public function setReports($arg){	$this->reports=$arg;}
	public function setTreeviews($arg){	$this->treeviews=$arg;}
	public function setPlots($arg){		$this->plots=$arg;}
	
	//getters
	public function getReports(){	return $this->reports;}
	public function getTreeviews(){	return $this->treeviews;}
	public function getPlots(){		return $this->plots;}
}




?>