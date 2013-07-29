<?php

/*
 * Output Report
 * For Debugging
 */
class output {
	
	public static function info($array){
		print "<pre>";
		print_r($array);
		print "</pre>";
	}
	
}