<?php
//error_reporting(E_WARNING|E_PARSE|E_ERROR);
error_reporting(E_ERROR);
require_once('control.php');
require_once('pdf_invoice.php');
require_once('../Connections/sugar.php'); ?><?php
//MX Widgets3 include
require_once('../includes/wdg/WDG.php');
?><?php
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
$MM_authorizedUsers = "Finance Credit and Control,Finance - Collections,Super,Bill Delivery,Customer Care - Operations";
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
<?php  $tracker = $_SESSION['MM_Username']; ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

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
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
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
$query_users = "SELECT user_name, first_name, last_name,department FROM users WHERE user_name = '$tracker'";
$users = mysql_query($query_users, $sugar) or die(mysql_error());
$row_users = mysql_fetch_assoc($users);
$totalRows_users = mysql_num_rows($users);
$tracker = $_SESSION['MM_Username'];


if($_POST[print_invoices]){
	error_reporting(E_ERROR);
	//echo display_selected_invoices(generate_invoices_list($_POST['account_id'], $_POST['generation'], $_POST['billing'], $_POST['customer_types']));
	pdf_selected_invoices(generate_invoices_list($_POST['account_id'], $_POST['generation'], $_POST['billing'], $_POST['customer_types']));
}else{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:wdg="http://ns.adobe.com/addt">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Wimax Invoice List</title>
<style type="text/css">
<!--
.style11 {	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style14 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #000000; font-weight: bold; }
.style15 {color: #000000}
.style16 {color: #061F7B}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
-->
</style>
<script type="text/javascript" src="../includes/common/js/sigslot_core.js"></script>
<script src="../includes/common/js/base.js" type="text/javascript"></script>
<script src="../includes/common/js/utility.js" type="text/javascript"></script>
<script type="text/javascript" src="../includes/wdg/classes/MXWidgets.js"></script>
<script type="text/javascript" src="../includes/wdg/classes/MXWidgets.js.php"></script>
<script type="text/javascript" src="../includes/wdg/classes/Calendar.js"></script>
<script type="text/javascript" src="../includes/wdg/classes/SmartDate.js"></script>
<script type="text/javascript" src="../includes/wdg/calendar/calendar_stripped.js"></script>
<script type="text/javascript" src="../includes/wdg/calendar/calendar-setup_stripped.js"></script>
<script src="../includes/resources/calendar.js"></script>
<link href="../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
<table width="100%" border="0" cellspacing="1">
  <tr>
    <td width="40%" rowspan="2"><img src="images/logo.jpg" alt="4" width="233" height="39" /></td>
    <td width="60%" align="right" valign="bottom" class="style14 style16"><span class="style14">Your Logged in as: <span class="style11"></span><?php echo $row_users['first_name']; ?> <?php echo $row_users['last_name']; ?></span></td>
  </tr>
  <tr>
    <td align="right" valign="bottom" class="style14 style16"><table height="25" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><div class="chromestyle">
            <ul>
              <li><a href="index.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Home&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="cashup_report.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Payments&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="rates.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Set Rate&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="perfoma.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Proforma Invoice&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="invoice_view.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Invoices&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="email_invoices.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;E-Invoices&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
	      <li><a href="adjustments_report.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Adjustments&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="<?php echo $logoutAction ?>"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Log Out&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
            </ul>
        </div></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td colspan="2" background="images/table_header2.jpg">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><form id="form1" name="form1" method="post" action="invoice_view.php?action=list">
<span class="style14">Account Number</span>
<input name="account_id" type="text" class="style11" id="account_id"  value="<?php echo $_POST[account_id]; ?>"/>
<span class="style14">Invoice Number</span>
<input name="invoice_no" type="text" class="style11" id="invoice_no"  value="<?php echo $_POST[invoice_no]; ?>"/>
<label>
<? 	echo display_parent_accounts_dropdown($_POST[parent_id]); ?>
</label><br>
      <span class="style14">Generation Date</span> 
      <label>
      <input name="generation" class="style11" id="generation" value="<?php echo $_POST[generation]; ?>" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
      </label>
      <span class="style14">Billing Date</span>
<label>
<input name="billing" class="style11" id="billing" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
</label><br>
<?php 
	echo display_customer_type_dropdown($_POST[customer_types]);
?>
	<label><span class="style14">Select Invoice State</span>
     	<select name="deleted" size="1" id="deleted" class="style11">
            <option value="" selected="selected">ALL</option>
            <option value="0">Active ONLY</option>
            <option value="1">Deleted ONLY</option>
    	</select>
    </label>
    <label><br>
       <?php if(($row_users['department']!="Finance Credit and Control")&&($row_users['department']!="Super")&&($row_users['department']!="Bill Delivery")){ ?>
 		  <?php echo ""; ?>
          <?php }else{ ?>
           <span class="style14">Print these Invoices</span>
          <input name="print_invoices" type="checkbox" id="print_invoices" value="yes" />
          <?php } ?>
    </label>
     <label>
    <input name="button" type="submit" class="style14" id="button" value="Find" />
    </label>
    </form>    </td>
  </tr>
  <tr>
    <td colspan="2">
    <br /><br />
        <table width="100%" border="0" align="left" cellpadding="2" cellspacing="0">
            <?php
			echo display_invoices_list(generate_invoices_list($_POST[account_id], $_POST[generation], $_POST[billing], $_POST[customer_types],$_POST[parent_id],$_POST[deleted],$_POST[invoice_no]));
			?>
        </table>    </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
</table>
</body>
</html>
<?php
mysql_free_result($users);
}
?>
