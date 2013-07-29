<?php

$environment = "Test";
$php_info = FALSE;

/*
 * PHP_INFO
*/
if($php_info){
	echo phpinfo();
	exit();
}

/*
 * Error Reporting
*/
if($environment === "Development"){
	error_reporting(E_ALL);
	ini_set("display_errors", "1");
}

/*
 * SQL Server Info
*/
$database["INFO"] = array(
		"Database" => "JIM_DRS",
		"UID" => "sa",
		"PWD" => "0ri0nPax");

/*
 * IP Address
*/
$database["IP"] = "10.1.1.23";

/*
 * Debug Details
 */
($environment === "Development") ? output::info($database) : NULL;

/*
 * Log Details
 */
$log_path = "/log/log.txt";

/*
 * Start Session
 */
session_start();

$message = "";