<?
//MX Widgets3 include
require_once('../includes/wdg/WDG.php');

	require('control.php');

	function redo_invoices($billrun_date, $account_id){
		
		$alert = date("Y-m-d H:i:s")."<br>log -> Invoice redo request for bill run date ".$billrun_date;
		if(trim($account_id) != ''){
			$alert.= "on account(s) ".$account_id; 
		}else{
			$alert.= "on ALL billed accounts"; 
		}
		
		$alert .= " <br>";
		echo $alert; $mail_log .= $alert;
		
		if($billrun_date == ''){
			header("Location: invoice.php");
			exit('Bill run date is not set!!! '); 
		}
		
		$billing = new wimax_billing();
		$invoicing = new wimax_invoicing();
		$myquery = new uniquequerys();
	
		//Getting Bill run and start dates
		$result = $myquery->uniquequery("SELECT LAST_DAY('$billrun_date') as thedate");
		$billrun_date = $myquery->Unescape($result[thedate]);
		$result = $myquery->uniquequery("SELECT concat(date_format(LAST_DAY('$billrun_date'),'%Y-%m-'),'01') as period_start");
		$period_start_date = $result[period_start];
		
		if($account_id == NULL){
			$alert = "log -> Re Bill running <br>";
			echo $alert; $mail_log .= $alert;
			/*$accountid_query = "
				(SELECT DISTINCT 
					accounts_cstm.mem_id_c AS parent_id
				FROM
					accounts_cstm
					INNER JOIN wimax_invoicing ON (accounts_cstm.mem_id_c=wimax_invoicing.parent_id) 
				where
					accounts_cstm.invoicing_type_c = 'normal' AND
					wimax_invoicing.billing_date = '$billrun_date' AND
					wimax_invoicing.details NOT LIKE '%Prepaid%' AND
					wimax_invoicing.details LIKE '%generated_by\";s:8:\"Bill Run\"%'
				)
				union
				(SELECT DISTINCT 
					wimax_billing.parent_id
				FROM
					wimax_billing
					INNER JOIN accounts_cstm ON (wimax_billing.parent_id=accounts_cstm.mem_id_c)
				where 
					accounts_cstm.invoicing_type_c = 'normal' AND
					(wimax_billing.entry_date BETWEEN '$period_start_date' AND '$billrun_date') AND
					accounts_cstm.service_type_internet_c LIKE '%Postpaid%'
				)
				order by parent_id
			";*/
			$accountid_query = "
				SELECT DISTINCT 
					TRIM(wimax_billing.parent_id) AS parent_id
				FROM
					wimax_billing
					INNER JOIN accounts_cstm ON (TRIM(wimax_billing.parent_id)=TRIM(accounts_cstm.mem_id_c))
				WHERE 
					accounts_cstm.invoicing_type_c = 'normal' AND
					(wimax_billing.entry_date BETWEEN '$period_start_date' AND '$billrun_date') AND
					accounts_cstm.service_type_internet_c LIKE '%Postpaid%'
				GROUP BY
					parent_id
			";
			//echo nl2br($accountid_query); exit();
			$ids = $myquery->multiplerow_query($accountid_query);
			
			$i = 0;
			foreach($ids as $id_row){
				$account_ids[$i++] = $id_row[parent_id];
			}
		}else{
			$account_ids = explode(",",$account_id);
		}
		
		//Populating parent contact Information	
		foreach($account_ids as $id){
			$id = trim($id);
			$alert = "log -> Getting contact info for account number [".$id."]<br>";
			echo $alert; $mail_log .= $alert;
			$contact_query = "
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
					accounts_cstm.crn_c = '$id' AND
					cn_contracts.deleted = '0' AND
					accounts_cstm.invoicing_type_c = 'normal'
			";
			//accounts_cstm.service_type_internet_c = 'Postpaid'
			//echo $contact_query."<br>";
			
			$parent_data = $myquery->uniquequery($contact_query);
			
			if(strlen($parent_data[crn_c]) != 0){
				$parent_accts[$id][xtra][Other_details][username] = $parent_data[preferred_username_c];
				$parent_accts[$id][xtra][Other_details][account_name] = $parent_data[name];
				$parent_accts[$id][xtra][Other_details][individual] = $parent_data[contact_person_c];
				$parent_accts[$id][xtra][Other_details][physical_address] =	$parent_data[billing_add_plot_c]."<br>".
																		$parent_data[billing_add_strt_c]."<br>".
																		$parent_data[billing_add_district_c]."<br>";
				$parent_accts[$id][xtra][Other_details][account_number] = $parent_data[crn_c];
				$parent_accts[$id][xtra][Other_details][invoice_date] = $billrun_date;
				$parent_accts[$id][xtra][Other_details][invoice_currency] = $parent_data[selected_billing_currency];
				$parent_accts[$id][xtra][Other_details][invoice_start] = $period_start_date;
				$parent_accts[$id][xtra][Other_details][invoice_end] = $billrun_date;
				$parent_accts[$id][xtra][Other_details][invoice_due_date] = AddDays($billrun_date,14);
				$parent_accts[$id][xtra][Other_details][Title] = 'TAX INVOICE';
				$parent_accts[$id][xtra][Other_details][contract_start] = $parent_data[start_date];
				$parent_accts[$id][xtra][Other_details][service_type] = $parent_data[service_type];
				$parent_accts[$id][xtra][Other_details][customer_type] = $parent_data[customer_type];
				$parent_accts[$id][xtra][Other_details][invoicing_type] = $parent_data[invoicing_type];
				
				//Summing up payments, charges and adjustments per parent account
				$select_conditions = array(
										array("entry_date","<=",$billrun_date),
										array("entry_date",">=",$period_start_date),
										array("parent_id","=",$id)
										);
				
				$billing_data = $billing->GetList($select_conditions);
				foreach($billing_data as $billing_row){
					$billing_row->entry = unserialize($billing_row->entry);
					
					if($billing_row->entry[parent_account_billing_currency] == ''){
						$billing_row->entry[parent_account_billing_currency] = 'USD';
					}
					
					$billing_row->amount = convert_value($billing_row->amount,$billing_row->entry[parent_account_billing_currency], $billing_row->rate_date, $parent_accts[$id][xtra][Other_details][invoice_currency]);
					if($billing_row->entry_type == 'Payment'){
						$parent_accts[$id][payments_sum] += $billing_row->amount;
					}
		
					$item_array[account_number] = $billing_row->account_id;
					$item_array[currency] = $billing_row->currency;
					$item_array[rate_date] = $billing_row->rate_date;

					if($billing_row->entry_type == 'Charges'){
						$item_array[item] = $billing_row->entry[entry];
						$item_array[details] = $billing_row->entry[details];
						$item_array[grouping] = $billing_row->entry[grouping];
						$item_array[type] = $billing_row->entry[type];
						$item_array[discount] = $billing_row->entry[discount];
						$item_array[quantity] = $billing_row->entry[quantity];
						if($billing_row->entry[period] == ''){$billing_row->entry[period] = 'One time';}
						$item_array[period] = $billing_row->entry[period];
						
						if($billing_row->entry[entry] != 'Equipment Deposit'){
							$parent_accts[$id][Charges] += $billing_row->amount;
							$item_array[unit_price] = $billing_row->entry[unit_price]/1.18;
							$item_array[value] = ($billing_row->amount/1.18);
							if($parent_accts[$id][xtra]['Break Down'][items]){
								array_push($parent_accts[$id][xtra]['Break Down'][items], $item_array);
							} else {
								$parent_accts[$id][xtra]['Break Down'][items][0] = $item_array;
							}
						}else{
							$parent_accts[$id][xtra]['Break Down'][untaxed_total] += $billing_row->amount;
							$item_array[unit_price] = $billing_row->entry[unit_price];
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
						$item_array[details] = $billing_row->entry[details];
						$item_array[grouping] = $billing_row->entry[grouping];
						$item_array[type] = $billing_row->entry[type];
						$item_array[discount] = $billing_row->entry[discount];
						$item_array[quantity] = $billing_row->entry[quantity];
						if($billing_row->entry[period] == ''){$billing_row->entry[period] = 1;}
						$item_array[period] = $billing_row->entry[period];
						
						$item_array[unit_price] = $billing_row->entry[unit_price]/1.18;
						$item_array[value] = $billing_row->amount/1.18;
						if($parent_accts[$id][xtra]['Break Down'][items]){
							array_push($parent_accts[$id][xtra]['Break Down'][items], $item_array);
						} else {
							$parent_accts[$id][xtra]['Break Down'][items][0] = $item_array;
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
								$parent_accts[$id][prorate_adjustments_sum] += $item_array[value];

								if($parent_accts[$id][xtra]['Break Down'][prorate_adjustments]){
									array_push($parent_accts[$id][xtra]['Break Down'][prorate_adjustments], $item_array);
								} else {
									$parent_accts[$id][xtra]['Break Down'][prorate_adjustments][0] = $item_array;
								}
							}else{//None prorate adjustments
								$parent_accts[$id][adjustments_sum] += $item_array[value];
								
								if($parent_accts[$id][xtra]['Break Down'][adjustments]){
									array_push($parent_accts[$id][xtra]['Break Down'][adjustments], $item_array);
								} else {
									$parent_accts[$id][xtra]['Break Down'][adjustments][0] = $item_array;
								}
							}
						}else{
							$item_array[value] = $billing_row->amount;
							if($parent_accts[$id][xtra]['Break Down'][other_adjustments]){
								array_push($parent_accts[$id][xtra]['Break Down'][other_adjustments], $item_array);
							} else {
								$parent_accts[$id][xtra]['Break Down'][other_adjustments][0] = $item_array;
							}
							$parent_accts[$id][other_adjustments_sum] += $item_array[value];
						}
					}	
				}
			}else{
				$alert = "WARNING!! -> No Billing entries for for account number -> [".$id."]<br>";
				echo $alert; $mail_log .= $alert;
			}
		}
		
		//print_r($parent_accts['200902-279'][xtra]['Break Down']); echo "<br>";

		//generating and saving the invoices per parent account
		if(count($parent_accts) != 0){
			foreach($parent_accts as $parent_id => $parent_acct){
				$parent_acct[previous_balance] = previousBalance($parent_id,$period_start_date,$parent_acct[xtra][Other_details][invoice_currency]);
				$parent_acct[xtra]['Break Down'][Services] = $parent_acct[Services]/1.18;
				$parent_acct[xtra]['Break Down'][Charges] = $parent_acct[Charges]/1.18;
				$parent_acct[xtra]['Break Down'][prorate_adjustments_sum] = $parent_acct[prorate_adjustments_sum];
				$parent_acct[xtra]['Break Down'][tax_adjustments] = $parent_acct[adjustments_sum];
				$parent_acct[xtra]['Break Down'][notax_adjustments] = $parent_acct[other_adjustments_sum];
				$parent_acct[xtra]['Break Down'][sub_total] = $parent_acct[xtra]['Break Down'][Services] + 
															  $parent_acct[xtra]['Break Down'][Charges] +
															  $parent_acct[xtra]['Break Down'][prorate_adjustments_sum] +
															  $parent_acct[xtra]['Break Down'][tax_adjustments];
				$parent_acct[xtra]['Break Down'][total_vat] = $parent_acct[xtra]['Break Down'][sub_total] * 0.18;
				
				$parent_acct[xtra]['Break Down'][total_charges] = $parent_acct[xtra]['Break Down'][sub_total] +
																  $parent_acct[xtra]['Break Down'][total_vat] + 
																  $parent_acct[xtra]['Break Down'][untaxed_total] +
																  $parent_acct[xtra]['Break Down'][notax_adjustments];
				//monthly charges
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

				/*foreach($parent_acct[xtra]['Break Down'] as $key=>$label){
					echo $key." ->> "; print_r($label); echo "<br>";
				}*/

				$invoicing->generation_date = date('Y-m-d');
				$invoicing->parent_id = $parent_acct[xtra][Other_details][account_number];
				$invoicing->billing_date = $billrun_date;
				$invoicing->previous_balance = $parent_acct[previous_balance];
				$invoicing->payments_sum = $parent_acct[payments_sum];
				$invoicing->adjustments_sum = ($parent_acct[xtra]['Break Down'][tax_adjustments]*1.18) +
											   $parent_acct[xtra]['Break Down'][notax_adjustments];
				$invoicing->charges_sum = $parent_acct[xtra]['Break Down'][months_charges];						  
				$invoicing->amount_payable = $parent_acct[amount_payable];
				$invoicing->details = serialize($parent_acct[xtra]);
				$invoicing->invoice_number = generate_invoice_no('');
				$invoicing->deleted = 0;

				if(!(
					 	($invoicing->charges_sum == 0)&&
						($invoicing->adjustments_sum == 0)
					) and
				   ($invoicing->parent_id != '')
				   ){

					$checks = array(
									array('parent_id','=',$invoicing->parent_id),
									array('billing_date','=',$invoicing->billing_date),
									array('deleted','=','0')
									);

					$check_objects = $invoicing->GetList($checks);

					//echo ++$GG.' ->> '.test_invoice($invoicing).'<p style="page-break-before: always">';			
					if(count($check_objects) == 0){
					$saved_id = $invoicing->SaveNew();
					//echo "Uncomment the save instruction <br>";
						if($saved_id){
							$invoices[saved][$id] = $parent_accts[$id];
							$alert .= "log -> Saving Invoice number ".$invoicing->invoice_number." for [".$invoicing->parent_id."] - ".$parent_acct[xtra][Other_details][account_name]."<br>";
							echo $alert; $mail_log .= $alert;
						}else{
							$invoices[not_saved][$id] = $parent_accts[$id];
							$alert .= "ERROR! -> NOT SAVED Invoice number ".$invoicing->invoice_number." for [".$invoicing->parent_id."] - ".$parent_acct[xtra][Other_details][account_name]."<br>";
							echo $alert; $mail_log .= $alert;
						}
					}else{
						$i = 0;
						$check_object = $check_objects[$i];
						$invoicing->invoice_number = $check_object->invoice_number;
						$invoicing->id = $check_object->id;
						
						$alert .= "WARNING! -> Invoice Already exists. Updating Invoice no ".$check_object->invoice_number." of Account no ".$check_object->parent_id."<br>OLD-> Bal ".number_format($check_object->previous_balance,2)." Adjustments ".number_format($check_object->adjustments_sum,2)." Charges ".number_format($check_object->charges_sum,2)." Amount Payable ".number_format($check_object->amount_payable,2)."<br>NEW-> Bal ".number_format($invoicing->previous_balance,2)." Adjustments ".number_format($invoicing->adjustments_sum,2)." Charges ".number_format($invoicing->charges_sum,2)." Amount Payable ".number_format($invoicing->amount_payable,2)."<br>";
						
						echo $alert; $mail_log .= $alert;
						
						$saved_id = $invoicing->Save();
						while(count($check_objects[++$i]) != 0){
							$invoice = $check_objects[$i];
							$invoice->Delete();
						}
					}
				}else{
					$mail_log . "Zero invoice or no parent ID defined ...<BR>".print_r($invoicing,TRUE)."<BR>";
				}
			}
			$alert = date("Y-m-d H:i:s");
			echo $alert; $mail_log .= $alert;
		}else{
			$alert .= "No accounts specified <br>".date("Y-m-d H:i:s");
			echo $alert; $mail_log .= $alert;
		}
		
		sendHTMLemail($to='ccbusinessanalysis@waridtel.co.ug',$bcc='',$message=$mail_log,$subject='Invoice Redo log on '.$billrun_date,$from='Infinity Wimax <ccnotify@waridtel.co.ug>');
		
		return $invoices[not_saved];
	}
	
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
			$output = '<body style="font-size:90%; font-family: calibri,Arial; margin-left:auto; margin-right:auto;"><fieldset>A request to delete the following invoices with the following numbers ['.$cvs_invoice_nos.'] was submitted ';
			
			if(trim($delete_request_txt) != '') {$output .= "with the following notes <br>".$delete_request_txt."<br><br>";}
			
			if(trim($_REQUEST[requester]) != ''){ $output .= 'By '.ucwords(trim($_REQUEST[requester]));}
			
			$output .= '<br><br>';
			
			if($log[undeleted_info] != ''){
				$output .= "THESE INVOICES COULD NOT BE DELETED <BR>".$log[undeleted_info];
				//$output .= "<BR>".display_selected_invoices($log[undeleted_invoices],TRUE)."<BR>";
				$output .= "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++<BR>";
			}
			
			if($log[deleted_info] != ''){
				$output .= "These Invoices were deleted <BR>".$log[deleted_info];
				//$output .= "<BR>".display_selected_invoices($log[deleted_invoices],TRUE)."<BR>";
			}
			
			$output .= "</fieldset></body>";
			
			echo $output;
			
			//sendHTMLemail($to = 'CREDITCOLLECTION@waridtel.co.ug,ccbusinessanalysis@waridtel.co.ug',$bcc='ccbusinessanalysis@waridtel.co.ug',$message=$output,$subject=' Wimax Invoice Delete Request',$from="Data Reporting <ccnotify@waridtel.co.ug>");
			sendHTMLemail($to = 'ccbusinessanalysis@waridtel.co.ug',$bcc='',$message=$output,$subject=' Wimax Invoice Delete Request',$from="Data Reporting <ccnotify@waridtel.co.ug>");
		}
	}
	
	switch($_POST[button]){
		case 'balances':
			echo "running the repair balances ...<br>";
			repairbals();
			break;
		case 'bill':
			if($_POST[bill_date]){
				monthly_bill($_POST[bill_date], $_POST[account_ids]);
			}
			break;
		case 're_invoice':
			if($_POST[reinvoice_date]){
				echo "Redoing invoices ...<br>";
				$invoices = redo_invoices($_POST[reinvoice_date],$_POST[account_id]);
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
<title>Invoice Generation</title>
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
    <td>
    <fieldset>
    <form id="inv" name="inv" method="post" action="invoice.php">
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
  <tr>
      <td>
   <fieldset>
  	<form id="inv" name="inv" method="post" action="invoice.php">
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
  <td>
   <fieldset>
  	<form id="bill" name="bill" method="post" action="invoice.php">
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
  	<form id="del_inv" name="del_inv" method="post" action="invoice.php">
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

 </table>
</body>
</html>