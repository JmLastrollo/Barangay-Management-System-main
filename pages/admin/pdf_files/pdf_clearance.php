<?php
require_once '../../../backend/db_connect.php';
require_once '../../../backend/tcpdf/tcpdf.php'; 

if (!isset($_GET['id'])) die("Error: ID missing.");
$id = $_GET['id'];

try {
    $sql = "SELECT i.*, r.first_name, r.middle_name, r.last_name, r.birth_date, 
                   r.civil_status, r.purok, r.street, i.purpose, p.amount, p.reference_no
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
    
    // Age Calc
    $age = 'N/A';
    if($data['birth_date']) {
        $bday = new DateTime($data['birth_date']);
        $today = new DateTime();
        $age = $today->diff($bday)->y;
    }

    $civilStatus = strtoupper($data['civil_status']);
    $address = strtoupper("Block " . $data['purok'] . " Lot " . $data['street'] . ", Brgy. Langkaan II");
    $purpose = strtoupper($data['purpose']);
    $dateIssued = date('jS \d\a\y \o\f F Y');
    $punongBarangay = "HON. ENRICO SANGO"; 

} catch (PDOException $e) { die($e->getMessage()); }

// Custom Header Class
class MYPDF extends TCPDF {
    public function Header() {
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
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);
$pdf->SetMargins(25, 50, 25);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

$pdf->SetFont('times', 'B', 24);
$pdf->Cell(0, 10, 'BARANGAY CLEARANCE', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('times', '', 12);

$html = '
<p><strong>TO WHOM IT MAY CONCERN:</strong></p>
<br><br>
<p style="text-align:justify; line-height: 1.5;">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that <b>'.$fullName.'</b>, '.$age.' years old, '.$civilStatus.', is a bonafide resident of '.$address.'.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This certifies further that the above-named person has <b>NO DEROGATORY RECORD</b> on file in this Barangay as of this date.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This certification is issued upon the request of the interested party for the purpose of: <b>'.$purpose.'</b>.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Issued this '.$dateIssued.' at Barangay Langkaan II, Dasmariñas City, Cavite.
</p>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Ln(30);
$pdf->SetFont('times', 'B', 12);
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

$pdf->SetY(-40);
$pdf->SetFont('times', '', 10);
$pdf->Cell(0, 5, "O.R. No: " . ($data['reference_no'] ?? 'N/A'), 0, 1);
$pdf->Cell(0, 5, "Amount Paid: " . number_format($data['amount'] ?? 0, 2), 0, 1);
$pdf->Cell(0, 5, "Date Issued: " . date('Y-m-d'), 0, 1);

$pdf->Output('Barangay_Clearance.pdf', 'I');
?>