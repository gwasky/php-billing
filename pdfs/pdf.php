<?php

require_once('FPDF17/fpdf.php');

//echo "This is a tempt ... <br>"; exit();

$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',20);
$pdf->Cell(40,10,'Is this a PDF doc?');
$pdf->Ln();
$pdf->SetFont('Arial','B',15);
$pdf->Cell(110,10,'Powered by FPDF.',0,1,'C');
$pdf->Output();

?>