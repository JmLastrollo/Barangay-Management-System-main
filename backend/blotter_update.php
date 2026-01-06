<?php
session_start();
require_once "db_connect.php";
require_once "log_audit.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['blotter_id'])) {
    
    $id = $_POST['blotter_id'];

    // --- ARCHIVE ACTION ---
    if (isset($_POST['action']) && $_POST['action'] === 'archive') {
        try {
            $stmt = $conn->prepare("UPDATE blotter_records SET status_archive = 'Archived' WHERE blotter_id = :id");
            $stmt->execute([':id' => $id]);

            if (isset($_SESSION['user_id'])) {
                logActivity($conn, $_SESSION['user_id'], "Archived blotter case #$id");
            }

            $_SESSION['toast'] = ['type' => 'warn', 'msg' => 'Case moved to archives.'];
            header("Location: ../pages/admin/admin_rec_blotter.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Error archiving: ' . $e->getMessage()];
            header("Location: ../pages/admin/admin_rec_blotter.php");
            exit();
        }
    }

    // --- UPDATE ACTION ---
    $status = $_POST['status'];
    $narrative = $_POST['narrative'];
    $hearing_sched = NULL;

    // Kung Hearing, kunin ang schedule
    if ($status === 'Hearing' && !empty($_POST['hearing_schedule'])) {
        $hearing_sched = $_POST['hearing_schedule'];
    }

    try {
        $sql = "UPDATE blotter_records SET status = :stat, narrative = :narr, hearing_schedule = :sched WHERE blotter_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':stat' => $status,
            ':narr' => $narrative,
            ':sched' => $hearing_sched,
            ':id' => $id
        ]);

        if (isset($_SESSION['user_id'])) {
            logActivity($conn, $_SESSION['user_id'], "Updated blotter case #$id status to $status");
        }

        $_SESSION['toast'] = ['type' => 'info', 'msg' => 'Case details updated successfully.'];

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Update failed: ' . $e->getMessage()];
    }

    header("Location: ../pages/admin/admin_rec_blotter.php");
    exit();
}
?>