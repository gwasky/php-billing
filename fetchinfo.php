<?php require_once('../Connections/sugar.php');
include('../includes/wdg/WDG.php');
require_once('control.php');
$myquery = new uniquequerys();

$account_id = $_GET['account_id'];
if(isset($_GET['account_id'])){
$query = "
SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
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
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts.`status` = 'Active' AND
				  crn_c = '$account_id'
	UNION
	SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts.start_date,
				  cn_contracts.expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
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
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts.`status` = 'Active' AND
				 crn_c = '$account_id'
	UNION
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.domain_hosting_start_date_c AS start_date,
				  cn_contracts_cstm.domain_hosting_end_date_c as expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.no_domains_d_hosting_c as quantity,
				  accounts_cstm.discount_domain_hosting_c as discount,
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
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.domain_hosting_status_c = 'Active' AND
				  crn_c = '$account_id'
	UNION
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.domain_reg_start_date_c as start_date,
				  cn_contracts_cstm.domain_reg_end_date_c as expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.no_domains_registration_c as quantity,
				  accounts_cstm.discount_domain_registration_c as discount,
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
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.domain_reg_status_c = 'Active' AND
				  crn_c = '$account_id'
		UNION
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.mail_hosting_start_date_c as start_date,
				  cn_contracts_cstm.mail_hosting_end_date_c as expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.no_of_100mb_email_c as quantity,
				  accounts_cstm.discount_mail_hosting_c as discount,
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
				 INNER JOIN ps_products ON (accounts_cstm.package_mail_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  cn_contracts_cstm.mail_hosting_status_c = 'Active' AND
				  crn_c = '$account_id'
	UNION
				SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.web_hosting_start_c as start_date,
				  cn_contracts_cstm.web_hosting_end_date_c as expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.no_domains_web_hosting_c as quantity,
				  accounts_cstm.discount_web_hosting_c as discount,
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
				 INNER JOIN ps_products ON (accounts_cstm.package_web_hosting_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  web_hosting_status_c = 'Active' AND
				  crn_c = '$account_id'
				  
UNION
				  
				  SELECT 
				  accounts_cstm.mem_id_c as parent_id,
				  accounts_cstm.crn_c,
				  cn_contracts_cstm.hire_purchase_start_c as start_date,
				  cn_contracts_cstm.hire_purchase_end_c as expiry_date,
				  accounts_cstm.service_type_internet_c as service_type,
				  accounts_cstm.hire_purchase_count_c as quantity,
				  accounts_cstm.hire_purchase_discount_c as discount,
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
				 INNER JOIN ps_products ON (accounts_cstm.hire_purchase_product_c=ps_products.name)
				 INNER JOIN ps_products_cstm ON (ps_products.id=ps_products_cstm.id_c)
				WHERE
				  accounts.deleted = '0' AND 
				  cn_contracts.deleted = '0' AND
				  ps_products.deleted != '1' AND
				  hire_purchase_status_c = 'Active' AND
				  crn_c = '$account_id'
";

$client_services = $myquery->multiplerow_query($query);

foreach($client_services as $service){
	$client[account_id] = $service[crn_c];
	$client[client_currency] = $service[selected_billing_currency];
	
	$client[$service[grouping]][start_date] = $service[start_date];
	$client[$service[grouping]][expiry_date] = $service[expiry_date];
	$client[$service[grouping]][value] = $service[product_name].'#'.$service[product_price].'#'.$service[grouping].'#'.$service[billing_currency];
	$client[$service[grouping]][name] = $service[product_name];
	$client[$service[grouping]][disc] = $service[discount];
	$client[$service[grouping]][num] = $service[quantity];
	$client[$service[grouping]][product_currency] = $service[billing_currency];
	if(($client[$service[grouping]][product_currency] != $client[client_currency])&&($service[product_price]>0)){
		$client[currency_error] = 'This client has different preferred billing currency from one or more product billing currency. Please create seperate accounts for each preferred billing currency and attach products in that preferred billing currency. ie if the parent account has UGX preferred billing currency, all products attached to the parent and child accounts MUST BE IN UGX. Same applies for USD';
		$client[$service[grouping]][currency_error] = 'Product Currency ['.$client[$service[grouping]][product_currency].'] is not the same as the clients preffered billing currency ['.$client[client_currency].']';
	}
}

//for those who do not have Packages
if((!$client['Rental Fees'][start_date]) && (!$client['Rental Fees'][expiry_date])){
	$client['Rental Fees'][start_date] = $client['Service'][start_date];
	$client['Rental Fees'][expiry_date] = $client['Service'][expiry_date];

}


?>
<style type="text/css">
<!--
.style112 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style112 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style11 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style11 {	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style113 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style113 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:200px;
}
.style111 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	width:150px;
}
.style111 {font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style14 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #000000; font-weight: bold; }
.style114 {color: #FFFFFF}
-->
</style>

<table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
<? if($client[currency_error]){ ?>
  <tr>
    <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style114" scope="row">Internet/Data</th>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row" colspan="2"><? echo $client[currency_error]; ?></th>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row" colspan="2"><input type="hidden" name="client_currency" class="style112" id="client_currency" value="<?php echo $client[client_currency]; ?>" />
	<input type="hidden" name="user" id="user" value = "<?php echo $row_users['first_name']." ".$row_users['last_name'] ; ?>" />
	<?php echo "Clients preffered billing currency: ".$client[client_currency]; ?></th>
  </tr>
  
<? } ?>
 <tr>
    <th align="left" valign="top" class="style14" scope="row" colspan="2"></th>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Start Date</th>
    <td align="left">
    <!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
    <input size="10" maxlength="10" name="start_date" class="style112" id="start_date" value="<?php echo $client['Rental Fees'][start_date]; ?>" />
    </td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">End Date</th>
    <td align="left">
    <input size="10" maxlength="10" name="end_date" class="style113" id="end_date" value="<?php echo $client['Rental Fees'][expiry_date]; ?>" />
    </td>
  </tr>
  <?php if($client['Rental Fees'][currency_error]){ ?>
  <tr>
    <th colspan="2" align="left" valign="top" class="style14" scope="row"><? echo $client['Rental Fees'][currency_error]; ?></th>
  </tr>
  <?php } ?>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
    <td align="left"><select name="package" class="style111" id="package">
					<option value="<?php echo $client['Rental Fees'][value]; ?>" selected="selected"><?php echo $client['Rental Fees'][name]; ?></option>
    	</select>
        <br />
        <label>Quantity
        <input readonly="readonly" name="p_quantity" type="text" size="4" maxlength="5" class="style11" id="p_quantity" value="<? echo $client['Rental Fees'][num];?>" />
        </label><br />
        <label>% Discount
        <input readonly="readonly" name="p_discount" type="text" size="4" maxlength="5" class="style11" id="p_discount" value="<? echo $client['Rental Fees'][disc];?>" />
        </label>    </td>
  </tr>
  <?php if($client['Service'][currency_error]){ ?>
  <tr>
    <th colspan="2" align="left" valign="top" class="style14" scope="row"><? echo $client['Service'][currency_error]; ?></th>
  </tr>
  <?php } ?>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Service</th>
    <td align="left"><select name="service" class="style111" id="service">
      <option value="<?php echo $client['Service'][value]; ?>"><?php echo $client['Service'][name]; ?></option>
    </select>    <br />
    <label>Quantity
    <input readonly="readonly" name="b_quantity" type="text" size="4" maxlength="5" class="style11" id="b_quantity" value="<?php echo $client['Service'][num]; ?>" />
    </label><br />
    <label>% Discount
    <input readonly="readonly" name="b_discount" type="text" size="4" maxlength="5" class="style11" id="b_discount" value="<?php echo $client['Service'][disc]; ?>" />
    </label>    </td>
  </tr>
</table>
<p>
</p>
<table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
  
  <tr>
    <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style114" scope="row">Web Hosting </th>
  </tr>
  <?php if($client['Web Hosting'][currency_error]){ ?>
  
  <tr>
    <th colspan="2" align="left" valign="top" class="style14" scope="row"><? echo $client['Web Hosting'][currency_error]; ?></th>
  </tr>
  <?php } ?>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Start Date</th>
    <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
    <input readonly="readonly" name="start_date_web_hosting" class="style112" id="start_date_web_hosting" value="<?php echo $client['Web Hosting'][start_date]; ?>" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">End Date</th>
    <td align="left"><input readonly="readonly" name="end_date_web_hosting" class="style113" id="end_date_web_hosting" value="<?php echo $client['Web Hosting'][expiry_date]; ?>" /></td>
  </tr>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
    <td align="left"><select name="package_web_hosting" class="style111" id="package_web_hosting">
      <option value="<?php echo $client['Web Hosting'][value]; ?>" selected="selected"><?php echo $client['Web Hosting'][name]; ?></option>
    </select>
        <br />
        <label>Quantity
        <input readonly="readonly" name="p_quantity_web_hosting" type="text" size="4" maxlength="5" class="style11" id="p_quantity_web_hosting" value="<? echo $client['Web Hosting'][num];?>" />
        </label>
      <br />
        <label>% Discount
        <input readonly="readonly" name="p_discount_web_hosting" type="text" size="4" maxlength="5" class="style11" id="p_discount_web_hosting" value="<? echo $client['Web Hosting'][disc];?>" />
        </label>    </td>
  </tr>
</table>
<p>&nbsp;</p>
<table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
  
  <tr>
    <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style114" scope="row">Domain Hosting</th>
  </tr>
  <? if($client[currency_error]){ ?>
  
  <tr>
    <th align="left" valign="top" class="style14" scope="row" colspan="2"><? echo $client['Domain Hosting'][currency_error]; ?></th>
  </tr>
  <? } ?>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Start Date</th>
    <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
      <input readonly="readonly" name="start_date_dom_hosting" class="style112" id="start_date_dom_hosting" value="<?php echo $client['Domain Hosting'][start_date]; ?>" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">End Date</th>
    <td align="left"><input readonly="readonly" name="end_date_dom_hosting" class="style113" id="end_date_dom_hosting" value="<?php echo $client['Domain Hosting'][expiry_date]; ?>" /></td>
  </tr>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
    <td align="left"><select name="package_dom_hosting" class="style111" id="package_dom_hosting">
      <option value="<?php echo $client['Domain Hosting'][value]; ?>" selected="selected"><?php echo $client['Domain Hosting'][name]; ?></option>
    </select>
        <br />
        <label>Quantity
        <input readonly="readonly" name="p_quantity_dom_hosting" type="text" size="4" maxlength="5" class="style11" id="p_quantity_dom_hosting" value="<? echo $client['Domain Hosting'][num];?>" />
        </label>
        <br />
        <label>% Discount
        <input readonly="readonly" name="p_discount_dom_hosting" type="text" size="4" maxlength="5" class="style11" id="p_discount_dom_hosting" value="<? echo $client['Domain Hosting'][disc];?>" />
        </label>    </td>
  </tr>
</table>
<p>&nbsp;</p>
<table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
  
  <tr>
    <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style114" scope="row">Domain Registration</th>
  </tr>
  <? if($client[currency_error]){ ?>
  
  <tr>
    <th align="left" valign="top" class="style14" scope="row" colspan="2"><? echo $client['Domain Registration'][currency_error]; ?></th>
  </tr>
  <? } ?>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Start Date</th>
    <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
        <input readonly="readonly" name="start_date_dom_reg" class="style112" id="start_date_dom_reg" value="<?php echo $client['Domain Registration'][start_date]; ?>" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">End Date</th>
    <td align="left"><input readonly="readonly" name="end_date_dom_reg" class="style113" id="end_date_dom_reg" value="<?php echo $client['Domain Registration'][expiry_date]; ?>" /></td>
  </tr>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
    <td align="left"><select name="package_dom_reg" class="style111" id="package_dom_reg">
      <option value="<?php echo $client['Domain Registration'][value]; ?>" selected="selected"><?php echo $client['Domain Registration'][name]; ?></option>
    </select>
        <br />
        <label>Quantity
        <input readonly="readonly" name="p_quantity_dom_reg" type="text" size="4" maxlength="5" class="style11" id="p_quantity_dom_reg" value="<? echo $client['Domain Registration'][num];?>" />
</label>
      <br />
        <label>% Discount
          <input readonly="readonly" name="p_discount_dom_reg" type="text" size="4" maxlength="5" class="style11" id="p_discount_dom_reg" value="<? echo $client['Domain Registration'][disc];?>" />
        </label>    </td>
  </tr>
</table>
<p>&nbsp;</p>
<table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
  
  <tr>
    <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style114" scope="row">Email Hosting</th>
  </tr>
  <? if($client[currency_error]){ ?>
  
  <tr>
    <th align="left" valign="top" class="style14" scope="row" colspan="2"><? echo $client['Mail Hosting'][currency_error]; ?></th>
  </tr>
  <? } ?>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Start Date</th>
    <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
        <input readonly="readonly" name="start_date_email" class="style112" id="start_date_email" value="<?php echo $client['Mail Hosting'][start_date]; ?>" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">End Date</th>
    <td align="left"><input readonly="readonly" name="end_date_email" class="style113" id="end_date_email" value="<?php echo $client['Mail Hosting'][expiry_date]; ?>" /></td>
  </tr>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
    <td align="left"><select name="package_email" class="style111" id="package_email">
      <option value="<?php echo $client['Mail Hosting'][value]; ?>" selected="selected"><?php echo $client['Mail Hosting'][name]; ?></option>
    </select>
        <br />
        <label>Quantity
        <input readonly="readonly" name="p_quantity_email" type="text" size="4" maxlength="5" class="style11" id="p_quantity_email" value="<? echo $client['Mail Hosting'][num];?>" />
</label>
        <br />
        <label>% Discount
        <input readonly="readonly" name="p_discount_email" type="text" size="4" maxlength="5" class="style11" id="p_discount_email" value="<? echo $client['Mail Hosting'][disc];?>" />
</label>    </td>
  </tr>
</table>
<br />
<br />
<table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <th colspan="2" align="left" valign="top" bgcolor="#0000FF" class="style14 style114" scope="row">Hire Purchase </th>
  </tr>
  <? if($client[currency_error]){ ?>
  
  <tr>
    <th align="left" valign="top" class="style14" scope="row" colspan="2"><? echo $client['Hire Purchase'][currency_error]; ?></th>
  </tr>
  <? } ?>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Start Date</th>
    <td align="left"><!--<input type="hidden" name="contact_info" class="style112" id="account_id" value="<? echo $client[account_id]; ?>" />-->
      <input readonly="readonly" name="start_date_lease" class="style112" id="start_date_lease" value="<?php echo $client['Hire Purchase'][start_date]; ?>" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">End Date</th>
    <td align="left"><input  name="end_date_lease" class="style112" id="end_date_lease" value="<?php echo $client['Hire Purchase'][expiry_date]; ?>" /></td>
  </tr>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Equipment</th>
    <td align="left"><select name="package_lease" class="style1111" id="package_email2">
      <option value="<?php echo $client['Hire Purchase'][value]; ?>" selected="selected"><?php echo $client['Hire Purchase'][name]; ?></option>
      </select>
      <br />
      <label>Quantity
        <input  name="p_quantity_lease" type="text" size="4" maxlength="5" class="style117" id="p_quantity_lease" value="<? echo $client['Hire Purchase'][num];?>" />
      </label>
      <br />
      <label>% Discount
        <input  name="p_discount_lease" type="text" size="4" maxlength="5" class="style117" id="p_discount_lease" value="<? echo $client['Hire Purchase'][disc];?>" />
      </label></td>
  </tr>
</table>
<p>
  <?php
}else{
mysql_select_db($database_sugar, $sugar);
$query_service_ps_products = "SELECT name,price,type FROM ps_products WHERE deleted = '0' AND type = 'Service' AND name != 'Monthly Equipment Rental'";
$service_ps_products = mysql_query($query_service_ps_products, $sugar) or die(mysql_error());
$row_service_ps_products = mysql_fetch_assoc($service_ps_products);
$totalRows_service_ps_products = mysql_num_rows($service_ps_products);
?>
</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<table width="407" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Period Start</th>
    <td align="left"><input name="start_date" class="style112" id="start_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Period End</th>
    <td align="left"><input name="end_date" class="style113" id="end_date" value="" wdg:mondayfirst="false" wdg:subtype="Calendar" wdg:mask="<?php echo $KT_screen_date_format; ?>" wdg:type="widget" wdg:singleclick="false" wdg:restricttomask="no" wdg:readonly="true" /></td>
  </tr>
  <tr>
    <th width="155" align="left" valign="top" class="style14" scope="row">Package</th>
    <td align="left"><select name="package2" class="style111" id="package2">
      <option value="">SELECT PACKAGE</option>
      <option value="Standard(with outright CPE purchase)#0">Standard(with outright CPE purchase)</option>
      <option value="Advanced#0">Advanced</option>
      <option value="CIR Internet#0">CIR Internet</option>
      <option value="Monthly Equipment Rental#10">Standard</option>
      <option value="Dark fibre (1)#5000#Rental Fees">Dark fibre (1)</option>
    </select><br />
    <label>Quantity
    <input name="p_quantity" type="text" size="4" maxlength="5" class="style11" id="p_quantity" value="1" />
    </label><br />
    <label>% Discount
    <input name="p_discount" type="text" size="4" maxlength="5" class="style11" id="p_discount" value="0.00" />
    </label>
    </td>
  </tr>
  <tr>
    <th align="left" valign="top" class="style14" scope="row">Service</th>
    <td align="left"><select name="service2" class="style111" id="service2">
      <?php
do {  
?>
      <option value="<?php echo $row_service_ps_products['name']?>#<?php echo $row_service_ps_products['price']?>#<?php echo $row_service_ps_products['type']?>"><?php echo $row_service_ps_products['name']?></option>
      <?php
} while ($row_service_ps_products = mysql_fetch_assoc($service_ps_products));
  $rows = mysql_num_rows($service_ps_products);
  if($rows > 0) {
      mysql_data_seek($service_ps_products, 0);
	  $row_service_ps_products = mysql_fetch_assoc($service_ps_products);
  }
?>
    </select>
    <br />
    <label>Quantity
    <input name="b_quantity" type="text" size="4" maxlength="5" class="style11" id="b_quantity" value="1" />
    </label><br />
    <label>% Discount
    <input name="b_discount" type="text" size="4" maxlength="5" class="style11" id="b_discount" value="0.00" />
    </label>
    </td>
  </tr>
</table>
<?php } ?>