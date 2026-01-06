<?php
require_once '../../../backend/db_connect.php';
require_once '../../../backend/tcpdf/tcpdf.php'; 

if (!isset($_GET['id'])) die("Error: ID missing.");
$id = $_GET['id'];

try {
    $sql = "SELECT i.*, r.first_name, r.middle_name, r.last_name, i.purpose
            FROM issuance i
            JOIN resident_profiles r ON i.resident_id = r.resident_id
            WHERE i.issuance_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) die("Record not found.");

    $fullName = strtoupper($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']);
    $purpose = strtoupper($data['purpose']);
    $day = date('jS');
    $monthYear = date('F Y');
    $punongBarangay = "HON. ENRICO SANGO"; 

} catch (PDOException $e) { die($e->getMessage()); }

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
$pdf->Cell(0, 10, 'CERTIFICATE OF INDIGENCY', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('times', '', 12);

$html = '
<p><strong>TO WHOM IT MAY CONCERN:</strong></p>
<br><br>
<p style="text-align:justify; line-height: 1.5;">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify that <b>'.$fullName.'</b>, of legal age, Filipino, is a resident of Barangay Langkaan II, Dasmariñas City.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to certify further that the above-named person belongs to an <b>INDIGENT FAMILY</b> in this barangay.
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This certification is being issued upon the request of the interested party for the purpose of:
<br><br>
<div style="text-align:center; font-weight:bold;">>> '.$purpose.' <<</div>
<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Issued this <b>'.$day.'</b> day of <b>'.$monthYear.'</b> at Barangay Langkaan II, Dasmariñas City, Cavite.
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

$pdf->Output('Certificate_Indigency.pdf', 'I');
?>