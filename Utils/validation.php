<?php

class validation {
	private $value;
	
	public function __construct($post){
		$this->value = mysql_escape_string( $post );	
	}
	
	public static function validateAll( $params ){
	
		foreach( $params as $key ){
			$key = htmlentities( stripslashes( $key ) );
		}
		
		return $params;
	}
	
	public function validateHTML(){
		$this->value = htmlentities($this->value);
		return $this;
	}
	
	public function validateStrip(){
		$this->value = stripslashes($this->value);
		return $this;
	}
	
	public function htmlEntityDecode(){
		$this->value = html_entity_decode($this->value);
		return $this;
	}
	
	public function ret_val(){
		return $this->value;
	}
	
}