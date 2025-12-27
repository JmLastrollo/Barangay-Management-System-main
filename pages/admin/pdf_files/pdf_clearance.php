<?php
require_once "../../../backend/auth_admin.php";  
require_once "../../../backend/config.php";
require_once "../../../backend/fpdf186/fpdf.php";

class ResidencyPDF extends FPDF {
    function Header(){
        $leftLogo = __DIR__ . '/../../../assets/img/cdologo.png';
        $rightLogo = __DIR__ . '/../../../assets/img/barangaygusalogo.png';

        if (file_exists($leftLogo)) $this->Image($leftLogo,10,8,28);
        if (file_exists($rightLogo)) $this->Image($rightLogo,170,8,28);

        $this->SetY(12);
        $this->SetFont('Times','',10);
        $this->Cell(0,5,'Republic of the Philippines',0,1,'C');
        $this->Cell(0,5,'Province of Misamis Oriental',0,1,'C');
        $this->Cell(0,5,'City of Cagayan de Oro',0,1,'C');

        $this->SetFont('Times','B',12);
        $this->Cell(0,6,'BARANGAY GUSA',0,1,'C');

        $this->Ln(4);
        $this->SetFont('Times','B',16);
        $this->Cell(0,8,'OFFICE OF THE PUNONG BARANGAY',0,1,'C');
        $this->Ln(6);
    }

    function Footer(){
        $this->SetY(-45);
        $this->SetFont('Arial','',11);

        $this->Cell(0,6,'',0,1);
        $this->Cell(0,6,'______________________________',0,1,'R');
        
        $this->Cell(0,6,'Punong Barangay / Authorized Official',0,1,'R');

        $this->Ln(5);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,6,'This is a generated document from Barangay Management System',0,0,'C');
    }
}

$pdf = new ResidencyPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetMargins(18, 10, 18);
$pdf->SetFont('Times','',12);

// Data
$name = strtoupper(trim(
    $residentArr['first_name'].' '.
    ($residentArr['middle_name'] ?? '').' '.
    ($residentArr['last_name'] ?? '')
));

$purok = $residentArr['purok'] ?? 'N/A';
$years = 'N/A';
if (!empty($residentArr['resident_since'])) {
    $startYear = (int)$residentArr['resident_since'];
    $currentYear = (int)date("Y");
    $years = $currentYear - $startYear; 
}

$civil_status = strtolower($residentArr['civil_status'] ?? 'N/A');
$issueDay = date('jS');
$issueMonthYear = strtoupper(date('F Y'));

$pdf->Ln(8);

// Title
$pdf->SetFont('Times','B',18);
$pdf->Cell(0,8,'BARANGAY CLEARANCE',0,1,'C');
$pdf->Ln(10);

// --- BODY ---

// TO WHOM IT MAY CONCERN (BOLD)
$pdf->SetFont('Times','B',12);
$pdf->Cell(0,7,'TO WHOM IT MAY CONCERN:',0,1);
$pdf->Ln(5);

// Switch back to normal
$pdf->SetFont('Times','',12);

// Paragraph with 8mm indent
$pdf->Cell(8);
$pdf->Write(7,"This is to certify that ");

// Name (BOLD + UNDERLINE)
$pdf->SetFont('Times','BU',12);
$pdf->Write(7, "___" . $name . "___");

// Continue sentence
$pdf->SetFont('Times','',12);
$pdf->Write(7,", legal age, single/");
$pdf->Write(7, $civil_status);

$pdf->Write(7,", and residence at ");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, $purok);

$pdf->SetFont('Times','',12);
$pdf->Write(7," of Barangay Gusa, Cagayan de Oro has no derogatory record filed in our Barangay. \n\n");

// Continue
$pdf->SetFont('Times','',12);
$pdf->Cell(8);
$pdf->Write(7," The above-named individual who is a bonafide resident in this Barangay has person of Good Moral Character \n\n");

$pdf->SetFont('Times','',12);
$pdf->Cell(8);
$pdf->Write(7," This certification is hereby issued upon request of the subject person in connection with his/her requirement purposes. \n\n");

// Indented second part
$pdf->SetFont('Times','',12);
$pdf->Cell(8);
$pdf->Write(7,"Given this ");

// DATE (BU)
$pdf->SetFont('Times','BU',12);
$pdf->Write(7, $issueDay);

$pdf->SetFont('Times','',12);
$pdf->Write(7," day of ");

// MONTH & YEAR (BU)
$pdf->SetFont('Times','BU',12);
$pdf->Write(7, $issueMonthYear);

$pdf->SetFont('Times','',12);
$pdf->Write(7,", at the Office of the Punong Barangay.");

$pdf->Ln(20);

// Output PDF
$pdf->Output('I', 'Certificate_of_Residency_' . str_replace(' ', '_', $name) . '.pdf');
exit;
?>
