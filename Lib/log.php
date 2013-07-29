<?php

/**
 * 
 * @author Michael
 * 
 */
class log {
	
	private $fp;
	
	public function write_log ( $path, $message, $date, $txtscn = NULL ) {
		
		$path = dirname( __DIR__ ).$path;
		
		//$fp = fopen( dirname(__DIR__).$path, 'w' ) or die( "Can't open file" );
		//( $txtscn ) ? fwrite( $fp, $txtscn ) : NULL ;
		
		$message = $message." - ".$date . PHP_EOL;
		$txtscn = ( $txtscn ) ? $txtscn . PHP_EOL : NULL;
		//fwrite($fp, $message);
		
		//fclose( $fp );
		
		file_put_contents($path, $message, FILE_APPEND | LOCK_EX);
		( $txtscn ) ? file_put_contents( $path, $txtscn, FILE_APPEND | LOCK_EX ) : NULL;
		
	}//end write_log
	
}//end log