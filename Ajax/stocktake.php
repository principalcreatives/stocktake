<?php

require_once("../Utils/output.php");
require_once("../Utils/database.php");
require_once("../Utils/config.php");
require_once("../Utils/validation.php");

require_once("../Lib/stocktake.php");

/*
 * Unauthorized Access 
 */
if( !isset($_SESSION['user']['auth']) && !isset($_COOKIE["token"]) ) header("Location: index.php");
if( $rs_login = $user->checkLoginXML($_COOKIE["token"]) ) $user->setUserData($rs_login);

$db = new database($database["IP"], $database["INFO"]);
$stocktake = new stocktake($db);

/*
$row_id = new validation($_POST["RowID"]);
$user_no = new validation($_POST["UserNo"]);
$qty_counted = new validation($_POST["QtyCounted"]);
$unit_qty = new validation($_POST["UnitQty"]);
$adj_qty = new validation($_POST["AdjustQty"]);
$state = new validation($_POST["State"]);
$confirmed = new validation($_POST["Confirmed"]);

$row_id->validateHTML()->validateStrip();
$user_no->validateHTML()->validateStrip();
$qty_counted->validateHTML()->validateStrip();
$unit_qty->validateHTML()->validateStrip();
$adj_qty->validateHTML()->validateStrip();
$state->validateHTML()->validateStrip();
$confirmed->validateHTML()->validateStrip();
*/

$params = array();

$params["id"] = new validation($_POST["id"]);
$params["row_id"] = new validation($_POST["row_id"]);
$params["user_no"] = new validation($_SESSION["user"]["CardNo"]);
$params["qty_counted"] = new validation($_POST["def_qoh"]);
$params["unit_qty"] = new validation($_POST["txt_cn"]);
$params["adj_qty"] = new validation($_POST["def_adj"]);
$params["state"] = new validation($_POST["state"]);
$params["confirmed"] = new validation($_POST["confirmed"]);

$params["stockno"] = new validation($_POST["scn_stockno"]);
$params["state"] = new validation($_POST["scn_state"]);
$params["card_no"] = new validation($_SESSION["user"]["CardNo"]);

$message = $stocktake->addCount($params);

echo $message;