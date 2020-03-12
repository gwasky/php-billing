<?php
//error_reporting(E_PARSE | E_ERROR | E_WARNING);
//error_reporting(E_ALL);

require_once('control.php');
require_once('pdfs/FPDF17/fpdf.php');

ini_set('memory_limit','800M');

class PDF extends FPDF{

function BasicTable($header, $data, $width)
{
    // Header
	if(is_array($header) and count($header) > 0){
    	foreach($header as $col)
    	    $this->Cell(40,7,$col,1);
    	$this->Ln();
	}
    // Data
    foreach($data as $row)
    {
        foreach($row as $col)
            $this->Cell(40,6,$col,1);
        $this->Ln();
    }
}

function ResetFillColor(){
	$this->SetFillColor(255,255,255);
}

}

function pdf_invoice($ids,$output_method='I'){

	$invoicing = new wimax_invoicing();
	$pdf = new PDF();
	
	//OLD
	$pay_to_bank_account[USD] = "0240063587701";
	$pay_to_bank_account[UGX] = "0140063587701";
	
	//NEW
	$pay_to_bank_account[USD] = "9030008121493";
	$pay_to_bank_account[UGX] = "9030005711508";
	
	//AIRTEL NEW
	$pay_to_bank_account['Standard Chartered Bank Uganda Limited'][USD] = "87044-1077-5400";
	$pay_to_bank_account['Standard Chartered Bank Uganda Limited'][UGX] = "010-44-1077-5400";
	
	$pay_to_bank_account['Stanbic Bank Uganda Limited'][USD] = "9030008073332";
	$pay_to_bank_account['Stanbic Bank Uganda Limited'][UGX] = "9030006387941";
	
	if(count($ids) == 0){
		return "NO INVOICE IDs submitted<br>";
	}
	
	foreach($ids as $id_key=>$id){
		$invoice = $invoicing->Get($id);
	
		if($invoice->id == ''){
			continue;
		}
	
		$invoice->details = unserialize($invoice->details);
	
		$pdf->AliasNbPages();
		$pdf->AddPage();
		//$pdf->SetMargins(15,15,15);
		$pdf->SetMargins(10,10,10);
		
		//HEADER
		$pdf->Image('images/header.png',10,10,190);
		$pdf->SetY(17);	
		$pdf->SetFont('Calibri','',9);
		$pdf->Cell(90,3,'WARID TELECOM UGANDA LTD',0,1,L);
		$pdf->SetFont('Calibri','',9);
		$pdf->Cell(90,3,'VAT No : 4601-Z; TIN : 1000028977',0,1,L);
		
		//CLIENT DETAILS
		$pdf->SetY(30);
		$pdf->SetFont('Calibri','B',9);
		$pdf->Cell(90,4,strtoupper(remove_account_suffix($invoice->details[Other_details][account_name])),0,1,L);
		$pdf->SetFont('Calibri','',9);
		$address = explode("<br>",$invoice->details[Other_details][physical_address]);
		foreach($address as $row){ $pdf->Cell(90,4,strtoupper($row),0,1,L); }
		
		//BILL DETAILS
		$pdf->SetFont('Calibri','',8);
		$bill_detail_left_pad = 131;
		$pdf->SetXY($bill_detail_left_pad,30);
		$bill_details = array(
			array('Account Number',': '.$invoice->details[Other_details][account_number]),
			array('Invoice Number',': '.$invoice->invoice_number),
			array('Service Type',': Broadband - '.$invoice->details[Other_details][service_type]),
			array('Invoice Currency',': '.$invoice->details[Other_details][invoice_currency]),
			array('Invoice Date',': '.date_reformat($invoice->details[Other_details][invoice_date],'')),
			array('Invoice Period',': '.date_reformat($invoice->details["Other_details"]["invoice_start"],'').' to '.date_reformat($invoice->details["Other_details"]["invoice_end"],'')),
			array('Due Date',': '.date_reformat($invoice->details["Other_details"]["invoice_due_date"],''))
		);
		foreach($bill_details as $row){
			foreach($row as $colindex=>$col){
				if($colindex == 0) { $width = 30; }else{ $width = 58; }
				$pdf->Cell($width,3.5,$col,0,0,L);
			}
			$pdf->Ln();
			$pdf->SetX($bill_detail_left_pad);
			unset($colindex,$width);
		}
		
		//ACCOUNT SUMMARY
		$w = 190/5; $h = 4;
		$pdf->Ln(); //$pdf->Ln();
		$pdf->SetFont('GillSansMT','B',10);
		$pdf->SetFillColor(209,210,212);
		$pdf->Cell(190,$h+1,'Your Account Summary',0,1,C,true);
		
		$pdf->SetFont('GillSansMT','U',10);
		$pdf->Cell($w, $h,'Previous Balance',0,0,C,true);
		$pdf->Cell($w, $h,'Payments',0,0,C,true);
		$pdf->Cell($w, $h,'Adjustments',0,0,C,true);
		$pdf->Cell($w, $h,'Charges',0,0,C,true);
		$pdf->Cell($w, $h,'Amount Payable',0,1,C,true);
		$pdf->SetFont('Calibri','B',10);
		$pdf->Cell($w, $h,number_format(-$invoice->previous_balance,2),0,0,C,true);
		$pdf->Cell($w, $h,number_format($invoice->payments_sum,2),0,0,C,true);
		$pdf->Cell($w, $h,number_format($invoice->adjustments_sum,2),0,0,C,true);
		$pdf->Cell($w, $h,number_format(-$invoice->details['Break Down'][months_charges],2),0,0,C,true);
		$pdf->Cell($w, $h,number_format(-$invoice->amount_payable,2),0,1,C,true);
		
		//CHARGES SUMMARY
		$break_down = $invoice->details['Break Down'];
		$charge_summary_Y = 72;
		$pdf->SetY($charge_summary_Y);
		$pdf->SetLineWidth(0.05);
		//81
		$w = 65; $w1 = 45.5; $w2 = $w - $w1; $h = 4.5;
		//CHARGE SUMMARY BORDER
		$pdf->Cell($w,153,'',1,1,L);
		
		$charge_summary_Y = 73;
		$pdf->SetY($charge_summary_Y);
		$pdf->SetFont('Calibri','B',11);
		$pdf->SetFillColor(237,28,36);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetLineWidth(0.1);
		$pdf->Cell($w,7,'This Period\'s Charge Summary',TB,1,L,true);
		$pdf->SetY($charge_summary_Y+8);
		$pdf->SetFont('Calibri','',9);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell($w1,$h,'One time charges (Taxable)',0,0,L); $pdf->Cell($w2,$h,number_format(-$break_down[Charges],2),0,1,R);
		$pdf->Cell($w1,$h,'Monthly service charges',0,0,L); $pdf->Cell($w2,$h,number_format(-$break_down[Services],2),0,1,R);
		$pdf->Cell($w1,$h,'Prorating Adjustments',0,0,L); $pdf->Cell($w2,$h,number_format(-$break_down[prorate_adjustments_sum],2),0,1,R);
		$pdf->SetFillColor(209,210,212);
		$pdf->SetFont('Calibri','B',9);
		$pdf->Cell($w1,6,'Subtotal',0,0,L,true); $pdf->Cell($w2,6,number_format(-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]),2),0,1,R,true);
		$pdf->SetFont('Calibri','',9);
		$pdf->ResetFillColor();
		$pdf->Cell($w1,$h,'Tax (VAT 18%)',0,0,L); $pdf->Cell($w2,$h,number_format(-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 0.18,2),0,1,R);
		$pdf->Cell($w1,$h,'One time charges (Non Taxable)',0,0,L); $pdf->Cell($w2,$h,number_format(-$break_down[untaxed_total],2),0,1,R);
		$pdf->Cell($w1,$h,'Adjustments (Non Taxable)',0,0,L); $pdf->Cell($w2,$h,number_format($break_down[notax_adjustments],2),0,1,R);
		$pdf->SetFillColor(209,210,212);
		$pdf->SetFont('Calibri','B',9);
		$pdf->Cell($w1,$h+1,'Total Charges for Period',0,0,L,true); $pdf->Cell($w2,$h+1,number_format(-((($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 1.18) + $break_down[untaxed_total] + $break_down[notax_adjustments]),2),0,1,R,true);
		$pdf->SetFont('Calibri','',9);
		$pdf->ResetFillColor();
		$pdf->Image('images/advert.png',10,140,$w);
		
		$pdf->SetLineWidth(0.2);
			
		//CHARGES DETAILS START
		$charge_details_X = 78;
		$charge_details_Y = 72;
		$w = 122;
		$pdf->SetXY($charge_details_X,$charge_details_Y);
		$pdf->SetLineWidth(0.05);
		//CHARGES DETAILS BORDER
		$pdf->Cell($w,153,'',1,1,L);
		
		$charge_details_Y += 1;
		$pdf->SetXY($charge_details_X,$charge_details_Y);
		$pdf->SetFont('Calibri','B',11);
		$pdf->SetFillColor(237,28,36);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetLineWidth(0.1);
		//81
		$pdf->Cell($w,7,'This Period\'s Charge Details',TB,2,L,true);
		//$pdf->SetLineWidth(0.1);
		$pdf->SetTextColor(0,0,0);
		
		if($break_down){
			$h = 4;
			//overall_max_items z`29; //+3
			
			$max_items = 14; //15
			$max_prorate_adj = 4; //+1
			$max_untaxed_items = 3; //+2
			$max_other_adj = 3; //+2
					
			$item_cnt = count($break_down[items]);
			$prorate_adj_cnt = count($break_down[prorate_adjustments]);
			$untaxed_items_cnt = count($break_down[untaxed][items]);
			$other_adj_cnt = count($break_down[other_adjustments]);
			
			if($other_adj_cnt == 0){
				$max_items += $max_other_adj+2;
			}elseif($other_adj_cnt < $max_other_adj){
				$max_items += ($max_other_adj - $other_adj_cnt);
			}
			if($untaxed_items_cnt == 0){
				$max_items += $max_untaxed_items+2;
			}elseif($untaxed_items_cnt < $max_untaxed_items){
				$max_items += ($max_untaxed_items - $untaxed_items_cnt);
			}
			if($prorate_adj_cnt == 0){
				$max_items += $max_prorate_adj+1;
			}elseif($prorate_adj_cnt < $max_prorate_adj){
				$max_items += ($max_prorate_adj - $prorate_adj_cnt);
			}
			
			$col1of3 = $w*100/697;
			$col2of3 = $w*517/697;
			$col3of3 = $w*80/697;
			$max_item_len = 90;
			
			if($break_down[items]){
				$items = $break_down[items];
				$pdf->SetXY($charge_details_X,$charge_details_Y + 7 + 0.5);
				$pdf->SetFont('Calibri','B',8);
				$pdf->SetFillColor(209,210,212);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell($w,$h,'Taxable Onetime charges and Monthly services',0,2,L,true);
				$pdf->Cell($col1of3,$h,'Account',0,0,L,true);$pdf->Cell($col2of3,$h+1,'Transaction Details',0,0,L,true);$pdf->Cell($col3of3,$h+1,'Amount',0,2,C,true);
				
				$pdf->ResetFillColor();
				$pdf->SetFont('Calibri','',7);
				$item_details_Y = $charge_details_Y + 7 + 0.5 + (2 * ($h));
				$pdf->SetXY($charge_details_X,$item_details_Y);
				
				foreach($items as $row_cnt=>$item){			
					if(($row_cnt + 1) <= $max_items){
						$pdf->Cell($col1of3,$h,get_child_link($item[account_number]),0,0,L,true);
						$item_details = strlen($item[grouping]."-".show_item_details($item)) > $max_item_len?substr($item[grouping]."-".show_item_details($item),0,($max_item_len - 4))." ...": $item[grouping]."-".show_item_details($item);
						$pdf->SetFont('Calibri','',6);
						$pdf->Cell($col2of3,$h,$item_details,0,0,L,true);
						$pdf->SetFont('Calibri','',7);
						$pdf->Cell($col3of3,$h,number_format(-$item[value],2),0,2,R,true);
						
						$item_details_Y += $h;
						$pdf->SetXY($charge_details_X,$item_details_Y);
					}else{
						$hidden_items_sum += $item[value];
						
						if(($row_cnt + 1) == $item_cnt){
							$pdf->Cell($col1of3,$h,get_child_link($invoice->details[Other_details][account_number]),0,0,L,true);
							$pdf->Cell($col2of3,$h,"Other ".(count($items) - $max_items)." Items",0,0,L,true);
							$pdf->Cell($col3of3,$h,number_format(-$hidden_items_sum,2),0,2,R,true);
							
							$item_details_Y += $h;
							$pdf->SetXY($charge_details_X,$item_details_Y);
						}
					}
				}
			}
			 
			unset($row_cnt,$hidden_items_sum);
			 
			if($break_down[prorate_adjustments]){
				$items = $break_down[prorate_adjustments];
				$pdf->SetFont('Calibri','B',8);
				$pdf->SetFillColor(209,210,212);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell($w,$h,'Prorating Ajustments',0,2,L,true);
				//$pdf->Cell($col1of3,$h,'Account',0,0,L,true);$pdf->Cell($col2of3,$h+1,'Transaction Details',0,0,L,true);$pdf->Cell($col3of3,$h+1,'Amount',0,2,C,true);
				
				$pdf->ResetFillColor();
				$pdf->SetFont('Calibri','',7);
				//$item_details_Y = $charge_details_Y + 7 + 0.5 + (2 * ($h));
				//$pdf->SetXY($charge_details_X,$item_details_Y);
				
				foreach($items as $row_cnt=>$item){		
					if(($row_cnt + 1) <= $max_prorate_adj){
						$pdf->Cell($col1of3,$h,get_child_link($item[account_number]),0,0,L,true);
						$item_details = strlen($item[grouping]."-".show_item_details($item)) > $max_item_len?substr($item[grouping]."-".show_item_details($item),0,($max_item_len - 4))." ...": $item[grouping]."-".show_item_details($item);
						$pdf->SetFont('Calibri','',6);
						$pdf->Cell($col2of3,$h,$item_details,0,0,L,true);
						$pdf->SetFont('Calibri','',7);
						$pdf->Cell($col3of3,$h,number_format(-$item[value],2),0,2,R,true);
						
						$item_details_Y += $h;
						$pdf->SetXY($charge_details_X,$item_details_Y);
					}else{
						$hidden_items_sum += $item[value];
						
						if(($row_cnt + 1) == $item_cnt){
							$pdf->Cell($col1of3,$h,get_child_link($invoice->details[Other_details][account_number]),0,0,L,true);
							$pdf->Cell($col2of3,$h,"Other ".(count($items) - $max_prorate_adj)." Items",0,0,L,true);
							$pdf->Cell($col3of3,$h,number_format(-$hidden_items_sum,2),0,2,R,true);
							
							$item_details_Y += $h;
							$pdf->SetXY($charge_details_X,$item_details_Y);
						}
					}
				}
			}
			
			unset($row_cnt,$hidden_items_sum);
			
			//SUBTOTAL CHARGES
			$pdf->SetFont('Calibri','B',8);
			$pdf->SetFillColor(209,210,212);
			$pdf->SetTextColor(0,0,0);
			$pdf->Cell($w - 32,$h+1,'Sub total Charges',0,0,L,true);
			$pdf->Cell(32,$h+1,number_format(-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]),2),0,0,R,true);
			$item_details_Y += $h;
			
			//TAXES
			$charge_details_vat_X = $charge_details_X;
			$charge_details_vat_Y = $item_details_Y;
			$pdf->SetXY($charge_details_vat_X,$charge_details_vat_Y);
			$pdf->SetFont('Calibri','',7);
			$pdf->ResetFillColor();
			$pdf->SetTextColor(0,0,0);
			$pdf->Cell($w - 32,$h,'VAT 18%',0,0,L,true);
			$pdf->Cell(32,$h,number_format((-($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 0.18),2),0,0,R,true);
			$item_details_Y += $h;
			 
			if($break_down[untaxed][items]){
				$items = $break_down[untaxed][items];
				$pdf->SetXY($charge_details_X,$item_details_Y);
				$pdf->SetFont('Calibri','B',8);
				$pdf->SetFillColor(209,210,212);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell($w,$h,'Non taxable One time charges',0,2,L,true);
				$pdf->ResetFillColor();
				
				$pdf->SetFont('Calibri','',7);
				foreach($items as $row_cnt=>$item){
					if(($row_cnt + 1) <= $max_untaxed_items){
						$pdf->Cell($col1of3,$h,get_child_link($item[account_number]),0,0,L,true);
						$item_details = strlen($item[grouping]."-".show_item_details($item)) > $max_item_len?substr($item[grouping]."-".show_item_details($item),0,($max_item_len - 4))." ...": $item[grouping]."-".show_item_details($item);
						$pdf->SetFont('Calibri','',6);
						$pdf->Cell($col2of3,$h,$item_details,0,0,L,true);
						$pdf->SetFont('Calibri','',7);
						$pdf->Cell($col3of3,$h,number_format(-$item[value],2),0,2,R,true);
						
						$item_details_Y += $h;
						$pdf->SetXY($charge_details_X,$item_details_Y);
					}else{
						$hidden_items_sum += $item[value];
						
						if(($row_cnt + 1) == $item_cnt){
							$pdf->Cell($col1of3,$h,get_child_link($invoice->details[Other_details][account_number]),0,0,L,true);
							$pdf->Cell($col2of3,$h,"Other ".(count($items) - $max_untaxed_items)." Items",0,0,L,true);
							$pdf->Cell($col3of3,$h,number_format(-$hidden_items_sum,2),0,2,R,true);
							
							$item_details_Y += $h;
							$pdf->SetXY($charge_details_X,$item_details_Y);
						}
					}
				}
				
				$pdf->SetFont('Calibri','B',8);
				$pdf->SetFillColor(209,210,212);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell($w - 32,$h+1,'Sub Total UNTAXED Charges',0,0,L,true);
				$pdf->Cell(32,$h+1,number_format(-$break_down[untaxed_total],2),0,0,R,true);
				$pdf->ResetFillColor();
				
				$item_details_Y += $h;
				$pdf->SetXY($charge_details_X,$item_details_Y);
			}
			 
			if($break_down[other_adjustments]){
				$items = $break_down[other_adjustments];
				$charge_details_Y += $h;
				$pdf->SetXY($charge_details_X,$item_details_Y);
				$pdf->SetFont('Calibri','B',8);
				$pdf->SetFillColor(209,210,212);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell($w,$h,'Non taxable Adjustments',0,2,L,true);
				$pdf->ResetFillColor();
				
				$pdf->SetFont('Calibri','',7);
				foreach($items as $row_cnt=>$item){
					if(($row_cnt + 1) <= $max_other_adj){
						$pdf->Cell($col1of3,$h,get_child_link($item[account_number]),0,0,L,true);
						$item_details = strlen($item[grouping]."-".show_item_details($item)) > $max_item_len?substr($item[grouping]."-".show_item_details($item),0,($max_item_len - 4))." ...": $item[grouping]."-".show_item_details($item);
						$pdf->SetFont('Calibri','',6);
						$pdf->Cell($col2of3,$h,$item_details,0,0,L,true);
						$pdf->SetFont('Calibri','',7);
						$pdf->Cell($col3of3,$h,number_format(-$item[value],2),0,2,R,true);
						
						$item_details_Y += $h;
						$pdf->SetXY($charge_details_X,$item_details_Y);
					}else{
						$hidden_items_sum += $item[value];
						
						if(($row_cnt + 1) == $item_cnt){
							$pdf->Cell($col1of3,$h,get_child_link($invoice->details[Other_details][account_number]),0,0,L,true);
							$pdf->Cell($col2of3,$h,"Other ".(count($items) - $max_other_adj)." Items",0,0,L,true);
							$pdf->Cell($col3of3,$h,number_format(-$hidden_items_sum,2),0,2,R,true);
							
							$item_details_Y += $h;
							$pdf->SetXY($charge_details_X,$item_details_Y);
						}
					}
				}
				
				$pdf->SetFont('Calibri','B',8);
				$pdf->SetFillColor(209,210,212);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell($w - 32,$h+1,'Total non taxable Adjustments',0,0,L,true);
				$pdf->Cell(32,$h+1,number_format(-$break_down[other_adjustments_sum],2),0,0,R,true);
				$pdf->ResetFillColor();
				
				$item_details_Y += $h;
				$pdf->SetXY($charge_details_X,$item_details_Y);
			}
			
			//TOTAL CHARGES
			$charge_details_total_charges_X = $charge_details_X;
			$charge_details_total_charges_Y = 219;
			$pdf->SetXY($charge_details_total_charges_X,$charge_details_total_charges_Y);
			$pdf->SetFont('Calibri','B',9);
			$pdf->SetFillColor(209,210,212);
			$pdf->SetTextColor(0,0,0);
			$pdf->Cell($w - 32,$h+1,'Total Charges',0,0,L,true);
			$pdf->Cell(32,$h+1,number_format(-((($break_down[Charges] + $break_down[Services] + $break_down[prorate_adjustments_sum]) * 1.18) + $break_down[untaxed_total] + $break_down[notax_adjustments]),2),0,0,R,true);
		}
		//CHARGES DETAILS END
		
		//TEAR OFF HERE
		$pdf->SetLineWidth(0.1);
		$pdf->SetTextColor(0,0,0);
		$pdf->Image('images/tear_here.png',10,225,190);
		$pdf->SetY(227);
		$pdf->SetFont('Calibri','',8);
		$pdf->Write('5','Please detach this slip & return with payment. Make payments to ');
		$pdf->SetFont('Calibri','U',8);
		$pdf->SetTextColor(255,0,0);
		$pdf->Write('5','Airtel Uganda Limited');
		$pdf->SetFont('Calibri','',8);
		$pdf->SetTextColor(0,0,0);
		$pdf->Write('5',' Standard Chartered Bank Uganda Limited ');
		$pdf->SetFont('Calibri','U',8);
		$pdf->SetTextColor(255,0,0); 
		$pdf->Write('5','Account No '.$pay_to_bank_account['Standard Chartered Bank Uganda Limited'][$invoice->details[Other_details][invoice_currency]]);
		
		$pdf->SetFont('Calibri','',8);
		$pdf->SetTextColor(0,0,0);
		$pdf->Write('5',' OR  Stanbic Bank Uganda Limited ');
		$pdf->SetFont('Calibri','U',8);
		$pdf->SetTextColor(255,0,0); 
		$pdf->Write('5','Account No '.$pay_to_bank_account['Stanbic Bank Uganda Limited'][$invoice->details[Other_details][invoice_currency]]);
			
		//BILL SUMMARY
		$bill_summary_Y = 238;
		$bill_summary_left_pad = 30;
		$pdf->SetXY($bill_summary_left_pad,$bill_summary_Y);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('Calibri','',8);
		$bill_summary = array(
			array('Account Number :',$invoice->details[Other_details][account_number]),
			array('Bill Number :',$invoice->invoice_number),
			array('Account Balance :',$invoice->details[Other_details][invoice_currency].' '.number_format(-$invoice->details[Other_details][acct_bal],2))
		);
		foreach($bill_summary as $row){
			foreach($row as $colkey=>$col){
				if($colkey == 0) { $align = 'R'; $pdf->SetFont('Calibri','B',8); }else{ $align = 'L'; $pdf->SetFont('Calibri','',8); }
				$pdf->Cell(35,3.5,$col,0,0,$align);
			}
			$pdf->Ln();
			$pdf->SetX($bill_summary_left_pad);
			unset($colkey,$width);
		}
		$pdf->SetXY(110,$bill_summary_Y);
		$bill_summary_left_pad = 110;
		$bill_summary = array(
			array('Bill Date :',date_reformat($invoice->details[Other_details][invoice_date],'')),
			array('Amount Payable :',$invoice->details[Other_details][invoice_currency].' '.number_format(-$invoice->amount_payable,2)),
			array('Due Date :', date_reformat($invoice->details[Other_details][invoice_due_date],''))
		);
		foreach($bill_summary as $row){
			foreach($row as $colkey=>$col){
				if($colkey == 0) { $align = 'R'; $pdf->SetFont('Calibri','B',8); }else{ $align = 'L'; $pdf->SetFont('Calibri','',8);}
				$pdf->Cell(35,3.5,$col,0,0,$align);
			}
			$pdf->Ln();
			$pdf->SetX($bill_summary_left_pad);
			unset($colkey,$width);
		}
		
		//PAYMENT TABLE
		$payment_table_Y = 250;
		$payment_table_X = 24;
		$w = 31; $h = 3;
		$pdf->SetXY($payment_table_X,$payment_table_Y);
		$pdf->SetFont('Calibri','B',7);
		$pdf->Cell($w+5,$h,'Payment Mode',1,0,C);
		$pdf->Cell($w,$h,'Amount',1,0,C);
		$pdf->Cell($w,$h,'Date',1,0,C);
		$pdf->Cell($w,$h,'Cheque Number',1,0,C);
		$pdf->Cell($w,$h,'Bank/Branch',1,1,C);
		$pdf->SetX($payment_table_X);
		$pdf->Cell($w+5,$h,'Cheque / DD / Pay Order',1,0,C);
		$pdf->Cell($w,$h,'',1,0,C);
		$pdf->Cell($w,$h,'',1,0,C);
		$pdf->Cell($w,$h,'',1,0,C);
		$pdf->Cell($w,$h,'',1,1,C);
		$pdf->SetX($payment_table_X);
		$pdf->Cell($w+5,$h,'Cash',1,0,C);
		$pdf->Cell($w,$h,'',1,0,C);
		$pdf->Cell($w,$h,'',1,1,C);
		
		//FOOTER BANNER
		//$pdf->Image('images/footer.png',10,253,190);
		
		//FOOTER TEXT
		$footer_txt_Y = 268;
		$footer_txt_X = 10;
		$pdf->SetXY($footer_txt_X,$footer_txt_Y);
		$pdf->SetFont('ArialNarrow','',8);
		$footer_text = 'Warid Telecom Uganda Limited : Customer care centres - Plaza Kampala Rd, Forest Mall Lugogo Jinja Rd and Head office Plot 16A Clement Hill Road';
		$pdf->Cell(0,4,$footer_text,0,1,C);
		$footer_text = 'P.O.B0x 70665 Kampala : customercare@waridtel.co.ug : 070 077 7000 : www.waridtel.co.ug';
		$pdf->Cell(0,4,$footer_text,0,1,C);
		
		//ACTUAL PAGE FOOTER
		/*$pdf->SetY(-10);
		$pdf->SetFont('Calibri','B',7);
		$pdf->Cell(0,4,"Page ".$pdf->PageNo()." of 1",0,0,'R');*/
	}
	
	$pdf->SetAuthor('Warid Telecom Enterprise Billing');
	$pdf->SetCreator('Warid Telecom Enterprise CRM');
	
	if($output_method != 'S'){
		if(count($ids) == 1){
			$file_name = $invoice->details["Other_details"]["invoice_end"]." - ".$invoice->details["Other_details"]["invoice_start"]."_".$invoice->details[Other_details][account_number]." ".strtoupper(remove_account_suffix($invoice->details[Other_details][account_name]));
		}else{
			$file_name = 'Invoice List';
		}
	}else{
		$file_name = '';
		return $pdf->Output($file_name,$output_method);
	}
	
	//$pdf->Output($file_name.'.pdf',$output_method);
	$pdf->Output($file_name,$output_method);
}

?>