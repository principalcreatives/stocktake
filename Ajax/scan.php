<?php

require_once("../Utils/output.php");
require_once("../Utils/database.php");
require_once("../Utils/config.php");
require_once("../Utils/validation.php");

require_once("../Lib/stocktake.php");
require_once("../Lib/functions.php");

$db = new database($database["IP"], $database["INFO"]);
$stocktake = new stocktake($db);

/*
 * Unauthorized Access 
 */
if( !isset($_SESSION['user']['auth']) && !isset($_COOKIE["token"]) ) header("Location: index.php");
if( $rs_login = $user->checkLoginXML($_COOKIE["token"]) ) $user->setUserData($rs_login);

$message = "";

$params["stockcode"] = new validation($_POST["stock_code"]);
$params["stockid"] = new validation($_SESSION["stocktake"]["id"]);

//$params["stockcode"] = new validation("30CCCA10");
//$params["stockcode"] = new validation("31000524");
//$params["stockcode"] = new validation("HDD.ARA.64GBSSDULTRA");
//$params["stockcode"] = new validation("HDD.GEN.160IDE");
//$params["stockcode"] = new validation("PSU.BLI.350");
//$params["stockcode"] = new validation("HDD.SEA.120IDE");
//$params["stockcode"] = new validation("SND.MOT.AAYN1108B");
//$params["stockcode"] = new validation("SND.JAB.GN9330E");
//$params["stockcode"] = new validation("CAR.EPS.T0822");
//$params["stockcode"] = new validation("60IEPT0731");
//$params["stockcode"] = new validation("fghfgh");
$rs_stockcode = json_encode($stocktake->scanStock($params));

header('Content-type: text/json');
header('Content-type: application/json');

print $rs_stockcode;
