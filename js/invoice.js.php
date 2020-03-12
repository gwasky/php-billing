<?php
//require_once('../control.php');

function generate_invoice_javascript(){
?>

<SCRIPT language="javascript">
//JavaScript Document
function addRow(tableID) {

	var table = document.getElementById(tableID);

	var rowCount = table.rows.length;
	var row = table.insertRow(rowCount);

	var cell_id = row.insertCell(0);
	var element_id = document.createElement("input");
	element_id.type = "hidden";
	element_id.value = Math.round(new Date().getTime() / 100);
	cell_id.appendChild(element_id);
	
	var cell_select = row.insertCell(1);
	var element_select = document.createElement("input");
	element_select.type = "checkbox";
	element_select.name = "invoice[entries]["+ element_id.value +"][select]";
	cell_select.appendChild(element_select);

	var cell_account = row.insertCell(2);
	if(document.getElementById('invoice[type]').value == 'TAX'){
		var element_account = document.createElement("SELECT");
		element_account.name="invoice[entries]["+ element_id.value +"][account]";
		<?php echo display_accounts_dropdown_javascript(''); ?>
	}else{
		var element_account = document.createElement("input");
		element_account.type = "text";
		element_account.size = "20";
		element_account.value = document.getElementById('invoice[account_name_input]').value;
	}
	element_account.name="invoice[entries]["+ element_id.value +"][account]";
	cell_account.appendChild(element_account);

	var cell_product = row.insertCell(3);
	var element_product_price = document.createElement("input");
	element_product_price.type = "hidden";
	element_product_price.value = "";
	var element_product = document.createElement("SELECT");
	element_product.name = "invoice[entries]["+ element_id.value +"][product]";
	<?php echo display_products_dropdown_javascript(''); ?>
	cell_product.appendChild(element_product);
	
	var cell_from = row.insertCell(4);
	var element_from = document.createElement("input");
	element_from.type = "text";
	element_from.size = "10";
	element_from.value = define_default(document.getElementById('invoice[start_date]').value,"<Start date>");
	element_from.setAttribute("Onclick","javascript:check_onclick_period(this,'<Start date>');");
	element_from.setAttribute("Onblur","javascript:check_onblur_period(this,'<Start date>');");
	element_from.name="invoice[entries]["+ element_id.value +"][from]";
	cell_from.appendChild(element_from);
	
	var cell_to = row.insertCell(5);
	var element_to = document.createElement("input");
	element_to.type = "text";
	element_to.size = "10";
	element_to.value = define_default(document.getElementById('invoice[end_date]').value,"<End date>");
	element_to.setAttribute("Onclick","javascript:check_onclick_period(this,'<End date>');");
	element_to.setAttribute("Onblur","javascript:check_onblur_period(this,'<End date>');");
	element_to.name="invoice[entries]["+ element_id.value +"][to]";
	cell_to.appendChild(element_to);
	
	var cell_quantity = row.insertCell(6);
	var element_quantity = document.createElement("input");
	element_quantity.type = "text";
	element_quantity.size = "5";
	element_quantity.value = "1.0000";
	element_quantity.name = "invoice[entries]["+ element_id.value +"][quantity]";
	cell_quantity.appendChild(element_quantity);
	
	var cell_discount = row.insertCell(7);
	var element_discount = document.createElement("input");
	element_discount.type = "text";
	element_discount.size = "6";
	element_discount.value = "0.0000";
	element_discount.name = "invoice[entries]["+ element_id.value +"][discount]";
	cell_discount.appendChild(element_discount);
}

function deleteRow(tableID) {
	try {
	var table = document.getElementById(tableID);
	var rowCount = table.rows.length;

	for(var i=0; i<rowCount; i++) {
		var row = table.rows[i];
		var chkbox = row.cells[1].childNodes[0];
		if(null != chkbox && true == chkbox.checked) {
			if(rowCount <= 1) {
				alert("Cannot delete all the rows.");
				break;
			}
			table.deleteRow(i);
			rowCount--;
			i--;
		}

	}
	}catch(e) {
		alert(e);
	}
}

function define_default(reference,custom){
	if(reference == ''){
		current = custom;
	}else{
		current = reference;
	}
	
	return current;
}

function check_onclick_period(form_element,default_input){
	//alert(value);
	if(form_element.value == default_input){
		form_element.value = '';
	}
	
	return form_element;
}

function check_onblur_period(form_element,previous_value){
	if(form_element.value == ''){
		form_element.value = previous_value;
	}
	
	return form_element;
}

function addOption(selectbox,text,value,selected) {
	var optn = document.createElement("OPTION");
	optn.text = text;
	optn.value = value;
	optn.selected = selected;
	selectbox.options.add(optn);
}

function return_select(checked,reference_element){
	if(checked == reference_element.value){
		result = 'selected';
	}else{
		result = '';
	}
	
	return result;
}

function show_account_inputs(){
	document.getElementById('invoice[parent_account_name]').value = '';
	/*if(document.getElementById('invoice[type]').value == 'PROFORMA'){
		document.getElementById('client_name_input').className = 'show';
		document.getElementById('client_name_dropdown').className = 'hide';
	}else if (document.getElementById('invoice[type]').value == 'TAX'){
		document.getElementById('client_name_input').className = 'hide';
		document.getElementById('client_name_dropdown').className = 'show';
		
		
		document.getElementById('invoice[account_name_input]').value = '';
	}else{
		document.getElementById('client_name_input').className = 'hide';
		document.getElementById('client_name_dropdown').className = 'hide';
		document.getElementById('invoice[address]').className = 'hide';
		document.getElementById('invoice[contact_person]').className = 'hide';
	}*/
	if(document.getElementById('invoice[type]').value == 'PROFORMA'){
		document.getElementById('invoice[address]').className = 'show';
		document.getElementById('invoice[contact_person]').className = 'show';
		document.getElementById('invoice[invoice_currency]').className = 'show';
		document.getElementById('account_name_td').innerHTML = ''+
			'<label id="client_name_input">'+
				'<input name="invoice[account_name_input]" type="text" id="invoice[account_name_input]" size="40" value="" onblur="javascript:set_account_name(\'invoice[account_name_input]\',\'invoice[parent_account_name]\');" />'+
			'</label>'+
		'';
		
		//Only exists on selection
		//document.getElementById('invoice[account_name_dropdown]').value = '';
	}else if (document.getElementById('invoice[type]').value == 'TAX'){
		document.getElementById('account_name_td').innerHTML = ''+
			'<label id="client_name_dropdown">'+
        		'<?php echo display_inv_child_accounts_dropdown($selected="",$element_name="invoice[account_name_dropdown]",$onblur="javascript:set_account_name(\'invoice[account_name_dropdown]\',\'invoice[parent_account_id]\')"); ?>'+
        	'</label>'+
		'';
		
		//Only exists on selection
		//document.getElementById('invoice[account_name_input]').value = '';
		
		document.getElementById('invoice[invoice_currency]').className = 'hide';
		document.getElementById('invoice[address]').className = 'hide';
		document.getElementById('invoice[contact_person]').className = 'hide';
		document.getElementById('invoice[address]').value = '';
		document.getElementById('invoice[contact_person]').value = '';
	}else{
		document.getElementById('account_name_td').innerHTML = 'Define the invoice type first ...';
		document.getElementById('invoice[account_name_dropdown]').value = '';
		document.getElementById('invoice[account_name_input]').value = '';
		document.getElementById('invoice[address]').value = '';
		document.getElementById('invoice[contact_person]').value = '';
		
		document.getElementById('invoice[invoice_currency]').className = 'hide';
		document.getElementById('invoice[address]').className = 'hide';
		document.getElementById('invoice[contact_person]').className = 'hide';
	}
}

function set_account_name(element,account_input){
	
	//alert("Setting "+document.getElementById(account_input).name+" to "+document.getElementById(element).value);
	
	if(document.getElementById(element).value != ''){
		document.getElementById(account_input).value = document.getElementById(element).value;
	}
}
</SCRIPT>
<?php
}
?>