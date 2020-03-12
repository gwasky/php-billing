<?
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
$MM_authorizedUsers = "admin,stevennt,vincentlu,emmanuelag,parickpa,mosesok";
$MM_authorizedDepts = "";
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

	require('control.php');
	//error_reporting(E_ALL);

	function monthly_bill($billrun_date, $account_ids){
	
		$billing = new wimax_billing();
		$invoicing = new wimax_invoicing();
		$myquery = new uniquequerys();
	
		//Getting Bill run and start dates
		if($billrun_date == ''){
			$result = $myquery->uniquequery("SELECT LAST_DAY(now()) as today");
			$billrun_date = $myquery->Unescape($result[today]);
			$result = $myquery->uniquequery("SELECT concat(date_format(LAST_DAY(now()),'%Y-%m-'),'01') as period_start");
			$period_start_date = $myquery->Unescape($result[period_start]);
		}
		else{
			$result = $myquery->uniquequery("SELECT LAST_DAY('$billrun_date') as thedate");
			$billrun_date = $myquery->Unescape($result[thedate]);
			$result = $myquery->uniquequery("SELECT concat(date_format(LAST_DAY('$billrun_date'),'%Y-%m-'),'01') as period_start");
			$period_start_date = $myquery->Unescape($result[period_start]);
		}
		
		if(count($account_ids) == 0){
			$billing_data = $myquery->multiplerow_query("
						SELECT
						  accounts_cstm.preferred_username_c,
						  accounts_cstm.mem_id_c as parent_id,
						  accounts_cstm.service_type_internet_c,
						  accounts_cstm.crn_c,
						  cn_contracts.start_date,
						  cn_contracts.expiry_date,
						  ps_products.name as product_name,
						  ps_products.price * 1.18 as product_price,
						  ps_products_cstm.product_grouping_c as grouping
						FROM
						 accounts
						 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						 INNER JOIN ps_products ON (accounts_cstm.shared_packages_c=ps_products.name) OR (accounts_cstm.download_bandwidth_c=ps_products.name)
						 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						  cn_contracts.start_date <= '$billrun_date' AND
						  cn_contracts.expiry_date > '$billrun_date' AND
						  cn_contracts.status = 'Active' AND
						  accounts.deleted = '0' AND 
						  cn_contracts.deleted = '0' AND
						  ps_products.deleted = '0'
						");
		}else{
			$billing_data = array();
			foreach($account_ids as $id){
				$acct_billing_data = $myquery->multiplerow_query("
						SELECT
						  accounts_cstm.preferred_username_c,
						  accounts_cstm.mem_id_c as parent_id,
						  accounts_cstm.crn_c,
						  cn_contracts.start_date,
						  cn_contracts.expiry_date,
						  ps_products.name as product_name,
						  ps_products.price * 1.18 as product_price,
						  ps_products_cstm.product_grouping_c as grouping
						FROM
						 accounts
						 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						 INNER JOIN ps_products ON (accounts_cstm.shared_packages_c=ps_products.name) OR (accounts_cstm.download_bandwidth_c=ps_products.name)
						 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
						where
						  accounts.deleted = '0' AND 
						  cn_contracts.deleted = '0' AND
						  ps_products.deleted != '1' AND
						  accounts_cstm.mem_id_c = '$id'
						");
				if(count($acct_billing_data) != 0){
					foreach($acct_billing_data as $row){
						array_push($billing_data,$row);
					}
				}
			}
		}
		
		foreach($billing_data as $row){
			//BILLING ALL ACCOUNTS
			$billing->entry_id = generateRecieptNo('');
			$billing->parent_id = $row[parent_id];
			$billing->account_id = $row[preferred_username_c];
			$billing->bill_start = $row[start_date];
			$billing->bill_end = $row[expiry_date];
			$billing->billing_date = $billrun_date;
			$billing->entry_date = $billrun_date;
			$billing->currency = 'USD';
			$billing->entry_type = 'Services';
				$entry[grouping] = $row[grouping];
				$entry[entry] = $row[product_name];
				//$entry[details] = '';
			$billing->entry = serialize($entry);
			$billing->amount = -$row[product_price];
			$billing->balance = newBalance($billing->amount,$billing->parent_id, $billing->entry_date);
			$billing->user = 'Bill Run';
		
			$check_object = $billing->GetList(array(
												array('entry_date','=',$billrun_date),
												array('username','=',$billing->account_id),
												array('entry_type','=',$billing->entry_type),
												array('entry','LIKE','%'.$entry[grouping].'%')
													)
												);
			if(count($check_object) != 0){
				$check_object = $check_object[0];
				$billing->id = $check_object->id;
				$check_object->entry = unserialize($check_object->entry);
				//print_r($check_object);	echo "<br> ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ <br>";
				echo "!! Charge [".$check_object->amount."] on Product ".$check_object->entry[entry]." for Username ".$check_object->account_id." is Already there <br>Updating [".$billing->amount."] on Product ".$entry[entry]." for Username ".$billing->account_id."<br><br>";
				$id = Adjust_Balances_and_Save($billing);
			}else{
				if($billing->amount != ''){
					//echo ++$r." --->> "; print_r($billing); echo "<br>";
					echo "!! Saving regular Charge [".$entry[entry]."] at [".$billing->amount."] on [".$billing->account_id."]<br>";
					$id = Adjust_Balances_and_Save($billing);
				}else{
					echo "!! Charge is blank <br>";
				}
			}
		}
	}
	
	function display_accounts_multiselect($service_type){
	$myquery = new uniquequerys();
	
	//This selects the child account
	
	$query = "SELECT 
		accounts.name, 
		accounts_cstm.crn_c as acc_num 
		FROM 
		accounts 
		INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
		INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account) 
		WHERE 
		accounts_cstm.mem_id_c  != '' AND 
		cn_contracts.deleted = '0'
		";
	
	if($service_type != ''){
		$query .= " and accounts_cstm.service_type_internet_c = '$service_type'";
	}
	
	$query .= " order by name asc";
	
	$accounts_list = $myquery->multiplerow_query($query);
	
	$html = '<select name="account_ids[]" size="10" multiple="multiple" id="account_ids[]" class="style11">';
	$html .= '<option value="" selected="selected"></option>';
	foreach($accounts_list as $account){
		$html .= '<option value="'.$account[acc_num].'">'.$account[name].'</option>';
	}
	$html .= '</select>';
	
	return $html;
	}
	
	function delete_invoice_numbers($cvs_invoice_nos,$delete_request_txt){
		
		$invoicing = new wimax_invoicing();
		$myquery = new uniquequerys();
		
		//echo $cvs_invoice_nos." => ".print_r($invoice_nos,TRUE)."<BR>";
		
		$invoice_nos = explode(",",$cvs_invoice_nos);
		
		if(count($invoice_nos) <= 0){ echo "No Invoice Numbers"; return "";}
		
		foreach($invoice_nos as $invoice_no){
			$invoice_no = trim($invoice_no);
			$condition = array(
							array('invoice_number','=',$invoice_no)
						);
			
			$invoices = $invoicing->GetList($condition);

			foreach($invoices as $invoice){
				if(intval($invoice->id) > 0){
					$invoice->details = unserialize($invoice->details);
					
					$invoice->deleted=1;
					$invoice->details[deleted_on] = date("Y-m-d H:i:s",strtotime("- 3 hours"));
					$invoice->details[deleted_info] = $delete_request_txt;
					
					if(trim($_REQUEST[requester]) != ''){ $invoice->details[deleted_info] .= '<br><br>Requested by '.ucwords(trim($_REQUEST[requester]));}
					
					$invoice->details[deleted_info] .= '<br><br>Deleted by '.$_SESSION['MM_Username']."/".$_SESSION['MM_UserGroup'].' at '.date('H:i').' HRS on '.date('D jS M Y');
					
					$invoice->details = serialize($invoice->details);
					
					$savedid = $invoice->Save();
					if(intval($savedid) > 0){
						$log[deleted_invoices][$invoice->invoice_number] = $invoice;
						$log[deleted_info] .= ++$DI.'. Invoice No <a href="http://wimaxcrm.waridtel.co.ug/billing/print_invoice.php?id='.$invoice->id.'" target="_blank">['.$invoice->invoice_number.']</a><br>';
					}else{
						$log[undeleted_invoices][$invoice->invoice_number] = $invoice;
						$log[undeleted_info] .= ++$UDI.'. Invoice No <a href="http://wimaxcrm.waridtel.co.ug/billing/print_invoice.php?id='.$invoice->id.'" target="_blank">['.$invoice->invoice_number.']</a><br>';
					}
				}
			}
		}

		if(count($log) > 0){
			$output = '<body style="font-size:90%; font-family: calibri,Arial; margin-left:auto; margin-right:auto;"><fieldset>A request to delete the following invoices with the following numbers ['.$cvs_invoice_nos.'] was submitted by '.str_replace(array('<','>'),array('(',')'),ucwords(trim($_REQUEST[requester])));
			
			if(trim($delete_request_txt) != '') {$output .= ' with the following notes <hr><p style="font-family:calibri;">'.nl2br($delete_request_txt).'</p><hr>';}
			
			$output .= '<br><br>';
			
			if($log[undeleted_info] != ''){
				$output .= "THESE INVOICES COULD NOT BE DELETED <BR>".$log[undeleted_info];
				$output .= "<BR>".display_selected_invoices($log[undeleted_invoices],TRUE)."<BR>";
				$output .= "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<BR>";
			}
			
			if($log[deleted_info] != ''){
				$output .= "These Invoices were deleted <BR>".$log[deleted_info];
				$output .= "<BR>".display_selected_invoices($log[deleted_invoices],TRUE)."<BR>";
			}
			
			$output .= "</fieldset></body>";

			echo $output;
			
			//sendHTMLemail($to = trim($_REQUEST[requester]).',CREDITCOLLECTION@waridtel.co.ug',$bcc='ccbusinessanalysis@waridtel.co.ug',$message=$output,$subject='DATA CRM : Invoice Delete Request',$from="Data CRM <ccnotify@waridtel.co.ug>");
			$bcc='Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>';
			sendHTMLemail($to = trim($_REQUEST[requester]),$bcc,$message=$output,$subject='Data Invoice Delete Request',$from="Data Reporting <ccnotify@waridtel.co.ug>");
		}
	}
	
	function repairbals($parent_id){
	
		$billing = new wimax_billing();
	
		if($parent_id != ''){
			$id_condition = array(array('parent_id','=',$parent_id));
		}
	
		$bill_data = $billing->GetList($id_condition);
		
		foreach($bill_data as $tx){
			if(count($accts[trim($tx->parent_id)]) == 0){
				$accts[trim($tx->parent_id)][0] = $tx;
			}else{
				array_push($accts[trim($tx->parent_id)],$tx);
			}
		}
		
		foreach($accts as $acct_id=>$acct){
			foreach($acct as $tx){
				$tx->parent_id = trim($tx->parent_id);
				$tx->account_id = trim($tx->account_id);
				$balance[$tx->parent_id] += $tx->amount;
				$tx->balance = $balance[$tx->parent_id];

				$tx->Save();
			}
		}
	}
	
	function delete_entry_numbers($entry_no_list,$requester,$description){
		$invoicing = new wimax_invoicing();
		$billing = new wimax_billing();
		$myquery = new uniquequerys();
		
		//Not necessary apparently
		//$requester = '"'.str_replace(array(' <'),'" <',$requester);
		
		$entry_nos = explode(",",trim($entry_no_list));
		
		foreach($entry_nos as $entry_no){
			$entry_no = trim($entry_no);
			$id_query = "select id from wimax_billing where entry_id = '".$entry_no."' limit 1";
			$result = $myquery->uniquequery($id_query);
			if($result[id] != ''){
				$receipt_data = generate_receipt($result[id]);
				$entry_html = display_receipt($receipt_data);
				$delete_query = "delete from wimax_billing where id = '".$result[id]."'";
				$result = $myquery->uniquenonquery($delete_query);
				$html_deleted .= $entry_html."<hr>";
				//echo "Repairing balances for ".trim($receipt_data['db_data']->parent_id)."<hr>";
				repairbals(trim($receipt_data['db_data']->parent_id));
			}else{
				$error .= $entry_no.",";
				echo "Entry No ".$entry_no." is either already deleted or not is there. Check with other CCBA Members to see if they deleted it<br>";
				return "";
			}
		}
		
		$html = '
			<fieldset style="font-family:calibri;">
			<p style="font-family:calibri;">A request to delete the following entries '.trim($entry_no_list).' was submitted by '.str_replace(array('<','>'),array('(',')'),$requester).'. with the following description</p>
			<hr>
			'.nl2br($description).'</fieldset>
			<fieldset style="font-family:calibri;"><hr>Delete Request effected by '.$_SESSION['MM_Username']."/".$_SESSION['MM_UserGroup'].' at '.date('H:i').' HRS on '.date('D jS M Y').'</fieldset>';
			if($error != ''){
				$html .= '
					<fieldset style="font-family:calibri;">
					<hr>
					WITH THE EXCEPTION OF ('.$error.'),
					</fieldset>
				';
			}
			$html .= '
				<fieldset style="font-family:calibri;">
				<hr>
				THE FOLLOWING ENTRIES HAVE BEEN DELETED
				'.$html_deleted.'
				</fieldset>
		';
		
		sendHTMLemail(
					  $to = $requester.',CREDITCOLLECTION@waridtel.co.ug,Leonard Kibuuka/Finance/Kampala <leonard.kibuuka@ug.airtel.com>, Rita Tamale/Credit Control/Uganda <Rita.Tamale@ug.Airtel.com>',
					  $bcc='Steven Ntambi/Customer Service/Uganda <steven.ntambi@ug.airtel.com>, Emmanuel Agwa/IBM/Uganda <emmlagwa@ug.ibm.com>, Jamil Kireri/IT/Uganda <jamil.kireri@ug.airtel.com>',
					  $message = $html,
					  $subject = "DATA CRM : Entry delete request",
					  $from="Data CRM <ccnotify@waridtel.co.ug>"
					 );
		
		echo "sending mail to [".$to."]<br><hr>";
		
		echo $html;
	}
	
	function view_invoices($account_num, $invoice_num, $invoice_date){
		$myquery = new uniquequerys();
		
		$query = "
			SELECT
				wimax_invoicing.id
			FROM
				wimax_invoicing
			WHERE
		";
		if($account_num != ''){
			$query .= "wimax_invoicing.parent_id = '".$account_num."' ";
		}
		if($account_num != '' and $invoice_num != '' ){
			$query .= " AND ";
		}
		if($invoice_num != '' ){
			$query .= " wimax_invoicing.invoice_number = '".$invoice_num."'	";
		}
		
		if($invoice_date != ''){
			$query .= " AND wimax_invoicing.billing_date = '".$invoice_date."' ";
		}
		
		$invoice_ids = $myquery->multiplerow_query($query);
		
		if(count($invoice_ids) > 0){
			$invoices_HTML .= '<p style="page-break-before: always">';
			foreach($invoice_ids as $invoice_row){
				$invoices_HTML .= display_invoice_byid($invoice_row[id]);
				$invoices_HTML .= '<p style="page-break-before: always">';
			}
		}else{
			$invoices_HTML = "No Invoices to display <br>";
		}
		
		return $invoices_HTML;
	}
	
	switch($_POST[button]){
		case 'repair_balances':
			$acount_scope = trim($_POST[account_id])==''?'All Accounts':'Account '.trim($_POST[account_id]);
			echo "Running the repair balances on ".$acount_scope."...<br>";
			repairbals(trim($_POST[account_id]));
			break;
		case 'bill':
			if($_POST[bill_date]){
				monthly_bill($_POST[bill_date], $_POST[account_ids]);
			}
			break;
		case 're_invoice':
			if($_POST[reinvoice_date]){
				echo "Redoing invoices ...<br>";
				$invoices = build_invoices($_POST[reinvoice_date],$_POST[account_id]);
			}
			break;
		case 'view_invoice':
			if(trim($_POST[account_id]) !='' or trim($_POST[inv_num]) != '' or trim($_POST[invoice_date])){
				$_POST[inv_num] = trim($_POST[inv_num]);
				$_POST[account_id] = trim($_POST[account_id]);
				$_POST[invoice_date] = trim($_POST[invoice_date]);
				echo "Showing invoices ... <br>";
				echo view_invoices($_POST[account_id], $_POST[inv_num], $_POST[invoice_date]);
			}
			break;
		case 'billrun':
			if($_REQUEST['billrun_date'] != ''){
				$invoices = billrun_invoiceGeneration($_REQUEST['billrun_date']);
			}
			break;
		case 'delete_invoices':
			if($_REQUEST['inv_nos'] != '' and trim($_REQUEST['inv_txt']) != ''){
				delete_invoice_numbers($_REQUEST['inv_nos'],$_REQUEST['inv_txt']);
			}else{
				echo "Missing Invoice numbers and or notes on the description request<br>";
			}
			break;
		case 'delete_entries':
			echo "Running the delete entries ...<br>";
			if($_REQUEST['entry_nos'] != '' and trim($_REQUEST['requester_txt']) != '' and $_REQUEST['requester'] != ''){
				delete_entry_numbers($_REQUEST['entry_nos'],$_REQUEST['requester'],$_REQUEST['requester_txt']);
			}else{
				echo "Missing Entry numbers and or notes on the description request<br>";
			}
			break;
		default:
			if(isset($_POST[button])){
				echo "No valid submission received ";
			}
			break;
	}

	//print_r($_POST); echo "<br><br>"; print_r($_GET);
	/*if($invoices){
		echo "The following Invoice Data was not saved<br>";
		foreach($invoices as $invoice){
			foreach($invoice as $key => $lock){
				echo "The `".$key."` is <br>";
				if(is_array($lock)){
					print_r($lock); echo "<br><br>";
				}else{
					echo $lock."<br><br>";
				}
			}
		}
	}*/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:wdg="http://ns.adobe.com/addt">
<head>
<title>Enterprise Administration Tools</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
td img {display: block;}.style1 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: bold;
}
.style2 {font-size: 12px; font-family: Arial, Helvetica, sans-serif;}
.style4 {font-size: 14}
.style5 {font-size: 14px; font-family: Arial, Helvetica, sans-serif; }
.style6 {font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: bold; }
</style>
<!--Fireworks CS3 Dreamweaver CS3 target.  Created Mon Oct 20 15:38:49 GMT+0300 2008-->
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
<script type="text/javascript">
	function confirm_input(statement){
		return window.alert(statement);
	}
</script>

<link href="../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>
<body bgcolor="#ffffff">

<table width="100%" border="0" cellspacing="1">
  <tr>
    <td align="right">
    	<fieldset>
    	Welcome [<? echo $_SESSION['MM_Username']."/".$_SESSION['MM_UserGroup']; ?>] | <a href="../index.php">CRM</a> | <a href="index.php">Billing Interface</a> | <a href="<?php echo $logoutAction ?>">LOG OUT</a>
    	</fieldset>
	</td>
  </tr>
  <TR>
  <td>
   <fieldset>
  	<form id="del_entries" name="del_entries" method="post" action="invoice.php">
    	<span class="style14" style="font-weight:bold; font-size:18px;">DELETE ENTRY/RECEIPT NUMBERS</span> <br>
      <span class="style14">Type comma seperated ENTRY NUMBERS</span> 
      <label>
      <input name="entry_nos" class="style11" id="entry_nos" value="" size="60" onchange="javascript:confirm_input('Confirm that these are/this is Entry Numbers AND NOT IDS!');"/>
      </label>
      <br>
      <span class="style14">Name of Requester</span>
      <label>
      	<input name="requester" class="style11" id="requester" value="" size="60" />
      </label>
      <br>
      <span class="style14">Notes on the description request</span>
      <br>
      <label>
      	<textarea name="requester_txt" id="requester_txt" rows="5" cols="100"></textarea>
      </label>
      <br>
      <label>
        <input name="button" type="submit" class="style14" id="button" value="delete_entries" />
      </label>
      </form>
    </fieldset>
      </td>
  </tr>
  <tr>
      <td>
   <fieldset>
  	<form id="inv" name="inv" method="post" action="invoice.php">
    <span class="style14" style="font-weight:bold; font-size:18px;">RE GENERATE INVOICES (NO CHARGING)</span> <br>
      <span class="style14">Select Invoice Redo Date</span> 
      <label>
      <input name="reinvoice_date" class="style11" id="reinvoice_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
      </label>
     <label>
      <input name="account_id" id="account_id" type="text" class="style11" value="" />
     </label>
      <label>
        <input name="button" type="submit" class="style14" id="button" value="re_invoice" />
      </label>
     </form>
  </fieldset>
     </td>
  </tr>
  <tr>
  <tr>
  <td>
   <fieldset>
  	<form id="repair_balances" name="repair_balances" method="post" action="invoice.php">
      <span class="style14" style="font-weight:bold; font-size:18px;">REPAIR BALANCES</span> <br>
      <label>
      <span class="style14">Enter Account No (All accounts if blank)</span> 
      <input name="account_id" id="account_id" type="text" class="style11" value="" />
      </label>
      <label>
        <input name="button" type="submit" class="style14" id="button" value="repair_balances" />
      </label>
      </form>
    </fieldset>
      </td>
  </tr>
  <tr>
  <td>
   <fieldset>
  	<form id="del_inv" name="del_inv" method="post" action="invoice.php">
      <span class="style14" style="font-weight:bold; font-size:18px;">DELETE INVOICE NUMBERS</span> <br>
      <span class="style14">Type INVOICE NUMBERS NOT IDS</span> 
      <label>
      <input name="inv_nos" class="style11" id="inv_nos" value="" size="60" onchange="javascript:confirm_input('Confirm that these are/this is Invoice Numbers AND NOT IDS!');"/>
      </label>
      <br>
      <span class="style14">Name of Requester</span>
      <label>
      	<input name="requester" class="style11" id="requester" value="" size="60" />
      </label>
      <br>
      <span class="style14">Notes on the description request</span>
      <br>
      <label>
      	<textarea name="inv_txt" id="inv_txt" rows="5" cols="100"></textarea>
      </label>
      <br>
      <label>
        <input name="button" type="submit" class="style14" id="button" value="delete_invoices" />
      </label>
      </form>
    </fieldset>
      </td>
  </tr>
  <tr>
      <td>
   <fieldset>
  	<form id="inv" name="inv" method="post" action="invoice.php">
    <span class="style14" style="font-weight:bold; font-size:18px;">VIEW INVOICES - BY NUM AND OR ACCOUNT</span> <br>
      <span class="style14">Select Invoice and or Account</span> <br>
      <label>Account No : 
      <input name="account_id" id="account_id" type="text" class="style11" value="" />
     </label>
     <label>Invoice date : 
      <input name="invoice_date" class="style11" id="invoice_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
      </label>
     <label>Invoice Number : 
      <input name="invoice_num" id="invoice_num" type="text" class="style11" value="" />
     </label>
      <label>
        <input name="button" type="submit" class="style14" id="button" value="view_invoice" />
      </label>
     </form>
  </fieldset>
     </td>
  </tr>
  <tr>
  <td>
   <fieldset>
  	<form id="bill" name="bill" method="post" action="invoice.php">
      <span class="style14" style="font-weight:bold; font-size:18px;">CHARGE ACCOUTS (NO INVOICE)</span> <br>
      <span class="style14">Select Bill Date</span> 
      <label>
      <input name="bill_date" class="style11" id="bill_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
      </label>
      <label>
        <input name="button" type="submit" class="style14" id="button" value="bill" />
      </label>
      </form>
    </fieldset>
      </td>
  </tr>
  <tr>
    <td>
    <fieldset>
    <form id="inv" name="inv" method="post" action="invoice.php">
    <span class="style14" style="font-weight:bold; font-size:18px;">BILL AND GENERATE INVOICES</span> <br>
      <span class="style14">Select Invoice Date</span> 
      <label>
      <input name="billrun_date" class="style11" id="billrun_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
      </label>
      <? display_accounts_multiselect('Postpaid'); ?>
      <label>
        <input name="button" type="submit" class="style14" id="button" value="billrun" />
      </label>
      </form>
     </fieldset>
	</td>
  </tr>
 </table>
</body>
</html>
