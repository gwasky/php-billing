<?php require_once('../Connections/sugar.php'); ?>
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

//error_reporting(0);
$username = $_GET['preferred_username_c'];

mysql_select_db($database_sugar, $sugar);
$query_statement = "SELECT * FROM acc_statement WHERE username = '$username' ";
$statement = mysql_query($query_statement, $sugar) or die(mysql_error());
$row_statement = mysql_fetch_assoc($statement);
$totalRows_statement = mysql_num_rows($statement);


mysql_select_db($database_sugar, $sugar);
$query_cst_details = "SELECT accounts_cstm.crn_c, accounts.name,   accounts_cstm.preferred_username_c, contracts.start_date,   contracts.expiry_date, contracts.billing_date, contracts.`status`, accounts_cstm.billing_add_plot_c,   accounts_cstm.billing_add_town_c,   accounts_cstm.billing_add_area_c,   accounts_cstm.billing_add_strt_c,accounts_cstm.billing_add_district_c FROM accounts  INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)  INNER JOIN contracts ON (accounts.id=contracts.account_id)  INNER JOIN products ON (accounts_cstm.download_bandwidth_c=products.name) WHERE accounts.deleted = '0' AND contracts.deleted = '0' AND products.deleted = '0'";
$cst_details = mysql_query($query_cst_details, $sugar) or die(mysql_error());
$row_cst_details = mysql_fetch_assoc($cst_details);
$totalRows_cst_details = mysql_num_rows($cst_details);

mysql_select_db($database_sugar, $sugar);
$query_payments = "SELECT username, SUM(amount) FROM payment_history WHERE username = '$username' AND flag != '1' AND payment_for = 'Service' GROUP BY username";
$payments = mysql_query($query_payments, $sugar) or die(mysql_error());
$row_payments = mysql_fetch_assoc($payments);
$totalRows_payments = mysql_num_rows($payments);

mysql_select_db($database_sugar, $sugar);
$query_adjustments = "SELECT username, SUM(amount) FROM adjustment_history WHERE username = '$username' AND flag != '1' GROUP BY username";
$adjustments = mysql_query($query_adjustments, $sugar) or die(mysql_error());
$row_adjustments = mysql_fetch_assoc($adjustments);
$totalRows_adjustments = mysql_num_rows($adjustments);

mysql_select_db($database_sugar, $sugar);
$query_products = "SELECT name, price FROM products";
$products = mysql_query($query_products, $sugar) or die(mysql_error());
$row_products = mysql_fetch_assoc($products);
$totalRows_products = mysql_num_rows($products);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>

<script language="javascript" type="text/javascript">
function optionclick(obj){
if(obj == 'adjust'){
document.getElementById('payments').value = '<?php echo $row_statement['payments']; ?>';
document.getElementById('payments').readOnly = true;
document.getElementById('reciept').readOnly = true;
document.getElementById('adjustments').readOnly = false;
document.getElementById('request').disabled  = false;
document.getElementById('adjustments').value = '';
}

if(obj == 'pay'){
document.getElementById('adjustments').value = '<?php echo $row_statement['adjustments']; ?>';
document.getElementById('adjustments').readOnly = true;
document.getElementById('payments').readOnly = false;
document.getElementById('reciept').readOnly = false;
document.getElementById('request').disabled = true;
document.getElementById('payments').value = '';
}

}
</script>

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
.style3 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #0C1F86; font-weight: bold; }
.style4 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #FFFFFF; font-weight: bold; }
.style16 {color: #071F89}
-->
</style>
</head>
<?php 

		 
$payments = $row_payments['SUM(amount)'];
$adjustments = $row_adjustments['SUM(amount)']; 
$previous = $row_statement['prev_bal'];
$monthly = $row_cst_details['price'];
$amount_payable = $previous - $payments - $adjustments + $monthly;



   if ($_POST['RadioGroup1']=='New Entry')
{


$today = date('Y-m-d');  

$adjustments2 = $_POST['adjustments'];
$month = date("F");
$year = date("Y"); 
$payments22 = $_POST['payments'];
$type = $_POST['request'];
$reciept = $_POST['reciept'];
$payment_type = $_POST['payment_type'];
$cheque_num = $_POST['cheque_num'];
$billing_date = $row_cst_details['billing_date'];
$status = $_POST['status'];


require_once('../Connections/sugar.php');
$products = $_POST['payments'];
//do{
      //foreach($products as $product){
      //	$getquery = "select price from products where name = $product";
	//	$getquery_result = mysql_query(getquery);

$sql = "INSERT INTO acc_statement (username, prev_bal, status, billing_date, date_entered) VALUES ('$username','$previous','$status','$billing_date','$today')";
$result = mysql_query($sql);

$sql2 = "INSERT INTO adjustment_history (username, prev_bal, amount, adjustment_date, month, year, type, billing_date) VALUES ('$username','$previous','$adjustments2','$today', '$month', '$year', '$type','$billing_date')";
$result2 = mysql_query($sql2);


$payments_2 = $_POST['payments_2'];
mysql_select_db($database_sugar, $sugar);
foreach ($payments_2 as $payments_2){
$product = $payments_2;
$getquery = "select price from products where name = '$product'";
$getquery_result = mysql_query($getquery);
$getquery_row = mysql_fetch_array($getquery_result);
$price = $getquery_row['price'];
$actual = $payments22 - $price;
$sql3 = "INSERT INTO payment_history (username, prev_bal, amount, reciept, payment_type, cheque, payment_date, month, year, billing_date, payment_for,price,actual) VALUES ('$username','$previous','$payments22','$reciept','$payment_type','$cheque_num','$today','$month', '$year', '$billing_date','$payments_2','$price','$actual')";
$result3 = mysql_query($sql3);
}

//}
//while($getquery_result = mysql_query(getquery));

echo "<script> parent.location = 'cst_view.php'; </script>";

}



if ($_POST['RadioGroup1']=='Adjustment')
{
echo $_POST['RadioGroup1'];

$adjustments2 = $_POST['adjustments'];
$today = date('Y-m-d');
$month = date("F");
$year = date("Y"); 
$adjustments = $row_adjustments['SUM(amount)'];
$type = $_POST['request'];
$billing_date = $row_cst_details['billing_date'];


require_once('../Connections/sugar.php');
$sql = "INSERT INTO adjustment_history (username, prev_bal, amount, adjustment_date, month, year, type, billing_date) VALUES ('$username','$amount_payable','$adjustments2','$today', '$month', '$year', '$type','$billing_date')";
$result = mysql_query($sql);

$sql2 = "UPDATE acc_statement SET prev_bal_t = '$amount_payable' WHERE username = '$username'";
$result2 = mysql_query($sql2);

echo "<script> parent.location = 'cst_view.php'; </script>";

}

if ($_POST['RadioGroup1']=='Payment')
{
echo $_POST['RadioGroup1'];


$reciept = $_POST['reciept'];
$payments2 = $_POST['payments'];
$payments_2 = $_POST['payments_2'];
$payments_3 = $_GET['payments_2'];
$payment_type = $_POST['payment_type'];
$previous = $row_statement['prev_bal'];
$cheque_num = $_POST['cheque_num'];
$today = date('Y-m-d');
$month = date("F");
$year = date("Y");
$billing_date = $row_cst_details['billing_date'];

require_once('../Connections/sugar.php');

$sql = "INSERT INTO payment_history (username, prev_bal, amount, reciept, payment_type, cheque, payment_date, month, year, billing_date,payment_for) VALUES ('$username','$amount_payable','$payments2','$reciept','$payment_type','$cheque_num','$today','$month', '$year','$billing_date','$payments_2')";
$result = mysql_query($sql);

$sql2 = "UPDATE acc_statement SET prev_bal_t = '$amount_payable' WHERE username = '$username'";
$result2 = mysql_query($sql2);



echo "<script> parent.location = 'cst_view.php'; </script>";

}

?>
<body>

<table width="100%" border="0">
  <tr>
    <td><img src="images/logo.jpg" alt="4" width="233" height="39" /></td>
    <td align="right" valign="bottom" bgcolor="#FFFFFF"><span class="style14 style16"><a href="index.php">Home</a> | Payments | Adjustments |<a href="invoice_view.php"> Invoices</a> | Bill Printing</span></td>
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
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Package</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Username</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Status</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style15"></span></td>
      </tr>
      <?php do { ?>
      <tr>
        <td><span class="style1"><?php echo $row_cst_details['crn_c']; ?></span></td>
        <td class="style1"><?php echo $row_cst_details['cpe_type_c']; ?></td>
        <td class="style1"><?php echo $row_cst_details['name']; ?></td>
        <td class="style1"><?php echo $username; ?></td>
        <td class="style1"><?php echo $row_cst_details['status']; ?></td>
        <td>&nbsp;</td>
      </tr>
      <?php } while ($row_cst_details = mysql_fetch_assoc($cst_details)); ?>
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
        <td width="56%" class="style1"><?php echo $row_cst_details['price']; ?></td>
      </tr>
      <tr>
        <td class="style14">Start Date</td>
        <td class="style1"><?php echo $row_cst_details['start_date']; ?></td>
      </tr>
      <tr>
        <td class="style14">Expiry Date</td>
        <td class="style1"><?php echo $row_cst_details['expiry_date']; ?></td>
      </tr>
      <tr>
        <td class="style14">Billing Date</td>
        <td class="style1"><?php echo $row_cst_details['billing_date']; ?></td>
      </tr>

    </table></td>
    <td>&nbsp;</td>
  </tr>
</table>
<form id="form1" name="form1" method="post" action="cst_transaction.php?preferred_username_c=<?php echo $username; ?>">
  <table width="100%" border="0" align="left" cellpadding="2" cellspacing="1">
    <tr>
      <td colspan="4" align="center" valign="top" style="background-image:url(images/table_header.jpg); background-repeat:repeat-x;"><label></label>
        <p>
          <label>
            <input type="radio" name="RadioGroup1" class="style1" value="New Entry" id="new_entry" />
            <span class="style14">New Entry</span></label>
         
          <label>
            <input type="radio" name="RadioGroup1" class="style1" value="Adjustment" id="adjust" onclick="optionclick('adjust');" />
            <span class="style14">Adjustment</span></label>
          
          <label>
            <input type="radio" name="RadioGroup1" class="style1" value="Payment" id="pay" onclick="optionclick('pay');" />
            <span class="style14">Payment</span></label>
            
                      <label></label>
        </p>
      <label></label></td>
    </tr>
    <tr>
      <td colspan="2" background="images/table_header2.jpg" class="style14 style2">Payments<span class="style2"></span></td>
      <td colspan="2" background="images/table_header2.jpg" class="style4">Adjustments<span class="style2"></span></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Reciept Number</td>
      <td><label>
        <input name="reciept" type="text" class="style1" id="reciept" value="N/A" size="5" />
      </label></td>
      <td width="15%">&nbsp;</td>
      <td width="34%">&nbsp;</td>
    </tr>
    <tr>
      <td width="13%" height="93" bgcolor="#CCCCCC" class="style4">Payments</td>
      <td width="38%"><label>
        <input name="payments" type="text" class="style1" id="payments"  />
        <select name="payments_2[]" size="5" multiple="multiple" class="style1" id="payments_2[]">
          <?php
do {  
?>
          <option value="<?php echo $row_products['name']?>-<?php $row_products['price']?>"><?php echo $row_products['name']?>-<?php echo $row_products['price']?></option>
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
      <td bgcolor="#CCCCCC" class="style4">Adjustment Amount</td>
      <td><input name="adjustments" type="text" class="style1" id="adjustments" />
        <select name="request" class="style1" id="request">
          <option value="N/A" selected="selected">N/A</option>
          <option value="Waiver">Waiver</option>
          <option value="Credit Note">Credit Note</option>
          <option value="Debit Note">Debit Note</option>
          <option value="Reversal">Reversal</option>
          <option value="Other">Other</option>
          </select></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Payment Type</td>
      <td><label>
        <select name="payment_type" class="style1" id="payment_type">
          <option value="Cash">Cash</option>
          <option value="Cheque">Cheque</option> 
          <option value="None" selected="selected">None</option>
        </select>
      </label></td>
      <td>&nbsp;</td>
      <td><label></label></td>
    </tr>
    <tr>
      <td bgcolor="#CCCCCC" class="style4">Cheque Number</td>
      <td>
      <input type="hidden" name="billing_start" value = "<?php echo $row_cst_details['start_date']; ?>" />
      <input type="hidden" name="billing_expiry" value = "<?php echo $row_cst_details['expiry_date']; ?>" />
      <label>
        <input name="cheque_num" type="text" class="style1" id="cheque_num" value="N/A" />
      </label></td>
      <td>&nbsp;</td>
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
        <input name="button" type="submit" class="style1" id="button" value="Transact" />
      </label></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><label></label></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
  

</form>

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<table width="100%" border="0" cellspacing="0">
  <tr>
    <td class="style14"><span class="style3">Statement</span></td>
    <td class="style14">&nbsp;</td>
    <td class="style14">&nbsp;</td>
    <td class="style14">&nbsp;</td>
    <td class="style14">&nbsp;</td>
    <td class="style14">&nbsp;</td>
    <td class="style14">&nbsp;</td>
  </tr>
  <tr>
    <td width="15%" background="images/table_header.jpg" class="style14">Username</td>
    <td width="15%" background="images/table_header.jpg" class="style14">Previous Balance</td>
    <td width="7%" background="images/table_header.jpg" class="style14">Payments</td>
    <td width="9%" background="images/table_header.jpg" class="style14">Adjustments</td>
    <td width="13%" background="images/table_header.jpg" class="style14">Monthly Charge</td>
    <td width="12%" background="images/table_header.jpg" class="style14">Amount Payable Exc VAT</td>
    <td width="17%" background="images/table_header.jpg" class="style14">Billing Date</td>
  </tr>
  <?php do { ?>
    <tr>
      <td class="style1"><?php echo $row_statement['username']; ?></td>
      <td class="style1"><?php echo $row_statement['prev_bal']; ?></td>
      <td class="style1"><?php echo $row_payments['SUM(amount)']; ?></td>
      <td class="style1"><?php echo $row_adjustments['SUM(amount)']; ?></td>
      <td class="style1"><?php echo $row_cst_details['price']; ?></td>
      <td class="style1"><?php echo $amount_payable; ?></td>
      <td class="style1"><?php echo $row_statement['billing_date']; ?></td>
    </tr>
    <?php } while ($row_statement = mysql_fetch_assoc($statement)); ?>
</table>

</body>
</html>
<?php
mysql_free_result($statement);

mysql_free_result($cst_details);

mysql_free_result($payments);

mysql_free_result($adjustments);

mysql_free_result($products);
?>
