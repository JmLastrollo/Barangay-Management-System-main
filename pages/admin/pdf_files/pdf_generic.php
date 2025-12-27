<?php
session_start();
require_once "../../backend/config.php";
require_once "../../backend/fpdf186/fpdf.php";

class GenericPDF extends FPDF {
    function Header(){
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'ISSUANCE DOCUMENT',0,1,'C');
    }
}
$pdf = new GenericPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',12');
$pdf->MultiCell(0,7, "Document type: " . ($requestArr['document_type'] ?? 'N/A') . "\n\nPurpose: " . ($requestArr['purpose'] ?? 'N/A'));
$pdf->Output();
exit;
