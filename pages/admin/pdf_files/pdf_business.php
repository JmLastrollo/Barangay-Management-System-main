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

// RESIDENT
$name = strtoupper(trim(
    $residentArr['first_name'].' '.
    ($residentArr['middle_name'] ?? '').' '.
    ($residentArr['last_name'] ?? '')
));

$purok = strtoupper($residentArr['purok'] ?? 'N/A');

// BUSINESS DATA
$businessName = strtoupper($requestArr['business_name'] ?? 'N/A');
$businessLocation = strtoupper($requestArr['business_location'] ?? 'N/A');

// DATES
$issueDay = date('jS');
$issueMonth = date('F');
$issueYear = date('Y');
$expiryYear = $issueYear + 1;

$pdf->Ln(8);

// Title
$pdf->SetFont('Times','B',18);
$pdf->Cell(0,8,'BARANGAY BUSINESS CERTIFICATION',0,1,'C');
$pdf->Ln(10);

// TO WHOM IT MAY CONCERN
$pdf->SetFont('Times','B',12);
$pdf->Cell(0,7,'TO WHOM IT MAY CONCERN:',0,1);
$pdf->Ln(5);

// PARAGRAPH
$pdf->SetFont('Times','',12);
$pdf->Cell(8);
$pdf->Write(7,"This is to certify that the business or trade activity below:\n");

$pdf->Ln(5);
$pdf->SetFont('Times','B',12);
$pdf->Write(7,"NAME OF THE OWNER/OPERATOR:       $name\n");
$pdf->Write(7,"ADDRESS:                                                         $purok\n");
$pdf->Write(7,"BUSINESS NAME:                                           $businessName\n");
$pdf->Write(7,"LOCATION:                                                      $businessLocation\n\n");

$pdf->Ln(5);

$pdf->SetFont('Times','',12);
$pdf->Cell(8);
$pdf->Write(7,"I hereby granted clearance to start his/her business operation within Barangay Gusa, ");
$pdf->Write(7,"with no objection for the issuance of the corresponding Mayor's Permit being applied for.\n\n");

// DATE
$pdf->Cell(8);
$pdf->Write(7,"Issued this ");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, $issueDay);

$pdf->SetFont('Times','',12);
$pdf->Write(7," day of ");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, '_____' . $issueMonth . '_____');

$pdf->SetFont('Times','',12);
$pdf->Write(7,", $issueYear and will expire on \n");

$pdf->SetFont('Times','BU',12);
$pdf->Write(7, '_____' . date('F j', strtotime('+1 year')) . '_____');

$pdf->SetFont('Times','',12);
$pdf->Write(7,", $expiryYear.\n\n");

$pdf->Cell(8);
$pdf->Write(7,"This certification is subject to continuing compliance with Barangay Rules and Regulations.\n\n");

$pdf->Ln(20);

$pdf->Output('I', 'Business_Clearance_' . str_replace(' ', '_', $name) . '.pdf');
exit;
?>
