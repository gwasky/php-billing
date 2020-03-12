<?php
error_reporting(E_PARSE | E_ERROR);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0014)about:internet -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Invoice print <? echo $_GET['id']; ?></title>
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
</head>
<body bgcolor="#ffffff">
<?php 
require('control.php');

echo display_invoice_byid($_GET['id']);

?>
</body>
</html>