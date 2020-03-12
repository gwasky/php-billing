<?php 
error_reporting(0);
require_once('../Connections/sugar.php'); 

//MX Widgets3 include
require_once('../includes/wdg/WDG.php');

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
$MM_authorizedUsers = "Finance Credit and Control,Super";
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
<?php $tracker = $_SESSION['MM_Username']; ?>
<?php
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
?>
<?php 
require('control.php');

$receipt_number = generateRecieptNo($_GET['entry_id']);

if($_GET['action'] == 'save'){
	if(($_POST[request] == 'Services') || ($_POST[request] == 'Debit Note')||($_POST[request] == 'Refund')){
		$_POST[adjustments] = abs($_POST[adjustments]) * -1;
	}else{
		$_POST[adjustments] = abs($_POST[adjustments]);
	}
	if($_POST[request] == 'Services'){
		$_POST[details] = "Corrective Service billing ".$_POST[details];
		$_POST[entry_type] = $_POST[request];
		
		$myquery = new uniquequerys();
		
		$result = $myquery->uniquequery("SELECT ps_products_cstm.product_grouping_c as grouping FROM ps_products  inner join ps_products_cstm on (ps_products.id=ps_products_cstm.id_c) WHERE deleted = '0' AND ps_products.name = '$_POST[product]'");
		$_POST[request] = $result[grouping];
	}
	save_entry();
	header("Location:index.php");
	//exit;
} elseif($_GET['action'] == 'print'){
	if(intval($_GET['id']))
	{
	$receipt_data = generate_receipt($_GET['id']);
	$receipt_html = display_receipt($receipt_data);
	echo $receipt_html;
	}elseif(($_GET['control']) && ($_GET['control_value'])){
	$data = generate_quick_invoice($_GET['control_value'], '');
	
	echo display_invoice_data($data);
	}
	exit;
} elseif($_GET['account_id']){
?>
<?php require_once('../Connections/sugar.php'); ?>
<?php

mysql_select_db($database_sugar, $sugar);
$query_cst_details = "SELECT accounts_cstm.crn_c,   accounts_cstm.cpe_type_c,   accounts.name as acc_name,   accounts_cstm.preferred_username_c,accounts_cstm.shared_packages_c,accounts_cstm.mem_id_c AS parent_id, cn_contracts.start_date,   cn_contracts.expiry_date, cn_contracts.billing_date, cn_contracts.`status`,   ps_products.name,   ps_products.price, accounts_cstm.billing_add_plot_c,   accounts_cstm.billing_add_town_c,   accounts_cstm.billing_add_area_c, accounts_cstm.billing_add_strt_c,accounts_cstm.billing_add_district_c FROM accounts  INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)  INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)  INNER JOIN ps_products ON (accounts_cstm.download_bandwidth_c=ps_products.name) WHERE accounts.deleted = '0' AND cn_contracts.deleted = '0' AND ps_products.deleted = '0' AND accounts_cstm.crn_c = '$_GET[account_id]'";
$cst_details = mysql_query($query_cst_details, $sugar) or die(mysql_error());
$row_cst_details = mysql_fetch_assoc($cst_details);
$totalRows_cst_details = mysql_num_rows($cst_details);

$bill_start = $row_cst_details['start_date'];
$bill_end = $row_cst_details['expiry_date'];

mysql_select_db($database_sugar, $sugar);
$query_products = "SELECT name,price,type FROM ps_products WHERE deleted = '0' AND type != 'Service'";
$ps_products = mysql_query($query_products, $sugar) or die(mysql_error());
$row_products = mysql_fetch_assoc($ps_products);
$totalRows_products = mysql_num_rows($ps_products);

mysql_select_db($database_sugar, $sugar);
$query_period_invoice = "SELECT SUM(amount) FROM wimax_billing WHERE entry_type = 'Payment' AND bill_start = '$bill_start' AND bill_end = '$bill_end' AND account_id = '$_GET[account_id]'";
$period_invoice = mysql_query($query_period_invoice, $sugar) or die(mysql_error());
$row_period_invoice = mysql_fetch_assoc($period_invoice);
$totalRows_period_invoice = mysql_num_rows($period_invoice);

mysql_select_db($database_sugar, $sugar);
$query_perioad_adjustment = "SELECT SUM(amount) FROM wimax_billing WHERE entry_type = 'Adjustment' AND bill_start = '$bill_start' AND bill_end = '$bill_end' AND account_id = '$_GET[account_id]'";
$perioad_adjustment = mysql_query($query_perioad_adjustment, $sugar) or die(mysql_error());
$row_perioad_adjustment = mysql_fetch_assoc($perioad_adjustment);
$totalRows_perioad_adjustment = mysql_num_rows($perioad_adjustment);

mysql_select_db($database_sugar, $sugar);
$query_period_charges = "SELECT SUM(amount) FROM wimax_billing WHERE entry_type != 'Adjustment' AND entry_type != 'Payment' AND bill_start = '$bill_start' AND bill_end = '$bill_end' AND account_id = '$_GET[account_id]'";
$period_charges = mysql_query($query_period_charges, $sugar) or die(mysql_error());
$row_period_charges = mysql_fetch_assoc($period_charges);
$totalRows_period_charges = mysql_num_rows($period_charges);

mysql_select_db($database_sugar, $sugar);
$query_users = "SELECT user_name, first_name, last_name FROM users WHERE user_name = '$tracker'";
$users = mysql_query($query_users, $sugar) or die(mysql_error());
$row_users = mysql_fetch_assoc($users);
$totalRows_users = mysql_num_rows($users);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Wimax Adjustments for <? echo $row_cst_details[acc_name]; ?></title>
<style type="text/css">
<!--
.style1 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style14 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #000000; font-weight: bold; }
.style15 {color: #000000}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.style2 {
	color: #FFFFFF
}
.style4 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #FFFFFF; font-weight: bold; }
-->
</style>
<link href="css/styles.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
.style18 {color: #FF0000}
.style19 {font-size: 9px}
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
<script language="JavaScript" src="gen_validatorv2.js" type="text/javascript"></script>
</head>
<body>

<table width="100%" border="0" cellspacing="0">
  <tr>
    <td rowspan="2"><img src="images/logo.jpg" alt="4" width="233" height="39" /></td>
    <td align="right" valign="bottom" bgcolor="#FFFFFF"><span class="style1 style17 style19"><span class="style18">Your Logged in as:</span> <?php echo $row_users['first_name']; ?> <?php echo $row_users['last_name']; ?></span></td>
  </tr>
  <tr>
    <td align="right" valign="bottom" bgcolor="#FFFFFF"><table height="25" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><div class="chromestyle">
            <ul>
              <li><a href="index.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Home&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="cashup_report.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Payments&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="rates.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Set Rate&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="invoice_view.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Invoices&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
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
    <td width="30%">&nbsp;</td>
    <td width="70%">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" valign="top"><table width="800" border="0" align="left" cellpadding="2" cellspacing="0" style="border:#CCCCCC;">
      <tr>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Customer#</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">CPE Type</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Bandwidth</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Account Number</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC" class="style14">Package</td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Status</span></td>

      </tr>
    <?php echo $row_cst_details; ?>
      <tr>
        <td><span class="style1"><?php echo $row_cst_details['crn_c']; ?></span></td>
        <td class="style1"><?php echo $row_cst_details['cpe_type_c']; ?></td>
        <td class="style1"><?php echo $row_cst_details['name']; ?></td>
        <td class="style1"><?php echo $row_cst_details['crn_c']; ?></td>
        <td class="style1"><?php echo $row_cst_details['shared_packages_c']; ?></td>
        <td class="style1"><?php echo $row_cst_details['status']; ?></td>
      </tr>
    
    </table>
    <br /></td>
  </tr>
  <tr>
    <td valign="top"><table width="100%" border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td class="style14">&nbsp;</td>
        <td class="style1">&nbsp;</td>
      </tr>
      <tr>
        <td width="44%" class="style14">Monthly Charge</td>
        <td width="56%" class="style1 style15"></td>
      </tr>
      <tr>
        <td class="style14">Start Date</td>
        <td class="style1 style15"><?php echo date_reformat($row_cst_details['start_date'],''); ?></td>
      </tr>
      <tr>
        <td class="style14">Expiry Date</td>
        <td class="style1 style15"><?php echo date_reformat($row_cst_details['expiry_date'],''); ?></td>
      </tr>
      <tr>
        <td class="style14">Billing Date</td>
        <td class="style1 style15"><?php echo date_reformat($row_cst_details['billing_date'],''); ?></td>
      </tr>
       

    </table></td>
    <td>&nbsp;</td>
  </tr>
</table>
<form id="form1" name="form1" method="post" action="cst_adjustment.php?action=save">
  <table width="100%" border="0" align="left" cellpadding="2" cellspacing="1">
    
    <tr>
      <td colspan="2" background="images/table_header2.jpg" class="style14 style2"><span class="style4">Adjustments<span class="style2"></span></span><span class="style2"></span></td>
      <td colspan="2" background="images/table_header2.jpg" class="style4">&nbsp;</td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Entry ID</td>
      <td><label>
      <input type="hidden" name="account_id" value= "<?php echo $row_cst_details['crn_c']; ?>" />
      <input type="hidden" name="billing_start" value = "<?php echo $row_cst_details['start_date']; ?>" />
      <input type="hidden" name="billing_expiry" value = "<?php echo $row_cst_details['expiry_date']; ?>" />
      <input type="hidden" name="billing_date" value = "<?php echo $row_cst_details['billing_date']; ?>" />
      <input type="hidden" name="parent_id" value = "<?php echo $row_cst_details['parent_id']; ?>" />
      <input type="hidden" name="user" value = "<?php echo $row_users['first_name']." ".$row_users['last_name'] ; ?>" />
        <input name="reciept" type="text" class="style1" id="reciept" value="<?php echo $receipt_number; ?>" size="8" readonly="readonly" />
      </label></td>
      <td width="14%">&nbsp;</td>
      <td width="26%">&nbsp;</td>
    </tr>
   <tr>
      <td width="13%" valign="top" bgcolor="#CCCCCC" class="style4">Set Adjustment Date</td>
      <td width="47%" valign="middle"><input name="entry_date" class="style1" id="entry_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
      <td valign="top" class="style4">&nbsp;</td>
      <td valign="top">&nbsp;</td>
    </tr>
    <tr>
      <td width="13%" valign="top" bgcolor="#CCCCCC" class="style4">Adjustments</td>
      <td width="47%" valign="top"><label>
        <select name="request" class="style1" id="request">
          <option value="">Select Adjustment Type</option>
          <option value="Credit Note">Credit Note</option>
          <option value="Cash Discount">Cash Discount</option>
          <option value="Waiver on Equipment">Waiver (Credit note) on Equipment</option>
          <option value="Waiver">Waiver on other Taxable Product</option>
          <option value="Debit Note">Debit Note</option>
          <option value="Refund">Payment Refund</option>
          <option value="Services">Corrective service billing</option>
        </select></label>
        <? echo display_products_dropdown(''); ?>
        <span class="style4">
        <input name="adjustments" type="text" class="style1" id="adjustments" />
      </span></td>
      <td valign="top" class="style4">&nbsp;</td>
      <td valign="top">&nbsp;</td>
    </tr>   
    <tr>
      <td width="13%" valign="top" bgcolor="#CCCCCC" class="style4">Currency Choice</td>
      <td><select name="currency" class="style1" id="currency">
        <option value="" selected="selected">Select Currency</option>
        <option value="UGX">Uganda Shillings</option>
        <option value="USD">US Dollars</option>
      </select>
        <span class="style1">Today's Dollar Rate is: <?php echo get_rate(date('Y-m-d')); ?> </span></td>
      <td></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td width="13%" valign="top" bgcolor="#CCCCCC" class="style4">Being Adjustment for</td>
      <td><label>
        <input name="details" type="text" class="style1" id="details" size="60" />
        </label></td>
      <td></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td width="13%" valign="top" bgcolor="#CCCCCC" class="style4">Approved by</td>
      <td><label>
        <input name="approved_by" type="text" class="style1" id="approved_by" size="30" />
        </label></td>
      <td></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><label>
        <input name="Submit" type="submit" class="style14" id="button" value="Transact" />
</label></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  

</form>
<script language="JavaScript" type="text/javascript">
 var frmvalidator = new Validator("form1");
 frmvalidator.addValidation("request","req","Please Select An Adjustment Type");
 frmvalidator.addValidation("adjustments","req","Please Enter An Adjustment Amount");
 frmvalidator.addValidation("currency","req","Please Select A Currency");
 frmvalidator.addValidation("product","req","Please Select A Product");
 frmvalidator.addValidation("entry_date","req","Please Set the Adjustment Date");
 frmvalidator.addValidation("details","req","Please fill breif details about this adjustment");
 frmvalidator.addValidation("approved_by","req","Please fill in who approved this adjustment");
 </script>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
mysql_free_result($cst_details);

mysql_free_result($ps_products);

mysql_free_result($period_invoice);

mysql_free_result($perioad_adjustment);

mysql_free_result($period_charges);

mysql_free_result($users);
} else {
	echo "No option";
}
?>
