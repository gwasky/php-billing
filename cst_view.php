<?php require_once('../Connections/sugar.php'); ?>

<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$currentPage = $_SERVER["PHP_SELF"];


$maxRows_cstdetails = 10;
$pageNum_cstdetails = 0;
if (isset($_GET['pageNum_cstdetails'])) {
  $pageNum_cstdetails = $_GET['pageNum_cstdetails'];
}
$startRow_cstdetails = $pageNum_cstdetails * $maxRows_cstdetails;

mysql_select_db($database_sugar, $sugar);
$query_cstdetails = "SELECT    accounts_cstm.crn_c,   accounts_cstm.cpe_type_c,   accounts.name,   accounts_cstm.preferred_username_c,   contracts.start_date,   contracts.expiry_date, contracts.billing_date,   contracts.`status`,   products.name,   products.price FROM  accounts  INNER JOIN accounts_cstm ON (accounts.id=accounts_cstm.id_c)  INNER JOIN contracts ON (accounts.id=contracts.account_id)  INNER JOIN products ON (accounts_cstm.download_bandwidth_c=products.name) WHERE accounts.deleted = '0' AND products.deleted = '0' AND contracts.deleted = '0'";
$query_limit_cstdetails = sprintf("%s LIMIT %d, %d", $query_cstdetails, $startRow_cstdetails, $maxRows_cstdetails);
$cstdetails = mysql_query($query_limit_cstdetails, $sugar) or die(mysql_error());
$row_cstdetails = mysql_fetch_assoc($cstdetails);

if (isset($_GET['totalRows_cstdetails'])) {
  $totalRows_cstdetails = $_GET['totalRows_cstdetails'];
} else {
  $all_cstdetails = mysql_query($query_cstdetails);
  $totalRows_cstdetails = mysql_num_rows($all_cstdetails);
}
$totalPages_cstdetails = ceil($totalRows_cstdetails/$maxRows_cstdetails)-1;

$queryString_cstdetails = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_cstdetails") == false && 
        stristr($param, "totalRows_cstdetails") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_cstdetails = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_cstdetails = sprintf("&totalRows_cstdetails=%d%s", $totalRows_cstdetails, $queryString_cstdetails);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.style11 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.style14 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #000000; font-weight: bold; }
.style15 {color: #000000}
.style16 {color: #061F7B}
-->
</style></head>

<body>
<table width="100%" border="0" cellspacing="1">
  <tr>
    <td width="40%"><img src="images/logo.jpg" width="233" height="39" /></td>
    <td width="60%" align="right" valign="bottom" class="style14 style16">Home | Payments | Adjustments | Invoices | Bill Printing</td>
  </tr>
  <tr>
    <td colspan="2" background="images/table_header2.jpg">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><table width="700" border="0" align="center" cellpadding="2" cellspacing="0">
      <tr style bordercolor="#CCCCCC">
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Customer#</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">CPE Type</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Package</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Username</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style14">Status</span></td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC">&nbsp;</td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC">&nbsp;</td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC">&nbsp;</td>
        <td background="images/table_header.jpg" bgcolor="#CCCCCC"><span class="style15"></span></td>
      </tr>
      <?php do { ?>
      <tr>
        <td><span class="style11"><?php echo $row_cstdetails['crn_c']; ?></span></td>
        <td class="style11"><?php echo $row_cstdetails['cpe_type_c']; ?></td>
        <td class="style11"><?php echo $row_cstdetails['name']; ?></td>
        <td class="style11"><?php echo $row_cstdetails['preferred_username_c']; ?></td>
        <td class="style11"><?php echo $row_cstdetails['status']; ?></td>
        <td><a href="cst_transaction.php?preferred_username_c=<?php echo $row_cstdetails['preferred_username_c']; ?>" class="style11">View Current</a></td>
        <td class="style11">Payment History</td>
        <td class="style11">Adjustments</td>
        <td><a href="cst_transaction.php?preferred_username_c=<?php echo $row_cstdetails['preferred_username_c']; ?>" class="style11"></a></td>
      </tr>
      <?php } while ($row_cstdetails = mysql_fetch_assoc($cstdetails)); ?>
    </table>
      <table border="0" align="center">
        <tr>
          <td><?php if ($pageNum_cstdetails > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_cstdetails=%d%s", $currentPage, 0, $queryString_cstdetails); ?>"><img src="First.gif" alt="4" border="0" /></a>
              <?php } // Show if not first page ?>          </td>
          <td><?php if ($pageNum_cstdetails > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_cstdetails=%d%s", $currentPage, max(0, $pageNum_cstdetails - 1), $queryString_cstdetails); ?>"><img src="Previous.gif" alt="4" border="0" /></a>
              <?php } // Show if not first page ?>          </td>
          <td><?php if ($pageNum_cstdetails < $totalPages_cstdetails) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_cstdetails=%d%s", $currentPage, min($totalPages_cstdetails, $pageNum_cstdetails + 1), $queryString_cstdetails); ?>"><img src="Next.gif" alt="4" border="0" /></a>
              <?php } // Show if not last page ?>          </td>
          <td><?php if ($pageNum_cstdetails < $totalPages_cstdetails) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_cstdetails=%d%s", $currentPage, $totalPages_cstdetails, $queryString_cstdetails); ?>"><img src="Last.gif" alt="4" border="0" /></a>
              <?php } // Show if not last page ?>          </td>
        </tr>
      </table>
    <br /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
<?php
mysql_free_result($cstdetails);
?>
