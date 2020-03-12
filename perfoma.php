<?php
if(!isset($_SESSION)) {
  session_start();
}


require_once('../Connections/sugar.php'); 
//MX Widgets3 include
require_once('../includes/wdg/WDG.php');
require_once('control.php');
require_once('js/invoice.js.php');

error_reporting(E_ERROR|E_PARSE|E_WARNING);

$myquery = new uniquequerys();
//error_reporting(E_ALL);
//initialize the session

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

$myquery = new uniquequerys();
$user_data = $myquery->uniquequery("SELECT user_name, concat(first_name,' ',last_name) as full_user,department FROM users WHERE user_name = '".$_SESSION[MM_Username]."'");

$MM_authorizedUsers = "Finance Credit and Control,Finance - Collections,Customer Care - Operations,Super,Bill Delivery";
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
    }else{
		//echo "USER [".print_r($UserName,TRUE)."] IS NOT IN ".print_r($arrUsers,TRUE)."<br>";
	} 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    }else{
		echo "GROUP [".print_r($UserGroup,TRUE)."] NOT ALLOWED ACCESS<br>";
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

if($_GET['action'] == 'generate'){
	
	//print_r($_POST[invoice]);
	
	echo generate_n_perfoma_invoice($_POST[invoice]);
}else{
?>
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

//GETTING THE USER WHO HAS OPENED THIS INTERFACE ....
$user_result = $myquery->uniquequery("SELECT concat(first_name,' ',last_name) as username FROM users WHERE user_name = '".$_SESSION[MM_Username]."'");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="css/styles.css"/>
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
<?php generate_invoice_javascript(); ?>
<link href="../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
<form id="invoice[]" name="invoice[]" method="post" action="perfoma.php?action=generate">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
<td>
<table border="0" cellpadding="0" cellspacing="0" width="100%">

<tr>
    <td width="40%" rowspan="2"><img src="images/logo.jpg" width="233" height="39" /></td>
    <td width="60%" align="right" valign="bottom" class="style11 style20"><span class="style21">Your Logged in as:</span><span class="style22"> <?php echo $user_data[full_user]; ?></span><br /></td>
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
    <li><a href="perfoma.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Proforma Invoice&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
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

</table>
</td>
</tr>
<tr class="roundup">
<td>
<TABLE border="0" cellspacing="0" cellpadding="2">
  <tr>
    <th align="left" valign="top" >Invoice Type</th>
    <th align="left" valign="top" >Client Name</th>
    <th align="left" valign="top" >Contact Person</th>
    <th align="left" valign="top" >Client Address</th>
    
    
  </tr>
  <tr>
   	<td align="left" valign="top">
        <label>
        <select name="invoice[type]"id="invoice[type]" onchange="javascript:show_account_inputs()"/>
            <option value="">Select the Invoice type</option>
            <option value="PROFORMA">Proforma</option>
            <option value="TAX">Tax</option>
        </select>
        <input type="hidden" name="invoice[user]" id="invoice[user]" value = "<?php echo $user_data[full_user]; ?>" />
        </label>
  </td>
  <td align="left" valign="top" id="account_name_td">
  		Define the invoice type first ...
    </td>
    <td align="left" valign="top">
    	<input name="invoice[contact_person]" type="text" id="invoice[contact_person]" size="40" class="hide"/><br>
        <select name="invoice[invoice_currency]"id="invoice[invoice_currency]" class="hide" />
            <option value="">Select the Invoice currency</option>
            <option value="USD">USD</option>
            <option value="UGX">UGX</option>
        </select>
    </td>
    <td align="left" valign="top">
        <label>
        <textarea name="invoice[address]" id="invoice[address]" cols="50" rows="3" class="hide"></textarea>
        </label>
    </td>
  </tr>
</TABLE>
</td>
</tr>
<tr class="roundup">
<td>
<TABLE border="0" cellspacing="0" cellpadding="2">
  <tr>
    <th align="left" valign="top" >Period Start</th>
    <td valign="top">
        <input name="invoice[start_date]" id="invoice[start_date]" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="yyyy-mm-dd" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
    </td>
    <th align="left" valign="top" >Period End</th>
    <td valign="top">
        <input name="invoice[end_date]" id="invoice[end_date]" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="yyyy-mm-dd" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
    </td>
  </tr>
</table>
</tr>
</tr>
<tr>
    <td align="left" valign="top">
        <input name="invoice[parent_account_name]" type="hidden" id="invoice[parent_account_name]" size="40" />
        <input name="invoice[parent_account_id]" type="hidden" id="invoice[parent_account_id]" size="40" />
        <input type="hidden" name="invoice[user]" id="invoice[user]" value = "<?php echo $user_result[username]; ?>" />
    </td>
</tr>
<tr>
    <td align="left" valign="top">
    	<INPUT type="button" value="Add Product" onclick="addRow('invoice_entries')" />
        <INPUT type="button" value="Delete Product" onclick="deleteRow('invoice_entries')" />
    </td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">
        <TABLE id="invoice_entries" class="invoice_entries" border="0" cellspacing="0" cellpadding="2" width="1100">
            <tr class="header_row">
                <th></th>
                <th>Select</th>
                <th>Account</th>
                <th>Product</th>
                <th colspan="2">Period</th>
                <th>Quantity</th>
                <th>% Discount</th>
            </tr>
        </TABLE>
    </th>
    </tr>
  <tr>
    <td align="left" scope="row">
        <label>
        <input type="submit" name="Generate Invoice" id="Generate Invoice" value="Generate Invoice" />
        </label>
        <label>
        <input type="reset" name="clear" id="clear" value="Clear Data" />
        </label>
     </td>
  </tr>
</table>
</form>
</body>
</html>
<?php

}
?>
