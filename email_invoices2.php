<?php
//MX Widgets3 include
require_once('../includes/wdg/WDG.php');
require('control.php');
error_reporting(1);
?>
<?php 
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_expirynotice = "10.31.8.17";
//$hostname_expirynotice = "10.31.7.7";
$database_expirynotice = "wimax";
$username_expirynotice = "sugarcrm";
$password_expirynotice = "1sugarpass2";
$expirynotice = mysql_pconnect($hostname_expirynotice, $username_expirynotice, $password_expirynotice) or trigger_error(mysql_error(),E_USER_ERROR);  ?>
<?php	/*function sendHTMLemail($to,$HTML,$subject,$from){
// First we have to build our email headers
	if(!$from){
		$from = 'CCREPORTS <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
    mail($to,$subject,$HTML,$headers); 
}*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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
<form id="form1" name="form1" method="post" action="email_invoices.php">
  <table width="100%" border="1">
    <tr>
      <td width="14%">Select Billing Date:</td>
      <td width="20%"><label>
        <input name="billing_date" class="style11" id="billing_date" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
      </label></td>
      <td width="66%"><label>
        <input type="submit" name="button" id="button" value="Send These Emails" />
      </label></td>
    </tr>
  </table>
</form>
<?php 
if(isset($_POST['button'])){
$billing_date = $_POST['billing_date'];

echo $_POST['billing_date'];

mysql_select_db($database_expirynotice, $expirynotice);
$query_expires = "
SELECT 
			  wimax_invoicing.id AS id,accounts.name,
			  accounts_cstm.customer_type_c,
			  wimax_invoicing.billing_date AS selecteddate,
			  accounts_cstm.email_c AS customeremail
			  FROM
			  accounts
			  INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			  INNER JOIN wimax_invoicing ON (accounts_cstm.mem_id_c=wimax_invoicing.parent_id)
			  WHERE billing_date = '$billing_date' AND customer_type_c = 'WTU Staff'
					";
$expires = mysql_query($query_expires, $expirynotice) or die(mysql_error());
$row_expires = mysql_fetch_assoc($expires);
$totalRows_expires = mysql_num_rows($expires);

	do{
		$id = $row_expires['id'];
    	$email = $row_expires['customeremail'];
		$HTML = '<DIV style="font-weight:bold; font-size:17px; color:#FF0000;">TO VIEW IMAGES, RIGHT CLICK ON ANY IMAGE AND SELECT DOWNLOAD IMAGES</DIV>';
		$HTML .= display_emailinvoice_byid($id);
		$to = $email;
    	$subject = "Broadband Invoice For ".$row_expires['name'].", Invocie Date: ".date_reformat($row_expires['selecteddate'],'');
		echo  "Sending ".$row_expires['name']."'s invoice to ".$email."<br>";
    	sendHTMLemail($to,$HTML,$subject,'');  
		$count++;
	}while($row_expires = mysql_fetch_assoc($expires));



			
}
function sendHTMLemail($to,$HTML,$subject,$from){
// First we have to build our email headers
	if(!$from){
		$from = 'CCREPORTS <ccba01@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
    mail($to,$subject,$HTML,$headers); 
}
?>
</body>
</html>
<?php	/*function sendHTMLemail($to,$HTML,$subject,$from){
// First we have to build our email headers
	if(!$from){
		$from = 'CCREPORTS <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
    mail($to,$subject,$HTML,$headers); 
}*/
?>