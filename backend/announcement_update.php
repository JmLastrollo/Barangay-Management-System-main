<?php
session_start();
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST["id"];
    // Optional fields for Edit, ignored if Restore
    $title = $_POST["title"] ?? null;
    $details = $_POST["details"] ?? null;
    $location = $_POST["location"] ?? null;
    $date = $_POST["date"] ?? null;
    $time = $_POST["time"] ?? null;
    $status = $_POST["status"] ?? 'active';

    try {
        if ($title === null) {
            // Restore Mode
            $sql = "UPDATE announcements SET status = :status WHERE announcement_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':status' => $status, ':id' => $id]);
        } else {
            // Edit Mode
            $sql = "UPDATE announcements SET title=:title, details=:details, location=:location, date=:date, time=:time, status=:status";
            $params = [
                ':title' => $title, ':details' => $details, ':location' => $location,
                ':date' => $date, ':time' => $time, ':status' => $status, ':id' => $id
            ];

            if (!empty($_FILES["photo"]["name"])) {
                $uploadDir = "../uploads/announcements/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filename = time() . "_" . basename($_FILES["photo"]["name"]);
                move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadDir . $filename);
                $sql .= ", image = :img";
                $params[':img'] = $filename;
            }
            $sql .= " WHERE announcement_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        $_SESSION['toast'] = ['msg' => 'Success!', 'type' => 'success'];
    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }

    // FIX: Redirect using ../ instead of ../../
    if ($status === 'archived') {
        header("Location: ../pages/admin/admin_announcement_archive.php");
    } else {
        header("Location: ../pages/admin/admin_announcement.php");
    }
    exit;
}
?>