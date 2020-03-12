// JavaScript Document<SCRIPT language="javascript">
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
	var element_account = document.createElement("SELECT");
	element_account.name="invoice[entries]["+ element_id.value +"][account]";
	addOption(element_account,"Label 1","Value 1","");
	addOption(element_account,"Label 2","Value 2","");
	addOption(element_account,"Label 3","Value 3","");
	cell_account.appendChild(element_account);

	var cell_product = row.insertCell(3);
	var element_product = document.createElement("SELECT");
	element_product.type = "text";
	element_product.name = "invoice[entries]["+ element_id.value +"][product]";
	addOption(element_product,"Label 1","Value 1","");
	addOption(element_product,"Label 2","Value 2","");
	addOption(element_product,"Label 3","Value 3","");
	cell_product.appendChild(element_product);
	
	var cell_from = row.insertCell(4);
	var element_from = document.createElement("input");
	element_from.type = "text";
	element_from.size = "10";
	element_from.value = define_default(document.getElementById('invoice[start_date]').value,"<Start date>");
	element_from.setAttribute("Onclick","javascript:check_onclick_period(this);");
	element_from.setAttribute("Onblur","javascript:check_onblur_period(this,'<Start date>');");
	element_from.name="invoice[entries]["+ element_id.value +"][from]";
	//element_from.value = document.getElementById('invoice[start_date]').value;
	cell_from.appendChild(element_from);
	
	//wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="yyyy-mm-dd" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" />
	
	var cell_to = row.insertCell(5);
	var element_to = document.createElement("input");
	element_to.type = "text";
	element_to.size = "10";
	//element_to.value = "<End date>";
	element_to.value = define_default(document.getElementById('invoice[end_date]').value,"<End date>");
	element_to.setAttribute("Onclick","javascript:check_onclick_period(this);");
	element_to.setAttribute("Onblur","javascript:check_onblur_period(this,'<End date>');");
	element_to.setAttribute("wdg:mondayfirst","false");
	element_to.setAttribute("wdg:subtype","Calendar");
	element_to.setAttribute("wdg:mask","yyyy-mm-dd");
	element_to.setAttribute("wdg:type","widget");
	element_to.setAttribute("wdg:singleclick","false");
	element_to.setAttribute("wdg:restricttomask","no");
	/*element_to.setAttribute("wdg:readonly","true");*/
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

function check_onclick_period(form_element){
	//alert(value);
	if(form_element.value == '<Start date>' || form_element.value == '<End date>'){
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