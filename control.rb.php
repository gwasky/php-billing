<?php
require_once('configuration.php');
require_once('objects/class.wimax_billing.php');
/*ini_set("SMTP","ugkpexch01.waridtel.co.ug");
ini_set("sendmail_from", infinityWimax);*/
//error_reporting(E_WARNING|E_PARSE|E_ERROR);

function save_entry(){
	
	$todays_rate = get_rate(date('Y-m-d'));
	
	//exit("Exiting ...");//Un comment the mail fx
	
	if($_POST[charges] != ''){
		save_charges();
	}else{
		//echo "Not a charge entry <br>";
	}
	
	if(intval($_POST[payment]) > 0){
		if(intval($todays_rate[rate]) > 0){
			$receipt_id = save_payement();
			$receipt_data = generate_receipt($receipt_id);
			$receipt_html = display_receipt($receipt_data);
			echo $receipt_html;
		}else{
			//echo "Payment cannot be saved because the exchange rate for today has not been set<br>";
		}
	}else{
		//echo "Not a payment entry <br>";
	}

	if(floatval($_POST[adjustments])){
		$_POST[adjustments] = floatval($_POST[adjustments]);
		if(intval($todays_rate[rate]) > 0){
			save_adjustments();
		} else {
			echo "Adjustments cannot be saved because the exchange rate for today has not been set<br>";
		}
	}else{
		//echo "Not a adjustment entry <br>";
	}
}

function save_payement(){
	//	print_r($_POST);
	
	$billing = new wimax_billing();

	$billing->entry_id = $_POST['reciept'];
	$billing->parent_id = $_POST['parent_id'];
	$billing->account_id = $_POST['account_id'];
	if(!$billing->parent_id){ $billing->parent_id = $billing->account_id;}
	$billing->bill_start = $_POST['billing_start'];
	$billing->bill_end = $_POST['billing_expiry'];
	$billing->currency = $_POST['currency'];
	$billing->billing_date = '';
	$billing->entry_date = $_POST[entry_date];
	$rate_array = get_rate($billing->entry_date);
	$billing->rate_date = get_rate_date($billing->entry_date,$rate_array[rate_date]);
	
	check_rate_date($billing->entry_date,$billing->rate_date);
	
	$billing->entry_type = 'Payment';
	$billing->matched_invoice = $_POST[matched_invoice];
	$entry[grouping] = $_POST[payment_type];
	$entry[details] = $_POST[payment_details];
	$entry[parent_account_billing_currency] = $_POST[parent_account_billing_currency];
	$to = 'CREDITCOLLECTION@waridtel.co.ug,daniel.katatumba@waridtel.co.ug,stephen.wakasenza@waridtel.co.ug';
	//$to = 'ccbusinessanalysis@waridtel.co.ug';
	if($_POST[payment_type] == 'Cash'){
		$entry[entry] = 'Cash Payment';
	}elseif($_POST[payment_type] == 'Cheque'){
		$entry[entry] = 'Cheque Payment. Cheque Number '.$_POST['cheque'].' Bank: '.$_POST['bank'];
	}elseif($_POST[payment_type] == 'Transfer'){
		$entry[entry] = 'Money Transfer Payment. Transfer No ['.$_POST['cheque'].'] Bank: '.$_POST['bank'];
		$to = 'CREDITCOLLECTION@waridtel.co.ug,ronald.mutumba@waridtel.co.ug,deo.kamuntu@waridtel.co.ug,stephen.wakasenza@waridtel.co.ug';
	}elseif($_POST[payment_type] == 'Bounced cheque'){
		$entry[entry] = 'Reversal of Bounced Cheque Number '.$_POST['cheque'].' Bank '.$_POST['bank'];
		$_POST[payment] = abs($_POST[payment]) * -1;
		$to = 'zam.bitarabeho@waridtel.co.ug,charles.mwijukye@waridtel.co.ug';
	}elseif($_POST[payment_type] == 'Staff Recovery'){
		$entry[entry] = 'Recovered from Salary of Staff member';
		$to = 'zam.bitarabeho@waridtel.co.ug,charles.mwijukye@waridtel.co.ug';
	}elseif($_POST[payment_type] == 'Warid - Vendor offset'){
		$entry[entry] = 'Vendor payment offset by Warid';
		$to = 'zam.bitarabeho@waridtel.co.ug,charles.mwijukye@waridtel.co.ug';
	}else{
		$to = 'zam.bitarabeho@waridtel.co.ug,charles.mwijukye@waridtel.co.ug';
		$entry[entry] = 'Benefit/Entitled provided by the Company';
	}
	$billing->entry = serialize($entry);
	
	$billing->amount = convert_value($_POST[payment], $billing->currency, $billing->entry_date,$_POST[parent_account_billing_currency]);
	
	$billing->balance = newBalance($billing->amount,$billing->parent_id,$billing->entry_date);
	$billing->user = trim($_POST[user]);
	
	$check_object = $billing->GetList(array(array('entry_id','=',$billing->entry_id))); $check_object = $check_object[0];
	
	if($billing->amount !=0){
		if($check_object){
			$id = $check_object->id;
		}else{
			$id = Adjust_Balances_and_Save($billing);
			sendHTMLemail($to,$bcc='',$message=display_receipt(generate_receipt($id)),$subject='PAYMENT HAS BEEN MADE (INFINITY WIMAX)',$from='Infinity Wimax <ccnotify@waridtel.co.ug>');
			return $id;
		}
	}

	//echo "Un comment the mail line in control.php<br>";
}

function save_charges(){
	
	foreach($_POST['charges'] as $charge){
		$billing = new wimax_billing();
		$billing->entry_id = $_POST[reciept];
		$billing->parent_id = $_POST[parent_id];
		$billing->account_id = $_POST[account_id];
		if(!$billing->parent_id){ $billing->parent_id = $billing->account_id;}
		$billing->bill_start = $_POST[billing_start];
		$billing->bill_end = $_POST[billing_expiry];
		$billing->billing_date = '';
		if($_POST[entry_date]){
			$billing->entry_date = $_POST[entry_date];
		}else{
			$billing->entry_date = date('Y-m-d');
		}
		$charge = explode('#',$charge);
		$billing->currency = $charge[4];
		$billing->entry_type = 'Charges';
		
		$entry[grouping] = $charge[3];
		$entry[entry] = $charge[0];
		$entry[parent_account_billing_currency] = $_POST[parent_account_billing_currency];
		
		$billing->entry = serialize($entry);
		if($billing->currency != $entry[parent_account_billing_currency]){
			$rate_array = get_rate($billing->entry_date);
			$charge[1] = convert_value($charge[1], $billing->currency, $billing->entry_date,$_POST[parent_account_billing_currency]);
			//$charge[1] = $charge[1]/$rate_array[rate];
			$billing->rate_date = $rate_array[rate_date];
			$rate_array = '';
		}else{
			$billing->rate_date = $billing->entry_date;
		}
		
		check_rate_date($billing->entry_date,$billing->rate_date);
		
		if(($charge[0] != 'Equipment Deposit') && ($charge[0] != 'Equipment Deposit C&W')){
			$billing->amount = -$charge[1] * 1.18;
		} else {
			$billing->amount = -$charge[1];
		}
		$billing->balance = newBalance($billing->amount,$billing->parent_id,$billing->entry_date);
		if($_POST[user] == ''){$_POST[user] = $_POST[user2];}
		$billing->user = trim($_POST[user]);
		
		$check_object = $billing->GetList(array(
												array('entry_id','=',$billing->entry_id),
												array('entry','=',$billing->entry)
												)); $check_object = $check_object[0];
		if($check_object){
			$id = $check_object->id;
		}else{
			$id = Adjust_Balances_and_Save($billing);
		}
	}
}

function save_adjustments(){
	
	$billing = new wimax_billing();

	$billing->entry_id = $_POST[reciept];
	$billing->parent_id = $_POST[parent_id];
	$billing->account_id = $_POST[account_id];
	if(!$billing->parent_id){ $billing->parent_id = $billing->account_id;}
	$billing->bill_start = $_POST[billing_start];
	$billing->bill_end = $_POST[billing_expiry];
	$billing->matched_invoice = $_POST[matched_invoice];
	$billing->billing_date = '';
	$billing->currency = $_POST[currency];
	if(!$_POST[entry_date]){$billing->entry_date = date('Y-m-d');}else{ $billing->entry_date = $_POST[entry_date];}
	if($_POST[entry_type] == ''){$billing->entry_type = 'Adjustment';}else{ $billing->entry_type = $_POST[entry_type];}
	$rate_array = get_rate($billing->entry_date);
	$billing->rate_date = get_rate_date($billing->entry_date,$rate_array[rate_date]);
	
	$entry[wrong_value_submitted] = $_POST[wrong_value_submitted];
	$entry[correct_value] = $_POST[correct_value];
	$entry[details] = $_POST[details];
	$entry[parent_account_billing_currency] = $_POST[parent_account_billing_currency];
	$entry[approved_by] = $_POST[approved_by];
	if($_POST[request] != ''){//Adjustment and manual prorates
		$entry[grouping] = $_POST[request];
		$entry[entry] = $_POST[product];
	}else{//Automatic Prorating
		$entry[grouping] = $_POST[grouping];
		$entry[entry] = $_POST[product];
	}

	//( && ($_POST[request] == 'Refund'))
	
	//incorporating VAT for Service Waivers, Credit notes except discounts and equipment waivers
	if(!(
		 ($_POST[request] == 'Cash Discount') || 
		 ($_POST[request] == 'Waiver on Equipment') ||
		 ($_POST[request] == 'Refund')
		)
	  ){
		$_POST[adjustments] = $_POST[adjustments] * 1.18;
		$entry[wrong_value_submitted] = round($_POST[wrong_value_submitted] * 1.18,4);
		$entry[correct_value] = round($_POST[correct_value] * 1.18,4);
	}
	
	$billing->entry = serialize($entry);
	
	//Using rate date because adjustments can also be in the future so if we have already determined of the rate and entry date to use, we only need rate date here
	$billing->amount = convert_value($_POST[adjustments], $billing->currency, $billing->rate_date, $entry[parent_account_billing_currency]);
	
	$billing->balance = newBalance($billing->amount,$billing->parent_id,$billing->entry_date);
	$billing->user = trim($_POST[user]);
	
	$check_object = $billing->GetList(array(
											array('('),
											array('entry_date','=',$billing->entry_date),
											array('entry','=',$billing->entry),
											array('bill_start','=',$billing->bill_start),
											array('entry_type','=',$billing->entry_type),
											array('account_id','=',$billing->account_id),
											array('entry','NOT LIKE','%The Prorator function%'),
											array(')'),
											array('OR'),
											array('('),
											array('entry_date','=',$billing->entry_date),
											array('entry_type','=',$billing->entry_type),
											array('account_id','=',$billing->account_id),
											array('entry','LIKE','%Units from '.date_reformat($billing->bill_start,'').'%'),
											array('entry','LIKE','%'.$entry[entry].'%'),
											array(')'),
											array('OR'),
											array('('),
											array('entry_date','=',$billing->entry_date),
											array('entry_type','=',$billing->entry_type),
											array('account_id','=',$billing->account_id),
											array('entry','LIKE','%Units to '.date_reformat($billing->bill_end,'').'%'),
											array('entry','LIKE','%'.$entry[entry].'%'),
											array(')')
										)); $check_object = $check_object[0];
	if($check_object){
		$id = $check_object->id;
		echo "Duplicate entry detected. Amount ".$billing->amount." on entry date ".$billing->entry_date." Parent ID ".$billing->parent_id." with Account Number ".$billing->account_id." with details ".$entry[entry]." ".$entry[grouping]." not entered. <br> CHECK STATEMENT to identify any irregularities<br><br>";
		//echo "Not Saving ====>>> <br>"; print_r($billing); echo "<br><br>";
	}else{
		if(abs($billing->amount) > 0){
			//echo "Saving ====>>> <br>"; print_r($billing); echo "<br><br>";
			$id = Adjust_Balances_and_Save($billing);
		}
	}
}

function Adjust_Balances_and_Save($billing_obj){
	$billing = new wimax_billing();
	
	$check_condition = array(	array('entry_date','>',$billing_obj->entry_date),
								array('parent_id','=',$billing_obj->parent_id)
							);
	
	$check_billing_objects = $billing->GetList($check_condition,'id',true,'');
	
	$ids[line] = array();
	if($billing_obj->id){array_push($ids[line],$billing_obj->id);}
	
	if($check_billing_objects){
		foreach($check_billing_objects as $object){
			array_push($ids[line],$object->id);
		}
	}
	$i = 0;
	$billing = $billing_obj;
	$billing->id = $ids[line][$i];
	$running_balance = $billing->balance;
	$saved_id = $billing->Save();
	
	if($check_billing_objects){
		foreach($check_billing_objects as $object){
			$billing = $object;
			$billing->id = $ids[line][++$i];
			$running_balance += $billing->amount;
			$billing->balance = $running_balance;
			$billing->Save();
		}
	}
	
	return $saved_id;
}

function previousBalance($parent_id,$period_start,$currency){
	
	//secho "Previous bal of ".$parent_id." at ".$period_start." in ".$currency."<br>";
	$myquery = new uniquequerys();
	
	$query = "
		select
			accounts_cstm.selected_billing_currency_c as selected_billing_currency
		from
			accounts_cstm
		where
			accounts_cstm.crn_c='".$parent_id."'
	";
	
	$result = $myquery->uniquequery($query);
	$account_tx_currency = $result[selected_billing_currency];
	
	if(!$currency){
		$currency = $account_tx_currency;
	}
	
	$query = "
		select balance, rate_date from wimax_billing where `id` = (select max(`id`) from wimax_billing where entry_date < '$period_start' and  parent_id = '$parent_id')
	";
	//echo $query."<br>";

	$result = $myquery->uniquequery($query);
	if($result){
		$result[balance] = convert_value($result[balance],$account_tx_currency,$result[rate_date],$currency);
		//$result[balance] = convert_value($result[balance],"USD",$result[rate_date],$currency);
	}
	
	return $myquery->Unescape($result[balance]);
}

function AddDays($date,$days){
	$myquery = new uniquequerys();
	
	$query = "SELECT DATE_ADD('$date',INTERVAL $days DAY) as required_date";
	$result = $myquery->uniquequery($query);
	
	return $myquery->Unescape($result[required_date]);
}

function newBalance($amount, $parent_id, $date){
	
	$balance_billing = new wimax_billing();
	$myquery = new uniquequerys();
	
	$query = "select MAX(`id`) AS identifier FROM wimax_billing WHERE `parent_id` = '$parent_id' AND entry_date <= '$date';";
	$result = $myquery->uniquequery($query);
	$lastid = $myquery->Unescape($result[identifier]);
	
	$wimax_billing_row = $balance_billing->Get($lastid);
	$newbalance = $wimax_billing_row->balance + $amount;
	
	return $newbalance;
}

function bill($startdate, $enddate, $monthly_charge){
	
	//echo "Billing [".$monthly_charge."] from [".$startdate."] to [".$enddate."]<br>";
	
	$myquery = new uniquequerys();
	
	$result = $myquery->uniquequery("SELECT period_diff(date_format('$enddate','%Y%m'), date_format('$startdate','%Y%m')) AS identifier;");
	$bill['period'] = $myquery->Unescape($result[identifier]);
	$bill['amount'] = $bill['period'] * $monthly_charge;
	
	//calculating the number of days to remove from the begining and the total number of days in the start date month
	$bill['amount'] += prorate($startdate, $monthly_charge, 'subtract');
	
	//calculating the number of days to add to the begining of the last month and the number of days in the end date month
	$bill['amount'] += prorate($enddate, $monthly_charge, 'add');
	
	return $bill;
}

//calculating the number of days to add/remove from the end/begining and the total number of days in the end/start date month
function prorate($date, $monthly_charge, $operation){
	
	$myquery = new uniquequerys();
	
	$result_array = $myquery->multiplerow_query("select datediff('$date',concat(date_format('$date','%Y-%m-'),'01')) AS days, day( last_day('$date')) AS total_month_days;");
	$result_array = $result_array[0];

	if($operation == 'subtract'){
		$prorated_charge -= ($result_array[days]/$result_array[total_month_days]) * $monthly_charge;
	}else{
		$prorated_charge += (++$result_array[days]/$result_array[total_month_days]) * $monthly_charge;
	}
	
	//echo $prorated_charge."<br><br>";
	return $prorated_charge;
}

function save_prorated_values($account_id){
	
	echo "Prorating Account number [".$account_id."]<br>";

	$myquery = new uniquequerys();
	
	$currency_result = $myquery->uniquequery("
		select
			accounts_cstm.selected_billing_currency_c as selected_billing_currency
		from
			accounts_cstm
			inner join accounts on (accounts.id = accounts_cstm.id_c)
		where
			accounts_cstm.crn_c = (select accounts_cstm.mem_id_c from accounts_cstm where accounts_cstm.crn_c = '$account_id' and
			accounts.deleted = 0)
	");
	
	$accnt_services = array();
	
	$bandwidth = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.bandwidth_count_1_c as quantity,
				  accounts_cstm.bandwidth_discount_c as discount,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN ps_products ON (accounts_cstm.download_bandwidth_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts.`status` = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
											 ");
	//var_dump($bandwidth); echo "<br><br>";

	foreach($bandwidth as $row){
		array_push($accnt_services,$row);
	}
	
	$rental = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.bandwidth_package_count_c as quantity,
				  accounts_cstm.bandwidth_package_discount_c as discount,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN ps_products ON (accounts_cstm.shared_packages_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts.`status` = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
	");
	//var_dump($rental); echo "<br><br>";

	if($rental){
		foreach($rental as $row){
			array_push($accnt_services,$row);
		}
	}
	
	$maintenance = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.maintenance_option_count_c as quantity,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency,
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN ps_products ON (accounts_cstm.maintenance_option_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts.`status` = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
	");
	
	if($maintenance){
		foreach($maintenance as $row){
			array_push($accnt_services,$row);
		}
	}
	
	$domain_hosting = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.domain_hosting_start_date_c AS start_date,
				  cn_contracts_cstm.domain_hosting_end_date_c as expiry_date,
				  accounts_cstm.no_domains_d_hosting_c as quantity,
				  accounts_cstm.discount_domain_hosting_c as discount,
				  accounts_cstm.service_type_internet_c as service_type,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_type_domain_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.domain_hosting_status_c = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
	");
	//var_dump($domain_hosting); echo "<br><br>";
	
	if($domain_hosting){
		foreach($domain_hosting as $row){
			array_push($accnt_services,$row);
		}
	}
	
	$domain_registration = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.domain_reg_start_date_c as start_date,
				  cn_contracts_cstm.domain_reg_end_date_c as expiry_date,
				  accounts_cstm.no_domains_registration_c as quantity,
				  accounts_cstm.discount_domain_registration_c as discount,
				  accounts_cstm.service_type_internet_c as service_type,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_domain_registration_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND

				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.domain_reg_status_c = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
	");
	//var_dump($domain_registration); echo "<br><br>";
	
	if($domain_registration){
		foreach($domain_registration as $row){
			array_push($accnt_services,$row);
		}
	}
	
	$mail_hosting = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.mail_hosting_start_date_c as start_date,
				  cn_contracts_cstm.mail_hosting_end_date_c as expiry_date,
				  accounts_cstm.no_of_100mb_email_c as quantity,
				  accounts_cstm.discount_mail_hosting_c as discount,
				  ps_products.name as product_name,
				  accounts_cstm.service_type_internet_c as service_type,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_mail_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.mail_hosting_status_c = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
	");
	//var_dump($mail_hosting); echo "<br><br>";

	if($mail_hosting){
		foreach($mail_hosting as $row){
			array_push($accnt_services,$row);
		}
	}
	
	$web_hosting = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.web_hosting_start_c as start_date,
				  cn_contracts_cstm.web_hosting_end_date_c as expiry_date,
				  accounts_cstm.no_domains_web_hosting_c as quantity,
				  accounts_cstm.discount_web_hosting_c as discount,
				  ps_products.name as product_name,
				  accounts_cstm.service_type_internet_c as service_type,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_web_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.web_hosting_status_c = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
	");
	//var_dump($web_hosting); echo "<br><br>";

	if($web_hosting){
		foreach($web_hosting as $row){
			array_push($accnt_services,$row);
		}
	}
	
	//Hire purchase
	$hire_purchase = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.hire_purchase_start_c as start_date,
				  cn_contracts_cstm.hire_purchase_end_c as expiry_date,
				  accounts_cstm.hire_purchase_count_c as quantity,
				  accounts_cstm.hire_purchase_discount_c as discount,
				  ps_products.name as product_name,
				  accounts_cstm.service_type_internet_c as service_type,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.hire_purchase_product_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.hire_purchase_status_c = 'Active' AND
				  accounts_cstm.crn_c = '$account_id'
	");
	//var_dump($hire_purchase); echo "<br><br>";

	if($hire_purchase){
		foreach($hire_purchase as $row){
			array_push($accnt_services,$row);
		}
	}
	
	echo "For data <br>"; foreach($accnt_services as $gtu){
		print_r($gtu); echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<br>";
	}
	
	echo "<br>";
	
	$_POST[parent_account_billing_currency] = $currency_result[selected_billing_currency];
	$_POST[parent_id] = $accnt_services[0][parent_id];
	$_POST[user] = trim($_POST[user2]);
	$_POST[account_id] = $account_id;
	$_POST[approved_by] = "The Prorator function";
	foreach($accnt_services as $accnt_service){
		//echo "Dealing with "; print_r($accnt_service); echo "<br>";
		$_POST[billing_start] = $accnt_service[start_date];
		$_POST[billing_expiry] = $accnt_service[expiry_date];
		$_POST[currency] = $accnt_service[billing_currency];
		$accnt_service[product_price] = $accnt_service[product_price]*$accnt_service[quantity]*(1-$accnt_service[discount]/100);
		$_POST[product] = $accnt_service[product_name];
		//$_POST[product] = $accnt_service[product_name].'. '.$accnt_service[quantity].' Unit(s) at '.$accnt_service[product_price].' '.$accnt_service[billing_currency].' each';
		
		$op_day_array[$accnt_service[start_date]] = 'subtract';
		$op_day_array[$accnt_service[expiry_date]] = '';
		
		foreach($op_day_array as $date => $operation){
			$_POST[entry_date] = $date;
			$amount = -prorate($date,$accnt_service[product_price],$operation);
			//echo "Dealing with ".$accnt_service[product_name]." amount is ".$amount."<br>";
			$_POST[reciept] = generateRecieptNo('');
			
			$_POST[entry_type] = 'Adjustment';
			if($amount > 0){
				$_POST[adjustments] = $amount;
				$_POST[grouping] = 'Credit Note';
				$_POST[details] = "Prorating ".$accnt_service[product_name]." at ".accounts_format($accnt_service[discount])."% discount for ".accounts_format($accnt_service[quantity])." Units from ".date_reformat($date,'');
				//print_r($_POST); echo "<br><br>";
				save_adjustments();
			}elseif(($amount < 0) && (trim($accnt_service[service_type]) == 'Prepaid')){
				$_POST[details] = "Prorating ".$accnt_service[product_name]." at ".accounts_format($accnt_service[discount])."% discount for ".accounts_format($accnt_service[quantity])." Units to ".date_reformat($date,'');
				if(abs($amount) != abs($accnt_service[product_price])){
					$_POST[grouping] = 'Debit Note';
				}else{
					$_POST[grouping] = $accnt_service[grouping];
					$_POST[entry_type] = 'Services';
				}
				$_POST[adjustments] = $amount;
				//print_r($_POST); echo "<br><br>";
				save_adjustments();
			}else{
				//echo "Dealing with [[".$accnt_service[product_name]."]] Not saveing <br>";
			}
		}
		
		//clearing the array for the next
		unset($op_day_array);
	}
}

function generateRecieptNo($entry_id){
	
	$myquery = new uniquequerys();
	
	$result = $myquery->uniquequery("select MAX(entry_id) AS identifier FROM wimax_billing;");
	$lastentry = $myquery->Unescape($result[identifier]);
	
	if(!isset($entry_id) || ($entry_id == '')){
		if(intval($lastentry) < 1) { $entry_id = 1000000;} else { $entry_id = ++$lastentry;}
	}

	return $entry_id;
}

function generate_invoice_no($id){
	
	$myquery = new uniquequerys();
	
	$result = $myquery->uniquequery("select MAX(invoice_number) AS identifier FROM wimax_invoicing;");
	$lastentry = $myquery->Unescape($result[identifier]);
	
	if(!isset($id) || ($id == '')){
		$id = ++$lastentry;
	}

	return $id;
}

function generateInvoiceNumber($invoice_num){
	
	$myquery = new uniquequerys();
	
	$result = $myquery->uniquequery("select MAX(invoice_number) AS identifier FROM wimax_billing;");
	$last_invoice_num = $myquery->Unescape($result[identifier]);
	
	if(!isset($invoice_num) || ($invoice_num == '')){
		if(intval($last_invoice_num) < 1) { $invoice_num = 1000000; } else { $invoice_num = ++$last_invoice_num;}
	}

	return $invoice_num;
}

function accountStatement($parent_id,$start,$end){
	
	$billing = new wimax_billing();
	$myquery = new uniquequerys();
	
	$check_conditions = array(array('parent_id','=',$parent_id));
	
	if($end != ''){
		array_push($check_conditions,array("entry_date","<=",$end));
		//
		//array_push($check_conditions,array("entry_date","<=",date('Y-m-d')));
	}
	
	if($start){array_push($check_conditions,array("entry_date",">=",$start));}
	
	$user_data = $billing->GetList($check_conditions,'id',true,'');

	$html = '
	<table border="0" align="left" cellpadding="2" cellspacing="0">
      <tr>
        <td background="images/table_header.jpg" class="style14">Date</td>
        <td background="images/table_header.jpg" class="style14">Entry Number</td>
		<td background="images/table_header.jpg" class="style14">Account Name</td>
		<td background="images/table_header.jpg" class="style14">Invoice Number</td>
        <td background="images/table_header.jpg" class="style14">Category</td>
        <td background="images/table_header.jpg" class="style14">Description</td>
        <td background="images/table_header.jpg" class="style14">Amount</td>
        <td background="images/table_header.jpg" class="style14">Balance</td>
		<td background="images/table_header.jpg" class="style14"></td>
      </tr>';

	  if($user_data){
		$html .= '<tr style="font-weight:bold;">
						<td class="style11" colspan="7">Balance brought Forward '.date_reformat($start,'').'</td>
						<td class="style11" align="right">'.accounts_format($user_data[0]->balance - $user_data[0]->amount).'</td>
						<td class="style11" align="right"></td>
					  </tr>
		';

	  	foreach($user_data as $user_data_row){
			if(is_array(unserialize($user_data_row->entry))){
				$entry = unserialize($user_data_row->entry);
				$user_data_row->entry = $entry[entry];
			}else{
				echo "Not array<br>";
			}
			//Getting the child Account Name if it is a child otherwise it shall be the parent
			$query = "SELECT accounts.name FROM accounts INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c) where accounts_cstm.crn_c='$user_data_row->account_id';";		
			$result = $myquery->uniquequery($query);
			$user_data_row->account_name = $myquery->Unescape($result[name]);

		  $html .= '<tr>
						<td class="style11">'.date_reformat($user_data_row->entry_date,'').'</td>
						<td class="style11">'.$user_data_row->entry_id.'</td>
						<td class="style11">'.$user_data_row->account_name.'</td>
						<td class="style11">'.$user_data_row->invoice_number.'</td>
						<td class="style11">'.$user_data_row->entry_type.'</td>';
				if($user_data_row->entry_type == 'Service'){
					$html .= '<td class="style11">'.$user_data_row->entry.' For the Period ('.$user_data_row->bill_start.' to '.$user_data_row->bill_end.')</td>';
				} else {
					$html .= '<td class="style11">'.$user_data_row->entry.'</td>';
				}
					$html .= '<td class="style11" align="right">'.accounts_format($user_data_row->amount).'</td>
						<td class="style11" align="right">'.accounts_format($user_data_row->balance).'</td>
						<td class="style11" align="right"><a href="cst_transaction.php?action=print&id='.$user_data_row->id.'&title='.$user_data_row->entry_type.'" target="_blank">print</a></td>
					  </tr>
				';
		  }

		$html .= '<tr style="font-weight:bold;">
						<td class="style11" colspan="7">Closing Balance '.date_reformat($end,'').'</td>
						<td class="style11" align="right">'.accounts_format($user_data[count($user_data)-1]->balance).'</td>
						<td class="style11" align="right"></td>
					  </tr>
		';
	  }else{
	  		$html .= 'No Account selected or account_id Has no Data';
	  }
    $html .= '</table>';
	
	return $html;
}

function accounts_format($number){
	
	$myquery = new uniquequerys();
	
	$result = $myquery->uniquequery("select format(abs('$number'),2) as identifier;");
	$formated_number = $myquery->Unescape($result[identifier]);
	
	if($number < 0){
		$formated_number = "(".$formated_number.")";
	}
	
	return $formated_number;
}

function date_reformat($date,$format){

	$myquery = new uniquequerys();
	
	if(!$format){
		$format = '%d %b %Y';
	}
	
	$result = $myquery->uniquequery("select date_format('$date', '$format') as identifier;");
	$date = $myquery->Unescape($result[identifier]);
	
	return $date;
}

function get_month($date){
	$myquery = new uniquequerys();
	$result = $myquery->uniquequery("SELECT month('$date') as month");
	return $myquery->Unescape($result[month]);
}

function billrun_invoiceGeneration($billrun_date){

	$billing = new wimax_billing();
	$invoicing = new wimax_invoicing();
	$myquery = new uniquequerys();

	//Getting Bill run and last bill run dates
	if($billrun_date == ''){
		$result = $myquery->uniquequery("SELECT LAST_DAY(now()) as today");
		$billrun_date = $myquery->Unescape($result[today]);
		$result = $myquery->uniquequery("SELECT concat(date_format(LAST_DAY(now()),'%Y-%m-'),'01') as period_start");
		$period_start_date = $myquery->Unescape($result[period_start]);
	}else{
		$result = $myquery->uniquequery("SELECT LAST_DAY('$billrun_date') as thedate");
		$billrun_date = $myquery->Unescape($result[thedate]);
		$result = $myquery->uniquequery("SELECT concat(date_format(LAST_DAY('$billrun_date'),'%Y-%m-'),'01') as period_start");
		$period_start_date = $myquery->Unescape($result[period_start]);
	}
	
	//Getting parent ids for invoicing
	$query = "
		SELECT 
			accounts.name,
			accounts_cstm.contact_person_c,
			accounts_cstm.billing_add_strt_c,
			accounts_cstm.billing_add_area_c,
			accounts_cstm.billing_add_town_c,
			accounts_cstm.billing_add_plot_c,
			accounts_cstm.billing_add_district_c,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.invoicing_type_c as invoicing_type,
			accounts_cstm.mem_id_c as parent_id,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.crn_c,
			accounts_cstm.selected_billing_currency_c as selected_billing_currency
		FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
			INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
		WHERE
			 accounts.deleted = '0' AND 
			 cn_contracts.deleted = '0' AND
			 accounts_cstm.service_type_internet_c = 'Postpaid' AND
			 accounts_cstm.invoicing_type_c = 'normal' AND
			 accounts_cstm.mem_id_c != '' AND
			( 
			  (cn_contracts_cstm.web_hosting_status_c = 'Active' AND cn_contracts_cstm.web_hosting_start_c <= '$billrun_date') OR
			  (cn_contracts_cstm.mail_hosting_status_c = 'Active' AND cn_contracts_cstm.mail_hosting_start_date_c <= '$billrun_date') OR
			  (cn_contracts_cstm.domain_hosting_status_c = 'Active' AND cn_contracts_cstm.domain_hosting_start_date_c <= '$billrun_date') OR
			  (cn_contracts_cstm.domain_reg_status_c = 'Active' AND cn_contracts_cstm.domain_reg_start_date_c <= '$billrun_date') OR
			  (cn_contracts_cstm.hire_purchase_status_c = 'Active' and cn_contracts_cstm.hire_purchase_start_c = '$billrun_date')
			)
		GROUP BY
			accounts_cstm.mem_id_c
		UNION
		SELECT 
			accounts.name,
			accounts_cstm.contact_person_c,
			accounts_cstm.billing_add_strt_c,
			accounts_cstm.billing_add_area_c,
			accounts_cstm.billing_add_town_c,
			accounts_cstm.billing_add_plot_c,
			accounts_cstm.billing_add_district_c,
			accounts_cstm.customer_type_c as customer_type,
			accounts_cstm.invoicing_type_c as invoicing_type,
			accounts_cstm.mem_id_c as parent_id,
			accounts_cstm.service_type_internet_c as service_type,
			accounts_cstm.crn_c,
			accounts_cstm.selected_billing_currency_c as selected_billing_currency
		FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
		WHERE
			accounts.deleted = '0' AND 
			cn_contracts.deleted = '0' AND
			accounts_cstm.service_type_internet_c = 'Postpaid' AND
			accounts_cstm.invoicing_type_c = 'normal' AND
			accounts_cstm.mem_id_c != '' AND
			cn_contracts.status = 'Active' AND cn_contracts.start_date <= '$billrun_date'
		GROUP BY
			accounts_cstm.mem_id_c
		";

	$parent_data = $myquery->multiplerow_query($query);
	
	//Populating parent contact Information	
	foreach($parent_data as $parent_row){
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][username] = $parent_row[preferred_username_c];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][account_name] = $parent_row[name];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][individual] = $parent_row[contact_person_c];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][physical_address] =	$parent_row[billing_add_plot_c]."<br>".
																$parent_row[billing_add_strt_c]."<br>".
																$parent_row[billing_add_district_c]."<br>";
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][account_number] = $parent_row[parent_id];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][invoice_date] = $billrun_date;
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][invoice_currency] = $parent_row[selected_billing_currency];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][customer_type] = $parent_row[customer_type];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][invoice_start] = $period_start_date;
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][invoice_end] = $billrun_date;
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][invoice_due_date] = AddDays($billrun_date,14);
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][Title] = 'TAX INVOICE';
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][contract_start] = $parent_row[start_date];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][service_type] = $parent_row[service_type];
		$parent_accts[$parent_row[parent_id]][xtra][Other_details][invoicing_type] = $parent_row[invoicing_type];
	}
	
	//Populate service charges and do appropriate billing on ALL (Post and Pre Paid) parent account
	$accounts_data = array();
	
	//Bandwidth
	$data_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.bandwidth_count_1_c as quantity,
				  accounts_cstm.bandwidth_discount_c as discount,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN ps_products ON (accounts_cstm.download_bandwidth_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  cn_contracts.start_date <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts.`status` = 'Active'
	");
	
	foreach($data_data as $row){
		array_push($accounts_data,$row);
	}

	//Monthly rentals
	$rental_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.bandwidth_package_count_c as quantity,
				  accounts_cstm.bandwidth_package_discount_c as discount,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN ps_products ON (accounts_cstm.shared_packages_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  cn_contracts.start_date <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts.`status` = 'Active'
	");
	
	foreach($rental_data as $row){
		array_push($accounts_data,$row);
	}
	

//IMPORTANT TO ADD THIS CONDICTION TO ALL QUERIES BELOW
//ps_products.type = 'Service'

	//Maintenance options
	$maintenance_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.maintenance_option_count_c as quantity,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN ps_products ON (accounts_cstm.maintenance_option_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  cn_contracts.start_date <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  ps_products.type = 'Service' AND
				  cn_contracts.`status` = 'Active'
	");
	
	foreach($maintenance_data as $row){
		$row[discount] = 0;
		array_push($accounts_data,$row);
	}
	
	//Domain Hosting
	$domain_hosting_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts_cstm.domain_hosting_start_date_c AS start_date,
				  cn_contracts_cstm.domain_hosting_end_date_c as expiry_date,
				  accounts_cstm.no_domains_d_hosting_c as quantity,
				  accounts_cstm.discount_domain_hosting_c as discount,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_type_domain_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				where
				  cn_contracts_cstm.domain_hosting_start_date_c <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.domain_hosting_status_c = 'Active'
	");
	
	foreach($domain_hosting_data as $row){
		array_push($accounts_data,$row);
	}
	
	//Domain Registration
	$domain_registration_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts_cstm.domain_reg_start_date_c as start_date,
				  cn_contracts_cstm.domain_reg_end_date_c as expiry_date,
				  accounts_cstm.no_domains_registration_c as quantity,
				  accounts_cstm.discount_domain_registration_c as discount,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.name as product_name,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_domain_registration_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  cn_contracts_cstm.domain_reg_start_date_c <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.domain_reg_status_c = 'Active'
	");
	
	foreach($domain_registration_data as $row){
		array_push($accounts_data,$row);
	}
	
	//Email Hosting
	$mail_hosting_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts_cstm.mail_hosting_start_date_c as start_date,
				  cn_contracts_cstm.mail_hosting_end_date_c as expiry_date,
				  accounts_cstm.no_of_100mb_email_c as quantity,
				  accounts_cstm.discount_mail_hosting_c as discount,
				  ps_products.name as product_name,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_mail_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  cn_contracts_cstm.mail_hosting_start_date_c <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.mail_hosting_status_c = 'Active'
	");
	
	foreach($mail_hosting_data as $row){
		array_push($accounts_data,$row);
	}
	
	//Web Hosting
	$web_hosting_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts_cstm.web_hosting_start_c as start_date,
				  cn_contracts_cstm.web_hosting_end_date_c as expiry_date,
				  accounts_cstm.no_domains_web_hosting_c as quantity,
				  accounts_cstm.discount_web_hosting_c as discount,
				  ps_products.name as product_name,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.package_web_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  cn_contracts_cstm.web_hosting_start_c <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  web_hosting_status_c = 'Active'
	");
	
	foreach($web_hosting_data as $row){
		array_push($accounts_data,$row);
	}
	
	//lease/hire
	$hire_purchase_data = $myquery->multiplerow_query("
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  accounts.name,
				  cn_contracts_cstm.hire_purchase_start_c as start_date,
				  cn_contracts_cstm.hire_purchase_end_c as expiry_date,
				  accounts_cstm.hire_purchase_count_c as quantity,
				  accounts_cstm.hire_purchase_discount_c as discount,
				  ps_products.name as product_name,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.customer_type_c as customer_type,
				  accounts_cstm.invoicing_type_c as invoicing_type,
				  accounts_cstm.selected_billing_currency_c as selected_billing_currency,
				  ps_products.price as product_price,
				  ps_products_cstm.product_grouping_c as grouping,
				  ps_products_cstm.billing_currency_c as billing_currency
				FROM
				 accounts
				 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
				 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
				 INNER JOIN cn_contracts_cstm ON (cn_contracts.id=cn_contracts_cstm.id_c)
				 INNER JOIN ps_products ON (accounts_cstm.hire_purchase_product_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  cn_contracts_cstm.hire_purchase_start_c <= '$billrun_date' AND
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.hire_purchase_status_c = 'Active'
	");
	
	foreach($hire_purchase_data as $row){
		array_push($accounts_data,$row);
	}
	
	
	$html .= "Running Bill run on ".$_POST[HOST]."<br> \n";
	
	//Populate service charges and do appropriate billing on each parent account.
	foreach($accounts_data as $user){
		//creating an array of all including child POST PAID accounts regardless of services per account ie group by 
					
		$currency_result = $myquery->uniquequery("
				select
					accounts_cstm.selected_billing_currency_c as selected_billing_currency
				from
					accounts_cstm
					inner join accounts on (accounts.id = accounts_cstm.id_c)
				where
					accounts_cstm.crn_c = (select accounts_cstm.mem_id_c from accounts_cstm where accounts_cstm.crn_c = '$user[crn_c]' and
					accounts.deleted = 0)
		");
			
		//Converting Charge currencies to parent account currency
		$rate_array='';
		if($user[billing_currency] != $currency_result[selected_billing_currency]){
			//getting the UGX price
			$user[used_price] = $user[product_price];
			//Then converting it to Parent account currency
			$rate_array = get_rate($billrun_date);
			//$user[product_price] = $user[used_price]/$rate_array[rate];
			$user[product_price] = convert_value($user[used_price], $user[billing_currency], $billrun_date,$currency_result[selected_billing_currency]);
			//$html .= "Rate [".number_format($rate_array[rate],0)."] on [".$user[product_name]."] @ [".number_format(($user[product_price]*$rate_array[rate]*1.18),4)."] date [".$rate_array[rate_date]."] gives  [".number_format($user[product_price],4)."] <br> \n";
		}else{
			$user[used_price] = $user[product_price];
		}
		
		//Adding VAT
		$user[product_price] *= 1.18;
		
		//getting the Net charge
		$user[amount] = -$user[product_price]*$user[quantity]*(1-$user[discount]/100);
		if(abs($user[amount]) == 0){
			$html .= "Product [".$user[product_name]."] @ Price [".number_format(($user[product_price] /1.18),4)."] Quantity [".number_format($user[quantity],2)."] Discount [".number_format($user[discount],4)."] == [".number_format($user[amount],4)."] <br> \n";
		}
		
		if($user[service_type] == 'Postpaid'){
			if($unique_accts[$user[crn_c]] == ''){
				$unique_accts[$user[crn_c]] = $user;
			}
		}
		
		//BILLING ALL ACCOUNTS
		if(
			   	(
					($user[service_type] == 'Postpaid') ||
			   		(	($user[service_type] == 'Prepaid') && 
						(strtotime($user[expiry_date]) > strtotime($billrun_date))
					)
				)&&
			   	(strtotime($user[start_date]) <= strtotime($billrun_date))
		   ){
			$billing->entry_id = generateRecieptNo('');
			$billing->parent_id = $user[parent_id];
			$billing->account_id = $user[crn_c];
			if(!$billing->parent_id){$billing->parent_id = $billing->account_id;}
			$billing->bill_start = $user[start_date];
			$billing->bill_end = $user[expiry_date];
			$billing->billing_date = $billrun_date;
			$billing->entry_date = $billrun_date;
			if($rate_array[rate_date] != ''){
				$billing->rate_date = $rate_array[rate_date];
			}else{
				$billing->rate_date = $billrun_date;
			}
			$billing->currency = $user[billing_currency];
			$billing->entry_type = 'Services';
				$entry[grouping] = $user[grouping];
				$entry[entry] = $user[product_name];
				//$entry[details] = ($user[product_price]/1.18).$user[billing_currency].' X '.$user[quantity].' at '.round($user[discount],2).'% Discount';
				$entry[details] = number_format($user[quantity],1).' Unit(s) at ('.number_format($user[used_price],2).' with '.number_format($user[discount],2).'% discount) '.accounts_format($user[used_price]*(1-$user[discount]/100)).' '.$user[billing_currency].' per month';
				$entry[parent_account_billing_currency] = $currency_result[selected_billing_currency];
				$payment_details = $entry[details];
			$billing->entry = serialize($entry);
			$billing->amount = $user[amount];
			$billing->balance = newBalance($billing->amount,$billing->parent_id, $billing->entry_date);
			$billing->user = 'Bill Run';
		
			$check_object = $billing->GetList(array(
												array('entry_date','=',$billrun_date),
												array('account_id','=',$billing->account_id),
												array('entry_type','=',$billing->entry_type),
												array('entry','LIKE','%'.$entry[grouping].'%')
													)
												);
			if(count($check_object) != 0){
				$check_object = $check_object[0];
				$billing->id = $check_object->id;
				$check_object->entry = unserialize($check_object->entry);
				$billing->balance = $billing->balance - $check_object->amount;
				$html .= "ID is ".$check_object->id." Charge [".number_format($check_object->amount,2)."] on Product ".$check_object->entry[entry]." for Account Number ".$check_object->account_id." is Already there <br> Updating with [".number_format($billing->amount,2)."] on Product ".$user[product_name]." for Account Number ".$billing->account_id." = [".$user[name]."] <br> \n";
				$id = Adjust_Balances_and_Save($billing);
			}else{
				$billing->id = '';
				if($billing->amount != ''){
					$html .= "Saving Charge [".number_format($billing->amount,2)."] on Product ".$user[product_name]." for Account Number ".$billing->account_id." = [".$user[name]."]<br> \n";
					$id = Adjust_Balances_and_Save($billing);
				}else{
					$html .= "NOT saving Charge [".number_format($billing->amount,2)."] on Product ".$user[product_name]." for Account Number ".$billing->account_id." = [".$user[name]."] is blank. Not saving ...<br> \n";
				}
			}
			
			//Coment code in the if out coz Zam Jammed it
			if(($user[customer_type] == 'WTU Staff') || ($user[customer_type] == 'Entitled (WTU Staff)')){
				/*$payment = new wimax_billing();
			
				$payment->entry_id = generateRecieptNo('');
				$payment->parent_id = $user[parent_id];
				$payment->account_id = $user[crn_c];
				if(!$payment->parent_id){$payment->parent_id = $payment->account_id;}
				$payment->bill_start = $user[start_date];
				$payment->bill_end = $user[expiry_date];
				$payment->currency = $user[billing_currency];
				$payment->billing_date = $billrun_date;
				$payment->entry_date = $billrun_date; 
				$payment->rate_date = $payment->entry_date;
				$payment->entry_type = 'Payment';
				$payment->matched_invoice = '';
				if($user[customer_type] == 'WTU Staff'){
					$entry[grouping] = 'Off Staff Salary';
					$entry[details] = "This period's staff salary deduction for ".$payment_details;
					$entry[entry] = 'Recovered from Salary of Staff member';
					//$to = 'zam.bitarabeho@waridtel.co.ug,charles.mwijukye@waridtel.co.ug';
					$to = 'ccbusinessanalysis@waridtel.co.ug';
				}else{
					$entry[grouping] = 'Benefits/entitled';
					$entry[details] = "This period's company cover for ".$payment_details;
					//$to = 'zam.bitarabeho@waridtel.co.ug,charles.mwijukye@waridtel.co.ug';
					$to = 'ccbusinessanalysis@waridtel.co.ug';
					$entry[entry] = 'Benefit/Entitled provided by the Company';
				}
				$payment->entry = serialize($entry);
				
				$payment->amount = convert_value($user[amount], $payment->currency, $payment->entry_date,'USD');
				
				$payment->balance = newBalance($payment->amount,$payment->parent_id,$payment->entry_date);
				$payment->user = 'Bill Run';
				
				$check_payment = $payment->GetList(array(
														 array('entry','LIKE','%'.$entry[details].'%'),
														 array('account_id','=',$payment->account_id),
														 array('entry_date','=',$payment->entry_date),
														 )
												   ); 
				
				$check_payment = $check_payment[0];

				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= 'From: Infinity Wimax <ccnotify@waridtel.co.ug>'."\r\n";
				
				if($payment->amount !=0){
					if($check_payment){
						$id = $check_payment->id;
						$subject = 'PAYMENT HAD ALREADY BEEN MADE (INFINITY WIMAX)';
					}else{
						$id = Adjust_Balances_and_Save($payment);
						$subject = 'PAYMENT HAS BEEN MADE (INFINITY WIMAX)';
					}
					$receipt_html = display_receipt(generate_receipt($id));
					mail($to,$subject,$receipt_html,$headers);
				}*/
			}

		}else{
			$html .= "Not Billed >> regular Charge [".number_format($user[amount],2)."] on Product ".$user[product_name]." for Account Number ".$user[parent_id]." = [".$user[name]."]. Client is on service type => [".$user[service_type]."], start date => [".$user[start_date]."], Exp date => [".$user[expiry_date]."] <br> \n";
		}
	}
	
	//clearing the data in the object
	$billing = '';
	$billing = new wimax_billing();
	
	//Summing up payments, charges and adjustments per account onto their parent account FOR INVOICING
	foreach($unique_accts as $acct){
		if(($acct[service_type] == 'Postpaid') && ($acct[invoicing_type] == 'normal')){
			$select_conditions = array(
									array("date_format(entry_date,'%Y-%m')","=",date_reformat($billrun_date,'%Y-%m')),
									//Removing start date limitation
									//array("entry_date",">=", $acct[start_date]),
									array("account_id","=",$acct[crn_c])
									);
			
			$billing_data = $billing->GetList($select_conditions);
			
			foreach($billing_data as $billing_row){
				$billing_row->entry = unserialize($billing_row->entry);
				
				if($billing_row->entry[parent_account_billing_currency] == ''){
					$billing_row->entry[parent_account_billing_currency] = 'USD';
				}
				
				$billing_row->amount = convert_value($billing_row->amount, $billing_row->entry[parent_account_billing_currency], $billing_row->rate_date, $acct[selected_billing_currency]);
				if($billing_row->entry_type == 'Payment'){
					$parent_accts[$acct[parent_id]][payments_sum] += $billing_row->amount;
				}

				$item_array[account_number] = $acct[crn_c];
				$item_array[currency] = $billing_row->currency;
				$item_array[rate_date] = $billing_row->rate_date;
				
				if($billing_row->entry_type == 'Charges'){
					$item_array[item] = $billing_row->entry[entry];
					$item_array[details] = $billing_row->entry[details];
					$item_array[grouping] = $billing_row->entry[grouping];
					if(($billing_row->entry[entry] != 'Equipment Deposit') && ($billing_row->entry[entry] != 'Equipment Deposit C&W')){

						$parent_accts[$acct[parent_id]][Charges] += $billing_row->amount;
						$item_array[value] = ($billing_row->amount/1.18);
						if($parent_accts[$acct[parent_id]][xtra]['Break Down'][items]){
							array_push($parent_accts[$acct[parent_id]][xtra]['Break Down'][items], $item_array);
						} else {
							$parent_accts[$acct[parent_id]][xtra]['Break Down'][items][0] = $item_array;
						}
					}else{
						$parent_accts[$acct[parent_id]][xtra]['Break Down'][untaxed_total] += $billing_row->amount;
						$item_array[value] = $billing_row->amount;
						if($parent_accts[$acct[parent_id]][xtra]['Break Down'][untaxed][items]){
							array_push($parent_accts[$acct[parent_id]][xtra]['Break Down'][untaxed][items], $item_array);
						} else {
							$parent_accts[$acct[parent_id]][xtra]['Break Down'][untaxed][items][0] = $item_array;
						}
					}
				}
			
				if($billing_row->entry_type == 'Services'){
					$parent_accts[$acct[parent_id]][Services] += $billing_row->amount;
					$item_array[item] = $billing_row->entry[entry];
					$item_array[details] = $billing_row->entry[details];
					$item_array[grouping] = $billing_row->entry[grouping];
					$item_array[value] = $billing_row->amount/1.18;
					if($parent_accts[$acct[parent_id]][xtra]['Break Down'][items]){
						array_push($parent_accts[$acct[parent_id]][xtra]['Break Down'][items], $item_array);
					}else{
						$parent_accts[$acct[parent_id]][xtra]['Break Down'][items][0] = $item_array;
					}
				}
					
				if($billing_row->entry_type == 'Adjustment'){
					$item_array[item] = $billing_row->entry[entry];
					$item_array[details] = $billing_row->entry[details];
					$item_array[grouping] = $billing_row->entry[grouping];
					if(!(($billing_row->entry[grouping] == 'Cash Discount') || ($billing_row->entry[grouping] == 'Waiver on Equipment'))){
						$item_array[value] = $billing_row->amount/1.18;
						//Summing prorated adjustments
						if($billing_row->entry[approved_by] == 'The Prorator function'){
							$parent_accts[$acct[parent_id]][prorate_adjustments_sum] += $item_array[value];
							
							if($parent_accts[$acct[parent_id]][xtra]['Break Down'][prorate_adjustments]){
								array_push($parent_accts[$acct[parent_id]][xtra]['Break Down'][prorate_adjustments], $item_array);
							} else {
								$parent_accts[$acct[parent_id]][xtra]['Break Down'][prorate_adjustments][0] = $item_array;
							}
						}else{
							$parent_accts[$acct[parent_id]][adjustments_sum] += $item_array[value];
							
							if($parent_accts[$acct[parent_id]][xtra]['Break Down'][adjustments]){
								array_push($parent_accts[$acct[parent_id]][xtra]['Break Down'][adjustments], $item_array);
							} else {
								$parent_accts[$acct[parent_id]][xtra]['Break Down'][adjustments][0] = $item_array;
							}
						}
					}else{
						$item_array[value] = $billing_row->amount;
						if($parent_accts[$acct[parent_id]][xtra]['Break Down'][other_adjustments]){
							array_push($parent_accts[$acct[parent_id]][xtra]['Break Down'][other_adjustments], $item_array);
						} else {
							$parent_accts[$acct[parent_id]][xtra]['Break Down'][other_adjustments][0] = $item_array;
						}
						$parent_accts[$acct[parent_id]][other_adjustments_sum] += $item_array[value];
					}
				}	
			}
		}
	}
	
	//generating and saving the invoices per parent account
	foreach($parent_accts as $parent_id => $parent_acct){
		$contract_start = $parent_acct[xtra][Other_details][contract_start];
		$query = "SELECT datediff('$period_start_date','$contract_start') as daysdiff";
		$result = $myquery->uniquequery($query);
		if($result[daysdiff] >= 0){
			$use_date = $period_start_date;
		}else{
			$use_date = $parent_acct[xtra][Other_details][contract_start];
		}
		$parent_acct[previous_balance] = previousBalance($parent_id,$use_date,$parent_acct[xtra][Other_details][invoice_currency]);

		$parent_acct[xtra]['Break Down'][Services] = $parent_acct[Services]/1.18;
		$parent_acct[xtra]['Break Down'][Charges] = $parent_acct[Charges]/1.18;
		$parent_acct[xtra]['Break Down'][prorate_adjustments_sum] = $parent_acct[prorate_adjustments_sum];
		$parent_acct[xtra]['Break Down'][tax_adjustments] = $parent_acct[adjustments_sum];
		$parent_acct[xtra]['Break Down'][notax_adjustments] = $parent_acct[other_adjustments_sum];
		$parent_acct[xtra]['Break Down'][sub_total] = $parent_acct[xtra]['Break Down'][Services] + 
															  $parent_acct[xtra]['Break Down'][Charges] +
															  $parent_acct[xtra]['Break Down'][tax_adjustments] +
															  $parent_acct[xtra]['Break Down'][prorate_adjustments_sum];
		$parent_acct[xtra]['Break Down'][total_vat] = $parent_acct[xtra]['Break Down'][sub_total] * 0.18;
		$parent_acct[xtra]['Break Down'][total_charges] = $parent_acct[xtra]['Break Down'][sub_total] +
														  $parent_acct[xtra]['Break Down'][total_vat] + 
														  $parent_acct[xtra]['Break Down'][untaxed_total] +
														  $parent_acct[xtra]['Break Down'][notax_adjustments];
		$parent_acct[xtra]['Break Down'][months_charges] = ($parent_acct[xtra]['Break Down'][Services]*1.18) +
														   ($parent_acct[xtra]['Break Down'][Charges]*1.18) +
														   ($parent_acct[xtra]['Break Down'][prorate_adjustments_sum]*1.18) +
														   $parent_acct[xtra]['Break Down'][untaxed_total];
		$parent_acct[xtra][Other_details][acct_bal] = $parent_acct[previous_balance] +
													$parent_acct[payments_sum] +
													$parent_acct[xtra]['Break Down'][total_charges];
		$parent_acct[xtra][Other_details][generated_by] = 'Bill Run';
		if($parent_acct[xtra][Other_details][acct_bal] < 0 ){
			$parent_acct[amount_payable] = $parent_acct[xtra][Other_details][acct_bal];
			$parent_acct[xtra][Other_details][fined_payable] = -$parent_acct[amount_payable] + 0;
		}

		$invoicing->generation_date = date('Y-m-d');
		$invoicing->parent_id = $parent_acct[xtra][Other_details][account_number];
		$invoicing->billing_date = $billrun_date;
		//setting the start date
		$invoicing->period = $period_start_date;
		$invoicing->previous_balance = $parent_acct[previous_balance];
		$invoicing->payments_sum = $parent_acct[payments_sum];
		$invoicing->adjustments_sum = ($parent_acct[xtra]['Break Down'][tax_adjustments]*1.18) +
									   $parent_acct[xtra]['Break Down'][notax_adjustments];
		$invoicing->charges_sum = $parent_acct[xtra]['Break Down'][months_charges];
		$invoicing->amount_payable = $parent_acct[amount_payable];
		$invoicing->details = serialize($parent_acct[xtra]);
		$invoicing->invoice_number = generate_invoice_no('');
			
		if(	!(($invoicing->previous_balance == 0)&&
			($invoicing->charges_sum == 0)&&
			(strlen($invoicing->parent_id) < 4)&&
			($invoicing->payments_sum == 0)&&
			($invoicing->adjustments_sum == 0)&&
			($invoicing->amount_payable == 0)
			)){
			
			$checks = array(
							array('parent_id','=',$invoicing->parent_id),
							array('billing_date','=',$invoicing->billing_date)
							);
			
			$check_objects = $invoicing->GetList($checks);
					
			if(count($check_objects) == 0){
				$saved_id = $invoicing->SaveNew();
				if($saved_id){
					$invoices[saved][$parent_id] = $parent_accts[$parent_id];
					$html .= "Saving Invoice [".$saved_id."]<br>";
				}else{
					$invoices[not_saved][$parent_id] = $parent_accts[$parent_id];
					$html .= "Invoice for Account [".$parent_id."] Not saved <br>";
				}
			}else{
				$i = 0;
				$check_object = $check_objects[$i];
				$invoicing->invoice_number = $check_object->invoice_number;
				$invoicing->id = $check_object->id;
				$saved_id = $invoicing->Save();
				$html .= "Invoice Already exists. Updating Invoice no ".$check_object->invoice_number." of Account no ".$check_object->parent_id."<br>FROM Bal ".$check_object->previous_balance." Adjustments ".$check_object->adjustments_sum." Charges ".$check_object->charges_sum." Amount Payable ".$check_object->amount_payable."<br> TO : Bal ".$invoicing->previous_balance." Adjustments ".$invoicing->adjustments_sum." Charges ".$invoicing->charges_sum." Amount Payable ".$invoicing->amount_payable."<br>";
				while(count($check_objects[++$i]) != 0){
					$invoice = $check_objects[$i];
					$invoice->Delete();
				}
			}
		}
	}
	
	echo $html;
	sendHTMLemail($to='ccbusinessanalysis@waridtel.co.ug',$bcc='',$message=$html,$subject='Data Services Bill Run '.date('l jS F Y'),$from='Data Billrun<ccnotify@waridtel.co.ug>');
	
	return $invoices[not_saved];
}

function generate_n_perfoma_invoice($invoice_in){
	$key = $invoice_in[type];
	print_r($invoice_in); echo "<br><br>";
	
	function add_disc_n_num($value,$discount=0,$quantity=1){
		return $value * $quantity * (1 - $discount/100);
	}
	
	function get_product_by_id($input,$input_type='id'){
		$myquery = new uniquequerys();
		
		if($input != ''){
			$query = "
				SELECT
					ps_products.name,
					ps_products_cstm.billing_currency_c as currency,
					ps_products.price,
					if(ps_products.type='Goods','One time','Repeatitive') as type,
					ps_products_cstm.product_grouping_c as grouping
				FROM
					ps_products
					Inner Join ps_products_cstm ON ps_products.id = ps_products_cstm.id_c
				where
					ps_products.deleted = 0 and 
					ps_products.".$input_type." = '".$input."'
			";
			return $myquery->uniquequery($query);
		}else{
			echo "No product input ...";
			return "No product input ...";
		}
	}
	
	/*function add_invoice_entry($entry,&$inv_key,$date=date('Y-m-d')){
		
		$product = get_product_by_id($entry[product],$input_type='id');
		
		$item_array[rate_date] = $date;
		$item_array[item] = $product[name];
		$item_array[unit_price] = $product[price];
		$item_array[grouping] = $product[grouping];
		$item_array[details] = '';
		$item_array[discount] == $entry[discount];
		$item_array[quantity] == $entry[quantity];
		$item_array[account_id] == $entry[account];
		$item_array[currency] = $product[currency];
		
		$inv_entries[currencies][$item_array[currency]] = $item_array[currency];
		
		$unit_bill = add_disc_n_num($product[price],$entry[discount],$entry[quantity]);
		
		if($product[type] == 'Repeatitive'){
			//ie one time charges
			$item_array[value] = -$unit_bill;
			$inv_key[Charges] 
		}
		if($product[grouping] == 'Equipment Deposits'){
			$product[taxable_group] = 'taxed';
		}else{
			$product[taxable_group] = 'untaxed';
		}
	}*/
	
	$myquery = new uniquequerys();
	
	$invoices[$key][xtra][Other_details][account_id] = $invoice_in[parent_account_id];
	$invoices[$key][xtra][Other_details][invoice_date] = date('Y-m-d');
	$invoices[$key][xtra][Other_details][Title] = $invoice_in[type];
	
	//check invoice type
	if($invoice_in[type] == 'PROFORMA'){
		$invoices[$key][xtra][Other_details][account_name] = $invoice_in[parent_account_name];
	}elseif($invoice_in[type] == 'TAX'){
		if($invoice_in[parent_account_id] != ''){
			$account_data = $myquery->uniquequery("
					SELECT 
					  accounts.name,
					  accounts_cstm.billing_add_strt_c,
					  accounts_cstm.billing_add_area_c,
					  accounts_cstm.billing_add_town_c,
					  accounts_cstm.billing_add_plot_c,
					  accounts_cstm.billing_add_district_c,
					  accounts_cstm.service_type_internet_c as service_type,
					  accounts_cstm.invoicing_type_c as invoicing_type,
					  accounts_cstm.customer_type_c as customer_type
					FROM
					 accounts
					 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
					WHERE
					 accounts.deleted = '0' AND 
                     accounts_cstm.crn_c = '".$invoice_in[parent_account_id]."'
				");
		}
		$invoices[$key][xtra][Other_details][account_name] = $account_data[name];
	}
	
	//check currency from products
	$invoices[$key][xtra][Other_details][invoice_currency] = '';
	
	//enter charges before u determine the start date ....
	$invoices[$key][xtra][Other_details][invoice_start] = '';
	$invoices[$key][xtra][Other_details][invoice_end] = '';
	
	foreach($invoice_in[entries] as $entry){
		add_invoice_entry($entry,$invoices[$key]);
	}
	
	$invoice_array = 'df s.fn .';
	
	return $invoice_array;
}

function generate_perfoma_invoice(){
	
	$myquery = new uniquequerys();
	
	$key = $_POST[invoice_type];
	if($_POST['client_currency']){
		$invoices[$key][xtra][Other_details][invoice_currency] = $_POST['client_currency'];
	}else{
		$invoices[$key][xtra][Other_details][invoice_currency] = 'USD';
	}
	
	//sorting dates
	//Start dates
	if($_POST['start_date']){
		$start_date_values['start_date'] = $_POST['start_date'];
	}
	if($_POST['start_date_web_hosting']){
		$start_date_values['start_date_web_hosting'] = $_POST['start_date_web_hosting'];
	}
	if($_POST['start_date_dom_hosting']){
		$start_date_values['start_date_dom_hosting'] = $_POST['start_date_dom_hosting'];
	}
	if($_POST['start_date_dom_reg']){
		$start_date_values['start_date_dom_reg'] = $_POST['start_date_dom_reg'];
	}
	if($_POST['start_date_email']){
		$start_date_values['start_date_email'] = $_POST['start_date_email'];
	}

	sort($start_date_values);
	$invoices[$key][xtra][Other_details][invoice_start] = array_shift($start_date_values);
	//expirydates
	
	$end_date_values = array(
		'end_date'=>$_POST['end_date'],
		'end_date_web_hosting'=>$_POST['end_date_web_hosting'],
		'end_date_dom_hosting'=>$_POST['end_date_dom_hosting'],
		'end_date_dom_reg'=>$_POST['end_date_dom_reg'],
		'end_date_email'=>$_POST['end_date_email']
	);
	
	arsort($end_date_values);
	$invoices[$key][xtra][Other_details][invoice_end] = array_shift($end_date_values);
	
	$invoices[$key][xtra][Other_details][account_id] = $account_id;
	$invoices[$key][xtra][Other_details][account_name] = $_POST[client_name];
	$invoices[$key][xtra][Other_details][invoice_date] = date('Y-m-d');

	$invoices[$key][xtra][Other_details][Title] = $_POST[invoice_type];

	$item_array[rate_date] = $invoices[$key][xtra][Other_details][invoice_date];
	foreach($_POST[charges] as $charge){
		$charge = explode('#',$charge);
		$item_array[item] = $charge[0];
		$item_array[grouping] = $charge[3];
		$item_array[details] = $charge[0];
		$item_array[currency] = $charge[4];
		$charge[1] = convert_value($charge[1], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		if($charge[0] != 'Equipment Deposit'){
			$invoices[$key][Charges] += -$charge[1];
			$item_array['value'] = -$charge[1];
			if($invoices[$key][xtra]['Break Down'][items]){
				array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
			} else {
				$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
			}
		} else {
			$invoices[$key][xtra]['Break Down'][untaxed_total] += -$charge[1];
			$item_array[value] = -$charge[1];
			if($invoices[$key][xtra]['Break Down'][untaxed][items]){
				array_push($invoices[$key][xtra]['Break Down'][untaxed][items], $item_array);
			} else {
				$invoices[$key][xtra]['Break Down'][untaxed][items][0] = $item_array;
			}
		}
	}
	
	//Services that are attached to packages
	$package = explode('#',$_POST[package]);
	$monthly_bill = $package[1] * $_POST[p_quantity] * (1 - $_POST[p_discount]/100);
	$package_bill = bill($_POST[start_date],$_POST[end_date],$monthly_bill);
	if(intval($package[1]) != 0){
		$item_array[currency] = $package[3];
		$item_array[item] = $package[0];
		$item_array[details] = '. '.$_POST[p_quantity].' Unit(s) at '.($package[1] * (1 - $_POST[p_discount]/100)).' '.$item_array[currency].' per month';
		$item_array[grouping] = $package[2];
		$package_bill[amount]= convert_value($package_bill[amount], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = -$package_bill[amount];
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Services] += -$package_bill[amount];
	
	//Services
	$service = explode('#',$_POST[service]);
	$monthly_bill = $service[1] * $_POST[b_quantity] * (1 - $_POST[b_discount]/100);
	$service_bill = bill($_POST[start_date],$_POST[end_date],$monthly_bill);
	$item_array[currency] = $service[3];
	$item_array[item] = $service[0];
	$item_array[details] = '. '.$_POST[b_quantity].' Unit(s) at '.($service[1] * (1 - $_POST[b_discount]/100)).' '.$item_array[currency].' per month';
	$item_array[grouping] = $service[2];
	$service_bill['amount']= convert_value($service_bill['amount'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
	$item_array[value] = -$service_bill['amount'];
	if($invoices[$key][xtra]['Break Down'][items]){
		array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
	} else {
		$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
	}
	$invoices[$key][Services] += -$service_bill[amount];
	
	//Web Hosting packages
	$package_web_hosting = explode('#',$_POST[package_web_hosting]);
	$monthly_bill = $package_web_hosting[1] * $_POST[p_quantity_web_hosting] * (1 - $_POST[p_discount_web_hosting]/100);
	$package_bill_web_hosting = bill($_POST[start_date_web_hosting],$_POST[end_date_web_hosting],$monthly_bill);
	if(intval($package_web_hosting[1]) != 0){
		$item_array[currency] = $package_web_hosting[3];
		$item_array[item] = $package_web_hosting[0];
		$item_array[details] = '. '.$_POST[p_quantity_web_hosting].' Unit(s) at '.($package_web_hosting[1] * (1 - $_POST[p_discount_web_hosting]/100)).' '.$item_array[currency].' per month';
		$item_array[grouping] = $package_web_hosting[2];
		$package_bill_web_hosting['amount'] = convert_value($package_bill_web_hosting['amount'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = -$package_bill_web_hosting[amount];
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Services] += -$package_bill_web_hosting[amount];

	//Domain Hosting packages
	$package_dom_hosting = explode('#',$_POST[package_dom_hosting]);
	$monthly_bill = $package_dom_hosting[1] * $_POST[p_quantity_dom_hosting] * (1 - $_POST[p_discount_dom_hosting]/100);
	$package_bill_dom_hosting = bill($_POST[start_date_dom_hosting],$_POST[end_date_dom_hosting],$monthly_bill);
	if(intval($package_dom_hosting[1]) != 0){
		$item_array[currency] = $package_dom_hosting[3];
		$item_array[item] = $package_dom_hosting[0];
		$item_array[details] = '. '.$_POST[p_quantity_dom_hosting].' Unit(s) at '.($package_dom_hosting[1] * (1 - $_POST[p_discount_dom_hosting]/100)).' '.$item_array[currency].' per month';
		$item_array[grouping] = $package_dom_hosting[2];
		$package_bill_dom_hosting['amount'] = convert_value($package_bill_dom_hosting['amount'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = -$package_bill_dom_hosting[amount];
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Services] += -$package_bill_dom_hosting[amount];
	
	//Domain Reg packages
	$package_dom_reg = explode('#',$_POST[package_dom_reg]);
	$monthly_bill = $package_dom_reg[1] * $_POST[p_quantity_dom_reg] * (1 - $_POST[p_discount_dom_reg]/100);
	$package_bill_dom_reg = bill($_POST[start_date_dom_reg],$_POST[end_date_dom_reg],$monthly_bill);
	if(intval($package_dom_reg[1]) != 0){
		$item_array[currency] = $package_dom_reg[3];
		$item_array[item] = $package_dom_reg[0];
		$item_array[details] = '. '.$_POST[p_quantity_dom_reg].' Unit(s) at '.($package_dom_reg[1] * (1 - $_POST[p_discount_dom_reg]/100)).''.$item_array[currency].' per month';
		$item_array[grouping] = $package_dom_reg[2];
		$package_bill_dom_reg['amount'] = convert_value($package_bill_dom_reg['amount'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = -$package_bill_dom_reg[amount];
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Services] += -$package_bill_dom_reg[amount];
	
	//Email packages
	$package_email = explode('#',$_POST[package_email]);
	$monthly_bill = $package_email[1] * $_POST[p_quantity_email] * (1 - $_POST[p_discount_email]/100);
	$package_bill_email = bill($_POST[start_date_email],$_POST[end_date_email],$monthly_bill);
	if(intval($package_email[1]) != 0){
		$item_array[currency] = $package_email[3];
		$item_array[item] = $package_email[0];
		$item_array[details] = '. '.$_POST[p_quantity_email].' Unit(s) at '.($package_email[1] * (1 - $_POST[p_discount_email]/100)).''.$item_array[currency].' per month';
		$item_array[grouping] = $package_email[2];
		$package_bill_email['amount'] = convert_value($package_bill_email['amount'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = -$package_bill_email[amount];
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Services] += -$package_bill_email[amount];
	
	
	//Lease/Hire (Equipment/Connection) [LOW]
	
	$package_lease = explode('#',$_POST[package_lease]);
	$monthly_bill = $package_lease[1] * $_POST[p_quantity_lease] * (1 - $_POST[p_discount_lease]/100);
	$package_bill_lease = bill($_POST[start_date_lease],$_POST[end_date_lease],$monthly_bill);
	if(intval($package_lease[1]) != 0){
		$item_array[currency] = $package_lease[3];
		$item_array[item] = $package_lease[0];
		$item_array[details] = '. '.$_POST[p_quantity_lease].' Unit(s) at '.($package_lease[1] * (1 - $_POST[p_discount_lease]/100)).''.$item_array[currency].' per month';
		$item_array[grouping] = $package_lease[2];
		$package_bill_lease['amount'] = convert_value($package_bill_lease['amount'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = -$package_bill_lease[amount];
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Services] += -$package_bill_lease[amount];


	if(($_POST['Access_Point_Feesproduct'] != '') && (intval($_POST['Access_Point_Feesvalue']) > 0)){
		$p_name = $_POST['Access_Point_Feesproduct'];
		$discount = $_POST['Access_Point_Feesvalue'];
		$result = $myquery->uniquequery("SELECT ps_products.price, ps_products_cstm.billing_currency_c AS billing_currency FROM ps_products INNER JOIN  ps_products_cstm ON (ps_products.id = ps_products_cstm.id_c) WHERE ps_products.name = '$p_name'");
		$_POST['Access_Point_Feesvalue'] = ($_POST['Access_Point_Feesvalue']/100)*$result[price];
		$item_array[item] = $_POST['Access_Point_Feesproduct'];
		$item_array[grouping] = 'Credit Note';
		$item_array[currency] = $result[billing_currency];
		$_POST['Access_Point_Feesvalue'] = convert_value( $_POST['Access_Point_Feesvalue'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = $_POST['Access_Point_Feesvalue'];
		$item_array[details] = number_format($discount,2).'% Discount on '.$p_name;
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Charges] += $_POST['Access_Point_Feesvalue'];
	

	if(($_POST['Equipment_Depositsproduct'] != '') && (intval($_POST['Equipment_Depositsvalue']) > 0)){
		$p_name = $_POST['Equipment_Depositsproduct'];
		$discount = $_POST['Equipment_Depositsvalue'];
		$result = $myquery->uniquequery("SELECT ps_products.price, ps_products_cstm.billing_currency_c AS billing_currency FROM ps_products INNER JOIN  ps_products_cstm ON (ps_products.id = ps_products_cstm.id_c) WHERE ps_products.name = '$p_name'");
		$_POST['Equipment_Depositsvalue'] = ($_POST['Equipment_Depositsvalue']/100)*$result[price];
		$item_array[item] = $_POST['Equipment_Depositsproduct'];
		$item_array[currency] = $result[billing_currency];
		$item_array[grouping] = 'Cash Discount';
		$_POST['Equipment_Depositsvalue'] = convert_value($_POST['Equipment_Depositsvalue'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = $_POST['Equipment_Depositsvalue'];
		$item_array[details] = number_format($discount,2).'% Discount on '.$p_name;
		if($invoices[$key][xtra]['Break Down'][untaxed][items]){
			array_push($invoices[$key][xtra]['Break Down'][untaxed][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][untaxed][items][0] = $item_array;
		}
	}
	$invoices[$key][xtra]['Break Down'][untaxed_total] += $_POST['Equipment_Depositsvalue'];

	if(($_POST['Connection_Feesproduct'] != '') && (intval($_POST['Connection_Feesvalue']) > 0)){
		$p_name = $_POST['Connection_Feesproduct'];
		$discount = $_POST['Connection_Feesvalue'];
		$result = $myquery->uniquequery("SELECT ps_products.price, ps_products_cstm.billing_currency_c AS billing_currency FROM ps_products INNER JOIN  ps_products_cstm ON (ps_products.id = ps_products_cstm.id_c) WHERE ps_products.name = '$p_name'");
		$_POST['Connection_Feesvalue'] = ($_POST['Connection_Feesvalue']/100)*$result[price];
		$item_array[item] = $_POST['Connection_Feesproduct'];
		$item_array[grouping] = 'Credit Note';
		$item_array[currency] = $result[billing_currency];
		$_POST['Connection_Feesvalue'] = convert_value($_POST['Connection_Feesvalue'], $item_array[currency], $item_array[rate_date], $invoices[$key][xtra][Other_details][invoice_currency]);
		$item_array[value] = $_POST['Connection_Feesvalue'];
		$item_array[details] = number_format($discount,2).'% Discount on '.$p_name;
		if($invoices[$key][xtra]['Break Down'][items]){
			array_push($invoices[$key][xtra]['Break Down'][items], $item_array);
		} else {
			$invoices[$key][xtra]['Break Down'][items][0] = $item_array;
		}
	}
	$invoices[$key][Charges] += $_POST['Connection_Feesvalue'];

	//Adding VAT to use the same generate HTML file
	$invoices[$key][Charges] *= 1.18;
	//Adding VAT to use the same generate HTML file
	$invoices[$key][Services] *= 1.18;
	
	$invoices[$key][xtra]['Break Down'][Services] = $invoices[$key][Services]/1.18;
	$invoices[$key][xtra]['Break Down'][Charges] = $invoices[$key][Charges]/1.18;
	$invoices[$key][xtra]['Break Down'][tax_adjustments] = 0;
	$invoices[$key][xtra]['Break Down'][notax_adjustments] = 0;
	
	$invoices[$key][xtra]['Break Down'][sub_total] = $invoices[$key][xtra]['Break Down'][Charges] + 
													 $invoices[$key][xtra]['Break Down'][Services];
	$invoices[$key][xtra]['Break Down'][total_vat] = $invoices[$key][xtra]['Break Down'][sub_total] * 0.18;
	
	$invoices[$key][xtra]['Break Down'][total_charges] = $invoices[$key][xtra]['Break Down'][sub_total] + 
														 $invoices[$key][xtra]['Break Down'][total_vat] + 
														 $invoices[$key][xtra]['Break Down'][untaxed_total];
	
	$invoices[$key][xtra]['Break Down'][months_charges] = ($invoices[$key][xtra]['Break Down'][Services]*1.18) + 
														  ($invoices[$key][xtra]['Break Down'][Charges]*1.18) + 
														  $invoices[$key][xtra]['Break Down'][untaxed_total];
	
	
	if($_POST[account_id] != ''){
		$account_data = $myquery->uniquequery("
					SELECT 
					  accounts.name,
					  accounts_cstm.billing_add_strt_c,
					  accounts_cstm.billing_add_area_c,
					  accounts_cstm.billing_add_town_c,
					  accounts_cstm.billing_add_plot_c,
					  accounts_cstm.billing_add_district_c,
					  accounts_cstm.preferred_username_c,
					  accounts_cstm.mem_id_c as parent_id,
					  accounts_cstm.service_type_internet_c as service_type,
					  accounts_cstm.invoicing_type_c as invoicing_type,
					  accounts_cstm.crn_c,
					  accounts_cstm.customer_type_c as customer_type,
					  cn_contracts.start_date
					FROM
					 accounts
					 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
					 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
					WHERE
					 accounts.deleted = '0' AND 
					 cn_contracts.deleted = '0' AND
					 cn_contracts.status = 'Active' AND
                     accounts_cstm.crn_c = '$_POST[account_id]'
				");
	}
		
	if($_POST[invoice_type] == 'PROFORMA INVOICE'){
		$invoices[$key][xtra][Other_details][individual] = $_POST[contact_person];
		$invoices[$key][xtra][Other_details][physical_address] = $_POST[address];
		return display_invoice_data($invoices[$key]);
	}else{
		$invoices[$key][xtra][Other_details][individual] = $_POST[contact_person_taxinv];
		$invoices[$key][xtra][Other_details][physical_address] = $_POST[contact_info];

		$invoicing = new wimax_invoicing();	
		//Populating parent contact Information
		
		//Some accounts may not have usernames yet
		$invoices[$key][xtra][Other_details][account_number] = $account_data[crn_c];
		
		if($account_data[service_type] == 'Postpaid'){
			$invoices[$key][previous_balance] = previousBalance($invoices[$key][xtra][Other_details][account_number],$invoices[$key][xtra][Other_details][invoice_start],$invoices[$key][xtra][Other_details][invoice_currency]);
		}else{
			$invoices[$key][previous_balance] = 0;
		}
		
		$invoices[$key][amount_payable] = $invoices[$key][xtra]['Break Down'][total_charges] +
										   $invoices[$key][previous_balance]; 
		$invoices[$key][xtra][Other_details][username] = $account_data[preferred_username_c];
		$invoices[$key][xtra][Other_details][account_name] = $account_data[name];
		$invoices[$key][xtra][Other_details][individual] = $account_data[salutation]." ".$account_data[first_name]." ".$account_data[last_name];
		$invoices[$key][xtra][Other_details][physical_address] = $account_data[billing_add_plot_c]."<br>".
																$account_data[billing_add_strt_c]."<br>".
																$account_data[billing_add_district_c]."<br>";
		$invoices[$key][xtra][Other_details][invoice_due_date] = AddDays($invoices[$key][xtra][Other_details][invoice_date] ,0);
		$invoices[$key][xtra][Other_details][contract_start] = $account_data[start_date];
		$invoices[$key][xtra][Other_details][customer_type] = $account_data[customer_type];
		$invoices[$key][xtra][Other_details][invoicing_type] = $account_data[invoicing_type];
		$invoices[$key][xtra][Other_details][generated_by] = trim($_POST[user]);
		$invoices[$key][xtra][Other_details][service_type] = $account_data[service_type];
		$invoices[$key][xtra][Other_details][fined_payable] = -$invoices[$key][amount_payable];
		$invoices[$key][xtra][Other_details][acct_bal] = -$invoices[$key][xtra][Other_details][fined_payable];
		
		if(count($invoices[$key][xtra]['Break Down'][items]) != 0){
			foreach($invoices[$key][xtra]['Break Down'][items] as &$item){
				$item[account_number] = $invoices[$key][xtra][Other_details][account_number];
				$item_array[rate_date] = date('Y-m-d');
			}
		}
		
		if(count($invoices[$key][xtra]['Break Down'][untaxed][items]) != 0){
			foreach($invoices[$key][xtra]['Break Down'][untaxed][items] as &$item){
				$item[account_number] = $invoices[$key][xtra][Other_details][account_number];
				$item_array[rate_date] = date('Y-m-d');
			}
		}
	
		$invoicing->generation_date = date('Y-m-d');
		$invoicing->parent_id = $invoices[$key][xtra][Other_details][account_number];
		$invoicing->billing_date = $invoices[$key][xtra][Other_details][invoice_end];
		$invoicing->previous_balance = $invoices[$key][previous_balance];
		$invoicing->payments_sum = 0;
		$invoicing->period = $invoices[$key][xtra][Other_details][invoice_start];
		$invoicing->adjustments_sum = 0;
		$invoicing->charges_sum = $invoices[$key][xtra]['Break Down'][total_charges];
		$invoicing->amount_payable = $invoices[$key][amount_payable];
		$invoicing->details = serialize($invoices[$key][xtra]);
		$invoicing->invoice_number = generate_invoice_no('');
				
		if(!($invoicing->charges_sum == 0)){
			$checks = array(
							array("parent_id","=",$invoicing->parent_id),
							array("billing_date","=",$invoicing->billing_date),
							array("details","LIKE","%invoice_end\";s:10:\"".$invoicing->billing_date."%"),
							array("details","LIKE","%invoice_start\";s:10:\"".$invoices[$key][xtra][Other_details][invoice_start]."%")
							);
			
			$check_object = $invoicing->GetList($checks);
			
			//echo ++$GG.' ->> '.test_invoice($invoicing).'<p style="page-break-before: always">';			
			if(count($check_object) == 0){
				$invoice_id = $invoicing->Save();
			//echo "Uncomment the save instruction <br>";
				if($saved_id){
					$invoices[saved][$parent_id] = $parent_accts[$parent_id];
					//echo "Saving Invoice <br>";
				}else{
					$invoices[not_saved][$parent_id] = $parent_accts[$parent_id];
					//echo "Invoice Not saved <br>";
				}
			}else{
				$object = $check_object[0]; 
				$invoicing->invoice_number = $object->invoice_number;
				$invoicing->generation_date = $object->generation_date;
				$invoicing->id = $object->id;
				$invoice_id = $invoicing->Save();
				$invoicing->id = '';
				//echo "Invoice Already exists <br>";
			}
		}else{
			echo "<!-- Invoice charge is 0 -->";
		}
	
		$_POST[account_id] = $invoices[$key][xtra][Other_details][account_number];
		$_POST[billing_start] = $invoices[$key][xtra][Other_details][invoice_start];
		$_POST[billing_expiry] = $invoices[$key][xtra][Other_details][invoice_end];
		$_POST[billing_date] = $invoices[$key][xtra][Other_details][invoice_date];
		$_POST[parent_id] = $account_data[parent_id];
		$_POST[matched_invoice] = $invoice_id;
		
		$reciept_id = save_payement();
		
		return '<p style="page-break-before: always">'.display_invoice_byid($invoice_id).'<p style="page-break-before: always">'.display_receipt(generate_receipt($reciept_id));
	}
}

function generate_invoices_list($account_id, $generation_date, $billing_date, $customer_types, $account_name){
	
	$invoicing = new wimax_invoicing();
	$myquery = new uniquequerys();
	
	$conditions = array(array('id','>',0));
	
	if($account_id){
		$account_id = '%'.trim($account_id).'%';
		array_push($conditions, array('parent_id','LIKE',$account_id));
	}
	if($generation_date){
		array_push($conditions, array('generation_date','=',$generation_date));
	}
	if($billing_date){
		$result = $myquery->uniquequery("SELECT LAST_DAY('$billing_date') as thedate");
		$billing_date = $myquery->Unescape($result[thedate]);
		array_push($conditions, array('billing_date','=',$billing_date));
	}elseif(($billing_date == '')&&(($generation_date != '')||($account_id != ''))){
		//Do nothing
	}else{
		$result = $myquery->uniquequery("SELECT LAST_DAY(date_add(date(now()), INTERVAL -1 MONTH)) as thedate");
		$billing_date = $myquery->Unescape($result[thedate]);
		$_POST[billing] = $billing_date;
		array_push($conditions, array('billing_date','>=',$billing_date));
	}
	if($account_name){
		$account_name = '%'.trim($account_name).'%';
		array_push($conditions, array('parent_id','LIKE',$account_name));
	}
	if($customer_types){
		array_push($conditions, array('AND'));
		array_push($conditions, array('('));
		foreach($customer_types as $count=>$customer_type){
			array_push($conditions, array('details','LIKE','%customer_type'.'%'.$customer_type.'%'));
			if($customer_types[$count+1] != ''){array_push($conditions, array('OR'));}
		}
		array_push($conditions, array(')'));
	}
	
	return $invoicing->GetList($conditions,'billing_date',false,'');
}

function display_invoices_list($invoices){
	
	//print_r($invoices);
	if($invoices){
		$invoices_html = '
      	<tr>
			<td align="center" background="images/table_header.jpg" class="style14">Invoice Number</td>
			<td align="center" background="images/table_header.jpg" class="style14">Service Type</td>
			<td align="center" background="images/table_header.jpg" class="style14">Peroid Start</td>
			<td align="center" background="images/table_header.jpg" class="style14">Account Number</td>
			<td align="center" background="images/table_header.jpg" class="style14">Account Name</td>
			<td align="center" background="images/table_header.jpg" class="style14">Previous Balance</td>
			<td align="center" background="images/table_header.jpg" class="style14">Payments</td>
			<td align="center" background="images/table_header.jpg" class="style14">Adjustments</td>
			<td align="center" background="images/table_header.jpg" class="style14">Month\'s Charges</td>
			<td align="center" background="images/table_header.jpg" class="style14">Amount Payable</td>
			<td align="center" background="images/table_header.jpg" class="style14">Billing Date</td>
			<td align="center" background="images/table_header.jpg" class="style14">Created By</td>
			<td align="center" background="images/table_header.jpg" class="style14">&nbsp;</td>
      	</tr>
		';
		foreach($invoices as $invoice_row){
			$invoice_row->details = unserialize($invoice_row->details); //echo "<br>";
			//echo "<br><br>"; print_r($invoice_row->details); echo "<br><br>";
			if($invoice_row->amount_payable > 0){
				$invoice_row->amount_payable = 0;
			}
			if(!$invoice_row->details[Other_details][contract_start]){
				$invoice_row->details[Other_details][contract_start] = $invoice_row->details[Other_details][invoice_start];
			}
			
			$invoices_html .= '
			<tr>
				<td align="right" class="style11">'.$invoice_row->invoice_number.'</td>
				<td align="right" class="style11">'.$invoice_row->details[Other_details][service_type].'</td>
				<td align="right" class="style11">'.$invoice_row->details[Other_details][contract_start].'</td>
				<td align="right" class="style11">'.$invoice_row->parent_id.'</td>
				<td align="right" class="style11">'.$invoice_row->details[Other_details][account_name].'</td>
				<td align="right" class="style11">'.accounts_format(-$invoice_row->previous_balance).'</td>
				<td align="right" class="style11">'.accounts_format($invoice_row->payments_sum).'</td>
				<td align="right" class="style11">'.accounts_format($invoice_row->adjustments_sum).'</td>
				<td align="right" class="style11">'.accounts_format(-$invoice_row->charges_sum).'</td>
				<td align="right" class="style11">'.accounts_format(-$invoice_row->amount_payable).'</td>
				<td align="right" class="style11">'.date_reformat($invoice_row->billing_date,'').'</td>
				<td align="right" class="style11">'.$invoice_row->details[Other_details][generated_by].'</td>
				<td align="right" class="style11"><a href="print_invoice.php?id='.$invoice_row->id.'" target="_blank">Print</a></td>
			</tr>
			';
		}
			
	}else{
		echo "There is no Data to display";
	}
	
	return $invoices_html;
}

function get_invoice($id){
	
	$invoicing = new wimax_invoicing();
	
	return $invoicing->Get($id);
}

function show_either($item,$item_detail){
	if(trim($item_detail)!=''){
		return trim($item_detail);
	}else{
		return trim($item);
	}
}

function display_invoice_charges($break_down){
	
	if($break_down){
		$charge_html = '
		  <table width="100%" border="0" align="center" cellpadding="2" cellspacing="0">
			<tr>
                <td colspan="2" class="charges_summary_head">This period\'s charge details</td>
		  	</tr>';
          
		  if($break_down[items]){
		  	$charge_html .= '
			  <tr><td colspan="2">
			  <table width="100%" border="0" align="center" cellpadding="2" cellspacing="0" style="font-size:90%;">
				<tr class="breakdown_highlight">
					<td colspan="4">Taxable Onetime charges and Monthly services</td>
				</tr>
				<tr class="breakdown_highlight">
					<td width="80">Account Number(s)</td>
					<!--<td>Transaction Type</td>-->
					<td>Transaction Details</td>
					<td align="right">Amount</td>
				</tr>';
			  foreach($break_down[items] as $item){
						$charge_html .= '
				  <tr class="breakdown_detail">
					<td>'.$item[account_number].'</td>
					<!--<td>'.$item[grouping].'</td>-->
					<td><span style="font-weight:bold;">'.$item[grouping].' -</span> ';
					if($item[grouping] == 'Service'){
						$charge_html .= $item[item].' ';
					}
					$charge_html .= show_either($item[item],$item[details]).'</td>
					<td align="right">'.accounts_format(-$item[value]).'</td>
				  </tr>';
			  }
			  $charge_html .= '
			  </table>
			  </td></tr>';
		  }
		  
		  if($break_down[prorate_adjustments]){
		  	$charge_html .= '
			  <tr><td colspan="2">
			  <table width="100%" border="0" align="center" cellpadding="2" cellspacing="0" style="font-size:90%;">
				<tr class="breakdown_highlight">
					<td colspan="4">Prorating Ajustments</td>
				</tr>
				<tr class="breakdown_highlight">
					<td width="80">Account Number(s)</td>
					<!--<td>Transaction Type</td>-->
					<td>Transaction Details</td>
					<td align="right">Amount</td>
				</tr>';
			  foreach($break_down[prorate_adjustments] as $item){
						$charge_html .= '
				  <tr class="breakdown_detail">
					<td>'.$item[account_number].'</td>
					<!--<td>'.$item[grouping].'</td>-->
					<td><span style="font-weight:bold;">'.$item[grouping].' -</span> '.show_either($item[item],$item[details]).'</td>
					<td align="right">'.accounts_format(-$item[value]).'</td>
				  </tr>';
			  }
			  $charge_html .= '
			  </table>
			  </td></tr>';
		  }
		  
		  $charge_html .= '
          <tr class="breakdown_highlight">
		  <td style="padding-left:2px;" class="breakdown_highlight2">Sub Total Charges</td>
		  <td align="right" style="padding-right:2px;" class="breakdown_highlight2">'.accounts_format(-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum])).'</td>
		  </tr>
		  <tr  class="breakdown_highlight">
		  <td colspan="2" style="padding-left:2px;" class="breakdown_highlight2">Taxes</td>
		  </tr>
		  <tr class="breakdown_detail" style="font-size:90%;">
		  <td width="65%" style="padding-left:2px;">VAT (18%)</td>
		  <td width="65%" align="right" style="padding-right:2px;">'.accounts_format(-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 0.18).'</td>
		  </tr>';

          if($break_down[untaxed][items]){
		 	$charge_html .='<tr><td colspan="2">
          		<table width="100%" border="0" align="center" cellpadding="2" cellspacing="0" style="font-size:90%;">
				<tr class="breakdown_highlight">
					<td colspan="4">Non taxable One time charges</td>
				</tr>
				<tr class="breakdown_highlight">
					<td width="80">Account Number(s)</td>
					<!--<td>Transaction Type</td>-->
					<td>Transaction Details</td>
					<td align="right">Amount</td>
				</tr>';
				foreach($break_down[untaxed][items] as $item){
					$charge_html .='
					<tr class="breakdown_detail">
						<td>'.$item[account_number].'</td>
				  		<!--<td>'.$item[grouping].'</td>-->
				  		<td><span style="font-weight:bold;">'.$item[grouping].' -</span> '.show_either($item[item],$item[details]).'</td>
				  		<td align="right">'.accounts_format(-$item[value]).'</td>
				  	</tr>';
				}
			$charge_html .='
           	</table>
           	</td></tr>
            <tr class="breakdown_highlight">
				<td width="65%" style="padding-left:2px;">Sub Total UNTAXED Charges</td>
				<td width="65%" align="right" style="padding-right:2px;">'.accounts_format(-$break_down[untaxed_total]).'</td>
			</tr>
			';
		 }

		if($break_down[other_adjustments]){
		 	$charge_html .='<tr><td colspan="2">
          		<table width="100%" border="0" align="center" cellpadding="2" cellspacing="0" style="font-size:90%;">
				<tr class="breakdown_highlight">
					<td colspan="4">Non taxable Adjustments</td>
				</tr>
				<tr class="breakdown_highlight">
					<td width="80">Account Number(s)</td>
					<!--<td>Transaction Type</td>-->
					<td>TransactionDetails</td>
					<td align="right">Amount</td>
				</tr>';
				foreach($break_down[other_adjustments] as $item){
					$charge_html .='
					<tr class="breakdown_detail">
						<td>'.$item[account_number].'</td>
				  		<!--<td>'.$item[grouping].'</td>-->
				  		<td><span style="font-weight:bold;">'.$item[grouping].' -</span> '.show_either($item[item],$item[details]).'</td>
				  		<td align="right">'.accounts_format(-$item[value]).'</td>
				  	</tr>';
				}
			$charge_html .='
           	</table>
           	</td></tr>
            <tr style="font-size:90%;" class="breakdown_highlight">
				<td width="65%" style="padding-left:2px;">Total non taxable Adjustments</td>
				<td width="65%" align="right" style="padding-right:2px;">'.accounts_format(-$break_down[other_adjustments_sum]).'</td>
			</tr>
			';
		}
		
		  $charge_html .= '
          <tr style="font-size:90%;" class="breakdown_highlight">
		  	<td width="65%" style="padding-left:2px;">Total Charges</td>
		  	<td width="65%" align="right" style="padding-right:2px;">'.accounts_format(-((($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 1.18) + $break_down[untaxed_total] + $break_down[notax_adjustments])).'</td>
		  </tr>
		  ';

		  $charge_html .= '</table>';
	
	}else{
		echo "<!-- No break down provided --> <br>";
	}
	
	return $charge_html;
}

function display_invoice_charges_summary($break_down){
	
	if($break_down){
		$charge_html = '
			<table width="100%" border="0" align="left" cellpadding="2" cellspacing="0" >
			<tr>
                <td colspan="2" class="charges_summary_head">This period\'s charge summary</td>
		  	</tr>
         	<tr class="breakdown_">
		 		<td >One time charges (Taxable)</td>
		 		<td class="valuez">'.accounts_format(-$break_down[Charges]).'</td>
		 	</tr>
         	<tr class="breakdown_">
		 		<td >Monthly service charges</td>
		 		<td class="valuez">'.accounts_format(-$break_down[Services]).'</td>
		 	</tr>
         	<tr class="breakdown_">
		 		<td >Prorating Adjustments</td>
		 		<td class="valuez">'.accounts_format(-$break_down[prorate_adjustments_sum]).'</td>
		 	</tr>
         	<tr class="breakdown_highlight">
		 		<td class="breakdown_highlight2" >Sub Total</td>
		 		<td class="valuez">'.accounts_format(-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum])).'</td>
		 	</tr>
         	<tr class="breakdown_">
		 		<td >Tax (VAT 18%)</td>
		 		<td class="valuez">'.accounts_format(-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 0.18).'</td>
		 	</tr>
         	<tr class="breakdown_">
		 		<td >One time charges (Non Taxable)</td>
		 		<td class="valuez">'.accounts_format(-$break_down[untaxed_total]).'</td>
		 	</tr>
         	<tr class="breakdown_">
		 		<td >Adjustments (Non Taxable)</td>
		 		<td class="valuez">'.accounts_format($break_down[notax_adjustments]).'</td>
		 	</tr>
         	<tr class="breakdown_highlight">
		 		<td class="breakdown_highlight2">Total Charges for Period</td>
		 		<td class="valuez">'.accounts_format(-((($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 1.18) + $break_down[untaxed_total] + $break_down[notax_adjustments])).'</td>
		 	</tr>
          </table>';
	}else{
		echo "<!-- No break down provided --> <br>";
	}
	
	return $charge_html;
}

function display_invoice_byid($id){
	
	$invoicing = new wimax_invoicing();
	
	$invoice = $invoicing->Get($id);
	if($invoice){
		$invoice->details = unserialize($invoice->details);
		//print_r($invoice->details);
		$invoice_html = '
		<style type="text/css">
		td img {display: block;}
		
		.body{
			font-size:100%;
			font-family: calibri,Arial;
			margin-left:auto;
			margin-right:auto;
		}
		
		.client_details{
			width:297px;
			height:120px;
			vertical-align:top;
		}
		
		.billing_intro{
			vertical-align:top;
			width:377px;
			height:120px;
			overflow:hidden;
		}
		.account_summary_figures{
			font-weight:bold;
			background-color:#D1D2D4;
		}
		.charges_summary_head{
			font-weight:bold;
			font-size:130%;
			color:#FFFFFF;
			height:35px;
			padding-left:10px;
			background-color:#ED1C24;
			border-top:1px #000000 solid;
			border-bottom:1px #000000 solid;
		}
		.valuez{
			text-align:right;
			padding-left:5px;
			font-size:90%;
		}
		.breakdown_highlight{
			height:25px;
			background-color:#D1D2D4;
			border-top:1px #000000 solid;
			border-bottom:1px #000000 solid;
			font-weight:bold;
		}
			.breakdown_highlight2{
			font-size:90%;
		}
		.breakdown_{
			height:20px;
		}
		.breakdown_ td{
			font-size:80%;
		}
		.breakdown_detail{
			border:#444 1px solid;
		}
		.breakdown_detail td{
			height:15px;
			font-size:90%;
		}
		.tear_off_tb tr td{
			height:20px;
		}
		.point{
			background:url(images/cust_bullet.png) left no-repeat;
			padding:0px 15px 0px 15px;
		}
		</style>
		<table class="body" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="1126">
		<tr>
			<td>
			<img name="header" src="images/header.png" width="1126" height="157" border="0" id="header" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" style="width:1126px; height:120px; overflow:hidden;">
			<tr>
				<td class="client_details" >
					<strong>'.strtoupper($invoice->details[Other_details][account_name]).'</strong><br>
					<!--<strong>'.$invoice->details["Other_details"]["individual"].'</strong><br />-->
					'.strtoupper($invoice->details["Other_details"]["physical_address"]).'
				</td>
				<td valign="top"><img src="images/spacer.png" width="244" height="120" /></td>
				<td class="billing_intro">
					<table border="0" cellpadding="0" cellspacing="0" width="377">
						<tr>
							<td width="37%" >Account Number</td>
							<td width="63%">: '.$invoice->details["Other_details"]["account_number"].'</td>
						  </tr>
						  <tr>
							<td >Invoice Number</td>
							<td >: '.$invoice->invoice_number.'</td>
						  </tr>
						  <tr>
							<td >Service Type</td>
							<td >: Broadband - '.$invoice->details[Other_details][service_type].'</td>
						  </tr>
						  <tr>
							<td >Invoice Currency</td>
							<td >: '.$invoice->details["Other_details"]["invoice_currency"].'</td>
						  </tr>
						  <tr>
							<td >Invoice Date</td>
							<td >: '.date_reformat($invoice->details["Other_details"]["invoice_date"],'').'</td>
						  </tr>
						  <tr>
							<td >Invoice Period</td>
							<td >: '.date_reformat($invoice->details["Other_details"]["invoice_start"],'').' to '.date_reformat($invoice->details["Other_details"]["invoice_end"],'').'</td>
						  </tr>
						  <tr>
							<td >Due Date</td>
							<td >: '.date_reformat($invoice->details["Other_details"]["invoice_due_date"],'').'</td>
						  </tr>
					</table>
				</td>
				<td>
				<img name="bar_code" src="images/bar_code.png" width="208" height="120" border="0" id="bar_code" alt="" />
				</td>
			</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td style="padding:10px 0px 10px 0px;">
			<img name="VAT_N_TIN" src="images/VAT_N_TIN3.png" width="1126" height="37" border="0" id="VAT_N_TIN" alt="" />
			</td>
		</tr>
		<tr class="account_summary_figures">
			<td>
			<img name="account_summary" src="images/account_summary2.png" width="1126" height="63" border="0" id="wimaxbill_r4_c2" alt="" />
			</td>
		</tr>
		<tr class="account_summary_figures">
			<td>
				<table border="0" cellpadding="0" cellspacing="0" width="1126">
				  <tr style="height:50px;">
					<td align="center" style="width:188px;" valign="top">';
					$invoice_html .= accounts_format(-$invoice->previous_balance);
					/*
					if($invoice->details[Other_details][generated_by] == 'Bill Run'){
					//if($invoice->details[Other_details][service_type] == 'Postpaid'){
						$invoice_html .= accounts_format(-$invoice->previous_balance);
					}else{
						$invoice_html .= 'Not Applicable';
					}
					*/
					$invoice_html .= '</td>
					<td align="center" style="width:155px;" valign="top">'.accounts_format($invoice->payments_sum).
					'</td>
					<td align="center" style="width:168px;" valign="top">'.accounts_format($invoice->adjustments_sum).'</td>
					<td align="center" style="width:203px;" valign="top">'.accounts_format(-$invoice->details['Break Down'][months_charges]).'</td>
					<td align="center" style="width:191px;" valign="top">'.date_reformat($invoice->details[Other_details][invoice_date],'').'<br />
					  ';
					$invoice_html .= accounts_format(-$invoice->amount_payable);
					/*
					if($invoice->details[Other_details][generated_by] == 'Bill Run'){
					//if($invoice->details[Other_details][service_type] == 'Postpaid'){
						$invoice_html .= accounts_format(-$invoice->amount_payable);
					}else{
						$invoice_html .= accounts_format(-$invoice->details['Break Down'][months_charges]);
					}
					*/
					  $invoice_html .= 
					  '</td>
					<td align="center" style="width:221px;" valign="top">'.date_reformat($invoice->details[Other_details][invoice_due_date],'').'<br />
					  ';
					$invoice_html .= accounts_format($invoice->details[Other_details][fined_payable]);
					/*  
					if($invoice->details[Other_details][generated_by] == 'Bill Run'){
					//if($invoice->details[Other_details][service_type] == 'Postpaid'){
						$invoice_html .= accounts_format($invoice->details[Other_details][fined_payable]);
					}else{
						$invoice_html .= accounts_format(-$invoice->details['Break Down'][months_charges]);
					}
					*/
					  $invoice_html .= 
					  '</td>
				  </tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height="20">
			<img name="spacer" src="images/spacer.png" width="1126" height="16" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="1126">
            	<tr>
                	<td valign="top" style="width:400px; border:1px #CCCCCC solid; padding:2px; background:url(images/detail_bot_remove.png) bottom no-repeat;">
						<table border="0" cellpadding="0" cellspacing="0" width="400" height="687" style="height:687px overflow:hidden;">
						<tr>
							<td valign="top">'.display_invoice_charges_summary($invoice->details['Break Down']).'</td>
						</tr>
						<tr>
							<td valign="top" style="padding-top:2px;">
							<img name="advert" src="images/advert.png" width="400" height="478" border="0" id="advert" alt="" />
							</td>
						</tr>
						</table>
					</td>
                	<td valign="top" width="23" style="padding:0px 1px 0px 1px;">
                    	<img name="spacer" src="images/spacer.png" width="20" height="687" border="0" id="spacer" alt="" />
                    </td>
                	<td style="padding:2px; border:1px #CCCCCC solid; width:697px; height:687px;" height="687" valign="top">'.
						display_invoice_charges($invoice->details['Break Down'])
					.'</td>
            	</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td height="17">
				<img name="spacer" src="images/page_nav.png" width="1126" height="15" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
				<img name="tear_here" src="images/tear_here.png" width="1126" height="13" border="0" id="tear_here" alt="" />
			</td>
		</tr>
		<tr>
			<td style="padding:0px 100px 0px 100px;">
				<table border="0" cellpadding="0" cellspacing="0" width="926" class="tear_off_tb">
				 <tr>
				 	<td colspan="5" style="font-weight:bold; height:40px;">Please detach this slip and return with payment. Refer to back for payment locations.</td>
				 </tr>
                 <tr>
                    <td align="right" width="24%" style="font-weight:bold;">Account Number:</td>
                    <td>&nbsp;'.$invoice->details[Other_details][account_number].'</td>
                    <td align="center" >&nbsp;</td>
                    <td width="19%" align="right" style="font-weight:bold;">Bill Date:</td>
                    <td align="left">&nbsp;'.date_reformat($invoice->details[Other_details][invoice_date],'').'</td>
                  </tr>
                  <tr>
                    <td align="right" width="24%" style="font-weight:bold;">Bill Number:</td>
                    <td >&nbsp;'.$invoice->invoice_number.'</td>
                    <td >&nbsp;</td>
                    <td align="right" style="font-weight:bold; ">Amount Payable: </td>
                    <td align="left">&nbsp;'.accounts_format(-$invoice->amount_payable).'</td>
                  </tr>
                  <tr >
                    <td align="right" width="24%" style="font-weight:bold;">Account Balance:</td>
                    <td >&nbsp;';
					$invoice_html .= number_format(-$invoice->details[Other_details][acct_bal],2);
					/*
					if($invoice->details[Other_details][generated_by] == 'Bill Run'){
					//if($invoice->details[Other_details][service_type] == 'Postpaid'){
						$invoice_html .= number_format(-$invoice->details[Other_details][acct_bal],2);
					}else{
						$invoice_html .= 'Not Applicable';
					}
					*/					
					$invoice_html .= '</td>
                    <td >&nbsp;</td>
                    <td align="right" style="font-weight:bold;">Due Date:</td>
                    <td align="left" >&nbsp;'.date_reformat($invoice->details[Other_details][invoice_due_date],'').'</td>
                  </tr>
                  <tr bgcolor="#CCCCCC" style="font-weight:bold">
                    <td width="24%" align="center" style="border:#000000 1px solid;">Payment Mode</td>
                    <td style="border:#000000 1px solid;" align="center">Amount</td>
                    <td width="19%" style="border:#000000 1px solid;" align="center">Date</td>
                    <td style="border:#000000 1px solid;" align="center">Cheque Number</td>
                    <td align="center" style="border:#000000 1px solid;">Bank/Branch</td>
                  </tr>

                  <tr>
                    <td width="24%" style="border:#000000 1px solid;" align="left">Cheque / DD / Pay Order</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                  </tr>
                  <tr>
                    <td width="24%" style="border:#000000 1px solid;" align="left">Cash</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td align="center">&nbsp;</td>
                    <td align="center">&nbsp;</td>
                  </tr>
				  <tr>
					<td colspan="5">
						<img name="spacer" src="images/spacer.png" width="700" height="10" border="0" id="spacer" alt="" />
					</td>
				</tr>
                </table>
			</td>
		</tr>
		<tr>
			<td>
			<img name="footer" src="images/footer.png" width="1126" height="90" border="0" id="footer" alt="" />
			</td>
		</tr>
		<tr>
			<td style="font-size:80%; padding:10px 0px 0px 0px" align="center">
				<span class="point"> Warid Telecom Uganda Limited </span>
				<span class="point"> Plot 16A Clement Hill Road, P.O.B0x 70665, Kampala </span>
				<span class="point"> Email: customercare@waridtel.co.ug, Call: 070 077 7000, Web: www.waridtel.co.ug</span>
			</td>
		</tr>
		</table>
	';
	} else {
		echo "No data retrieved<br>";
	}
	
	return $invoice_html;
}

function display_emailinvoice_byid($id){
	
	$invoicing = new wimax_invoicing();
	
	$invoice = $invoicing->Get($id);
	if($invoice){$invoice->details = unserialize($invoice->details);
		//print_r($invoice->details);
		$invoice_html = '
		<style type="text/css">
		td img {display: block;}
		
		.body{
			font-size:100%;
			font-family: Arial, Helvetica, sans-serif;
			margin-left:auto;
			margin-right:auto;
		}
		
		.client_details{
			width:297px;
			height:120px;
			vertical-align:top;
		}
		
		.billing_intro{
			vertical-align:top;
			width:377px;
			height:120px;
			overflow:hidden;
		}
		.account_summary_figures{
			font-weight:bold;
			background-color:#D1D2D4;
		}
		.charges_summary_head{
			font-weight:bold;
			font-size:130%;
			color:#FFFFFF;
			height:35px;
			padding-left:10px;
			background-color:#ED1C24;
			border-top:1px #000000 solid;
			border-bottom:1px #000000 solid;
		}
		.valuez{
			text-align:right;
			padding-left:5px;
		}
		.breakdown_highlight{
			height:25px;
			background-color:#D1D2D4;
			border-top:1px #000000 solid;
			border-bottom:1px #000000 solid;
			font-weight:bold;
			
		}
		.breakdown_{
			height:20px;
		}
		.breakdown_detail{
			border:#444 1px solid;
		}
		.breakdown_detail td{
			height:15px;
			font-size:90%;
		}
		.tear_off_tb tr td{
			height:20px;
		}
		.point{
			background:url(http://wimaxcrm.waridtel.co.ug/billing/images/cust_bullet.png) left no-repeat;
			padding:0px 15px 0px 15px;
		}
		</style>
		<table class="body" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="1126">
		<tr>
			<td>
			<img name="header" src="http://wimaxcrm.waridtel.co.ug/billing/images/header.png" width="1126" height="157" border="0" id="header" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" style="width:1126px; height:120px; overflow:hidden;">
			<tr>
				<td class="client_details" >
					<strong>'.strtoupper($invoice->details[Other_details][account_name]).'</strong><br>
					<!--<strong>'.$invoice->details["Other_details"]["individual"].'</strong><br />-->
					'.strtoupper($invoice->details["Other_details"]["physical_address"]).'
				</td>
				<td valign="top"><img src="http://wimaxcrm.waridtel.co.ug/billing/images/spacer.png" width="244" height="120" /></td>
				<td class="billing_intro">
					<table border="0" cellpadding="0" cellspacing="0" width="377">
						<tr>
							<td width="37%" >Account Number</td>
							<td width="63%" >: '.$invoice->details["Other_details"]["account_number"].'</td>
						  </tr>
						  <tr>
							<td >Invoice Number</td>
							<td >: '.$invoice->invoice_number.'</td>
						  </tr>
						  <tr>
							<td >Service Type</td>
							<td >: Broadband - '.$invoice->details[Other_details][service_type].'</td>
						  </tr>
						  <tr>
							<td >Invoice Currency</td>
							<td >: '.$invoice->details["Other_details"]["invoice_currency"].'</td>
						  </tr>
						  <tr>
							<td >Invoice Date</td>
							<td >: '.date_reformat($invoice->details["Other_details"]["invoice_date"],'').'</td>
						  </tr>
						  <tr>
							<td >Invoice Period</td>
							<td >: '.date_reformat($invoice->details["Other_details"]["invoice_start"],'').' to '.date_reformat($invoice->details["Other_details"]["invoice_end"],'').'</td>
						  </tr>
						  <tr>
							<td >Due Date</td>
							<td >: '.date_reformat($invoice->details["Other_details"]["invoice_due_date"],'').'</td>
						  </tr>
					</table>
				</td>
				<td>
				<img name="bar_code" src="http://wimaxcrm.waridtel.co.ug/billing/images/bar_code.png" width="208" height="120" border="0" id="bar_code" alt="" />
				</td>
			</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td style="padding:10px 0px 20px 0px;">
			<img name="VAT_N_TIN" src="http://wimaxcrm.waridtel.co.ug/billing/images/VAT_N_TIN3.png" width="1126" height="37" border="0" id="VAT_N_TIN" alt="" />
			</td>
		</tr>
		<tr class="account_summary_figures">
			<td>
			<img name="account_summary" src="http://wimaxcrm.waridtel.co.ug/billing/images/account_summary2.png" width="1126" height="71" border="0" id="wimaxbill_r4_c2" alt="" />
			</td>
		</tr>
		<tr class="account_summary_figures">
			<td>
				<table border="0" cellpadding="0" cellspacing="0" width="1126">
				  <tr>
					<td align="center" style="width:188px; height:63px;">'.accounts_format(-$invoice->previous_balance).'</td>
					<td align="center" style="width:155px; height:63px;">'.accounts_format($invoice->payments_sum).'</td>
					<td align="center" style="width:168px; height:63px;">'.accounts_format($invoice->adjustments_sum).'</td>
					<td align="center" style="width:203px; height:63px;">'.accounts_format(-$invoice->details['Break Down'][months_charges]).'</td>
					<td align="center" style="width:191px; height:63px;">'.date_reformat($invoice->details[Other_details][invoice_date],'').'<br />
					  '.accounts_format(-$invoice->amount_payable).'</td>
					<td align="center" style="width:221px; height:63px;">'.date_reformat($invoice->details[Other_details][invoice_due_date],'').'<br />
					  '.accounts_format($invoice->details[Other_details][fined_payable]).'</td>
				  </tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
			<img name="spacer" src="http://wimaxcrm.waridtel.co.ug/billing/images/spacer.png" width="1126" height="35" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="1126">
            	<tr>
                	<td valign="top" style="border:1px #CCCCCC solid; padding:2px; background:url(http://wimaxcrm.waridtel.co.ug/billing/images/detail_bot_remove.png) bottom no-repeat;">
						<table border="0" cellpadding="0" cellspacing="0" width="507" style="width:507px; overflow:hidden;">
						<tr>
							<td>'.display_invoice_charges_summary($invoice->details['Break Down']).'</td>
						</tr>
						<tr>
							<td style="padding-top:15px;">'.display_invoice_charges($invoice->details['Break Down']).'</td>
						</tr>
						</table>
					</td>
                	<td valign="top">
                    	<img name="spacer" src="http://wimaxcrm.waridtel.co.ug/billing/images/spacer.png" width="114" height="649" border="0" id="spacer" alt="" />
                    </td>
                	<td valign="top">
                    	<img name="advert" src="http://wimaxcrm.waridtel.co.ug/billing/images/advert.png" width="501" height="649" border="0" id="advert" alt="" />
                    </td>
            	</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
				<img name="spacer" src="http://wimaxcrm.waridtel.co.ug/billing/images/spacer.png" width="1126" height="17" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
				<img name="tear_here" src="http://wimaxcrm.waridtel.co.ug/billing/images/tear_here.png" width="1126" height="13" border="0" id="tear_here" alt="" />
			</td>
		</tr>
		<tr>
			<td style="padding:0px 100px 0px 100px;">
				<table border="0" cellpadding="0" cellspacing="0" width="926" class="tear_off_tb">
				 <tr>
				 	<td colspan="5" style="font-weight:bold; height:40px;">Please detach this slip and return with payment. Refer to back for payment locations.</td>
				 </tr>
                 <tr>
                    <td align="right" width="24%" style="font-weight:bold;">Account Number:</td>
                    <td>&nbsp;'.$invoice->details[Other_details][account_number].'</td>
                    <td align="center" >&nbsp;</td>
                    <td width="19%" align="right" style="font-weight:bold;">Bill Date:</td>
                    <td align="left">&nbsp;'.date_reformat($invoice->details[Other_details][invoice_date],'').'</td>
                  </tr>
                  <tr>
                    <td align="right" width="24%" style="font-weight:bold;">Bill Number:</td>
                    <td >&nbsp;'.$invoice->invoice_number.'</td>
                    <td >&nbsp;</td>
                    <td align="right" style="font-weight:bold; ">Amount Payable: </td>
                    <td align="left">&nbsp;'.accounts_format(-$invoice->amount_payable).'</td>
                  </tr>
                  <tr >
                    <td align="right" width="24%" style="font-weight:bold;">Account Balance:</td>
                    <td >&nbsp;'.accounts_format(-$invoice->details[Other_details][acct_bal]).'</td>
                    <td >&nbsp;</td>
                    <td align="right" style="font-weight:bold;">Due Date:</td>
                    <td align="left" >&nbsp;'.date_reformat($invoice->details[Other_details][invoice_due_date],'').'</td>
                  </tr>
                  <tr bgcolor="#CCCCCC" style="font-weight:bold">
                    <td width="24%" align="center" style="border:#000000 1px solid;">Payment Mode</td>
                    <td style="border:#000000 1px solid;" align="center">Amount</td>
                    <td width="19%" style="border:#000000 1px solid;" align="center">Date</td>
                    <td style="border:#000000 1px solid;" align="center">Cheque Number</td>
                    <td align="center" style="border:#000000 1px solid;">Bank/Branch</td>
                  </tr>

                  <tr>
                    <td width="24%" style="border:#000000 1px solid;" align="left">Cheque / DD / Pay Order</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                  </tr>
                  <tr>
                    <td width="24%" style="border:#000000 1px solid;" align="left">Cash</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                    <td align="center">&nbsp;</td>
                    <td align="center">&nbsp;</td>
                  </tr>
				  <tr>
                    <td> </td>
                  </tr>
                </table>
			</td>
		</tr>
		<tr>
			<td>
				<img name="spacer" src="http://wimaxcrm.waridtel.co.ug/billing/images/spacer.png" width="1126" height="7" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<img name="footer" src="http://wimaxcrm.waridtel.co.ug/billing/images/footer.png" width="1126" height="90" border="0" id="footer" alt="" />
			</td>
		</tr>
		<tr>
			<td style="font-size:80%; padding:15px 0px 0px 0px" align="center">
				<span class="point"> Warid Telecom Uganda Limited </span>
				<span class="point"> Plot 16A Clement Hill Road, P.O.B0x 70665, Kampala </span>
				<span class="point"> Email: customercare@waridtel.co.ug, Call: 070 077 7000, Web: www.waridtel.co.ug</span>
			</td>
		</tr>
		</table>
	';	} else {
		echo "No data retrieved<br>";
	}
	
	return $invoice_html;
}

function display_n_invoice_data($invoice){
	
	$invoice_html = $invoice;
	
	return $invoice_html;
}

function display_invoice_data($invoice){

	//print_r($invoice); echo "<br><br>";
$invoice_html = '
		<style type="text/css">
		td img {display: block;}
		
		.body{
			font-size:100%;
			font-family: calibri,Arial;
			margin-left:auto;
			margin-right:auto;
		}
		
		.client_details{
			width:297px;
			height:120px;
			vertical-align:top;
		}
		
		.billing_intro{
			vertical-align:top;
			width:377px;
			height:120px;
			overflow:hidden;
		}
		.account_summary_figures{
			font-weight:bold;
			background-color:#D1D2D4;
		}
		.charges_summary_head{
			font-weight:bold;
			font-size:130%;
			color:#FFFFFF;
			height:35px;
			padding-left:10px;
			background-color:#ED1C24;
			border-top:1px #000000 solid;
			border-bottom:1px #000000 solid;
		}
		.valuez{
			text-align:right;
			padding-left:5px;
		}
		.breakdown_highlight{
			height:25px;
			background-color:#D1D2D4;
			border-top:1px #000000 solid;
			border-bottom:1px #000000 solid;
			font-weight:bold;
			
		}
		.breakdown_{
			height:20px;
		}
		.breakdown_detail{
			border:#444 1px solid;
		}
		.breakdown_detail td{
			height:15px;
			font-size:90%;
		}
		.tear_off_tb tr td{
			height:20px;
		}
		.point{
			background:url(images/cust_bullet.png) left no-repeat;
			padding:0px 15px 0px 15px;
		}
		</style>
		<table class="body" bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="1126">
		<tr>
			<td>
			<img name="header" src="images/header_proforma.png" width="1126" height="157" border="0" id="header" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" style="width:1126px; height:120px; overflow:hidden;">
			<tr>
				<td class="client_details" >
					<strong>'.strtoupper($invoice[xtra][Other_details][account_name]).'</strong><br>
					<!--<strong>'.$invoice[xtra]["Other_details"]["individual"].'</strong><br />-->
					'.strtoupper($invoice[xtra]["Other_details"]["physical_address"]).'
				</td>
				<td valign="top"><img src="images/spacer.png" width="244" height="120" /></td>
				<td class="billing_intro">
					<table border="0" cellpadding="0" cellspacing="0" width="377">
						  <!--<tr>
							<td width="37%" >Account Number</td>
							<td width="63%" >: '.$invoice[xtra]["Other_details"]["account_number"].'</td>
						  </tr>
						  <tr>
							<td >Invoice Number</td>
							<td >: '.$invoice->invoice_number.'</td>
						  </tr>
						  <tr>
							<td >Service Type</td>
							<td >: Broadband - '.$invoice[xtra][Other_details][service_type].'</td>
						  </tr>-->
						  <tr>
							<td >Invoice Currency</td>
							<td >: '.$invoice[xtra]["Other_details"]["invoice_currency"].'</td>
						  </tr>
						  <tr>
							<td >Invoice Date</td>
							<td >: '.date_reformat($invoice[xtra]["Other_details"]["invoice_date"],'').'</td>
						  </tr>
						  <tr>
							<td >Invoice Period</td>
							<td >: '.date_reformat($invoice[xtra]["Other_details"]["invoice_start"],'').' to '.date_reformat($invoice[xtra]["Other_details"]["invoice_end"],'').'</td>
						  </tr>
						  <!--<tr>
							<td >Due Date</td>
							<td >: '.date_reformat($invoice[xtra]["Other_details"]["invoice_due_date"],'').'</td>
						  </tr>-->
					</table>
				</td>
				<td>
				<img name="bar_code" src="images/bar_code.png" width="208" height="120" border="0" id="bar_code" alt="" />
				</td>
			</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td style="padding:10px 0px 20px 0px;">
			<img name="VAT_N_TIN" src="images/VAT_N_TIN.png" width="1126" height="37" border="0" id="VAT_N_TIN" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<img name="spacer" src="images/spacer.png" width="1126" height="70" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="1126">
            	<tr>
                	<td valign="top" style="border:1px #CCCCCC solid; padding:2px; background:url(images/detail_bot_remove.png) bottom no-repeat;">
						<table border="0" cellpadding="0" cellspacing="0" width="507" style="width:507px; overflow:hidden;">
						<tr>
							<td>'.display_invoice_charges_summary($invoice[xtra]['Break Down']).'</td>
						</tr>
						<tr>
							<td style="padding-top:15px;">'.display_invoice_charges($invoice[xtra]['Break Down']).'</td>
						</tr>
						</table>
					</td>
                	<td valign="top">
                    	<img name="spacer" src="images/spacer.png" width="114" height="649" border="0" id="spacer" alt="" />
                    </td>
                	<td valign="top">
						<img name="advert" src="images/advert.png" width="501" height="649" border="0" id="advert" alt="" />
                    </td>
            	</tr>
			</table>
			</td>
		</tr>
		<tr>
			<td>
				<img name="spacer" src="images/spacer.png" width="1126" height="17" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
				<img name="tear_here" src="images/tear_here.png" width="1126" height="13" border="0" id="tear_here" alt="" />
			</td>
		</tr>
		<tr>
			<td>
				<img name="spacer" src="images/spacer.png" width="1126" height="20" border="0" id="spacer" alt="" />
			</td>
		</tr>
		<tr>
			<td>
			<img name="footer" src="images/footer.png" width="1126" height="90" border="0" id="footer" alt="" />
			</td>
		</tr>
		<tr>
			<td style="font-size:80%; padding:15px 0px 0px 0px" align="center">
				<span class="point"> Warid Telecom Uganda Limited </span>
				<span class="point"> Plot 16A Clement Hill Road, P.O.B0x 70665, Kampala </span>
				<span class="point"> Email: customercare@waridtel.co.ug, Call: 070 077 7000, Web: www.waridtel.co.ug</span>
			</td>
		</tr>
		</table>
	';

	return $invoice_html;
}

function display_selected_invoices($invoices_data){
	if($invoices_data){
		foreach($invoices_data as $invoice){
			$invoices_HTML .= display_invoice_byid($invoice->id);
			$invoices_HTML .= '<p style="page-break-before: always">';
		}
	}else{
		echo "No Invoices to display <br>";
	}
	
	return $invoices_HTML;
}

function generate_receipt($id){
	
	if($id){
		$billing = new wimax_billing();
		$myquery = new uniquequerys();
		
		$receipt['db_data'] = $billing->Get($id);
		$account_id = $receipt['db_data']->account_id;
		$receipt['db_data']->title = $_GET[title];
		$account_info = $myquery->multiplerow_query("
			SELECT 
			accounts.name,
			accounts_cstm.billing_add_strt_c,
			accounts_cstm.billing_add_area_c,
			accounts_cstm.billing_add_town_c,
			accounts_cstm.billing_add_plot_c,
			accounts_cstm.billing_add_district_c,
			accounts_cstm.crn_c
			FROM
			accounts
			INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
			WHERE 
			accounts_cstm.crn_c = '$account_id' AND accounts.deleted = '0';
		");
		
		$receipt[xtra]['account number'] = $account_info[0]['crn_c'];
		$receipt[xtra]['account name'] = $account_info[0]['name'];
		$receipt[xtra]['physical address'] = $account_info[0]['billing_add_strt_c']."<br>".
											$account_info[0]['billing_add_plot_c']."<br>".
											$account_info[0]['billing_add_district_c']."<br>";
	
	}
	return $receipt;
}

function display_receipt($receipt_data){

	if($receipt_data){
		$receipt_data[db_data]->entry = unserialize($receipt_data[db_data]->entry);
		
		if($receipt_data[db_data]->matched_invoice == 0){
			$receipt_data[db_data]->matched_invoice = 'N/P';
		}

		//Customising thet titles
		if($receipt_data[db_data]->entry_type == 'Payment'){
			$receipt_data[db_data]->title = strtoupper($receipt_data[db_data]->entry[grouping].' '.$receipt_data[db_data]->title.' Receipt');
		}else{
			$receipt_data[db_data]->title = strtoupper($receipt_data[db_data]->entry[grouping]);
		}
		
		if($receipt_data[db_data]->entry[parent_account_billing_currency] == ''){
			$receipt_data[db_data]->entry[parent_account_billing_currency] = 'USD';
		}
		
		if($receipt_data[db_data]->entry[grouping] == 'Credit Note'){
			if(accounts_format($receipt_data[db_data]->entry[wrong_value_submitted]) == '0.00'){
				$receipt_data[db_data]->entry[wrong_value_submitted] = -$receipt_data[db_data]->amount;
			}
			$receipt_data[db_data]->amount = $receipt_data[db_data]->amount/1.18;
		}elseif($receipt_data[db_data]->entry[grouping] == 'Debit Note'){
			if(accounts_format($receipt_data[db_data]->entry[correct_value]) == '0.00'){
				$receipt_data[db_data]->entry[correct_value] = -$receipt_data[db_data]->amount;
			}
			$receipt_data[db_data]->amount = $receipt_data[db_data]->amount/1.18;
		}
		
		//Old
		/*if(($receipt_data[db_data]->currency == 'UGX') && ($receipt_data[db_data]->matched_invoice != 'N/A')){
			$invoice_row = get_invoice($receipt_data[db_data]->matched_invoice);
			$receipt_data[db_data]->amount = convert_value($receipt_data[db_data]->amount, 'USD', $invoice_row->generation_date);
		}elseif(($receipt_data[db_data]->currency == 'UGX') && ($receipt_data[db_data]->matched_invoice == 'N/A')){
			$receipt_data[db_data]->amount = convert_value($receipt_data[db_data]->amount,'USD',$receipt_data[db_data]->entry_date);
		}*/
		
		$rate_date = get_rate_date($receipt_data[db_data]->entry_date,$receipt_data[db_data]->rate_date);
		$receipt_data[db_data]->amount = convert_value($receipt_data[db_data]->amount,$receipt_data[db_data]->entry[parent_account_billing_currency],$rate_date,$receipt_data[db_data]->currency);
		
		/*else{
			echo "Un accounted for Currency Matched (or not) scenario<br>";
		}*/
		
		$receipt_html = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>'.$receipt_data[db_data]->title.' Receipt: No. '.$receipt_data[db_data]->entry_id.'</title>
		</head>
		<body style="font-family:calibri;">
		<table width="670" border="0" cellspacing="2" cellpadding="2" align="center">
		<br><br><br><br><br><br>
		  <tr>
			<td>&nbsp;</td>
			</tr>
		  <tr>
			<td align="center"><span style="font-size:20px; text-decoration:underline; font-weight:bold">'.$receipt_data[db_data]->title.'</span></td>
			</tr>
		  <tr>
			<td><table width="100%" border="0" cellspacing="2" cellpadding="2">
		
			  <tr>
				<td width="45%" valign="top"><table width="100%" border="0" cellspacing="2" cellpadding="2">
				  <tr>
					<td><span style="text-decoration:underline; font-weight:bold">CUSTOMER:</span></td>
				  </tr>
				  <tr>
					<td><strong>'.$receipt_data[xtra]['account name'].'</strong></td>
				  </tr>
				  <tr>
					<td><strong>'.$receipt_data[xtra]['physical address'].'</strong></td>
				  </tr>
				  
				</table></td>
				<td width="10%">&nbsp;</td>
				<td width="45%"><table width="100%" border="0" cellspacing="2" cellpadding="2">
				  <tr>
					<td><span style="text-decoration:underline; font-weight:bold">WARID TELECOM (U) LTD:</span></td>
				  </tr>
				  <tr>
					<td></td>
				  </tr>
				  <tr>
					<td><strong>TIN NO: 1000028977</strong></td>
				  </tr>
				</table></td>
		
			  </tr>
			</table></td>
			</tr>
		  <tr>
			<td>&nbsp;</td>
			</tr>
		  <tr>
			<td ><table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr>
		
				<td width="45%" valign="top"><table width="100%" border="0" cellspacing="2" cellpadding="2">
				  <tr>
					<td><span style="font-weight:bold">Account Number:</span>'.$receipt_data[xtra]['account number'].'</td>
				  </tr>
				  <tr>
					<td align="right"></td>
				  </tr>
				  
				</table></td>
				<td width="10%">&nbsp;</td>
				<td width="45%"><table width="100%" border="0" cellspacing="2" cellpadding="2">
				  <tr>
					<td><span style="font-weight:bold">ENTRY NO: '.$receipt_data[db_data]->entry_id.'</span></td>
				  </tr>
		
				  <tr>
					<td><strong>ENTRY DATE:</strong> <span style="font-weight:normal">'.date_reformat($receipt_data[db_data]->entry_date,'%D %M %Y').'</span></td>
				  </tr>
				  <!-- 
					  <tr>
						<td>&nbsp;</td>
					  </tr> 
				  -->
				</table></td>
			  </tr>
			</table></td>
			</tr>
		  <tr>
			<td>&nbsp;</td>
			</tr>
		  <tr>
			<td><table width="100%" cellspacing="0" cellpadding="4">
			  <tr bgcolor="#CCCCCC" style="font-weight:bold">
				<td style="border:#000000 1px solid;" align="center">INVOICE No</td>
				<td style="border:#000000 1px solid;" align="center" width="30%">DESCRIPTION</td>
				<td style="border:#000000 1px solid;" align="center">SPECIFICS</td>
				<td style="border:#000000 1px solid;" align="center">CURRENCY</td>
				<td style="border:#000000 1px solid;" align="center">AMOUNT</td>
			  </tr>';
			  //The correct and wrong values
			 /*if(($receipt_data[db_data]->entry[grouping] == 'Credit Note')||($receipt_data[db_data]->entry[grouping] == 'Debit Note')){
				  $receipt_html .= '
				  <tr>
					<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->matched_invoice.'</td>
					<td style="border:#000000 1px solid;" align="center">Before Adjustment</td>
					<td style="border:#000000 1px solid;" align="left">'.$receipt_data[db_data]->entry[entry].'</td>
					<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->currency.'</td>
					<td style="border:#000000 1px solid;" align="right">'.accounts_format($receipt_data[db_data]->entry[wrong_value_submitted]/1.18).'</td>
				  </tr>
				  <tr>
					<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->matched_invoice.'</td>
					<td style="border:#000000 1px solid;" align="center">After adjustment</td>
					<td style="border:#000000 1px solid;" align="left">'.$receipt_data[db_data]->entry[entry].'</td>
					<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->currency.'</td>
					<td style="border:#000000 1px solid;" align="right">'.accounts_format($receipt_data[db_data]->entry[correct_value]/1.18).'</td>
				  </tr>';
			  }*/
			 $receipt_html .= '
			 <tr>
				<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->matched_invoice.'</td>
				<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->entry[grouping].'</td>
				<td style="border:#000000 1px solid;" align="left">'.$receipt_data[db_data]->entry[entry].'</td>
				<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->currency.'</td>
				<td style="border:#000000 1px solid;" align="right">'.accounts_format($receipt_data[db_data]->amount).'</td>
			  </tr>';
			  //VAT Difference and total
			  if(($receipt_data[db_data]->entry[grouping] == 'Credit Note')||($receipt_data[db_data]->entry[grouping] == 'Debit Note')){
			  $receipt_html .= '
			  	<tr>
					<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->matched_invoice.'</td>
					<td style="border:#000000 1px solid;" align="center">VAT (18%)</td>
					<td style="border:#000000 1px solid;" align="left">'.$receipt_data[db_data]->entry[entry].'</td>
					<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->currency.'</td>
					<td style="border:#000000 1px solid;" align="right">'.accounts_format($receipt_data[db_data]->amount * 0.18).'</td>
			  	</tr>
				<tr bgcolor="#CCCCCC" style="font-weight:bold">
					<td style="border:#000000 1px solid;" align="center" colspan="3">TOTAL </td>
					<td style="border:#000000 1px solid;" align="center">'.$receipt_data[db_data]->currency.'</td>
					<td style="border:#000000 1px solid;" align="right">'.accounts_format($receipt_data[db_data]->amount * 1.18).'</td>
			  	</tr>';
			  }
			$receipt_html .= '
			</table></td>
			</tr>
			<!-- 
				<tr>
				<td height="10px">&nbsp;</td>
				</tr>
			-->
		  	<tr bgcolor="#CCCCCC" style="font-weight:bold">
			<td align="left" style="border:#000000 1px solid;">ENTRY DETAILS</td>
			</tr>
		  	<tr>
			<td style="border:#000000 1px solid; font-size:13px;" align="left" height="50px">'.$receipt_data[db_data]->entry[details].'</td>
			</tr>
		  	<tr>
			<td>&nbsp;</td>
			</tr>
		
		  <tr>
			<td><table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr>
				<td width="45%">
					<table width="100%" border="0" cellspacing="2" cellpadding="2">
				  		<tr>
							<td align="right" width="50%"><strong>ENTERED BY :</strong></td>
							<td style="border-bottom: 1px #000000 solid;" width="50%">'.$receipt_data[db_data]->user.'</td>
				  		</tr>
					</table>
				</td>
				<td width="45%">
					<table width="100%" border="0" cellspacing="2" cellpadding="2">
				  		<tr>
							<td align="right" width="50%"><strong>SIGNATURE:</strong></td>
							<td style="border-bottom: 1px #000000 solid;" width="50%">&nbsp;</td>
				  		</tr>
					</table>
				</td>
			  </tr>
			';
			if(($receipt_data[db_data]->entry[grouping] == 'Credit Note')||($receipt_data[db_data]->entry[grouping] == 'Debit Note')){
				$receipt_html .= '
					<!--<tr>
					<td width="45%">
						<table width="100%" border="0" cellspacing="2" cellpadding="2">
							<tr>
								<td align="right" width="50%"><strong>APPROVED BY :</strong></td>
								<td style="border-bottom: 1px #000000 solid;" width="50%">'.$receipt_data[db_data]->entry[approved_by].'</td>
							</tr>
						</table>
					</td>
					<td width="45%">
						<table width="100%" border="0" cellspacing="2" cellpadding="2">
							<tr>
								<td align="right" width="50%"><strong>SIGNATURE:</strong></td>
								<td style="border-bottom: 1px #000000 solid;" width="50%">&nbsp;</td>
							</tr>
						</table>
					</td>
				  </tr>-->
			  ';
			}
			$receipt_html .= '
			</table></td>
			</tr>
		  <tr>
			<td>&nbsp;</td>
			</tr>
		  <tr>
			<td height="50">&nbsp;</td>
			</tr>
		</table>
		</body>
		</html>
		';
	} else {
		echo "<!-- No receipt data entered<br> -->";
	}
	
	return $receipt_html;
}

function save_rate(){
	$rating = new wimax_rates();
	
	$saved_rate = $rating->GetList(array(array('rate_date','=',$_POST['date'])));
	$saved_rate = $saved_rate[0];
	
	if(!$saved_rate){
		$rating->rate_date = $_POST['date'];
		$rating->rate = $_POST['rate'];
		
		$savedid = $rating->SaveNew();
		
		$saved_rate = $rating->Get($savedid);
		
		$return_value['entry_status'] = "Rate for ".$rating->rate_date." has been entered at ".$saved_rate->rate;
		$return_value['rate'] = $saved_rate->rate;
	} else {
		$return_value['entry_status'] = "Rate for ".$_POST['date']." was already entered at ".$saved_rate->rate;
		$return_value['rate'] = $saved_rate->rate;
	}
	
	return $return_value;
}

function get_rate($date){
	/*
	$rating = new wimax_rates();
	
	$rate_rows = $rating->GetList(array(array('rate_date','=',$date)));
	$row = $rate_rows[0];
	
	return $row->rate;
	*/
	
	$myquery = new uniquequerys();

	//CCBA
	$query = "SELECT rate_date, rate FROM wimax_rates where id = (select max(id) from wimax_rates where rate_date <= '".$date."')";

	//Live crm
	//$query = "SELECT rate_date, rate FROM wimax_rates where rate_date <= '$date' ORDER BY rate_date DESC LIMIT 1";
	
	//echo $query."<br>";

	$result = $myquery->uniquequery($query);
	
	/*foreach($result as $key=>$value){
		echo "Key [".$key."] Value [".$value."]<br>";
	}*/
	
	return $result;
}

function check_rate_date($entry_date,$rate_date){
	$difference = (strtotime($entry_date) - strtotime($rate_date));
	
	//echo "Entry date is ".$entry_date." => ".strtotime($entry_date)." and Rate date is ".$rate_date." => ".strtotime($rate_date)." and difference is ".$difference."<br>";
	
	if($difference > 86400){
		$rate_array = get_rate($entry_date);
		echo '
		<span style="font-size:140%; font-weight:bold; color:#F00;">Sorry but the dollar rate has not been set for over two days!<br>
		Last time the dollar rate was set was '.$rate_array[rate_date].' at '.number_format($rate_array[rate],0).'<br><br>
		Please have that sorted before entering any transaction.</span><br><br>
		<a href="'.$_SERVER[HTTP_REFERER].'" style="text-decoration:none; font-weight:bold;">Try Again <span style="font-weight:bold; color:#F00;">(AFTER ENTERING THE RATE)</span></a>
		';
		
		$message = '
			Hello,<br><br>Some one is trying to enter a transaction in the Wimax CRM billing system but the dollar rate for '.$entry_date.' is not set.<br>
			Last time the dollar rate was set was '.$rate_array[rate_date].' at '.number_format($rate_array[rate],0).'<br>
			Please set one from <a href="http://wimaxcrm.waridtel.co.ug/billing/rates.php">the Rates interface</a>.<br><br>
			
			Regards,<br>
			The Wimax CRM Billing system
		';
		
		//$to='deo.kamuntu@waridtel.co.ug,henry.kakembo@waridtel.co.ug'; $bcc='ccbusinessanalysis@waridtel.co.ug';
		$to='ccbusinessanalysis@waridtel.co.ug'; $bcc='';
		sendHTMLemail($to,$bcc,$message,$subject='Notification Type: Wimax CRM Dollar rate not sate for '.$entry_date,$from);
		
		exit();
	}
}

function get_rate_date($date1,$date2){
	
	if($date1 != $date2){
		$date = $date2;
	}else{
		$date = $date1;
	}
	
	return $date;
}

function convert_value($value, $from, $date, $to){
	
	//echo "Converting [".$value."] From [".$from."] to [".$to."] with date `".$date."`";
	
	$rating = new wimax_rates();
	
	if(($from =='USD')&&($to =='')){
		$to = 'UGX';
	}elseif(($from == 'UGX')&&($to =='')){
		$to = 'USD';
	}

	//echo "Converting [".$value."] From [".$from."] to [".$to."] with date `".$date."`<br>";
	
	//$rate_rows = $rating->GetList(array(array('rate_date','=',$date)));
	//$rate_row = $rate_rows[0];
	$result = get_rate($date);
	$rate_row->rate = $result[rate];
	
	if($from != $to){
		if($rate_row){
			if(($from == 'UGX')&&($to == 'USD')){
				$new_value = $value / $rate_row->rate;
			}elseif(($from == 'USD')&&($to == 'UGX')){
				$new_value = $value * $rate_row->rate;
			}else{
				$new_value = $value;
			}
		}else{
			echo "No rate set for ".$date."<br>";
		}
	}else{
		$new_value = $value;
	}
	
	//echo " = ".$new_value." \n <br>";
	
	return $new_value;
}

function display_products_dropdown($grouping){
	$myquery = new uniquequerys();
	
	$query = "SELECT ps_products.name FROM ps_products INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c) WHERE";
	
	if($grouping){
		$query .= " ps_products_cstm.product_grouping_c = '$grouping' and";
	}
	
	$query .= " ps_products.deleted = 0 order by name asc";
	
	$products_list = $myquery->multiplerow_query($query);
	
	$html = '<label><select name="'.$grouping.'product" size="1" id="'.$grouping.'products" class="style1">';
	$html .= '
		<option value="" selected="selected">SELECT THE PRODUCT</option>
	';
	
	if(!$grouping){
		$html .= '
			<option value="Payment">Payment Refund</option>
		';
	}
	
	foreach($products_list as $product){
		$html .= '<option value="'.$product[name].'">'.$product[name].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_accounts_dropdown($service_type){

	$myquery = new uniquequerys();
	$query = "
		SELECT 
			accounts.name, 
			accounts_cstm.crn_c as acc_num 
		FROM 
			accounts 
			INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account) 
		WHERE 
			accounts_cstm.mem_id_c  != '' AND 
			cn_contracts.deleted = '0' AND 
			accounts.deleted = '0'
		";
	
	if($service_type != ''){
		$query .= " and accounts_cstm.service_type_internet_c = '$service_type'";
	}
	
	$query .= " order by name asc";
	
	$accounts_list = $myquery->multiplerow_query($query);
	
	$html = '<select name="account_id" size="1" id="account_id" class="style11" onchange="contentpulse(\'fetchinfo.php?account_id=\' + this.value,\'fetchinfo\')">';
	$html .= '<option value="" selected="selected">ALL ACCOUNT NAMES</option>';
	foreach($accounts_list as $account){
		$html .= '<option value="'.$account[acc_num].'">'.$account[name].'</option>';
	}
	$html .= '</select>';
	
	return $html;
}

function display_accounts_dropdown_javascript($service_type){

	$myquery = new uniquequerys();
	$query = "
		SELECT 
			accounts.name, 
			accounts_cstm.crn_c as acc_num 
		FROM 
			accounts 
			INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id)
			INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account) 
		WHERE 
			accounts_cstm.mem_id_c  != '' AND 
			cn_contracts.deleted = '0' AND 
			accounts.deleted = '0'
		";
	
	if($service_type != ''){
		$query .= " and accounts_cstm.service_type_internet_c = '$service_type'";
	}
	
	$query .= " order by name asc";
	
	$accounts_list = $myquery->multiplerow_query($query);
	
	//$javascript = '<select name="account_id" size="1" id="account_id" class="style11" onchange="contentpulse(\'fetchinfo.php?account_id=\' + this.value,\'fetchinfo\')">';
	//$html .= '<option value="" selected="selected">ALL ACCOUNT NAMES</option>';
	
	$javascript = 'addOption(element_account,"Select an Account","",return_select("",document.getElementById(\'invoice[parent_account_id]\')));
	';
	
	foreach($accounts_list as $account){
		$javascript .= 'addOption(element_account,"'.ucwords(strtolower(trim($account[name]))).'","'.$account[acc_num].'",return_select("'.$account[acc_num].'",document.getElementById(\'invoice[parent_account_id]\')));
		';
	}
	
	return $javascript;
}

function display_products_dropdown_javascript($ver){

	$myquery = new uniquequerys();
	$query = "
		(
			SELECT
				ps_products.name,
				ps_products_cstm.billing_currency_c as currency,
				ps_products.price,
				'ONE TIME' as type,
				ps_products_cstm.product_grouping_c as category,
				ps_products.id,
				if(ps_products_cstm.product_grouping_c = 'Equipment Deposits','taxable','not_taxable') as taxable
			FROM
				ps_products
				Inner Join ps_products_cstm ON ps_products.id = ps_products_cstm.id_c
			where
				ps_products.deleted = 0 and
				ps_products.type='Goods'
			order by category,name asc
		)
		union
		(
			SELECT
				ps_products.name,
				ps_products_cstm.billing_currency_c as currency,
				ps_products.price,
				'MONTHLY' as type,
				ps_products_cstm.product_grouping_c as category,
				ps_products.id,
				if(ps_products_cstm.product_grouping_c = 'Equipment Deposits','taxable','not_taxable') as taxable
			FROM
				ps_products
				Inner Join ps_products_cstm ON ps_products.id = ps_products_cstm.id_c
			where
				ps_products.deleted = 0 and
				ps_products.type='Service'
			order by category,name asc
		)
	";
	
	$products_list = $myquery->multiplerow_query($query);
	
	//$javascript = '<select name="account_id" size="1" id="account_id" class="style11" onchange="contentpulse(\'fetchinfo.php?account_id=\' + this.value,\'fetchinfo\')">';
	//$html .= '<option value="" selected="selected">ALL ACCOUNT NAMES</option>';
	
	$javascript = '';
	
	foreach($products_list as $product){
		$javascript .= 'addOption(element_product,"'.trim($product[type]).' ['.trim($product[category]).'] '.trim($product[name]).' '.trim($product[currency]).' '.number_format(trim($product[price]),2).'","'.$product[id].'","");
		';
	}
	
	return $javascript;
}

function display_parent_accounts_dropdown($selected){
	$myquery = new uniquequerys();
	
	$accounts_list = $myquery->multiplerow_query("
						SELECT accounts.name, accounts_cstm.mem_id_c as parent_acc FROM accounts INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id) where accounts_cstm.mem_id_c  != '' group by parent_acc order by name asc"	);
	
	$html = '<label><span class="style14">Select Account</span> <select name="parent_id" size="1" id="parent_id" class="style11">';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ALL ACCOUNT NAMES</option>';
	foreach($accounts_list as $account){
		$html .= '<option value="'.$account[parent_acc].'" ';
		if($selected == $account[parent_acc]){
			$html .= 'selected="selected"';
		} $html .= '>'.$account[name].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function display_n_parent_accounts_dropdown($selected,$element_name,$onblur){
	$myquery = new uniquequerys();
	
	$accounts_list = $myquery->multiplerow_query("
						SELECT accounts.name, accounts_cstm.mem_id_c as parent_acc FROM accounts INNER JOIN accounts_cstm ON (accounts_cstm.id_c=accounts.id) where accounts_cstm.mem_id_c  != '' group by parent_acc order by name asc");
	
	$html = '<select name="'.$element_name.'" size="1" id="'.$element_name.'" onblur="'.$onblur.'" >';
	$html .= '<option value="" '; if($selected==''){$html .= 'selected="selected"';} $html .= '>ALL ACCOUNT NAMES</option>';
	foreach($accounts_list as $account){
		$html .= '<option value="'.$account[parent_acc].'" ';
		if($selected == $account[parent_acc]){
			$html .= 'selected="selected"';
		} $html .= '>'.ucwords(strtolower(trim($account[name]))).'</option>';
	}
	$html .= '</select>';
	
	return $html;
}

function test_invoice($invoice){
if($invoice){
		$invoice->details = unserialize($invoice->details);
		$invoice_html = '
		<!--
		body {
			margin-top: 140px;
		}
		-->
		</style>
						<table width="800" border="0" cellspacing="2" cellpadding="2" align="center">
						 <tr>
			<td align="right"><br><br><br><br><br><br><br>
			</td>
		  </tr>
		  <tr>
			<td align="right"><span style="font-size:20px; text-decoration:underline; font-weight:bold">'.$invoice->details["Other_details"]["Title"].'</span></td>
		  </tr>
		
		  <tr>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td align="center">&nbsp;</td>
		  </tr>
		  <tr>
			<td><table width="100%" border="0" cellspacing="2" cellpadding="2">
		
			  <tr>
				<td width="45%" valign="top"><table width="100%" border="0" cellspacing="2" cellpadding="2">
				  <tr>
					<td>'.$invoice->details["Other_details"]["individual"].'<br />
				    '.$invoice->details["Other_details"]["physical_address"].'</td>
				  </tr>
				  
		
				</table></td>
				<td width="10%">&nbsp;</td>

				<td width="45%"><table width="100%" border="0" cellspacing="2" cellpadding="2">
				  <tr>
				    <td width="37%"><strong>Account Number</strong></td>
					<td width="63%">'.$invoice->details["Other_details"]["account_number"].'</td>
				  </tr>
				  <tr>
				    <td><strong>Invoice Number</strong></td>
					<td>'.$invoice->id.'</td>
				  </tr>
				  <tr>
				    <td><strong>Invoice Currency</strong></td>
				    <td>'.$invoice->details["Other_details"]["invoice_currency"].'</td>
			      </tr>
				  <tr>
				    <td><strong>Invoice Date</strong></td>
				    <td>'.date_reformat($invoice->details["Other_details"]["invoice_end"],'').'</td>
			      </tr>
				  <tr>
				    <td><strong>Invoice Period</strong></td>
				    <td>'.date_reformat($invoice->details["Other_details"]["invoice_start"],'').' to '.date_reformat($invoice->details["Other_details"]["invoice_end"],'').'</td>
			      </tr>
				  <tr>
				    <td><strong>Due Date</strong></td>
				    <td>'.date_reformat($invoice->details["Other_details"]["invoice_due_date"],'').'</td>
			      </tr>
				  <tr>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
			      </tr>
				  <tr>
				    <td colspan="2"><strong>VAT REGISTRATION NO: 44601-Z</strong></td>
			      </tr>
				  <tr>
				    <td colspan="2"><strong>TIN NO: B06-1009-9500-X</strong></td>
				  </tr>
				</table></td>
			  </tr>
			</table></td>
		  </tr>
		  <tr>
		    <td>&nbsp;</td>
  </tr>
		  <tr>
		    <td><table width="100%" border="0">
		    <tr><strong>Accounts Summary</strong></tr><br>
              <tr>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Previous Balance</span></span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">- Payments</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">- Adjustments</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">+ Charges</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">= Amount Payable</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Amount Payable After</span></td>
              </tr>
              <tr>
                <td align="center">'.accounts_format(-$invoice->previous_balance).'</td>
                <td align="center">'.accounts_format($invoice->payments_sum).'</td>
                <td align="center">'.accounts_format(-$invoice->adjustments_sum).'</td>
                <td align="center">'.accounts_format(-$invoice->details['Break Down'][total_charges]).'</td>
                <td align="center">'.date_reformat($invoice->details[Other_details][invoice_date],'').'<br />
'.accounts_format(-$invoice->amount_payable).'</td>
                <td align="center">'.date_reformat($invoice->details[Other_details][invoice_due_date],'').'<br />
'.accounts_format($invoice->details[Other_details][fined_payable]).'</td>
              </tr>
            </table></td>
  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
		    <td ><strong>Charges</strong></td>
  </tr>
		  <tr>
			<td >'.display_invoice_charges($invoice->details['Break Down']).'</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>
	
		
		
		  <tr>
			<td><table width="100%" cellspacing="0" cellpadding="0">
			<tr>------------------------------------------------------------------------------------------------------------------------------------</tr>
              <tr>
                <td align="right" width="24%"><strong>Account Number:</strong></td>
                <td width="24%">&nbsp;'.$invoice->details[Other_details][account_number].'</td>
                <td align="center" width="24%">&nbsp;</td>
                <td  align="right" width="24%"><strong>Bill Date:</strong></td>
                <td align="left" width="24%">&nbsp;'.date_reformat($invoice->details[Other_details][invoice_date],'').'</td>
              </tr>
              <tr>
                <td align="right" width="24%"><strong>Bill Number:</strong></td>
                <td width="24%">&nbsp;'.$invoice->id.'</td>
                <td width="24%">&nbsp;</td>
                <td align="right" width="24%"><strong>Amount Payable:</strong></td>
                <td align="left" width="24%">&nbsp;'.accounts_format(-$invoice->amount_payable).'</td>
              </tr>
              <tr >
                <td align="right" width="24%" >&nbsp;</td>
                <td width="24%">&nbsp;</td>
                <td width="24%">&nbsp;</td>
                <td align="right" width="24%"><strong>Due Date:</strong></td>
                <td align="left" width="24%">&nbsp;'.date_reformat($invoice->details[Other_details][invoice_due_date],'').'</td>
              </tr>
              <tr bgcolor="#CCCCCC" style="font-weight:bold">
                <td width="24%" align="center" style="border:#000000 1px solid;">Payment Mode</td>
                <td style="border:#000000 1px solid;" align="center" width="24%">Amount</td>
                <td style="border:#000000 1px solid;" align="center" width="24%">Date</td>
                <td style="border:#000000 1px solid;" align="center" width="24%">Cheque Number</td>
                <td width="24%" align="center" style="border:#000000 1px solid;">Bank/Branch</td>
              </tr>
              <tr>
                <td style="border:#000000 1px solid;" align="left">Cheque / DD / Pay Order</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
              </tr>
              <tr>
                <td style="border:#000000 1px solid;" align="left">Cash</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
                <td style="border:#000000 1px solid;" align="center">&nbsp;</td>
              </tr>
	      
            </table></td>
		  </tr>
		    <tr>
			<td>&nbsp;</td>
		  </tr>
		    <tr>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td align="center">Contact Us: email: customercare@waridtel.co.ug, For WIMAX inquiries Call: 0700 777 000 Website: www.waridtel.co.ug</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>
		
		  <tr>
			<td></td>
		  </tr>
		</table>
		';
		
		
	} else {
		echo "No data retrieved<br>";
	}
	
	return $invoice_html;
}

function generate_excel_file($body,$title){
	
	if(!$title){
		$title = 'Wimax_CRM_extract';
	}
	
	//$filename = urldecode($_GET['filename']).".xls";
	$filename = $title.".xls";
	// required for IE, otherwise Content-disposition is ignored
	if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
	
	# This line will stream the file to the user rather than spray it across the screen
	header("Content-type: application/vnd.ms-excel");
	
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment;filename=".$filename);
	header("Expires: 0");
	header("Cache-Control: private");
	session_cache_limiter("public");
	
	$xls = '
		<head>
			<meta http-equiv="Content-Type">
			<style type="text/css">
			
			th {
				font-weight: bold;
				font-size: 10px;
			}
			
			body{
				font-size: 10px;
			}
			
			</style>
		</head>
		<body>
		'.$body.'
		</body>
		';
		
		echo $xls; 
		exit;
}

function display_customer_type_dropdown($selected){
	$myquery = new uniquequerys();
	
	$query = "
			select distinct customer_type_c as customer_type
			from accounts_cstm
			inner join accounts on (accounts.id = accounts_cstm.id_c)
			where accounts.deleted = 0
			";

	$customer_types = $myquery->multiplerow_query($query);
	
	$html = '<label class="style14"> Customer Type <select name="customer_types[]" size="3" multiple="multiple"  id="customer_type[]" class="style1">
			<option value="" '; if(!$selected){$html .= 'selected="selected"';} $html .= '>ALL TYPES</option>
			';
	foreach($customer_types as $customer_type){
		$html .= '
		<option value="'.$customer_type[customer_type].'" '; if($selected==$customer_type[customer_type]){$html .= 'selected="selected"';} $html .= '>'.$customer_type[customer_type].'</option>';
	}
	$html .= '</select></label>';
	
	return $html;
}

function date_add($date,$length, $period){
	
	$myquery = new uniquequerys();
	
	$query = "select DATE_ADD('".$date."',INTERVAL ".$length." ".$period.") as new_date";
	$result = $myquery->uniquequery($query);
	

	return $result[new_date];
}

function last_day($date){
		
	$myquery = new uniquequerys();
	
	$result = $myquery->uniquequery("select last_day('$date') as last_date");
	
	return $result[last_date];
}

function sendHTMLemail($to,$bcc,$message,$subject,$from){
	if(!$from){
		$from = 'Automated Action <ccnotify@waridtel.co.ug>';
	}
	$headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: ".$from."\r\n";
	if($bcc){
		$headers .= "BCC: ".$bcc." \r\n";
	}
    return mail($to,$subject,$message,$headers);
}

function correct_quantity_value($input){
	if(intval($input) < 1){
		$input = 1;
	}
	
	return $input;
}

?>
