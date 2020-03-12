<?php require_once('../Connections/sugar.php'); ?>
<?php
//error_reporting(1);

$today = date('Y-m-d');

mysql_select_db($database_sugar, $sugar);
$query_statement = "SELECT * FROM acc_statement";
$statement = mysql_query($query_statement, $sugar) or die(mysql_error());
$row_statement = mysql_fetch_assoc($statement);
$totalRows_statement = mysql_num_rows($statement);

do {

$username = $row_statement['username'];

mysql_select_db($database_sugar, $sugar);
$query_cst_details = "SELECT accounts_cstm.crn_c, accounts_cstm.cpe_type_c, accounts.name,accounts_cstm.preferred_username_c,   contracts.start_date, contracts.expiry_date, contracts.billing_date, contracts.`status`, products.name, products.price, accounts_cstm.billing_add_plot_c, accounts_cstm.billing_add_town_c, accounts_cstm.billing_add_area_c,   accounts_cstm.billing_add_strt_c, accounts_cstm.billing_add_district_c FROM accounts INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)  INNER JOIN contracts ON (accounts.id=contracts.account_id) INNER JOIN products ON (accounts_cstm.download_bandwidth_c=products.name) WHERE accounts.deleted = '0' AND contracts.deleted = '0' AND products.deleted = '0' AND accounts_cstm.preferred_username_c = '$username'";
$cst_details = mysql_query($query_cst_details, $sugar) or die(mysql_error());
$row_cst_details = mysql_fetch_assoc($cst_details);
$totalRows_cst_details = mysql_num_rows($cst_details);

mysql_select_db($database_sugar, $sugar);
$query_payments = "SELECT username, SUM(amount) FROM payment_history WHERE username = '$username' AND billing_date = '$today' GROUP BY username ";
$payments = mysql_query($query_payments, $sugar) or die(mysql_error());
$row_payments = mysql_fetch_assoc($payments);
$totalRows_payments = mysql_num_rows($payments);

mysql_select_db($database_sugar, $sugar);
$query_adjustments = "SELECT username, SUM(amount) FROM adjustment_history WHERE username = '$username' AND billing_date = '$today' GROUP BY username";
$adjustments = mysql_query($query_adjustments, $sugar) or die(mysql_error());
$row_adjustments = mysql_fetch_assoc($adjustments);
$totalRows_adjustments = mysql_num_rows($adjustments);

mysql_select_db($database_sugar, $sugar);
$query_statement_t = "SELECT * FROM acc_statement WHERE username = '$username'";
$statement_t = mysql_query($query_statement_t, $sugar) or die(mysql_error());
$row_statement_t = mysql_fetch_assoc($statement_t);
$totalRows_statement_t = mysql_num_rows($statement_t);
 
$today = date('Y-m-d');
$payments = $row_payments['SUM(amount)'];
$adjustments = $row_adjustments['SUM(amount)']; 
$previous = $row_statement_t['prev_bal'];
$monthly = $row_cst_details['price'];
$amount_payable = $previous - $payments - $adjustments + $monthly;
$billing_date = $row_cst_details['billing_date'];

echo "user ".$username.", ";
echo "date ".$billing_date.", ";
echo "pay ".$payments.", ";
echo "adjust ".$adjustments.", ";
echo "prev ".$previous.", ";
echo "month ".$monthly.", ";
echo "amount ".$amount_payable."<br>";

if($amount_payable == 0 || $amount_payable < 0){
$status = "No Balance";
}
else{
$status = "Not Payed";
}

if($today == $billing_date){
require_once('../Connections/sugar.php');
$sql = "UPDATE acc_statement SET prev_bal = '$amount_payable' WHERE billing_date = '$billing_date' AND username='$username'";
$result = mysql_query($sql);

$sql2 = "UPDATE payment_history SET flag = '1' WHERE billing_date = '$billing_date'";
$result2 = mysql_query($sql2);

$sql3 = "UPDATE adjustment_history SET flag = '1' WHERE billing_date = '$billing_date'";
$result3 = mysql_query($sql3);



$sql4 = "INSERT INTO invoices (username, prev_bal, payments, adjustments, amount_payable, billing_date, status, monthly) VALUES ('$username','$previous','$payments','$adjustments','$amount_payable','$billing_date','$status','$monthly')";
$result4 = mysql_query($sql4);
}
} 
while ($row_statement = mysql_fetch_assoc($statement));

?>
<?php
mysql_free_result($statement);

mysql_free_result($cst_details);

mysql_free_result($payments);

mysql_free_result($adjustments);

mysql_free_result($statement_t);
?>
