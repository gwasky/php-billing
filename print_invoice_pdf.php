<?php
error_reporting(E_PARSE | E_ERROR);
require_once('pdf_invoice.php');
if($_GET[id] != ''){
	$ids = array($_GET[id]);
	pdf_invoice($ids);
}else{
	echo "NO INVOICE ID SUBMITTED [".$_GET[id]."]<BR>";
}
?>
