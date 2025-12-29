<?php
session_start();
require_once "../../../backend/config.php";
require_once "../../../backend/fpdf186/fpdf.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Barangay Staff') {
    $_SESSION['toast'] = [
        'msg'  => 'You are not authorized to access that page.',
        'type' => 'error'
    ];
    header("Location: /Barangay-Management-System/pages/resident/resident_dashboard.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid Blotter ID.");

$incident = $incidentsCollection->findOne(["_id" => new MongoDB\BSON\ObjectId($id)]);
if (!$incident) die("Blotter record not found.");

function formatDateText($date) {
    if (!$date) return '—';
    return date("F j, Y", strtotime($date));
}

class PDF extends FPDF {
    function Header() {
        $this->Image('../../../assets/img/dasma logo-modified.png', 10, 10, 25);
        $this->Image('../../../assets/img/Langkaan 2 Logo-modified.png', 175, 10, 25);

        $this->SetY(12);
        $this->SetFont('Times','',10); // Times New Roman
        $this->Cell(0,5,'Republic of the Philippines',0,1,'C');
        $this->Cell(0,5,'Province of Misamis Oriental',0,1,'C');
        $this->Cell(0,5,'City of Cagayan de Oro',0,1,'C');
        $this->SetFont('Times','B',11);
        $this->Cell(0,5,'BARANGAY GUSA',0,1,'C');

        $this->Ln(5);
        $this->SetFont('Times','B',14);
        $this->Cell(0,8,'BLOTTER REPORT',0,1,'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Times','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Times','',12);

// Blotter Details
$fields = [
    'Complainant Name:' => $incident->complainant,
    'Respondent Name:'  => $incident->respondent,
    'Date Filed:'       => formatDateText($incident->date_filed ?? null),
    'Date Happened:'    => formatDateText($incident->date_happened ?? null),
];

foreach($fields as $label => $value) {
    $pdf->SetFont('Times','B',12);
    $pdf->Cell(50,8,$label,0,0);
    $pdf->SetFont('Times','',12);
    $pdf->Cell(0,8,$value,0,1);
}

$pdf->Ln(5);

// Narrative with indentation
$pdf->SetFont('Times','B',14);
$pdf->Cell(0,8,'Narrative of the Report:',0,1);
$pdf->SetFont('Times','',12);

$pdf->Ln(5);

// Indent first line by 8mm
$indent = str_repeat(' ', 8); // approximate indent
$lines = explode("\n", $incident->description ?? '—');
foreach($lines as $line) {
    $pdf->MultiCell(0,6,"    ".$line); // 4 spaces indentation
}

// Output PDF
$pdf->Output('I','Blotter_'.$incident->case_no.'.pdf');
