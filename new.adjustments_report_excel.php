<?php
//$filename = urldecode($_GET['filename']).".xls";
$filename = "Adjustments.xls";
// required for IE, otherwise Content-disposition is ignored
if(ini_get('zlib.output_compression'))
	ini_set('zlib.output_compression', 'Off');

# This line will stream the file to the user rather than spray it across the screen
header("Content-type: application/vnd.ms-excel");

# replace excelfile.xls with whatever you want the filename to default to
header("Content-Disposition: attachment;filename=".$filename);
header("Expires: 0");
header("Cache-Control: private");
session_cache_limiter("public");
?>
<?php require_once('../Connections/sugar.php'); ?>
<?php
//MX Widgets3 include
require_once('../includes/wdg/WDG.php');
require_once('control.php');
?>
<?php
	$date3 = $_GET['entry_date1'];
	$date4 = $_GET['entry_date2'];
	
 ?>
<?php
//initialize the session
	if (!isset($_SESSION)) {
	  session_start();
	}
	
	// ** Logout the current user. **
	$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
	if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
	  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
	}
	
	if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	  //to fully log out a visitor we need to clear the session varialbles
	  $_SESSION['MM_Username'] = NULL;
	  $_SESSION['MM_UserGroup'] = NULL;
	  $_SESSION['PrevUrl'] = NULL;
	  unset($_SESSION['MM_Username']);
	  unset($_SESSION['MM_UserGroup']);
	  unset($_SESSION['PrevUrl']);
		
	  $logoutGoTo = "login.php";
	  if ($logoutGoTo) {
	  header("Location: $logoutGoTo");
	  exit;
	  }
	}
	?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "Finance Credit and Control,Finance - Collections,Super";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "access.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
	<?php $tracker = $_SESSION['MM_Username']; ?><?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}



mysql_select_db($database_sugar, $sugar);
$query_users = "SELECT user_name, first_name, last_name FROM users WHERE user_name = '$tracker'";
$users = mysql_query($query_users, $sugar) or die(mysql_error());
$row_users = mysql_fetch_assoc($users);
$totalRows_users = mysql_num_rows($users);

mysql_select_db($database_sugar, $sugar);
$query_period = "SELECT entry_date, entry_type, SUM(amount) FROM wimax_billing WHERE entry_type = 'Payment' AND entry_date >= '$date1' AND entry_date <= '$date2' GROUP BY entry_date";
$period = mysql_query($query_period, $sugar) or die(mysql_error());
$row_period = mysql_fetch_assoc($period);
$totalRows_period = mysql_num_rows($period);

mysql_select_db($database_sugar, $sugar);
$query_by_cashier = "SELECT entry_id, entry_date, entry_type, SUM(amount), `user` FROM wimax_billing WHERE entry_type = 'Adjustment' AND entry_date >= '$date1' AND entry_date <= '$date2' GROUP BY user";
$by_cashier = mysql_query($query_by_cashier, $sugar) or die(mysql_error());
$row_by_cashier = mysql_fetch_assoc($by_cashier);
$totalRows_by_cashier = mysql_num_rows($by_cashier);

mysql_select_db($database_sugar, $sugar);
$query_cashup_today = "SELECT    wimax_rates.rate,   wimax_billing.entry_date,   wimax_billing.entry_id, wimax_billing.username, wimax_billing.parent_id,   wimax_billing.entry_type,   wimax_billing.entry,   wimax_billing.matched_invoice,   wimax_billing.currency,   wimax_billing.amount,wimax_billing.user  FROM  wimax_rates  INNER JOIN wimax_billing ON (wimax_rates.rate_date=wimax_billing.entry_date) WHERE wimax_billing.entry_type = 'Adjustment' AND wimax_billing.entry_date >= '$date3' AND wimax_billing.entry_date <= '$date4'";
$cashup_today = mysql_query($query_cashup_today, $sugar) or die(mysql_error());
$row_cashup_today = mysql_fetch_assoc($cashup_today);
$totalRows_cashup_today = mysql_num_rows($cashup_today);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:wdg="http://ns.adobe.com/addt">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.style11 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style14 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #000000; font-weight: bold; }
.style15 {color: #000000}
.style16 {color: #061F7B}
-->
</style>
</head>

<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
  <tr>
    <td background="images/table_header.jpg" class="style14">Entry ID</td>
    <td background="images/table_header.jpg" class="style14">Entry Date</td>
    <td background="images/table_header.jpg" class="style14">Account No (Parent)</td>
    <td background="images/table_header.jpg" class="style14">User Name</td>
    <td background="images/table_header.jpg" class="style14">Entry Type</td>
    <td background="images/table_header.jpg" class="style14">Grouping</td>
    <td background="images/table_header.jpg" class="style14">Entry</td>
    <td background="images/table_header.jpg" class="style14">Details</td>
    <td background="images/table_header.jpg" class="style14">Approved by</td>
    <td background="images/table_header.jpg" class="style14">Matched Invoice</td>
    <td background="images/table_header.jpg" class="style14">Currency</td>
    <td background="images/table_header.jpg" class="style14">Amount (Dollars)</td>
    <td background="images/table_header.jpg" class="style14">Rate</td>
    <td background="images/table_header.jpg" class="style14">UGX Equivalent</td>
    <td background="images/table_header.jpg" class="style14">Assigned By</td>
  </tr>
  <?php do { 
  	$row_cashup_today[entry] = unserialize($row_cashup_today[entry]);
  ?>
  <tr>
    <td class="style11"><?php echo $row_cashup_today['entry_id']; ?></td>
    <td class="style11"><?php echo $row_cashup_today['entry_date']; ?></td>
    <td class="style11"><?php echo $row_cashup_today[parent_id]; ?></td>
    <td class="style11"><?php echo $row_cashup_today['username']; ?></td>
    <td class="style11"><?php echo $row_cashup_today['entry_type']; ?></td>
    <td class="style11"><?php echo $row_cashup_today[entry][grouping]; ?></td>
    <td class="style11"><?php echo $row_cashup_today[entry][entry]; ?></td>
    <td class="style11"><?php echo $row_cashup_today[entry][details]; ?></td>
    <td class="style11"><?php echo $row_cashup_today[entry][approved_by]; ?></td>
    <td class="style11"><?php echo $row_cashup_today['matched_invoice']; ?></td>
    <td class="style11"><?php echo $row_cashup_today['currency']; ?></td>
    <td align="right" class="style11"><?php echo accounts_format($row_cashup_today['amount']); ?></td>
    <td align="center" class="style11"><?php echo $row_cashup_today['rate']; ?></td>
    <td align="right" class="style11"><?php echo accounts_format($row_cashup_today['amount']*$row_cashup_today['rate']); ?></td>
    <td class="style11"><?php echo $row_cashup_today['user']; ?></td>
  </tr>
  <?php 
  
  $sum += $row_cashup_today['amount']; 
  $sum2 += round(($row_cashup_today['amount']*$row_cashup_today['rate']),0); 
  } while ($row_cashup_today = mysql_fetch_assoc($cashup_today)); ?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td></td>
    <td></td>
    <td></td>
    <td><label></label></td>
    <td class="style14">Total</td>
    <td align="right" class="style14"><?php echo accounts_format($sum); ?></td>
    <td></td>
    <td align="right" class="style14"><?php echo accounts_format($sum2); ?></td>
    <td></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
mysql_free_result($users);

mysql_free_result($period);

mysql_free_result($by_cashier);

mysql_free_result($cashup_today);

?>
