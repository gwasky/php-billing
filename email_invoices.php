<?php
//ACCESS RESTRICTION
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

//Allow only Super
$MM_authorizedUsers = "admin,stevennt,vincentlu,emmanuelag";
$MM_authorizedDepts = "Finance Credit and Control,Finance - Collections,Super,Bill Delivery,Customer Care - Operations";
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
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized($MM_authorizedUsers,$MM_authorizedDepts, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
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
//ACCESS RESTRICTION

//MX Widgets3 include
require_once('../includes/wdg/WDG.php');
require_once('control.php');
require_once('pdf_invoice.php');
error_reporting(1);
set_time_limit(0);

function customer_type_dropdown($selected,$name){
	$myquery = new uniquequerys();
	
	$query = "
		select distinct customer_type_c as customer_type
		from accounts_cstm
		inner join accounts on (accounts.id = accounts_cstm.id_c)
		where accounts.deleted = 0
	";

	$customer_types = $myquery->multiplerow_query($query);
	
	$html = '
		<label> Customer Type :<br>
			<select name="'.$name.'" size="5" multiple>
	';
	$list['ALL TYPES'] = '';	
	foreach($customer_types as $row){
		$list[$row[customer_type]] = $row[customer_type];
	}
	foreach($list as $label=>$value){
		$html .= '
			<option value="'.$value.'" '; if(in_array($value,$selected)){ $html .= 'selected="selected"';} $html .= ' >'.trim($label).'</option>';
	}
	$html .= '
		</select>
		</label>';
	
	return $html;
}

$invoicing = new wimax_invoicing();
$myquery = new uniquequerys();

if($_POST[email_invoices][billing_start_date] == ''){
	$_POST[email_invoices][billing_start_date] = last_day(date("Y-m-d",strtotime("-30 day")));
}
if($_POST[email_invoices][billing_end_date] == ''){
	$_POST[email_invoices][billing_end_date] = last_day(date("Y-m-d",strtotime("-30 day")));
}

if(!isset($_POST[email_invoices][send])){	

	if(count($_POST[email_invoices][customer_types]) > 0){
		if(!in_array('',$_POST[email_invoices][customer_types])){
			$customer_type_condition = " AND
				accounts_cstm.customer_type_c IN (";
							  
			foreach($_POST[email_invoices][customer_types] as $type_key=>$customer_type){
				$customer_type_condition .= "'".$customer_type."'";
				
				if($type_key+1 < count($_POST[email_invoices][customer_types])){ $customer_type_condition .= ","; }
			}
			
			$customer_type_condition .= ")";
		}
	}
	
	if(trim($_POST[email_invoices][parent_account]) != '') $account_condition = " AND wimax_invoicing.parent_id = '".$_POST[email_invoices][parent_account]."'";
	
	$query = "
		SELECT
			accounts.id as account_id,
			accounts.`name`,
			wimax_invoicing.id AS invoice_id,
			wimax_invoicing.parent_id AS parent_account_num,
			wimax_invoicing.charges_sum,
			wimax_invoicing.billing_date,
			wimax_invoicing.period,
			wimax_invoicing.invoice_number,
			wimax_invoicing.deleted,
			accounts_cstm.customer_type_c,
			accounts_cstm.invoice_by_email_c,
			trim(accounts_cstm.email_c) as email,
			wimax_invoicing.details,
			wimax_invoicing.generation_date
		FROM
			accounts
			INNER JOIN accounts_cstm ON accounts_cstm.id_c = accounts.id
			INNER JOIN wimax_invoicing ON trim(wimax_invoicing.parent_id) = trim(accounts_cstm.mem_id_c)
		WHERE
			accounts_cstm.invoice_by_email_c = 'Yes' AND
			wimax_invoicing.deleted = 0 
			".$customer_type_condition."
			".$account_condition." AND
			billing_date BETWEEN '".$_POST[email_invoices][billing_start_date]."' AND '".$_POST[email_invoices][billing_end_date]."'
	";
	
	$invoices = $myquery->multiplerow_query($query);
	$tr_style[0] = "background-color:#BCD2FC;";
	
	if(count($invoices) > 0){
		$HTML = '
			 <table width="100%" border="0" cellpadding="1" cellspacing="1">
			 	<TR >
					<th align="center" background="images/table_header.jpg" class="style14">#</th>
					<th align="center" background="images/table_header.jpg" class="style14">Email</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoice Number</th>
					<th align="center" background="images/table_header.jpg" class="style14">Billing Date</th>
					<th align="center" background="images/table_header.jpg" class="style14">Account Number</th>
					<th width="200" align="center" background="images/table_header.jpg" class="style14">Account Name</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoice Currency</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoice Amount</th>
					<th align="center" background="images/table_header.jpg" class="style14">Customer Type</th>
					<th align="center" background="images/table_header.jpg" class="style14">Email</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoiced By</th>
				</TR>
		';
		foreach($invoices as $row){
			$invoice = $invoicing->Get($row[invoice_id]);
			$invoice->details = unserialize($invoice->details);
			
			$HTML .= '
				<TR style="'.$tr_style[++$row_count%2].'">
					<td align="right">'.$row_count.'</td>
					<td>
						<input name="email_invoices[ids]['.$invoice->id.']" type="checkbox" value="'.$row[email].'" '; if(trim($row[email]) != '') { $HTML .= 'checked'; } else { $HTML .= 'disabled'; } $HTML .=' />
					</td>
					<td align="right">'.$invoice->invoice_number.' <a href="print_invoice_pdf.php?id='.$invoice->id.'" target="_blank">PDF</a></td>
					<td align="right">'.$invoice->billing_date.'</td>
					<td align="right">'.$invoice->details[Other_details][account_number].'</td>
					<td>'.$invoice->details[Other_details][account_name].'</td>
					<td>'.$invoice->details[Other_details][invoice_currency].'</td>
					<td align="right">'.number_format(-$invoice->charges_sum,2).'</td>
					<td>'.$row[customer_type_c].'</td>
					<td>'.$row[email].'</td>
					<td>'.$invoice->details[Other_details][generated_by].'</td>
				</TR>
			';
		}
		
		$HTML .'
			</table>
		';
	}else{
		$HTML = 'No invoices Match your selection criteria<hr><pre>'.$query.'</pre>';
	}
	
}else{	
	if(count($_POST[email_invoices][ids]) > 0){
		$bcc = 'WARID CUSTOMER CARE BILLING <billing@waridtel.co.ug>, Carolyn p. Angom Musunga/Customer service/Uganda <Carolyn.AngomMusonga@ug.airtel.com>, Angela Mirembe/Customer Service/Uganda <Angela.Mirembe@ug.airtel.com>';
		$HTML = '
			 <table width="100%" border="0" cellpadding="1" cellspacing="1">
			 	<TR>
					<th align="center" background="images/table_header.jpg" class="style14">#</th>
					<th align="center" background="images/table_header.jpg" class="style14">Email</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoice Number</th>
					<th align="center" background="images/table_header.jpg" class="style14">Billing Date</th>
					<th align="center" background="images/table_header.jpg" class="style14">Account Number</th>
					<th width="200" align="center" background="images/table_header.jpg" class="style14">Account Name</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoice Currency</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoice Amount</th>
					<th align="center" background="images/table_header.jpg" class="style14">Email Success</th>
					<th align="center" background="images/table_header.jpg" class="style14">Email</th>
					<th align="center" background="images/table_header.jpg" class="style14">Invoiced By</th>
				</TR>
		';
		foreach($_POST[email_invoices][ids] as $id=>$email){
			$invoice = $invoicing->Get($id);
			$invoice->details = unserialize($invoice->details);
			echo "Sending mail to [".$email."] for invoice id ".$id."<br>";
			$subject = "Your ".date('F Y',strtotime($invoice->billing_date))." Warid Broadband Electronic Invoice : No ".$invoice->invoice_number;
			$message = '
			<table style="font-family: Calibri, Verdana, Arial; font-size: 13px;">
				<tr>
				<td>
				Hello,<br>
				<br>
				You are listed as the billing contact person for '.strtoupper($invoice->details[Other_details][account_name]).'\'s Enterprise Account number <span style="font-weight:bold;">'.$invoice->details[Other_details][account_number].'</span>.<br>
				<br>Attached is your invoice of <span style="font-weight:bold;">'.$invoice->details[Other_details][invoice_currency].' '.number_format(-$invoice->charges_sum,2).'.</span> Please effect payment by <span style="font-weight:bold;">'.date('l jS F Y',strtotime($invoice->details[Other_details][invoice_due_date])).'</span> to enjoy an un interrupted service.<br>
				<br>
				Should you have any enquiries, please feel free to contact customer care through<br>
				Email: DATASUPPORT@waridtel.co.ug or <br>
				Call: <strong>0700 777 776</strong> from all local networks<br>
				<br>
				Thank you for choosing Warid Broadband as you preferred data service provider. <br>
				<br>
				<br>
				____________________________________________________<br>
				With best wishes,<br>
				<span style="font-size:18px; font-weight:bold;"><span style="color:#000066;">WARID CUST</span><span style="color:#FF0000;">OMER CARE</span></span>
				</td>
				</tr>
				</table>
			';
			$fileparams = array(
							'filename'=>date('F Y',strtotime($invoice->billing_date))." Invoice number ".$invoice->invoice_number.".pdf",
							'data'=>pdf_invoice(array($id),$output_method='S')
						);
			//echo "<fieldset>".print_r($fileparams[data])."</fieldset>";
			//$result = my_mail($to=$email,$cc='',$bcc,$message,$subject,$from,$fileparams,$reply_to=NULL);
			$mail_result = my_mail($to=$email,$cc='',$bcc,$message,$subject,$from,$fileparams,$reply_to=NULL);
			
			$HTML .= '
				<TR style="'.$tr_style[++$row_count%2].'">
					<td align="right">'.$row_count.'</td>
					<td>
						<input name="email_invoices[ids]['.$invoice->id.']" type="checkbox" value="'.$email.'" '; if(trim($email) != '') { $HTML .= 'checked'; } else { $HTML .= 'disabled'; } $HTML .=' />
					</td>
					<td align="right">'.$invoice->invoice_number.' <a href="print_invoice_pdf.php?id='.$invoice->id.'" target="_blank">PDF</a></td>
					<td align="right">'.$invoice->billing_date.'</td>
					<td align="right">'.$invoice->details[Other_details][account_number].'</td>
					<td>'.$invoice->details[Other_details][account_name].'</td>
					<td>'.$invoice->details[Other_details][invoice_currency].'</td>
					<td align="right">'.number_format(-$invoice->charges_sum,2).'</td>
					<td>'.($mail_result == 1?"Email Sent":"Email Failed").'</td>
					<td>'.$email.'</td>
					<td>'.$invoice->details[Other_details][generated_by].'</td>
				</TR>
			';
		}
	}
	
}

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

th{
	background-color:#006;
	color:#FFF;
	white-space:nowrap;
}

td{
	white-space:nowrap;
}

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
<form id="email_invoices[]" name="email_invoices[]" method="post" action="email_invoices.php">
	<table width="100%" border="0" cellspacing="1">

        <tr>
            <td width="40%" rowspan="2"><img src="images/logo.jpg" alt="4" width="233" height="39" /></td>
            <td width="60%" align="right" valign="bottom" class="style14 style16"><span class="style14">Your Logged in as: <span class="style11"></span><?php echo $_SESSION['MM_Username']."/".$_SESSION['MM_UserGroup']; ?></span></td>
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

    </table>
  <table width="100%" border="0" cellpadding="1" cellspacing="1">
    <tr>
      <td width="14%">
      <label>Account Number :
        <input name="email_invoices[parent_account]" class="style11" size="12" maxlength="10" id="email_invoices[parent_account]" value="<?php echo $_POST[email_invoices][parent_account]; ?>" />
      </label>
      </td>
      <td width="30%" >
      <label>Billing DateFrom :
        <input name="email_invoices[billing_start_date]" class="style11" id="email_invoices[billing_start_date]" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" value="<?php echo $_POST[email_invoices][billing_start_date]; ?>" />
      </label>
      <label>Billing Date To :
        <input name="email_invoices[billing_end_date]" class="style11" id="email_invoices[billing_end_date]" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" value="<?php echo $_POST[email_invoices][billing_end_date]; ?>" />
      </label>
      </td>
      <td width="20%">
      <?php echo customer_type_dropdown($_POST[email_invoices][customer_types],'email_invoices[customer_types][]'); ?>
      </td>
      <td width="66%">
      <label>
        <input type="submit" name="email_invoices[reveiw]" id="email_invoices[reveiw]" value="Review Invoices" />
      </label>
      <?php if($_POST[email_invoices][reveiw] == 'Review Invoices' or $_POST[email_invoices][send] == 'Send These Emails'){ ?>
      <label>
        <input type="submit" name="email_invoices[send]" id="email_invoices[send]" value="Send These Emails" />
      </label>
      <?php } ?>
      </td>
    </tr>
  </table>
  <?php
  
  echo $HTML;
  
  ?>
</form>
</body>
</html>
<?php

function my_mail($to,$cc,$bcc,$message,$subject,$from,$fileparams=NULL,$reply_to=NULL){
	if(!$from or $from == ''){
		$from = 'Warid Enterprise DATA Billing <ccnotify@waridtel.co.ug>';
	}
	
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "To: ".$to." \r\n";
	$headers .= "From: ".$from."\r\n";
	
	if($reply_to == '' or $reply_to == NULL){
		$headers .= "Reply-To: DATASUPPORT@waridtel.co.ug \r\n";
	}else{
		$headers .= "Reply-To: ".$reply_to." \r\n";
	}
	
	$headers .= "Return-Path: DATASUPPORT@waridtel.co.ug \r\n";
	
	if($cc != ''){
		$headers .= "CC: ".$cc." \r\n";
	}
	if($bcc != ''){
		$headers .= "BCC: ".$bcc." \r\n";
	}
	
	if(is_array($fileparams) and count($fileparams) > 0){
		$Boundary = md5(date('r', time()));
		
		$file_breaks = explode(".",$fileparams[filename]);
		$filetypes[html] = 'text/html';
		$filetypes[xlsx] = 'application/vnd.ms-excel';
		$filetypes[xls] = 'application/ms-excel';
		$filetypes[doc] = 'application/doc';
		$filetypes[zip] = 'application/zip';
		$filetypes[pdf] = 'application/pdf';
		
		$encoded_data = chunk_split(base64_encode($fileparams[data]));
		
		$headers .= 'Content-Type: multipart/mixed; boundary="'.$Boundary.'"' . "\r\n";
		$message = "
This is a multi-part message in MIME format.

--{$Boundary}
Content-Type: text/html; charset=iso-8859-1

$message

--$Boundary
Content-Type: ".$filetypes[$file_breaks[count($file_breaks)-1]]."; name=".$fileparams[filename]."
Content-Disposition: attachment; filename=".$fileparams[filename]."
Content-Transfer-Encoding: base64

$encoded_data

--$Boundary--
";
	}else{
    	//$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	}
	
	return mail($to,$subject,$message,$headers);
}

?>