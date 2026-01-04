<?php
require_once 'db_connect.php';
require_once 'fpdf186/fpdf.php'; // Siguraduhing tama ang path sa FPDF mo

if (!isset($_GET['token'])) {
    die("Access Denied: No token provided.");
}

$token = $_GET['token'];
$current_time = date('Y-m-d H:i:s');

try {
    // 1. Check Database
    $stmt = $conn->prepare("SELECT * FROM issuance WHERE print_token = :token");
    $stmt->execute([':token' => $token]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Validations
    if (!$data) {
        die("Error: Invalid Document Token.");
    }

    if ($data['download_count'] >= 1) {
        die("Security Alert: This document has already been printed/downloaded once. For security reasons, re-printing is disabled.");
    }

    if ($data['print_expiry'] < $current_time) {
        die("Error: The printing link has expired. It was only valid for 2 days.");
    }

    // 3. MARK AS DOWNLOADED (Burn the token)
    // Ito ang mag-l-lock sa file para di na maulit
    $update = $conn->prepare("UPDATE issuance SET download_count = download_count + 1 WHERE issuance_id = :id");
    $update->execute([':id' => $data['issuance_id']]);

    // 4. GENERATE PDF (Dito ilalagay ang FPDF code mo)
    // Example lang ito, kopyahin mo yung logic mula sa admin_issuance_print.php
    class PDF extends FPDF {
        function Header() {
            // Logo, Title, etc.
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,'BARANGAY OFFICIAL DOCUMENT',0,1,'C');
            $this->Ln(10);
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);
    
    $pdf->Cell(0,10,'Resident: ' . $data['resident_id'], 0, 1); // Note: I-join mo sa resident table para makuha name
    $pdf->Cell(0,10,'Document: ' . $data['document_type'], 0, 1);
    $pdf->Cell(0,10,'Purpose: ' . $data['purpose'], 0, 1);
    $pdf->Ln(20);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0,10,'OFFICIAL COPY - ISSUED ONLINE', 0, 1, 'C');

    // Force Download or Preview
    $pdf->Output('I', 'Barangay_Document.pdf'); 

} catch (Exception $e) {
    die("System Error: " . $e->getMessage());
}
?>