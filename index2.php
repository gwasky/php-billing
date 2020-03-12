<?php 

require_once('../Connections/sugar.php'); 
require_once('control.php');

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
$MM_authorizedUsers = "Treasury,Finance Credit and Control,Finance - Collections,Customer Care - Operations,Customer Care CS SMT,Customer Care - CC Operations,Super";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
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
?><?php $tracker = $_SESSION['MM_Username']; ?><?php
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

$currentPage = $_SERVER["PHP_SELF"];
$account_username = $_POST[account];
$today = date('Y-m-d');


mysql_select_db($database_sugar, $sugar);
/*SHORT CUT WAY OF JOINING ACCOUNTS TO CONTRACTS
$query_cstdetails = "
	SELECT
		accounts_cstm.crn_c,
		accounts_cstm.service_type_internet_c as service_type,
		accounts_cstm.customer_type_c as customer_type,
		accounts.name as account_name,
		accounts_cstm.mem_id_c,
		accounts_cstm.selected_billing_currency_c as selected_billing_currency
	FROM
		accounts
		INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
		INNER JOIN cn_contracts ON accounts_cstm.id_c = cn_contracts.account
	WHERE 
		accounts.deleted = 0 and cn_contracts.deleted = 0";
if($_POST[button3]){
	$query_cstdetails .= " AND 
		accounts_cstm.preferred_username_c LIKE '%$account_username%' AND
		accounts.name LIKE '%$_POST[account_name]%' AND 
		accounts_cstm.crn_c LIKE '%$_POST[account_num]%'
		";
	
}
*/

//SUGAR WAY OF JOINING ACCOUNTS TO CONTRACTS
$query_cstdetails = "
	SELECT
		accounts_cstm.crn_c,
		accounts_cstm.service_type_internet_c as service_type,
		accounts_cstm.customer_type_c as customer_type,
		accounts.name as account_name,
		accounts_cstm.mem_id_c,
		accounts_cstm.selected_billing_currency_c as selected_billing_currency
	FROM
		accounts
		INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
		INNER JOIN accounts_cn_contracts_c ON (accounts_cn_contracts_c.accounts_cntsaccounts_ida = accounts_cstm.id_c)
		INNER JOIN cn_contracts ON cn_contracts.id = accounts_cn_contracts_c.accounts_cn_contracts_idb
	WHERE 
		accounts.deleted = 0 AND cn_contracts.deleted = 0 AND accounts_cn_contracts_c.deleted = 0 ";
if($_POST[button3]){
	$query_cstdetails .= " AND 
		accounts_cstm.preferred_username_c LIKE '%$account_username%' AND
		accounts.name LIKE '%$_POST[account_name]%' AND 
		accounts_cstm.crn_c LIKE '%$_POST[account_num]%'
		";
	
}

//echo nl2br($query_cstdetails)."<hr>";


$cstdetails = mysql_query($query_cstdetails, $sugar) or die(mysql_error());
$row_cstdetails = mysql_fetch_assoc($cstdetails);
$totalRows_cstdetails = mysql_num_rows($cstdetails);

mysql_select_db($database_sugar, $sugar);
$query_users = "SELECT user_name, first_name, last_name FROM users WHERE user_name = '$tracker'";
$users = mysql_query($query_users, $sugar) or die(mysql_error());
$row_users = mysql_fetch_assoc($users);
$totalRows_users = mysql_num_rows($users);

mysql_select_db($database_sugar, $sugar);
$query_rate = "SELECT * FROM wimax_rates WHERE rate_date = '$today'";
$rate = mysql_query($query_rate, $sugar) or die(mysql_error());
$row_rate = mysql_fetch_assoc($rate);
$totalRows_rate = mysql_num_rows($rate);

$queryString_cstdetails = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_cstdetails") == false && 
        stristr($param, "totalRows_cstdetails") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_cstdetails = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_cstdetails = sprintf("&totalRows_cstdetails=%d%s", $totalRows_cstdetails, $queryString_cstdetails);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Wimax Billing Interface - Account List</title>
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
.style16 {color: #061F7B}
-->
</style>
<link href="css/styles.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript">
function myPopup(url,user,popname) {
window.open( url + user, popname, 
"status = 1, resizable = 1, scrollbars=Yes" )
}
</script>
<style type="text/css">
<!--
.style20 {font-size: 9px}
.style21 {color: #F70013}
.style22 {font-family: Verdana, Arial, Helvetica, sans-serif}
-->
</style>
</head>

<body>
<table width="100%" border="0" cellspacing="0">
  <tr>
    <td width="40%" rowspan="2"><img src="images/logo.jpg" width="233" height="39" /></td>
    <td width="60%" align="right" valign="bottom" class="style11 style20"><span class="style21">Your Logged in as:</span><span class="style22"> <?php echo $row_users['first_name']; ?> <?php echo $row_users['last_name']; ?></span><br /></td>
  </tr>
  <tr>
    <td align="right" valign="bottom" class="style14 style16">
    <table height="25" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>

    <div class="chromestyle">
    <ul>
    <li><a href="index.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Home&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
    <li><a href="cashup_report.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Payments&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
    <li><a href="rates.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Set Rate&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
    <li><a href="invoice_view.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Invoices&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
    <li><a href="adjustments_report.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Adjustments&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
    <li><a href="<?php echo $logoutAction ?>"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Log Out&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
    </ul>
    </div>   </td>
  </tr>
</table>    </td>
  </tr>
  <tr>
    <td colspan="2" background="images/table_header2.jpg">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><form id="form1" name="form1" method="post" action="index2.php">
      <span class="style14">Username:</span> 
      <label>
      <input name="account" type="text" class="style11" id="account" />
      </label>
      <span class="style14">Account Name:</span>
      <label>
      <input name="account_name" type="text" class="style11" id="account_name" />
      </label>
      <span class="style14">Account Number:</span>
      <label>
      <input name="account_num" type="text" class="style11" id="account_num" />
      </label>
        <label>
        <input name="button3" type="submit" class="style11" id="button3" value="Find" />
        </label>
</form>
    </td>
  </tr>
  <tr>
    <td colspan="2"><table width="100%" border="0" align="left" cellpadding="2" cellspacing="0">
      <tr style bordercolor="#CCCCCC">
        <td background="images/table_header.jpg" bgcolor="#CCCCCC" class="style14">Parent Id</td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Account</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">UGX/USD</span></td>
        <!--<td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Customer Type</span></td>-->
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Service Type</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Products</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC">&nbsp;</td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC">&nbsp;</td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC">&nbsp;</td>
        </tr>
      <?php do { 
	  		$myquery = new uniquequerys();
			$query = "
				SELECT 
					accounts_cstm.download_bandwidth_c as bandwidth,
					accounts_cstm.maintenance_option_c as maintenance_option,
					accounts_cstm.shared_packages_c as package,
					accounts_cstm.package_mail_hosting_c as mail_hosting,
					accounts_cstm.package_web_hosting_c as web_hosting,
					accounts_cstm.package_domain_registration_c as domain_registration,
					accounts_cstm.package_type_domain_hosting_c as domain_hosting
				FROM
					accounts_cstm
					INNER JOIN accounts ON accounts.id = accounts_cstm.id_c
				WHERE
					accounts_cstm.crn_c = '".$row_cstdetails[crn_c]."' AND
					accounts.deleted = 0
				LIMIT 1
			";
			$products = $myquery->uniquequery($query);
	  ?>
      <tr>
        <td><span class="style11" style="cursor:pointer"><?php echo $row_cstdetails['mem_id_c']; ?></span></td>
        <td><div style="cursor:pointer" class="style11" ondblclick="javascript:myPopup('payments.php?parent_id=','<?php echo $row_cstdetails['mem_id_c']; ?>','history')"><?php echo $row_cstdetails[account_name]; ?></div></td>
        <td class="style11"><div style="cursor:pointer" class="style11" ondblclick="javascript:myPopup('payments.php?parent_id=','<?php echo $row_cstdetails['mem_id_c']; ?>','history')"><?php echo $row_cstdetails['selected_billing_currency']; ?></div></td>
        <!--<td class="style11"><div style="cursor:pointer" class="style11" ondblclick="javascript:myPopup('payments.php?parent_id=','<?php echo $row_cstdetails['mem_id_c']; ?>','history')"><?php echo $row_cstdetails['customer_type']; ?></div></td>-->
        <td class="style11"><div style="cursor:pointer" class="style11" ondblclick="javascript:myPopup('payments.php?parent_id=','<?php echo $row_cstdetails['mem_id_c']; ?>','history')"><?php echo $row_cstdetails['service_type']; ?></div></td>
        <td class="style11"><div style="cursor:pointer" class="style11" ondblclick="javascript:myPopup('payments.php?parent_id=','<?php echo $row_cstdetails['mem_id_c']; ?>','history')"><?php 
		
		foreach($products as $group=>$product_name){
			if(strlen($product_name) > 3){
				echo "[".$group."] => ".$product_name."; ";
			}
		}
		
		?></div></td>
        <td>
          <input name="button2" type="button" class="style14" id="button2" onclick="javascript:myPopup('cst_transaction_charges.php?account_id=','<?php echo $row_cstdetails['crn_c']; ?>','charge')" value="Charge Customer" />                  </td>
        <td class="style11"><input name="button" type="button" class="style14" id="button" onclick="javascript:myPopup('cst_transaction.php?account_id=','<?php echo $row_cstdetails['crn_c']; ?>','charge')" value="Enter Payment" /></td>
        <td class="style11"><input name="button2" type="button" class="style14" id="button2" onclick="javascript:myPopup('cst_adjustment.php?account_id=','<?php echo $row_cstdetails['crn_c']; ?>','adjust')" value="Make Adjustment" /></td>
        </tr>
      <?php } while ($row_cstdetails = mysql_fetch_assoc($cstdetails)); ?>
    </table>
      <p><br />
      </p>    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td class="style14"><span class="style1">Using the following dollar rate : <?php $ret_set = get_rate(date('Y-m-d')); echo number_format($ret_set[rate],0)." set on date : ";?> <span style="font-size:18px; font-weight:bold; color:#F00;"> <? echo $ret_set[rate_date]; ?></span></span></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php


mysql_free_result($cstdetails);

mysql_free_result($users);

mysql_free_result($rate);
?>
