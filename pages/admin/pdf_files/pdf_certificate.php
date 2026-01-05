<?php
require_once '../../../backend/db_connect.php';
require_once '../../../backend/fpdf186/fpdf.php';

class CertificatePDF extends FPDF
{
    function Header()
    {
        $logoLeft = '../../../assets/img/Langkaan 2 Logo-modified.png';
        $logoRight = '../../../assets/img/dasma logo-modified.png';

        if (file_exists($logoLeft)) $this->Image($logoLeft, 10, 10, 40);
        if (file_exists($logoRight)) $this->Image($logoRight, 160, 10, 40);

        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(0, 18);
        $this->Cell(0, 7, 'Republic of the Philippines', 0, 1, 'C');
        $this->Cell(0, 7, 'PROVINCE OF CAVITE', 0, 1, 'C');
        $this->Cell(0, 7, 'OFFICE OF THE SANGGUNIANG BARANGAY LANGKAAN-II', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(0, 6, 'CITY OF DASMARIÑAS', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Tel. No-B. (046) 487 – 4071 / (046) 230 – 8048', 0, 1, 'C');
        $this->Cell(0, 6, 'Email: barangaylangkaanii@gmail.com', 0, 1, 'C');

        $this->Ln(8);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function LeftSidebar($officials)
    {
        $x = 10; 
        $y = $this->GetY(); 
        $width = 55; 
        $maxHeight = 185;

        $logoPath = '../../../assets/img/bagong_pilipinas.jpg';

        $this->SetDrawColor(0, 0, 0);
        $this->Rect($x - 2, $y - 2, $width + 4, $maxHeight);

        if (file_exists($logoPath)) $this->Image($logoPath, $x + 7, $y + 5, $width - 14);

        $this->SetY($y + 50);
        $this->SetLeftMargin($x + 2);
        $this->SetRightMargin(210 - $x - $width);
        $this->SetFont('Arial', 'B', 9);

        $usedHeight = 0;
        foreach ($officials as $official) {
            if ($usedHeight > $maxHeight - 50) break;

            if (stripos($official['title'], 'KAGAWAD') !== false) {
                $this->SetTextColor(188, 0, 38);
            } elseif (stripos($official['title'], 'Punong Barangay') !== false) {
                $this->SetTextColor(0, 102, 204);
            } else {
                $this->SetTextColor(0, 0, 128);
            }

            $name = iconv('UTF-8', 'windows-1252//IGNORE', $official['name']);
            $title = iconv('UTF-8', 'windows-1252//IGNORE', $official['title']);

            $lineHeightName = 5;
            $lineHeightTitle = 4;

            $this->Cell(0, $lineHeightName, $name, 0, 1, 'L');
            $this->SetFont('Arial', 'I', 7);
            $this->Cell(0, $lineHeightTitle, $title, 0, 1, 'L');
            $this->Ln(1);
            $this->SetFont('Arial', 'B', 9);

            $usedHeight += $lineHeightName + $lineHeightTitle + 1;
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetLeftMargin(75);
        $this->SetRightMargin(10);
    }

    function Watermark($sealPath)
    {
        if (file_exists($sealPath)) {
            $this->Image($sealPath, 65, 85, 80, 0, 'PNG'); // smaller and shifted up watermark
        }
    }

    function CertificateBody($residentName, $age, $address, $purpose, $dateIssue)
    {
        $this->SetLeftMargin(75);
        $this->SetRightMargin(10);
        $this->SetY(75);

        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'CERTIFICATION/CLEARANCE', 0, 1, 'C');

        $this->Ln(5);

        $this->SetFont('Arial', 'I', 14);
        $this->Cell(0, 8, 'TO WHOM IT MAY CONCERN:', 0, 1, 'L');

        $this->Ln(6);
        $this->SetFont('Arial', '', 12);

        $maxWidth = 120;

        $text1 = "This is to certify that " . $residentName . ", of age " . $age .
            ", is a resident of " . $address . ", known to be of good moral character.";

        $text2 = "This certifies further that as per records filed in this office, " . $residentName .
            " has no derogatory records as of this date issue.";

        $text3 = "This certification is hereby issued upon the request of the above mentioned person in connection with his/her application for:";

        $text4 = str_repeat("_", 70);

        $text5 = "Issued this ___" . date('jS', strtotime($dateIssue)) . "___ day of ___" . date('F', strtotime($dateIssue)) .
            "___, " . date('Y', strtotime($dateIssue)) . "___ at Barangay Hall Langkaan II, City of Dasmariñas, Cavite.";

        $this->MultiCell($maxWidth, 7, $text1, 0, 'J');
        $this->Ln(6);
        $this->MultiCell($maxWidth, 7, $text2, 0, 'J');
        $this->Ln(6);
        $this->MultiCell($maxWidth, 7, $text3, 0, 'J');
        $this->MultiCell($maxWidth, 7, $text4, 0, 'L');
        $this->Ln(7);
        $this->MultiCell($maxWidth, 7, $text5, 0, 'J');
    }

    function SignatureSection($signatory)
    {
        $x = 130;
        $y = 198; 

        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(75, 6, strtoupper($signatory), 0, 1, 'C');

        $this->SetFont('Arial', 'I', 9);
        $this->Cell(75, 5, '• Punong Barangay •', 0, 1, 'C');

        $this->Line($x + 10, $y + 22, $x + 65, $y + 22);

        $this->SetXY($x, $y + 24);
        $this->Cell(75, 5, 'Signature', 0, 1, 'C');

        $boxSize = 35;  // smaller boxes for thumb marks
        $space = 12;

        $this->Rect($x, $y + 32, $boxSize, $boxSize);
        $this->SetXY($x, $y + 32 + $boxSize + 3);
        $this->SetFont('Arial', '', 8);
        $this->Cell($boxSize, 5, 'Thumb Mark (Left)', 0, 0, 'C');

        $this->Rect($x + $boxSize + $space, $y + 32, $boxSize, $boxSize);
        $this->SetXY($x + $boxSize + $space, $y + 32 + $boxSize + 3);
        $this->Cell($boxSize, 5, 'Thumb Mark (Right)', 0, 0, 'C');
    }

    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(255, 0, 0);
        $this->Cell(0, 5, 'Not valid without official dry seal for security purposes.', 0, 0, 'C');
        $this->SetTextColor(0, 0, 0);
    }
}

// === Begin script ===

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid issuance ID.");
}
$issuanceId = intval($_GET['id']);

try {
    $sql = "SELECT i.*, 
                   rp.first_name, rp.middle_name, rp.last_name, rp.age, rp.address, p.reference_no
            FROM issuance i
            LEFT JOIN resident_profiles rp ON i.resident_id = rp.resident_id
            LEFT JOIN payments p ON i.issuance_id = p.issuance_id
            WHERE i.issuance_id = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $issuanceId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$record) die("Record not found.");
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Prepare resident name with optional middle initial
$mi = !empty($record['middle_name']) ? strtoupper(substr($record['middle_name'], 0, 1)) . '.' : '';
$residentName = strtoupper(trim($record['first_name'] . ' ' . $mi . ' ' . $record['last_name']));
$age = $record['age'] ?? 'N/A';
$address = $record['address'] ?? 'Barangay Langkaan-II, City of Dasmariñas, Province of Cavite';
$purpose = $record['purpose'] ?? 'N/A';
$dateIssue = $record['request_date'] ?? date('Y-m-d');
$signatory = "Hon. David John Paulo C. Laudato";

$officials = [
    ['name' => 'Hon. David John Paulo C. Laudato', 'title' => '• Punong Barangay •'],
    ['name' => 'Hon. Enrico S. Sango', 'title' => 'Committee on Appropriations'],
    ['name' => 'Hon. Danilo S. Galinato', 'title' => 'Committee on Rules & Privileges'],
    ['name' => 'Hon. Alfeo S. Sollegue', 'title' => 'Committee on Women and Family'],
    ['name' => 'Hon. Mark Henry A. Barcena', 'title' => 'Committee on Livelihood'],
    ['name' => 'Hon. Alberto D. Bautista', 'title' => 'Committee on Agriculture'],
    ['name' => 'Hon. Alberto Jr. G. Agustin', 'title' => 'Committee on Health & Sanitation and Environmental Protection'],
    ['name' => 'Hon. Fernando B. Laudato Jr.', 'title' => 'Committee on Peace and Order'],
    ['name' => 'Hon. Jhimwell M. Rivera', 'title' => 'SK Chairperson/Sports & Youth Development'],
    ['name' => 'Juan Paolo C. Melad', 'title' => 'Barangay Secretary'],
    ['name' => 'Luchi L. Antonio', 'title' => 'Barangay Treasurer'],
];

$pdf = new CertificatePDF('P', 'mm', 'A4');
$pdf->AddPage();

$pdf->LeftSidebar($officials);

$pdf->Watermark('../../../assets/img/barangay_seal_transparent.png');

$pdf->CertificateBody($residentName, $age, $address, $purpose, $dateIssue);

$pdf->SignatureSection($signatory);

$pdf->Output();