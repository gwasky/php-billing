<?
require('control.php');

echo "This repair 'action' is [".$_GET[action]."]<br>";

echo "
	1 - balances, specify parent_id <br>
	2 - userchange<br>
	3 - invoice_no<br>
	4 - ugx_txs<br>
	";

//repair_invoice_details();
//echo "Un comment the save switch on line 79 of repairs scripts <br><br>";
//repair_billing_entries();
//billrun_repair();

	switch($_GET[action]){
		case 'balances':
			echo "running the repair balances ...<br>";
			repairbals($_GET[parent_id]);
			break;
		case 'userchange':
			echo "Changing the username to account numbers ... <br>";
			userchange();
			break;
		case 'invoice_no':
			echo "Serialising the invoice number ... <br>";
			serialise_invoices();
			break;
		case 'ugx_txs':
			echo "Streamlining UGX TXs ... <br>";
			//correct_ugx_txs();
			break;
	}

/*function repair_billing_entries(){
	$billing = new wimax_billing();
	$myquery = new uniquequerys();
	
	$alltxs = $billing->GetList();
	
	foreach($alltxs as $tx){
		switch($tx->entry_type){
			case 'Payment':
				//echo ++$i.$tx->entry."<br>";
				if($tx->entry == 'Cash Payment'){
					$entry[entry] = $tx->entry;
					$entry[grouping] = 'Cash';
				}else{
					$split = explode("Cheque Payment. ",$tx->entry);
					//print_r($split); echo "<br>";
					$entry[entry] = $split[1];
					$entry[grouping] = 'Cheque';
				}
				$tx->entry = serialize($entry);
				$entry = '';
				//print_r($tx->entry); echo "<br><br>";
				break;
			case 'Charges':
			case 'Services':
				//echo ++$i.$tx->entry."<br>";
				$entry[entry] = $tx->entry;
				$query = "SELECT
							 ps_products_cstm.product_grouping_c as grouping
							FROM
							 ps_products_cstm
							 INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
							WHERE ps_products.name='$tx->entry'
							";
				$result = $myquery->uniquequery($query);
				$entry[grouping] = $result[grouping];
				//print_r($entry);
				$tx->entry = serialize($entry);
				//print_r($tx->entry); 
				//echo "<br><br>";
				$entry = '';
				break;
			case 'Adjustment':
				if(count(explode(" Credit Note",$tx->entry)) == 2){
					//echo ++$i.$tx->entry."<br>";
					$split = explode(" Credit Note",$tx->entry);
					$entry[entry] = trim($split[0]);
					$entry[grouping] = 'Credit Note';
					//print_r($entry);
					//echo "<br><br>";
				}elseif(count(explode(" Debit Note",$tx->entry)) == 2){
					//echo ++$i.$tx->entry."<br>";
					$split = explode(" Debit Note",$tx->entry);
					$entry[entry] = trim($split[0]);
					$entry[grouping] = 'Debit Note';
					//print_r($entry);
					//echo "<br><br>";
				}else{
					echo "Un catered for entry option".$tx->entry."<br>";
				}
				$tx->entry = serialize($entry);
				$entry = '';
				break;
			default:
				echo $tx->entry_type." - ".$tx->entry;
				break;
		}
		
		//if($tx->Save()){
			echo "Saving Billing ".$tx->id;
		}else{
			echo "Billing Not Saved ".$tx->id;
		}
	}
}
*/

/*//correcting details in an invoice
function billrun_repair($billrun_date){

	$invoicing = new wimax_invoicing();
	$myquery = new uniquequerys();

	$allinvs = $invoicing->GetList();

	foreach($allinvs as $inv){
		$inv->details = unserialize($inv->details);
		foreach($inv->details['Break Down'][items] as &$item){
			//print_r($item); echo "<br><br>";
			$query = "SELECT
						ps_products_cstm.product_grouping_c as grouping
						FROM
						ps_products_cstm
						INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
						WHERE ps_products.name='$item[item]'
						";
			$result = $myquery->uniquequery($query);
			if($result[grouping]){
				$item[grouping] = $result[grouping];
			}else{
				if(count(explode(" Credit Note",$item[item])) == 2){
					$split = explode(" Credit Note",$item[item]);
					$item[item] = trim($split[0]);
					$item[grouping] = 'Credit Note';
				}elseif(count(explode(" Debit Note",$item[item])) == 2){
					$split = explode(" Debit Note",$item[item]);
					$item[item] = trim($split[0]);
					$item[grouping] = 'Debit Note';
				}else{
					echo "Un catered for entry option".$item[item]."<br>";
				}
			}
		}
		foreach($inv->details['Break Down']['untaxed'][items] as &$item){
			//print_r($item); echo "<br><br>";
			$query = "SELECT
						ps_products_cstm.product_grouping_c as grouping
						FROM
						ps_products_cstm
						INNER JOIN ps_products ON (ps_products.id=ps_products_cstm.id_c)
						WHERE ps_products.name='$item[item]'
						";
			$result = $myquery->uniquequery($query);
			if($result[grouping]){
				$item[grouping] = $result[grouping];
			}else{
				if(count(explode(" Credit Note",$item[item])) == 2){
					$split = explode(" Credit Note",$item[item]);
					$item[item] = trim($split[0]);
					$item[grouping] = 'Credit Note';
				}elseif(count(explode(" Debit Note",$item[item])) == 2){
					$split = explode(" Debit Note",$item[item]);
					$item[item] = trim($split[0]);
					$item[grouping] = 'Debit Note';
				}else{
					echo "Un catered for entry option".$item[item]."<br>";
				}
			}
		}
		$inv->details = serialize($inv->details);
		//if($inv->Save()){
			echo "Saving Invoice ".$inv->id;
		}else{
			echo "Invoice Not Saved ".$inv->id;
		}
	}
}
*/

	function repairbals($parent_id){
	
		$billing = new wimax_billing();
	
		if($parent_id){
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
			//echo "Account is ".$acct_id."[".strlen($acct_id)."]<br><table border='1' cellpadding='1'>";
			foreach($acct as $tx){
				//removing spaces ...
				$tx->parent_id = trim($tx->parent_id);
				$tx->account_id = trim($tx->account_id);
				//echo "<tr><td>ID".$tx->id."[".$tx->entry_id."]</td><td>".$tx->entry_date."</td><td>AMNT</td><td>".number_format($tx->amount,2)."</td><td>OLD BAL</td><td>".number_format($tx->balance,2)."</td>";
				$balance[$tx->parent_id] += $tx->amount;
				$tx->balance = $balance[$tx->parent_id];
				//echo "<td>NEWBAL</td><td>".number_format($balance[$tx->parent_id],2)."</td></tr>";
				//Uncomment the line below
				$tx->Save();
			}
			//echo "</table><br><br>";
		}
}

function userchange(){
	
	$billing = new wimax_billing();
	$invoicing = new wimax_invoicing();
	$myquery = new uniquequerys();
	
	$acc_crn_list = $myquery->multiplerow_query("Select preferred_username_c as Username, crn_c from accounts_cstm where preferred_username_c != '' and crn_c !=''");
	
	foreach($acc_crn_list as $row){
		$query = "update wimax_billing set account_id='$row[crn_c]' where account_id='$row[Username]'; ";
		$result = $myquery->uniquenonquery($query);
		echo $query."<br>";
		print_r($result); echo "<br>";
	}	
	foreach($acc_crn_list as $row){
		$query = "update wimax_invoicing set parent_id='$row[crn_c]' where parent_id='$row[Username]'; ";
		$result = $myquery->uniquenonquery($query);
		echo $query."<br>";
		print_r($result); echo "<br>";
	}
}

function serialise_invoices(){

	$myquery = new uniquequerys();
	
	$query = "
		SELECT
			wimax_invoicing.id,
			wimax_invoicing.invoice_number
		FROM
			wimax_invoicing
		ORDER BY
			wimax_invoicing.invoice_number ASC
	";
	
	$invoice_no_list = $myquery->multiplerow_query($query);
	
	foreach($invoice_no_list as $row){
		$query = "Update wimax_invoicing set invoice_number='".++$i."' where id='".$row[id]."' and invoice_number != '".$i."'";
		$result = $myquery->uniquenonquery($query);
		if($i != $row[invoice_number]){
			++$changes;
			echo "Changing Invoice number [".$row[invoice_number]."] to [".$i."]<br>";
		}
	}
	
	echo "<br> Total Number of changes [".number_format($changes)."]";
}

function correct_ugx_txs(){
	$myquery = new uniquequerys();
	$billing = new wimax_billing();
	
	$query = "
		select
			accounts_cstm.mem_id_c as parent_id,
			accounts_cstm.selected_billing_currency_c as acnt_currency
		from
			accounts_cstm
			inner join accounts on (accounts.id = accounts_cstm.id_c)
		where
			accounts.deleted = 0 and
			accounts_cstm.selected_billing_currency_c = 'UGX'
	";
	
	$parent_id_list = $myquery->multiplerow_query($query);
	
	foreach($parent_id_list as $parent_row){
		$billing_entries = $billing->GetList(array(array('parent_id','=',$parent_row[parent_id])));
		echo "Number of entries for Account ".$parent_row[parent_id]." is ".count($billing_entries)."<br>";
		foreach($billing_entries as $billing_entry){
			$billing_entry->entry = unserialize($billing_entry->entry);
			if($billing_entry->entry[parent_account_billing_currency] == ''){
				$billing_entry->entry[parent_account_billing_currency] = 'USD';
				echo "Entry ".$billing_entry->entry_id." Type ".$billing_entry->entry_type.", Currency = ".$billing_entry->entry[parent_account_billing_currency]." amount = ".number_format($billing_entry->amount,2);
				
				//Using the rate date because it was used to begin with in the conversion
				$billing_entry->amount = convert_value($billing_entry->amount, $billing_entry->entry[parent_account_billing_currency], $billing_entry->rate_date,$parent_row[acnt_currency]);
				$billing_entry->rate_date = $billing_entry->entry_date;
				$billing_entry->entry[parent_account_billing_currency] = $parent_row[acnt_currency];
				echo "==>> New Currency = ".$billing_entry->entry[parent_account_billing_currency]." New amount = ".number_format($billing_entry->amount,2)."<br>";
				$billing_entry->entry = serialize($billing_entry->entry);
				$billing_entry->Save();
			}
		}
		echo "<br>";
	}
	
	echo "<a href='repairs_scripts.php?action=balances' target='_blank'>YOU MUST RAN REPAIR BALANCES AFTER!!!</a><br>";
}

/*
function repair_invoice_details(){
	$invoicing = new wimax_invoicing();
	
	$all = $invoicing->GetList();
	
	foreach($all as &$one){
		$one->details = unserialize($one->details);
		$one->details[Other_details][service_type] = 'Postpaid';
		$one->details[Other_details][generated_by] = 'Bill Run';
		$one->details = serialize($one->details);
		if($one->Save()){
			echo "Saved <br>";
		}
	}
}*/

/*function redo_invoices($billrun_date, $account_ids){

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
	
	if($account_ids){
		echo "Rebuilding invoices for selected Account numbers<br>";
	}else{
		$account_ids = array();
		echo "Retrieving account numbers of accounts with invoices in ".get_month($billrun_date)."<br>";
		$acc_query = "
					SELECT 
  						accounts_cstm.mem_id_c as parent_acc
					FROM wimax_invoicing
 						INNER JOIN accounts_cstm ON (wimax_invoicing.username=accounts_cstm.preferred_username_c)
					WHERE 
						wimax_invoicing.billing_date = '$billrun_date' AND
						wimax_invoicing.details LIKE '%Postpaid%';
		";
		$account_array = $myquery->multiplerow_query($acc_query);
		foreach($account_array as $row){
			array_push($account_ids,$row[parent_acc]);
		}
	}
	
	//Populating parent contact Information	
	foreach($account_ids as $id){
		$parent_data = $myquery->uniquequery("
						SELECT 
						  accounts.name,
						  accounts_cstm.contact_person_c,
						  accounts_cstm.billing_add_strt_c,
						  accounts_cstm.billing_add_area_c,
						  accounts_cstm.billing_add_town_c,
						  accounts_cstm.billing_add_plot_c,
						  accounts_cstm.billing_add_district_c,
						  accounts_cstm.preferred_username_c,
						  accounts_cstm.mem_id_c as parent_id,
						  accounts_cstm.service_type_internet_c as service_type,
						  accounts_cstm.crn_c,
						  cn_contracts.start_date
						FROM
						 accounts
						 INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)
						 INNER JOIN cn_contracts ON (accounts.id=cn_contracts.account)
						WHERE
						  accounts_cstm.crn_c = '$id'
						");
	
		$parent_accts[$id][xtra][Other_details][username] = $parent_data[preferred_username_c];
		$parent_accts[$id][xtra][Other_details][account_name] = $parent_data[name];
		$parent_accts[$id][xtra][Other_details][individual] = 'Mr/Mrs/Ms '.$parent_data[contact_person_c];
		$parent_accts[$id][xtra][Other_details][physical_address] =	$parent_data[billing_add_plot_c]."<br>".
																$parent_data[billing_add_strt_c]."<br>".
																$parent_data[billing_add_district_c]."<br>";
		$parent_accts[$id][xtra][Other_details][account_number] = $parent_data[crn_c];
		$parent_accts[$id][xtra][Other_details][invoice_date] = $billrun_date;
		$parent_accts[$id][xtra][Other_details][invoice_currency] = 'USD';
		$parent_accts[$id][xtra][Other_details][invoice_start] = $period_start_date;
		$parent_accts[$id][xtra][Other_details][invoice_end] = $billrun_date;
		$parent_accts[$id][xtra][Other_details][invoice_due_date] = AddDays($billrun_date,14);
		$parent_accts[$id][xtra][Other_details][Title] = 'TAX INVOICE';
		$parent_accts[$id][xtra][Other_details][contract_start] = $parent_data[start_date];
		$parent_accts[$id][xtra][Other_details][service_type] = $parent_data[service_type];
		
		//Summing up payments, charges and adjustments per parent account
		$select_conditions = array(
								array("date_format(entry_date,'%Y-%m')","=","date_format('$billrun_date','%Y-%m')"),
								array("parent_id","=",$id)
								);
		
		$billing_data = $billing->GetList($select_conditions);
		foreach($billing_data as $billing_row){
			$billing_row->entry = unserialize($billing_row->entry);
			if($billing_row->entry_type == 'Payment'){
				$parent_accts[$id][payments_sum] += $billing_row->amount;
			}

			$item_array[account_number] = $parent_accts[$id][xtra][Other_details][account_number];
			
			if($billing_row->entry_type == 'Charges'){
				$item_array[item] = $billing_row->entry[entry];
				$item_array[grouping] = $billing_row->entry[grouping];
				if($billing_row->entry[entry] != 'Equipment Deposit'){
					$parent_accts[$id][Charges] += $billing_row->amount;
					$item_array[value] = ($billing_row->amount/1.18);
					if($parent_accts[$id][xtra]['Break Down'][items]){
						array_push($parent_accts[$id][xtra]['Break Down'][items], $item_array);
					} else {
						$parent_accts[$id][xtra]['Break Down'][items][0] = $item_array;
					}
				}else{
					$parent_accts[$id][xtra]['Break Down'][untaxed][total] += $billing_row->amount;
					$item_array[value] = $billing_row->amount;
					if($parent_accts[$id][xtra]['Break Down'][untaxed][items]){
						array_push($parent_accts[$id][xtra]['Break Down'][untaxed][items], $item_array);
					} else {
						$parent_accts[$id][xtra]['Break Down'][untaxed][items][0] = $item_array;
					}
				}
			}
		
			if($billing_row->entry_type == 'Services'){
				$parent_accts[$id][Services] += $billing_row->amount;
				$item_array[item] = $billing_row->entry[entry];
				$item_array[grouping] = $billing_row->entry[grouping];
				$item_array[value] = $billing_row->amount/1.18;
				if($parent_accts[$id][xtra]['Break Down'][items]){
					array_push($parent_accts[$id][xtra]['Break Down'][items], $item_array);
				} else {
					$parent_accts[$id][xtra]['Break Down'][items][0] = $item_array;
				}
			}
				
			if($billing_row->entry_type == 'Adjustment'){
				$item_array[item] = $billing_row->entry[entry];
				$item_array[grouping] = $billing_row->entry[grouping];
				if(!(($billing_row->entry[grouping] == 'Cash Discount') || ($billing_row->entry[grouping] == 'Waiver on Equipment'))){
					$item_array[value] = $billing_row->amount/1.18;
					if($parent_accts[$id][xtra]['Break Down'][items]){
						array_push($parent_accts[$id][xtra]['Break Down'][items], $item_array);
					} else {
						$parent_accts[$id][xtra]['Break Down'][items][0] = $item_array;
					}
					$parent_accts[$id][adjustments_sum] += $item_array[value];
				}else{
					$item_array[value] = $billing_row->amount;
					if($parent_accts[$id][xtra]['Break Down'][other_adjustments]){
						array_push($parent_accts[$id][xtra]['Break Down'][other_adjustments], $item_array);
					} else {
						$parent_accts[$id][xtra]['Break Down'][other_adjustments][0] = $item_array;
					}
					$parent_accts[$id][other_adjustments] += $item_array[value];
				}
			}	
		}
	}
	
	//generating and saving the invoices per parent account
	if(count($parent_accts) != 0){
		foreach($parent_accts as $parent_id => $parent_acct){
			$parent_acct[previous_balance] = previousBalance($parent_id,$period_start_date);
			$parent_acct[xtra]['Break Down'][sub_total] = ($parent_acct[Services]/1.18) + ($parent_acct[Charges]/1.18);
			$parent_acct[all_adjustments] = $parent_acct[other_adjustments] + ($parent_acct[adjustments_sum] * 1.18);
			$parent_acct[xtra]['Break Down'][total_vat] = $parent_acct[xtra]['Break Down'][sub_total] * 0.18;
			$parent_acct[xtra]['Break Down'][total_charges] = $parent_acct[xtra]['Break Down'][sub_total] + $parent_acct[xtra]['Break Down'][total_vat] + $parent_acct[xtra]['Break Down'][untaxed][total];
			$parent_acct[amount_payable] = 	$parent_acct[previous_balance] +
														$parent_acct[payments_sum] +
														$parent_acct[other_adjustments] + 
														$parent_acct[xtra]['Break Down'][total_charges];
			$parent_acct[xtra][Other_details][generated_by] = 'Bill Run';
			if($parent_acct[amount_payable] < 0 ){
				$parent_acct[xtra][Other_details][fined_payable] = -$parent_acct[amount_payable] + 0;
			}
		
			$invoicing->generation_date = date('Y-m-d');
			$invoicing->account_id = $parent_acct[xtra][Other_details][username];
			$invoicing->billing_date = $billrun_date;
			$invoicing->previous_balance = $parent_acct[previous_balance];
			$invoicing->payments_sum = $parent_acct[payments_sum];
			$invoicing->adjustments_sum = $parent_acct[all_adjustments];
			$invoicing->charges_sum = $parent_acct[xtra]['Break Down'][total_charges];
			$invoicing->amount_payable = $parent_acct[amount_payable];
			$invoicing->details = serialize($parent_acct[xtra]);
			$invoicing->invoice_number = generate_invoice_no('');
				
			if(	!(($invoicing->previous_balance == 0)&&
				($invoicing->charges_sum == 0)&&
				($invoicing->payments_sum == 0)&&
				($invoicing->adjustments_sum == 0)&&
				($invoicing->amount_payable == 0)
				)){
				
				$checks = array(
								array('username','=',$invoicing->account_id),
								array('billing_date','=',$invoicing->billing_date)
								);
				
				$check_object = $invoicing->GetList($checks);
				
				//echo ++$GG.' ->> '.test_invoice($invoicing).'<p style="page-break-before: always">';			
				if(count($check_object) == 0){
				$saved_id = $invoicing->SaveNew();
				//echo "Uncomment the save instruction <br>";
					if($saved_id){
						$invoices[saved][$id] = $parent_accts[$id];
						echo "Saving Invoice <br>";
					}else{
						$invoices[not_saved][$id] = $parent_accts[$id];
						echo "Invoice Not saved <br>";
					}
				}else{
					echo "Invoice Already exists Updating now ...<br>";
					$check_object = $check_object[0];
					$invoicing->invoice_number = $check_object->invoice_number;
					$invoicing->generation_date = $check_object->generation_date;
					$invoicing->id = $check_object->id;
					$saved_id = $invoicing->Save();
				}
			}
		}
	}else{
		echo "No accounts specified <br>";
	}
	
	return $invoices[not_saved];
}*/

/*
function bill($billrun_date, $account_ids){

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
	
	foreach($account_ids as $id){
		//Populate service charges and do appropriate billing on ALL (Post and Pre Paid) parent account
		$acct_data = $myquery->uniquequery("
					SELECT 
					  accounts.name,
					  accounts_cstm.billing_add_strt_c,
					  accounts_cstm.billing_add_area_c,
					  accounts_cstm.billing_add_town_c,
					  accounts_cstm.billing_add_plot_c,
					  accounts_cstm.billing_add_district_c,
					  accounts_cstm.preferred_username_c,
					  accounts_cstm.mem_id_c as parent_id,
					  accounts_cstm.crn_c,
					  accounts_cstm.service_type_internet_c as service_type,
					  cn_contracts.start_date,
					  cn_contracts.expiry_date,
					  ps_products.name as product_name,
					  ps_products.price as product_price,
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
					//AND accounts_cstm.preferred_username_c = 'moses.bashasha'
	
	//Populate service charges and do appropriate billing on each parent account.
	
		//creating an array of all including child POST PAID accounts regardless of services per account ie group by 
		//adding VAT
		$acct_data[product_price] = $acct_data[product_price] * 1.18;
		
		//BILLING ALL ACCOUNTS
		$billing->entry_id = generateRecieptNo('');
		$billing->parent_id = $acct_data[parent_id];
		$billing->account_id = $acct_data[preferred_username_c];
		$billing->bill_start = $acct_data[start_date];
		$billing->bill_end = $acct_data[expiry_date];
		$billing->billing_date = $billrun_date;
		$billing->entry_date = $billrun_date;
		$billing->currency = 'USD';
		$billing->entry_type = 'Services';
			$entry[grouping] = $acct_data[grouping];
			$entry[entry] = $acct_data[product_name];
			$entry[details] = '';
		$billing->entry = serialize($entry);
		$billing->amount = -$acct_data[product_price];
		$billing->balance = newBalance($billing->amount,$billing->parent_id, $billing->entry_date);
		$billing->user = 'Bill Run';
	
		$check_object = $billing->GetList(array(
											array('entry_date','=',$billrun_date),
											array('username','=',$billing->account_id),
											array('entry_type','=',$billing->entry_type)
												)
											); $check_object = $check_object[0];
		if(count($check_object) != 0){
			$billing->id = $check_object->id;
			//print_r($check_object);	echo "<br> ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ <br>";
			echo "!! Charge is Already there. Updating the entry <br>"; print_r($acct_data); echo "<br><br>";
			$id = Adjust_Balances_and_Save($billing);
		}else{
			if($billing->amount != ''){
				//echo ++$r." --->> "; print_r($billing); echo "<br>";
				echo "!! Saving regular Charge <br>";
				$id = Adjust_Balances_and_Save($billing);
			}else{
				echo "!! Charge is blank <br>";
			}
		}
	
	}
}
*/

?>