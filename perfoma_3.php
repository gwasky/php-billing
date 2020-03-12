<?php require_once('../Connections/sugar.php'); ?>
<?php require_once('../Connections/sugar.php'); ?>
<?php require_once('../Connections/sugar.php'); 
//MX Widgets3 include
require_once('../includes/wdg/WDG.php');
require_once('control.php');

if($_GET['action'] == 'generate'){
	$invoice_data = generate_perfoma_invoice();
	echo display_invoice_data($invoice_data);
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

mysql_select_db($database_sugar, $sugar);
$query_products = "SELECT name,price,type FROM products WHERE deleted = '0' AND type != 'Service'";
$products = mysql_query($query_products, $sugar) or die(mysql_error());
$row_products = mysql_fetch_assoc($products);
$totalRows_products = mysql_num_rows($products);
$query_products = "SELECT name,price,type FROM products WHERE deleted = '0' AND type != 'Service'";
$products = mysql_query($query_products, $sugar) or die(mysql_error());
$row_products = mysql_fetch_assoc($products);
$totalRows_products = mysql_num_rows($products);

mysql_select_db($database_sugar, $sugar);
$query_service_products = "SELECT name,price,type FROM products WHERE deleted = '0' AND type = 'Service' AND name != 'Monthly Equipment Rental'";
$service_products = mysql_query($query_service_products, $sugar) or die(mysql_error());
$row_service_products = mysql_fetch_assoc($service_products);
$totalRows_service_products = mysql_num_rows($service_products);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
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
</head>

<body>
<form id="form1" name="form1" method="post" action="perfoma_3.php?action=generate">
<table width="407" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <th width="155" align="left" valign="top" scope="row">Client Name</th>
    <td width="236" align="left"><label>
      <input name="client_name" type="text" id="client_name" size="40" />
    </label></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Contact Person</th>
    <td align="left"><input name="contact_person" type="text" id="contact_person" size="40" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Client Address</th>
    <td align="left"><label>
      <textarea name="address" id="address" cols="37" rows="3"></textarea>
    </label></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Period Start</th>
    <td align="left"><input name="start_date" class="style11" id="start_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">Period End</th>
    <td align="left"><input name="end_date" class="style11" id="end_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">&nbsp;</th>
    <td align="left">&nbsp;</td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row">One time Charges</th>
    <td align="left"><label>
      <select name="charges[]" size="10" multiple="multiple" class="style1" id="charges[]">
        <?php
		 // print_r($row_products);
do {  
?>
        <option value="<?php echo $row_products['name'];?>#<?php echo $row_products['price'];?>#<?php echo $row_products['type'];?>"><?php echo $row_products['name']?></option>
        <?php
} while ($row_products = mysql_fetch_assoc($products));
  $rows = mysql_num_rows($products);
  if($rows > 0) {
      mysql_data_seek($products, 0);
	  $row_products = mysql_fetch_assoc($products);
  }
?>
      </select>
    </label></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row"> Package</th>
    <td align="left"><label>
            <select name="package" id="package">
              <option value="Standard(with outright CPE purchase)#0">Standard(with outright CPE purchase)</option>
              <option value="Advanced#0">Advanced</option>
              <option value="CIR Internet#0">CIR Internet</option>
              <option value="Monthly Equipment Rental#10" selected="selected">Standard</option>
          </select>

      </label></td>
  </tr>
  <tr>
    <th align="left" valign="top" scope="row"> Service</th>
    <td align="left"><label>
      <select name="service" id="service">
        <?php
do {  
?>
        <option value="<?php echo $row_service_products['name']?>#<?php echo $row_service_products['price']?>#<?php echo $row_service_products['type']?>"><?php echo $row_service_products['name']?></option>
        <?php
} while ($row_service_products = mysql_fetch_assoc($service_products));
  $rows = mysql_num_rows($service_products);
  if($rows > 0) {
      mysql_data_seek($service_products, 0);
	  $row_service_products = mysql_fetch_assoc($service_products);
  }
?>
      </select>
    </label></td>
  </tr>
  <tr>
    <th align="right" scope="row"><label>
      <input type="submit" name="Generate Perfoma Invoice" id="Generate Perfoma Invoice" value="Generate Perfoma Invoice" />
    </label></th>
    <td align="left"><label>
      <input type="reset" name="clear" id="clear" value="Clear Data" />
    </label></td>
  </tr>
</table>
</form>
</body>
</html>
<?php
mysql_free_result($products);
mysql_free_result($service_products);

}
?>
