<?php

//require_once("../Utils/output.php");

class stock {
	
	private $db;
	
	public function __construct($db){
		/**
			METHODS:
			getStock
		 */
		
		$this->db = $db;
			
	}//end construct
	
	public function getStock($stockNo){
		$sql = "
				SELECT TOP 1 *
				FROM JimStock
				WHERE StockNo = '".$stockNo."'";
		
		return $this->db->select($sql);
		
	}//end getStock
	
}//end class