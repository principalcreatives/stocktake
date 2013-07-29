<?php

class database {
	
	public $con = NULL;
	public $transact = array();
	
	public function __construct($serverName, $serverInfo){
		
		try{
			/*
			 * SQLSRV Connection
			*/
			$this->con = sqlsrv_connect($serverName, $serverInfo) or die("Connection Failed");

			/*
			 * ODBC Connection
			*/
			//$con = odbc_connect("Driver={SQL Server};Server=10.1.1.23;Database=HOBSQL2;", 'sa', '0ri0nPax') or die(odbc_error());
			
			return $this->con;
			
		}catch(Exception $e){
			print $e->getMessage();
		}
		
	}//end construct
	
	public function select($sql, $params = NULL){
		
		$stmt = ( $params ) ? sqlsrv_prepare( $this->con, $sql, $params ) : sqlsrv_prepare( $this->con, $sql );
		
		if( sqlsrv_execute( $stmt ) === false ) {
			var_dump(sqlsrv_errors());
			exit();
		}
		
		$fetch = NULL;
		
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) $fetch[] = $row;
		
		return $fetch;
		
	}//end select
	
	public function insert($sql, $params = NULL){
		
		try{
			
			$stmt = ( $params ) ? sqlsrv_prepare( $this->con, $sql, $params ) : sqlsrv_prepare( $this->con, $sql );
			if( sqlsrv_execute( $stmt ) === false ) {
				var_dump(sqlsrv_errors());
				exit();
			} else {
				//echo $sql."\n";
				array_push( $this->transact, $stmt );
				return $this->lastId( $stmt );
			}
			
		}catch(Exception $e){
			echo $e->getMessage();
		}
		
	}//end insert
	
	public function update( $sql, $params = NULL ){
		
		try{
			
			$stmt = ( $params ) ? sqlsrv_prepare( $this->con, $sql, $params ) : sqlsrv_prepare( $this->con, $sql );
			if( sqlsrv_execute( $stmt ) === false ) {
				var_dump(sqlsrv_errors());
				exit();
			} else {
				//echo $sql."\n";
				array_push( $this->transact, $stmt );
			}
			
		}catch(Exception $e){
			echo $e->getMessage();
		}
		
	}//end update
	
	public function delete( $sql, $params = NULL ){
	
		try{
			
			$stmt = ( $params ) ? sqlsrv_prepare( $this->con, $sql, $params ) : sqlsrv_prepare( $this->con, $sql );
			if( sqlsrv_execute( $stmt ) === false ) {
				var_dump(sqlsrv_errors());
				exit();
			} else {
				//echo $sql."\n";
				array_push( $this->transact, $stmt );
			}
			
		}catch(Exception $e){
			echo $e->getMessage();
		}
	
	}//end delete
	
	public function begin_transaction(){
		/* 
		 * Begin the transaction. 
		 */
		if ( sqlsrv_begin_transaction( $this->con ) === false ) {
			exit( print_r( sqlsrv_errors(), true ));
		}
	}
	
	public function commit(){
		$commit_flag = TRUE;
		
		for( $i = 0; $i < count($this->transact); $i++ ){
			if(!$this->transact[$i]){
				$commit_flag = FALSE;
				break;
			}
		}
		
		if( $commit_flag ){ 
			sqlsrv_commit( $this->con );
			return "Transaction Saved";
		}else{
			sqlsrv_rollback( $this->con ); 
			return "Transaction Failed";
		}
		
	}//end commit
	

	private function lastId( $queryID ) {
		sqlsrv_next_result( $queryID );
		sqlsrv_fetch( $queryID );
		
		return sqlsrv_get_field( $queryID, 0 );
	}//end lastID
	
}//end class