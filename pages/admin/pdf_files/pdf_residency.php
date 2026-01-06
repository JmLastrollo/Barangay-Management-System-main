<?php
require_once '../../../backend/db_connect.php';
// Siguraduhin na tama ang path ng tcpdf.php
require_once '../../../backend/tcpdf/tcpdf.php'; 

// 1. GET DATA
if (!isset($_GET['id'])) die("Error: ID missing.");
$id = $_GET['id'];

try {
    $sql = "SELECT i.*, r.first_name, r.middle_name, r.last_name, r.civil_status, 
                   r.purok, r.street, r.resident_since, p.amount, p.reference_no
            FROM issuance i
            JOIN resident_profiles r ON i.resident_id = r.resident_id
            LEFT JOIN payments p ON i.issuance_id = p.issuance_id
            WHERE i.issuance_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) die("Record not found.");

    // Format Data
    $fullName = strtoupper($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']);
    $civilStatus = strtoupper($data['civil_status']);
    $address = strtoupper("Block " . $data['purok'] . " Lot " . $data['street']);
    
    // Years Logic
    $years = "____";
    if(!empty($data['resident_since'])){
        $diff = date("Y") - $data['resident_since'];
        $years = ($diff < 1) ? "less than 1" : $diff;
    }

    $day = date('jS');
    $monthYear = date('F Y');
    $punongBarangay = "HON. ENRICO SANGO"; 

} catch (PDOException $e) { die($e->getMessage()); }

// 2. EXTEND TCPDF
class MYPDF extends TCPDF {
    public function Header() {
        // Logo Path
        $logoLeft = '../../../assets/img/Langkaan 2 Logo-modified.png';
        $logoRight = '../../../assets/img/dasma logo-modified.png';

        if(file_exists($logoLeft)) $this->Image($logoLeft, 15, 10, 25);
        if(file_exists($logoRight)) $this->Image($logoRight, 170, 10, 25);

        $this->SetY(12);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
        $this->Cell(0, 5, 'Province of Cavite', 0, 1, 'C');
        $this->Cell(0, 5, 'City of Dasmariñas', 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 6, 'BARANGAY LANGKAAN II', 0, 1, 'C');
        $this->Ln(4);
        $this->SetLineWidth(0.5);
        $this->Line(15, 42, 195, 42);
    }

    public function Footer() {
        $this->SetY(-25);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 5, 'This is a system generated document.', 0, 1, 'C');
        $this->Cell(0, 5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'R');
    }
}

// 3. GENERATE PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

// Document Info
$pdf->SetCreator('BMS');
$pdf->SetAuthor('Barangay Langkaan II');
$pdf->SetTitle('Certificate of Residency');
$pdf->SetMargins(25, 50, 25); // Left, Top (para di matakpan header), Right
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

// TITLE
$pdf->SetFont('times', 'B', 24);
$pdf->Cell(0, 10, 'CERTIFICATE OF RESIDENCY', 0, 1, 'C');
$pdf->Ln(10);

// BODY CONTENT (HTML)
$pdf->SetFont('times', '', 12);

$html = '
<p><strong>TO WHOM IT MAY CONCERN:</strong></p>
<br><br>
<p style="text-align:justify; line-height: 1.5;">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that <b><u>'.$fullName.'</u></b>, of legal age, '.$civilStatus.', Filipino citizen, is a <b>PERMANENT RESIDENT</b> of this Barangay.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Based on records of this office, he/she has been residing at '.$address.', Barangay Langkaan II, Dasmariñas City for <b>'.$years.' year(s)</b>.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This certification is issued upon the request of the above-named person for whatever legal purpose it may serve.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Issued this <b>'.$day.'</b> day of <b>'.$monthYear.'</b> at Barangay Langkaan II, Dasmariñas City, Cavite.
</p>';

$pdf->writeHTML($html, true, false, true, false, '');

// SIGNATORY
$pdf->Ln(30);
$pdf->SetFont('times', 'B', 12);

// Gamit ang Table sa loob ng writeHTML para sa signature layout
$signatory = '
<table border="0" style="width:100%">
    <tr>
        <td style="width:40%"></td>
        <td style="width:60%; text-align:center;">
            <b>'.$punongBarangay.'</b><br>
            <span style="font-weight:normal; font-size:11px;">Punong Barangay</span>
        </td>
    </tr>
</table>';

$pdf->writeHTML($signatory, true, false, false, false, '');

// FOOTER DETAILS (OR No.)
$pdf->SetY(-40);
$pdf->SetFont('times', '', 10);
$pdf->Cell(0, 5, "O.R. No: " . ($data['reference_no'] ?? 'N/A'), 0, 1);
$pdf->Cell(0, 5, "Amount Paid: " . number_format($data['amount'] ?? 0, 2), 0, 1);
$pdf->Cell(0, 5, "Date Issued: " . date('Y-m-d'), 0, 1);

$pdf->Output('Certificate_Residency.pdf', 'I');
?>