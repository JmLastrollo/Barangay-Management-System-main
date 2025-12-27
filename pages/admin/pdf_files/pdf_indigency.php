<?php
require_once "../../../backend/auth_admin.php";  
require_once "../../../backend/config.php";
require_once "../../../backend/fpdf186/fpdf.php";

class IndigencyPDF extends FPDF {
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

$pdf = new IndigencyPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetMargins(18, 10, 18);
$pdf->SetFont('Times','',12);

// RESIDENT NAME (FOR SELF)
$name = strtoupper(trim(
    $residentArr['first_name'].' '.
    ($residentArr['middle_name'] ?? '').' '.
    ($residentArr['last_name'] ?? '')
));

$issueDay = date('jS');
$issueMonthYear = strtoupper(date('F Y'));

$address = strtoupper($residentArr['purok'] ?? 'N/A');

// REQUEST DATA
$purpose = strtoupper($requestArr['purpose'] ?? 'N/A');
$certificateFor = strtoupper($requestArr['certificate_for'] ?? 'SELF');
$certificateFullname = strtoupper($requestArr['certificate_for_fullname'] ?? '');

$name = strtoupper(trim(
    $residentArr['first_name'].' '.
    ($residentArr['middle_name'] ?? '').' '.
    ($residentArr['last_name'] ?? '')
));


// --- FIX: DETERMINE NAME THAT APPEARS ON CERTIFICATE ---
if ($certificateFor === 'SELF' || $certificateFullname === '') {
    $nameOnCertificate = $name;
} else {
    $nameOnCertificate = $certificateFullname;
}

// TITLE
$pdf->Ln(6);
$pdf->SetFont('Times','B',18);
$pdf->Cell(0,8,'CERTIFICATE OF INDIGENCY',0,1,'C');
$pdf->Ln(10);

// “TO WHOM IT MAY CONCERN”
$pdf->SetFont('Times','B',12);
$pdf->Cell(0,7,'TO WHOM IT MAY CONCERN:',0,1);
$pdf->Ln(5);

// BODY
$pdf->Cell(20);
$pdf->SetFont('Times','',12);

$pdf->Write(7,"This is to certify as per record of this barangay registry, \n");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, "___" . $name . "___");
$pdf->SetFont('Times','',12);

$pdf->Write(7, " is a resident of Barangay Gusa, Cagayan de Oro.\n\n");

$pdf->Cell(20);
$pdf->Write(7, "This certification is being issued upon the verbal request of \n");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, "___" . $nameOnCertificate . "___");
$pdf->SetFont('Times','',12);

$pdf->Write(7, " in relation to the purpose stated below:\n\n");

$pdf->SetFont('Times','B',12);
$pdf->Write(7, "PURPOSE: " . $purpose . "\n\n");

// DATE LINE
$pdf->SetFont('Times','',12);
$pdf->Cell(8);
$pdf->Write(7,"Issued this ");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, $issueDay);

$pdf->SetFont('Times','',12);
$pdf->Write(7," day of ");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, $issueMonthYear);

$pdf->SetFont('Times','',12);
$pdf->Write(7,", at the Office of the Punong Barangay.");

$pdf->Ln(20);

$pdf->Output('I', 'Certificate_of_Indigency_' . str_replace(' ', '_', $nameOnCertificate) . '.pdf');
exit;
?>
