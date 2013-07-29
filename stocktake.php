<?php

require_once("Utils/output.php");
require_once("Utils/database.php");
require_once("Utils/config.php");
require_once("Utils/validation.php");

require_once("Lib/stocktake.php");
require_once("Lib/user.php");

$db = new database($database["IP"], $database["INFO"]);
$stocktake = new stocktake($db);
$user = new user($db);

/*
 * Unauthorized Access 
 */
if( !isset($_SESSION['user']['auth']) && !isset($_COOKIE["token"]) ) header("Location: index.php");
if( $rs_login = $user->checkLoginXML($_COOKIE["token"]) ) $user->setUserData($rs_login);

$message = "";

$initials = $_SESSION["user"]["UsrInitials"];

/*
 * Add Stocktake
 */
if(isset($_POST["submit"])){
	if ($_POST["branch"] == "select"){
		$message = "You did not select anything";
	}else{
		$jimstocktake["sql"] = "
				SELECT TOP 1 SessionNo
				FROM JimStockTake
				ORDER BY SessionNo DESC";
		
		$params = array();
		
		$params["rs_sessionno"] = $db->select( $jimstocktake["sql"] );
		$params["sessNo"] = $params["rs_sessionno"][0]["SessionNo"] + 1;
		
		date_default_timezone_set('Australia/Melbourne');
		$params["date"] = (string)date('Y-m-d H:i:s', time());
		
		$params["locNo"] = $_POST["branch"];
		$params["initials"] = (string)$_SESSION["user"]["UsrInitials"];
		
		$message = $stocktake->addStockTake($params);
		
	}
	
}

$data = $stocktake->viewsStockTakeList();
extract($data);

//output::info($rs_stocktake);

include("Views/stocktake.php");
