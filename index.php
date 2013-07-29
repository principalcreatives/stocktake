<?php

require_once("Utils/output.php");
require_once("Utils/database.php");
require_once("Utils/config.php");
require_once("Utils/validation.php");

require_once("Lib/user.php");

// If cookie is set
if ( isset($_COOKIE["token"])) header("Location: stocktake.php");

$db = new database($database["IP"], $database["INFO"]);
$user = new user($db);

if(isset($_POST['submit'])){
	
	$username = new validation($_POST['username']);
	$password = new validation($_POST['password']);
	
	// Check if user exists
	if( $rs_login = $user->login($username, $password) ){
		// Regenerate existing session id
		session_regenerate_id();
		
		$user->setUserData( $rs_login );
		$user->addLoginXML( $rs_login );
		
		header("Location: stocktake.php");
	}
	// if username and password does not match
	else{
		$message = "Username and password does not match!";
		include_once("login.php");
	}
}else{
	include_once("login.php");
}
