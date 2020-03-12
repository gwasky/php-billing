//

bill run invoice
{

		<!--
		body {
			margin-top: 140px;
		}
		-->
		</style>
						<table width="800" border="0" cellspacing="2" cellpadding="2" align="center">
						 <tr>
			<td align="right"><br><br><br><br><br><br><br><br><br><br><br><br>
			</td>
		  </tr>
		  <tr>
			<td align="right"><span style="font-size:20px; text-decoration:underline; font-weight:bold">'.$invoice->details['Other details']['title'].'</span></td>
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
					<td>'.$invoice->details["Other details"]["individual"].'<br />
				    '.$invoice->details["Other details"]["physical_address"].'</td>
				  </tr>
				  
		
				</table></td>
				<td width="10%">&nbsp;</td>
				<td width="45%"><table width="100%" border="0" cellspacing="2" cellpadding="2">
				  <tr>
				    <td width="38%"><strong>Account Number</strong></td>
					<td width="62%">'.$invoice->details["Other details"]['account_number'].'</td>
				  </tr>
				  <tr>
				    <td><strong>Invoice Number</strong></td>
					<td>'.$invoice->id.'</td>
				  </tr>
				  <tr>
				    <td><strong>Invoice Currency</strong></td>
				    <td>'.$invoice->details["Other details"]["invoice_currency"].'</td>
			      </tr>
				  <tr>
				    <td><strong>Invoice Date</strong></td>
				    <td>'.date_reformat($invoice->details["Other details"]["invoice_date"]).'</td>
			      </tr>
				  <tr>
				    <td><strong>Invoice Period</strong></td>
				    <td>'.date_reformat($invoice->details["Other details"]["invoice_start"]).' to '.date_reformat($invoice->details["Other details"]["invoice_end"]).'</td>
			      </tr>
				  <tr>
				    <td><strong>Due Date</strong></td>
					<td>'.date_reformat($invoice->details['xtra']["Other details"]["invoice_due_date"]).'</td>
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
              <tr>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Previous Balance</span></span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Payments</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Adjustments</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Charges</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Amount Payable</span></td>
                <td align="center"><span style="font-size:16px; text-decoration:underline; font-weight:bold">Amount Payable After</span></td>
              </tr>
              <tr>
                <td align="center">'.accounts_format(-$invoice->previous_balance).'</td>
                <td align="center">'.accounts_format($invoice->payments_sum).'</td>
                <td align="center">'.accounts_format($invoice->adjustments_sum).'</td>
                <td align="center">'.accounts_format(-$invoice->details['Break Down']['total_charges']).'</td>
                <td align="center">'.date_reformat($invoice->details["Other details"]["invoice_due_date"]).'<br />
'.accounts_format(-$invoice->amount_payable).'</td>
                <td align="center">'.date_reformat($invoice->details["Other details"]["invoice_due_date"]).'<br />
'.accounts_format($invoice->details["Other details"]["fined_payable"]).'</td>
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
			<td>&nbsp;</td>
  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>
		
		  <tr>
			<td><table width="100%" cellspacing="0" cellpadding="0">
			<tr>------------------------------------------------------------------------------------------------------------------------------------</tr>
              <tr>
                <td align="right" width="24%"><strong>Account Number</strong></td>
                <td width="24%">'.$invoice->details["Other details"]["account_number"].'</td>
                <td align="center" width="24%">&nbsp;</td>
                <td  align="right" width="24%"><strong>Bill Date</strong></td>
                <td align="left" width="24%">'.date_reformat($invoice->details["Other details"]["invoice_date"]).'</td>
              </tr>
              <tr>
                <td align="right" width="24%"><strong>Bill Number</strong></td>
                <td width="24%">'.$invoice->id.'</td>
                <td width="24%">&nbsp;</td>
                <td align="right" width="24%"><strong>Amount Payable</strong></td>
                <td align="left" width="24%">'.accounts_format(-$invoice->amount_payable).'</td>
              </tr>
              <tr >
                <td align="right" width="24%" >&nbsp;</td>
                <td width="24%">&nbsp;</td>
                <td width="24%">&nbsp;</td>
                <td align="right" width="24%"><strong>Due Date</strong></td>
                <td align="left" width="24%">'.date_reformat($invoice->details["Other details"]["invoice_due_date"]).'</td>
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
			<td></td>
		  </tr>
		</table> }

//

prevBal
{
	$username_billing_rows = $billing->GetList(array(array('username','=',$username)),'id',false,'');
	if($username_billing_rows){
		foreach($username_billing_rows as $username_billing_row){
			if($rows_by_bill_end[$username_billing_row->bill_end]){
				array_push($rows_by_bill_end[$username_billing_row->bill_end],$username_billing_row);
			} else {
				$rows_by_bill_end[$username_billing_row->bill_end][0] = $username_billing_row;
			}
		}
		$j = 0;
		foreach($rows_by_bill_end as $row){ // changing the references to digits
			$rows_by_order[$j++] = $row;
		}
		
		if($rows_by_order[1][0]){ //ie more than one peroid exists
			$previous_bal_row = $rows_by_order[1][0];
		} else {// ie only one or no bill period exists
			$result = $myquery->uniquequery("select max(bill_end) as end_date from wimax_billing where username = '$username'");
			$end_date = $myquery->Unescape($result[end_date]);
			//echo $end_date."<br>"; echo $rows_by_order[0][0]->bill_end."<br>";
			//print_r($rows_by_order);
			if($end_date == $rows_by_order[0][0]->bill_end){//same bill period (ie client's first period) ie 0 previous balance
				$previous_bal_row->balance = 0;
			}else{//New client period without new period's charge being entered
				echo "New period entered without new period's charge being entered. Please charge the account<br>";
				$previous_bal_row->balance = $rows_by_order[0][0]->balance;
			}
		}
	} 
}