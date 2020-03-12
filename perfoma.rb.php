<?php 
error_reporting(0);
require_once('../Connections/sugar.php');

//MX Widgets3 include
require_once('../includes/wdg/WDG.php');
require_once('control.php');

if($_GET['action'] == 'generate'){
	//print_r($_POST);
	echo generate_perfoma_invoice();
}else{
?>
<?php $tracker = $_SESSION['MM_Username']; ?>
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
$query_ps_products = "SELECT ps_products.name,ps_products.`type`,ps_products.price,ps_products_cstm.product_grouping_c as grouping, billing_currency_c as billing_currency FROM ps_products INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c) WHERE (ps_products.`type` != 'Service') and ps_products.deleted != 1 order by name asc";
$ps_products = mysql_query($query_ps_products, $sugar) or die(mysql_error());
$row_ps_products = mysql_fetch_assoc($ps_products);
$totalRows_ps_products = mysql_num_rows($ps_products);

mysql_select_db($database_sugar, $sugar);
$query_service_ps_products = "SELECT ps_products.name,ps_products.`type`,ps_products.price,ps_products_cstm.product_grouping_c as grouping, billing_currency_c as billing_currency FROM ps_products INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c) WHERE (ps_products.`type` = 'Service') and (ps_products.deleted != 1) and (ps_products_cstm.product_grouping_c = 'Service')  order by name asc";
$service_ps_products = mysql_query($query_service_ps_products, $sugar) or die(mysql_error());
$row_service_ps_products = mysql_fetch_assoc($service_ps_products);
$totalRows_service_ps_products = mysql_num_rows($service_ps_products);

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
<title>Proforma/ Tax Invoice</title>
<script language="javascript" type="text/javascript" src="jquery.js"></script>
<script language="javascript">
function contentpulse(urlpage,container)
{
$.ajax({
url : urlpage,
success : function (data) {
$("#" + container).html(data);
}
});
}


</script>
<style type="text/css">
<!--
.style11 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
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
	width:200px;
}
.style14 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #000000; font-weight: bold; }
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
.style111 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:220px;
}
.style111 {	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	
}
.paymenthidden
{
display:none;
}
.paymentshow
{
display:block;
}
.style112 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style112 {	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style113 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style113 {	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style114 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style114 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style115 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style115 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style116 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style116 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style117 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style117 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}

.border  {border-color:#CCCCCC;

}
.style118 {
	color: #FFFFFF;
	font-size: 10px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
.style119 {color: #FFFFFF}
-->
</style>

<script language="javascript" type="text/javascript">
function paymentdiv(data){

if(data=='TAX INVOICE'){

document.getElementById('payment').className = 'paymentshow';
document.getElementById('accountname').className = 'paymentshow';
document.getElementById('client').className = 'paymenthidden';
}else{
document.getElementById('payment').className = 'paymenthidden';
document.getElementById('accountname').className = 'paymenthidden';
document.getElementById('client').className = 'paymentshow';
contentpulse('fetchinfo.php','fetchinfo');
}
}
</script>
</head>
<body>

<table width="100%" border="0" cellspacing="0">
  <tr>
    <td rowspan="2"><img src="images/logo.jpg" alt="4" width="233" height="39" /></td>
    <td align="right" valign="bottom" bgcolor="#FFFFFF"><span class="style11 style20"><span class="style21">Your Logged in as:</span> <?php echo $row_users['first_name']; ?> <?php echo $row_users['last_name']; ?></span></td>
  </tr>
  <tr>
    <td align="right" valign="bottom" bgcolor="#FFFFFF"><table height="25" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><div class="chromestyle">
            <ul>
              <li><a href="index.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Home&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="cashup_report.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Payments&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
              <li><a href="rates.php"><img src="images/link_left.jpg" alt="t" align="absmiddle" style="border:0px;" />&nbsp;Set Rate&nbsp;<img src="images/link_right.jpg" alt="t" align="absmiddle" style="border:0px;" /></a></li>
                  <li><a href="perfoma.php"><img src="images/link_left.jpg" align="absmiddle" style="border:0px;" />&nbsp;Proforma Invoice&nbsp;<img src="images/link_right.jpg" align="absmiddle" style="border:0px;" /></a></li>
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
    <td colspan="2" valign="top"><br /></td>
  </tr>
</table>
<form id="form1" name="form1" method="post" action="perfoma.php?action=generate">
  <table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
    <tr>
      <th width="155" align="left" valign="top" class="style14" scope="row">Set Invoice Type</th>
      <td width="236" align="left"><label>
      <select name="invoice_type" class="style11" id="invoice_type" onchange="javascript:paymentdiv(this.value);">
        <option value="PROFORMA INVOICE" selected="selected">PROFORMA INVOICE</option>
        <option value="TAX INVOICE">TAX INVOICE</option> 
      </select>
       <input type="hidden" name="user" id="user" value = "<?php echo $row_users['first_name']." ".$row_users['last_name'] ; ?>" />
      </label>      </td>
    </tr>
    <tr>
      <th width="155" align="left" valign="top" class="style14" scope="row">Client Name</th>
      <td width="236" align="left"><label>
       <div id="client" class="paymentshow"> <input name="client_name" type="text" class="style11" id="client_name" /></div>
        <div id="accountname" class="paymenthidden"><?		
		echo display_accounts_dropdown(''); ?></div> 
      </label></td>
    </tr>
    <tr>
      <th align="left" valign="top" class="style14" scope="row">Contact Person</th>
      <td align="left"><input name="contact_person" type="text" class="style11" id="contact_person" /></td>
    </tr>
    <tr>
      <th align="left" valign="top" class="style14" scope="row">Client Address</th>
      <td align="left"><label>
        <textarea name="address" cols="37" rows="3" class="style11" id="address"></textarea>
      </label></td>
    </tr>
    <tr>
      <th colspan="2" align="left" valign="top" class="style14" scope="row">
      <div id="fetchinfo">
      <table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
   <tr>
    <th colspan="1" align="left" valign="top" bgcolor="#0000FF" class="style14 style119" scope="row">Invoice Currency</th>
    <td align="left">
    <select name="client_currency" class="style112" id="client_currency">
    	<option value="USD" selected="selected">US Dollars</option>
    	<option value="UGX">UG Shillings</option>
    </select></label>
    </td>
  </tr>
  <tr>
    <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style119" scope="row">Internet/Data</th>
    </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Period Start</th>
    <td align="left"><input name="start_date" class="style112" id="start_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Period End</th>
    <td align="left"><input name="end_date" class="style113" id="end_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
  </tr>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
    <td align="left"><select name="package" class="style111" id="package">
      <option value="Standard(with outright CPE purchase)#0#Rental Fees#USD">Standard(with outright CPE purchase)</option>
      <option value="Advanced#0#Rental Fees#USD">Advanced</option>
      <option value="CIR Internet#0#Rental Fees#USD">CIR Internet</option>
      <option value="Monthly Equipment Rental#10#Rental Fees#USD" selected="selected">Standard</option>
      <option value="Monthly Equipment Rental [New]#25#Rental Fees#USD" selected="selected">Standard [New]</option>
      <option value="Dark fibre (1)#5000#Rental Fees#USD">Dark fibre (1)</option>
      <option value="No Package Selection#0#Rental Fees#USD">No package Selection</option>
    </select><br />
    <label>Quantity
    <input name="p_quantity" type="text" size="4" maxlength="5" class="style11" id="p_quantity" value="1" />
    </label><br />
    <label>% Discount
    <input name="p_discount" type="text" size="4" maxlength="5" class="style11" id="p_discount" value="0.00" />
    </label>    </td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Service</th>
    <td align="left"><select name="service" class="style111" id="service">
      <?php
do {  
?>
      <option value="<?php echo $row_service_ps_products['name']."#".$row_service_ps_products['price']."#". $row_service_ps_products['type']."#".$row_service_ps_products['billing_currency'];?>"><?php echo $row_service_ps_products['name'];?></option>
      <?php
} while ($row_service_ps_products = mysql_fetch_assoc($service_ps_products));
  $rows = mysql_num_rows($service_ps_products);
  if($rows > 0) {
      mysql_data_seek($service_ps_products, 0);
	  $row_service_ps_products = mysql_fetch_assoc($service_ps_products);
  }
?>
    </select>
    <br />
    <label>Quantity
    <input name="b_quantity" type="text" size="4" maxlength="5" class="style11" id="b_quantity" value="1" />
    </label><br />
    <label>% Discount
    <input name="b_discount" type="text" size="4" maxlength="5" class="style11" id="b_discount" value="0.00" />
    </label>    </td>
  </tr>
</table>
      <p>&nbsp;</p>
      <table width="407" border="0" align="center" cellpadding="2" cellspacing="2" class="border">
        <tr>
          <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style118" scope="row">Web Hosting</th>
        </tr>
        <tr>
          <th align="left" valign="top" class="style14" scope="row">Start Date</th>
          <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
              <input  name="start_date_web_hosting" class="style112" id="start_date_web_hosting" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th align="left" valign="top" class="style14" scope="row">End Date</th>
          <td align="left"><input  name="end_date_web_hosting" class="style112" id="end_date_web_hosting" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
          <td align="left"><select name="package_web_hosting" class="style111" id="package_web_hosting">
            <option value="" selected="selected"></option>
            <option value="Web Hosting (UGX)#35000#Web Hosting#UGX">Web Hosting (UGX)</option>
            <option value="Web Hosting (USD)#19#Web Hosting#USD">Web Hosting (USD)</option>
                    </select>
            <br />
              <label>Quantity
                <input  name="p_quantity_web_hosting" type="text" size="4" maxlength="5" class="style114" id="p_quantity_web_hosting" value="" />
              </label>
              <br />
              <label>% Discount
                <input  name="p_discount_web_hosting" type="text" size="4" maxlength="5" class="style114" id="p_discount_web_hosting" />
              </label>          </td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="407" border="0" align="center" cellpadding="2" cellspacing="2">

        <tr>
          <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style119" scope="row">Domain Hosting</th>
        </tr>

        <tr>
          <th align="left" valign="top" class="style14" scope="row">Start Date</th>
          <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
              <input  name="start_date_dom_hosting" class="style112" id="start_date_dom_hosting" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th align="left" valign="top" class="style14" scope="row">End Date</th>
          <td align="left"><input  name="end_date_dom_hosting" class="style112" id="end_date_dom_hosting" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
          <td align="left"><select name="package_dom_hosting" class="style111" id="package_dom_hosting">
            <option value="" selected="selected"></option>
            <option value="Domain Hosting (UGX)#3333.33#Domain Hosting#UGX">Domain Hosting (UGX)</option>
            <option value="Domain Hosting (USD)#1.83#Domain Hosting#USD">Domain Hosting (USD)</option>
                    </select>
            <br />
              <label>Quantity
                <input  name="p_quantity_dom_hosting" type="text" size="4" maxlength="5" class="style115" id="p_quantity_dom_hosting" />
              </label>
              <br />
              <label>% Discount
                <input  name="p_discount_dom_hosting" type="text" size="4" maxlength="5" class="style115" id="p_discount_dom_hosting" />
              </label>          </td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="407" border="0" align="center" cellpadding="2" cellspacing="2">

        <tr>
          <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style119" scope="row">Domain Registration</th>
        </tr>

        <tr>
          <th align="left" valign="top" class="style14" scope="row">Start Date</th>
          <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
              <input  name="start_date_dom_reg" class="style112" id="start_date_dom_reg" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th align="left" valign="top" class="style14" scope="row">End Date</th>
          <td align="left"><input  name="end_date_dom_reg" class="style112" id="end_date_dom_reg" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
          <td align="left"><select name="package_dom_reg" class="style111" id="package_dom_reg">
            <option value="" selected="selected"></option>
            <option value="Domain Registration (UGX)#6666.67#Domain Registration#UGX">Domain Registration (UGX)</option>
            <option value="Domain Registration (UGX)#3.75#Domain Registration#USD">Domain Registration (USD)</option>
                    </select>
            <br />
              <label>Quantity
                <input  name="p_quantity_dom_reg" type="text" size="4" maxlength="5" class="style116" id="p_quantity_dom_reg" />
              </label>
              <br />
              <label>% Discount
                <input  name="p_discount_dom_reg" type="text" size="4" maxlength="5" class="style116" id="p_discount_dom_reg" />
              </label>          </td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
          <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style119" scope="row">Email Hosting</th>
        </tr>

        <tr>
          <th align="left" valign="top" class="style14" scope="row">Start Date</th>
          <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
              <input  name="start_date_email" class="style112" id="start_date_email" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th align="left" valign="top" class="style14" scope="row">End Date</th>
          <td align="left"><input  name="end_date_email" class="style112" id="end_date_email" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
          <td align="left"><select name="package_email" class="style111" id="package_email">
            <option value="" selected="selected"></option>
            <option value="Virtual Email Hosting (0-10) UGX#5000#Mail Hosting#USD">Virtual Email Hosting (0-10) UGX</option>
            <option value="Virtual Email Hosting (0-10) USD#3#Mail Hosting#USD">Virtual Email Hosting (0-10) USD</option>
              <option value="Virtual Email Hosting (11-20) UGX#4000#Mail Hosting#USD">Virtual Email Hosting (11-20) UGX</option>
            <option value="Virtual Email Hosting (11-20) USD#3#Mail Hosting#USD">Virtual Email Hosting (11-20) USD</option>
              <option value="Virtual Email Hosting (20 Plus) UGX#3000#Mail Hosting#USD">Virtual Email Hosting (20 Plus) UGX</option>
            <option value="Virtual Email Hosting (20 Plus) USD#2#Mail Hosting#USD">Virtual Email Hosting (20 Plus) USD</option>
                    </select>
            <br />
              <label>Quantity
                <input  name="p_quantity_email" type="text" size="4" maxlength="5" class="style117" id="p_quantity_email" />
              </label>
              <br />
              <label>% Discount
                <input  name="p_discount_email" type="text" size="4" maxlength="5" class="style117" id="p_discount_email" />
              </label>          </td>
        </tr>
      </table>
      <br />
      <br />
      <br />
      <br />
      <table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
        <tr>
          <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style119" scope="row">High Purchase</th>
        </tr>
        <tr>
          <th align="left" valign="top" class="style14" scope="row">Start Date</th>
          <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
            <input  name="start_date_lease" class="style112" id="start_date_lease" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th align="left" valign="top" class="style14" scope="row">End Date</th>
          <td align="left"><input  name="end_date_lease" class="style112" id="end_date_lease" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
        </tr>
        <tr>
          <th width="155" align="left" valign="top" class="style14" scope="row">Equipment</th>
          <td align="left"><select name="package_lease" class="style111" id="package_email2">
            <option value="" selected="selected"></option>
            <option value="Lease/Hire (Equipment/Connection) [LOW]#1000#Hire Purchase#USD">Lease/Hire (Equipment/Connection) [LOW]</option>
          </select>
            <br />
            <label>Quantity
              <input  name="p_quantity_lease" type="text" size="4" maxlength="5" class="style117" id="p_quantity_lease" />
            </label>
            <br />
            <label>% Discount
              <input  name="p_discount_lease" type="text" size="4" maxlength="5" class="style117" id="p_discount_lease" />
            </label></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      </div></th>
    </tr>
    <tr>
      <th align="left" valign="top" class="style14" scope="row">One time Charges</th>
      <td align="left"><select name="charges[]" size="10" multiple="multiple" class="style11" id="charges[]">
        <?php
		 // print_r($row_ps_products);
do {  
?>
        <option value="<?php echo $row_ps_products['name'].'#'.$row_ps_products['price'].'#'.$row_ps_products['type'].'#'.$row_ps_products['grouping'].'#'.$row_service_ps_products['billing_currency']?>"><?php echo $row_ps_products['name']?></option>
        <?php
} while ($row_ps_products = mysql_fetch_assoc($ps_products));
  $rows = mysql_num_rows($ps_products);
  if($rows > 0) {
      mysql_data_seek($ps_products, 0);
	  $row_ps_products = mysql_fetch_assoc($ps_products);
  }
?>
      </select>
      <br />
      <br /></td>
    </tr>
    <tr>
      <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style119" scope="row">Discount/Waiver For One Time Charges </th>
    </tr>
    <tr>
      <th align="left" valign="top" class="style14" scope="row">Discount/Waiver on Access Point Fees in %</th>
      <td align="left"><? echo display_products_dropdown('Access Point Fees'); ?> <input name="<? echo 'Access Point Fees'; ?>value" size="6" class="style11" type="text" id="<? echo 'Access Point Fees'; ?>value" value="0" /></td>
    </tr>
    <tr>
      <th align="left" valign="top" class="style14" scope="row">Discount/Waiver on Equipment Deposits in %</th>
      <td align="left"><? echo display_products_dropdown('Equipment Deposits'); ?> <input name="<? echo 'Equipment Deposits'; ?>value" size="6" class="style11" type="text" id="<? echo 'Equipment Deposits'; ?>value" value="0" /></td>
    </tr>
    <tr>
      <th align="left" valign="top" class="style14" scope="row">Discount/Waiver on Connection Fees in %</th>
      <td align="left"><? echo display_products_dropdown('Connection Fees'); ?> <input name="<? echo 'Connection Fees'; ?>value" size="6" class="style11" type="text" id="<? echo 'Connection Fees'; ?>value" value="0" /></td>
    </tr>
    <tr>
      <td align="left" valign="top" class="style14" colspan="2">
      <div id="payment" class="paymenthidden">
      <table width="100%" border="0" align="left" cellpadding="2" cellspacing="1">
    
    <tr>
      <td colspan="2" background="images/table_header2.jpg" class="style14 style2">Generate Payment<span class="style2"></span></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Entry ID</td>
      <td><label>
        <input name="reciept" type="text" class="style1" id="reciept" value="<?php echo generateRecieptNo($_GET[entry_id]); ?>" size="8" />
      </label></td>
    </tr>
    <tr>
      <td valign="top" bgcolor="#CCCCCC" class="style4">Enter Payment</td>
      <td valign="top"><input name="payment" type="text" class="style11" id="payment"  /></td>
    </tr>
    <tr>
      <td valign="top" bgcolor="#CCCCCC" class="style4">Currency</td>
      <td valign="top"><select name="currency" class="style11" id="currency">
        <option value="" selected="selected">Select Currency</option>
        <option value="UGX">Uganda Shillings</option>
        <option value="USD">US Dollars</option>
	  </select><br>
        <span class="style1">Today's Dollar Rate is: <?php echo get_rate(date('Y-m-d')); ?> </span></td>
    </tr>
    <tr>
      <td width="24%" valign="top" bgcolor="#CCCCCC" class="style4">Payment Mode</td>
      <td width="76%" valign="top"><label>
        <select name="payment_type" class="style11" id="payment_type">
          <option value="Cash">Cash</option>
          <option value="Cheque">Cheque</option>
          <option value="" selected="selected">Select Payment Mode</option>
        </select>
      </label></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Other Details</td>
      <td>Cheaque Num:<br>
          <input name="cheque" type="text" class="style11" id="cheque" />        </td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Other Details</td>
      <td>
        Bank Name:<br>
          <input name="bank" type="text" class="style11" id="bank" />        </td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Being Payment for</td>
      <td valign="top"><label>
        <textarea name="payment_details" cols="30" rows="3" class="style11" id="payment_details"></textarea>
      </label></td>
    </tr>
  </table>
  </div></td>
    </tr>
    <tr>
      <th align="left" valign="top" class="style14" scope="row">&nbsp;</th>
      <td align="left">&nbsp;</td>
    </tr>
    <tr>
      <th align="right" scope="row"><label>
        <input name="Generate Perfoma Invoice" type="submit" class="style14" id="Generate Invoice" value="Generate Invoice" />
      </label></th>
      <td align="left"><label>
        <input name="clear" type="reset" class="style14" id="clear" value="Clear Data" />
      </label></td>
    </tr>
  </table>
</form>

</body>
</html>
<?php
mysql_free_result($ps_products);

mysql_free_result($service_ps_products);

mysql_free_result($users);
}
?>
