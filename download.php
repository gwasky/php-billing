<?php
$filename = urldecode($_GET['filename']).".xls";
// required for IE, otherwise Content-disposition is ignored
if(ini_get('zlib.output_compression'))
	ini_set('zlib.output_compression', 'Off');

# This line will stream the file to the user rather than spray it across the screen
header("Content-type: application/vnd.ms-excel");

# replace excelfile.xls with whatever you want the filename to default to
header("Content-Disposition: attachment;filename=".$filename);
header("Expires: 0");
header("Cache-Control: private");
session_cache_limiter("public");
?>
<?php
	include_once '../include/commonfunctions.php';
	session_start(); 
	# printing details
	# priting the lists; The default print option is current page. We select a printing query based on the
	# option chosen by the user
	$COLUMN_STARTING_VARIABLE = $_GET['columncheck'];
	# the coluumns that have numbers, these have to be formatted differently from the rest of the
	# columns
	$number_column_array = getArrayFromCommaDelimitedList($_GET['numbercolumnlist']);
	$printquery = $_SESSION['allresultsquery'];
	$binaryfields = $_SESSION['binaryfields'];
?>

<table cellpadding="2" cellspacing="2" border="1">
  <tr>
    <td style="font-size:18px; font-weight:bold;"><?php echo str_replace("_"," ", $_GET['filename']); ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><table border="0" cellspacing="0" cellpadding="4" width="100%" style="background-color: #F7F3EF;">
  <?php //
		$counter = 0;
		openDatabaseConnection();
		$result = mysql_query($printquery) or die("Invalid query: " . mysql_error());
		$columnCount =  mysql_num_fields($result);
		$i = 0;
		$arraylist = array();
		# put all results in an array, and pop the first two
		while ($i < $columnCount) {
			$meta = mysql_fetch_field($result);
			array_push($arraylist, $meta->name);
			$i++;
		}
	
		print "<tr style=\"background-color: #666666; color: #FFFFFF;	font-weight: bold;	text-decoration: none;	position: relative;	height: 20px;\">";
		for ($cols = $COLUMN_STARTING_VARIABLE; $cols < count($arraylist); $cols++) {
			print "<td nowrap align=\"left\">".$arraylist[$cols]."</td>";
		}
		print "</tr>";
		# check if there are any rows returned
		if (mysql_num_rows($result) == 0) {
			print "<tr><td height=\"20\" colspan=\"".$columnCount."\">There are no records to display.</td></tr>";
		}else{
			# print the rows
			while ($line = mysql_fetch_array($result)) {
				
				# open the row
				print "<tr>";
				for ($row = $COLUMN_STARTING_VARIABLE; $row < count($line); $row++){																						
					# Process the row, ignore columns before the column starting variable
					# check if the column is in the number list
					# Note the user of === since the search function may return a key or false
					if (array_search($row, $number_column_array) === false) {
						if(in_array($arraylist[$row],$binaryfields)) {
							print "<td nowrap align=\"left\" border=\'1\'>".getTextFromBinaryInt($line[$row])."</td>";
						} else {																		
							print "<td nowrap align=\"left\" border=\'1\'>".$line[$row]."</td>";
						}
					} else {
						print "<td nowrap align=\"right\" style=\"vnd.ms-excel.numberformat:0.00;\">".$line[$row]."</td>";
					}
				 }
				// close the row
				print "</tr>";
			} // end of while
		}
?>
</table>
</td>
  </tr>
</table>
