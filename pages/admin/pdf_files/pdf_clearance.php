<?php
// pages/admin/pdf_files/pdf_clearance.php

// Create PDF Instance
$pdf = new FPDF('P','mm','Letter');
$pdf->AddPage();

// --- HEADER ---
// (Ayusin mo ito base sa actual logo/header image mo kung meron)
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,5,'Republic of the Philippines',0,1,'C');
$pdf->Cell(0,5,'Province of Cavite',0,1,'C');
$pdf->Cell(0,5,'City of Dasmariñas',0,1,'C');
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'BARANGAY LANGKAAN II',0,1,'C');
$pdf->Ln(10);
$pdf->SetLineWidth(1);
$pdf->Line(20, 45, 195, 45); // Line Separator
$pdf->Ln(10);

// --- DOCUMENT TITLE ---
$pdf->SetFont('Arial','B',24);
$pdf->Cell(0,10,'BARANGAY CLEARANCE',0,1,'C');
$pdf->Ln(15);

// --- BODY CONTENT ---
$pdf->SetFont('Arial','',12);
$text = "TO WHOM IT MAY CONCERN:\n\n" .
        "This is to certify that " . $residentName . ", " . $age . " years old, " . 
        strtolower($civilStatus) . ", is a bonafide resident of " . $address . ".\n\n" .
        "This certifies further that the above-named person has NO DEROGATORY RECORD on file in this Barangay as of this date.\n\n" .
        "This certification is issued upon the request of the interested party for the purpose of: " . 
        strtoupper($purpose) . ".\n\n" .
        "Issued this " . $dateIssued . " at Barangay Langkaan II, Dasmariñas City, Cavite.";

// MultiCell para mag-wrap ang text
$pdf->SetRightMargin(25);
$pdf->SetLeftMargin(25);
$pdf->MultiCell(0, 8, $text);

// --- SIGNATORY AREA ---
$pdf->Ln(40);
$pdf->SetRightMargin(10);
$pdf->SetLeftMargin(10);

$pdf->Cell(100); // Move to right side
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0, 5, $officialSignatory, 0, 1, 'C');
$pdf->Cell(100); // Reset position
$pdf->SetFont('Arial','',10);
$pdf->Cell(0, 5, 'Punong Barangay', 0, 1, 'C');

// --- FOOTER / OR NUMBER ---
// Set Y position near bottom (e.g., 260 mm from top)
$pdf->SetY(-40); 

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, "O.R. No: " . ($requestArr['reference_no'] ?? '____________'), 0, 1);
$pdf->Cell(0, 5, "Amount Paid: ₱" . number_format($requestArr['amount'] ?? 0, 2), 0, 1);
$pdf->Cell(0, 5, "Date Issued: " . date('Y-m-d'), 0, 1);

$pdf->Output();
?>