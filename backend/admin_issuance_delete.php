<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['issuance_id'] ?? null;

    if ($id) {
        try {
            $conn->beginTransaction();

            // 1. Burahin muna ang records sa payments table (kung meron) para walang foreign key error
            $stmt1 = $conn->prepare("DELETE FROM payments WHERE issuance_id = :id");
            $stmt1->execute([':id' => $id]);

            // 2. Burahin ang issuance record mismo
            $stmt2 = $conn->prepare("DELETE FROM issuance WHERE issuance_id = :id");
            $stmt2->execute([':id' => $id]);

            $conn->commit();

            // Redirect pabalik sa archive page
            header("Location: ../pages/admin/admin_issuance_archive.php?msg=deleted");
            exit();

        } catch (PDOException $e) {
            $conn->rollBack();
            // Pwede mong i-handle ang error dito o redirect with error msg
            header("Location: ../pages/admin/admin_issuance_archive.php?error=true");
            exit();
        }
    }
}
?>