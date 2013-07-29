<?php

require_once("Utils/output.php");
require_once("Utils/database.php");
require_once("Utils/config.php");
require_once("Utils/validation.php");

require_once("Lib/stocktake.php");
require_once("Lib/stock.php");
require_once("Lib/functions.php");
require_once("Lib/log.php");
require_once("Lib/user.php");

$db = new database($database["IP"], $database["INFO"]);
$stock = new stocktake($db);
$user = new user($db);

/*
 * Unauthorized Access 
 */
if( !isset($_SESSION['user']['auth']) && !isset($_COOKIE["token"]) ) header("Location: index.php");
if( $rs_login = $user->checkLoginXML($_COOKIE["token"]) ) $user->setUserData($rs_login);

/*
 * No Stocktake Value
 */
if( !isset($_GET) ) header("Location: stocktake.php");

$id = new validation($_GET['id']);

$initials = $_SESSION["user"]["UsrInitials"];
$_SESSION["stocktake"]["id"] = $id->ret_val();

$scn = "none";
$undo_block = "none";
$code = new validation("");

$undo_message = "";

/* 
 * -----------------------------------------------
 * Scan stock
 * -----------------------------------------------
 */
if(isset($_POST["h_scan"])){
	
	$log_message = $_POST["h_scan"];
	$log_date = $_SESSION['global']['datetime'];
	
	$log = new log();
	$log->write_log($log_path, $log_message, $log_date, $_POST["h_scan"]);
	
	$code = $params["stockcode"] = new validation(trim($_POST["h_scan"]));
	$params["stockid"] = new validation($_SESSION["stocktake"]["id"]);
	
	$rs_stockcode = $stock->scanStock($params);
	
	$message = (!$rs_stockcode["rs_jimstock"]) ? "No result found" : NULL;
	$scn = (!$rs_stockcode["rs_jimstock"]) ? "none" : "block";
}

/* 
 * -----------------------------------------------
 * Add count to stock
 * -----------------------------------------------
 */
if( isset($_POST["txt_cn"]) || isset($_POST["sel_cn"]) ){
	
	$params = array();
	
	$params["id"] = $id;
	$params["row_id"] = new validation($_POST["row_id"]);
	$params["stockno"] = new validation($_POST["scn_stockno"]);
	$params["qty_counted"] = new validation($_POST["def_qoh"]);
	$params["unit_qty"] = ( isset($_POST["sel_cn"]) ) ? new validation($_POST["sel_cn"]) : new validation($_POST["txt_cn"]);
	$params["adj_qty"] = new validation($_POST["def_adj"]);
	$params["user_no"] = new validation($_SESSION["user"]["CardNo"]);
	$params["card_no"] = new validation($_SESSION["user"]["CardNo"]);
	
	$undo = $stock->addCount($params);
	
	$stk = new stock($db);
	$stockCode = $stk->getStock($params["stockno"]->ret_val());
	
	if($undo["message"] == "Transaction Saved"){
		$undo_message = $undo["message"]." ".$stockCode[0]["StockCode"]." ".$params["unit_qty"]->ret_val()." counted. ";
		$undo_block = "block";
	} else{ 
		$message = "An error has occured.";
	}			   
	
}//end insert

/* 
 * -----------------------------------------------
 * Undo count to stock
 * -----------------------------------------------
 */
if( isset( $_POST["undo"] ) ){
	$params["row_id"] = new validation($_POST["row_id"]);
	$params["line_id"] = new validation($_POST["line_id"]);
	$params["new"] = new validation($_POST["new"]);
	
	$undo_message = $stock->undoCount($params);
	
	$undo_block = ( $undo_message == "Transaction Saved" ) ? "none" : "block";
	$message = ( $undo_message == "Transaction Saved" ) ? "Undo Successful" : "An error has occured";
}

$data = $stock->viewStockTakeStocks($id);
extract($data);

//output::info($rs_stocktake);

include("Views/view_stocktake.php");