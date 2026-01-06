<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $redirectPath = ($_SESSION['role'] === 'Staff') 
        ? "../pages/staff/staff_announcement.php" 
        : "../pages/admin/admin_announcement.php";

    $archivePath = ($_SESSION['role'] === 'Staff')
        ? "../pages/staff/staff_announcement_archive.php"
        : "../pages/admin/admin_announcement_archive.php";

    // 1. STATUS UPDATE (Archive/Restore)
    if (isset($_POST['id']) && isset($_POST['status']) && !isset($_POST['title'])) {
        $id = $_POST['id'];
        $status = $_POST['status'];

        try {
            $sql = "UPDATE announcements SET status = :status WHERE announcement_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':status' => $status, ':id' => $id]);
            
            // FIX: Scoped Session Key
            $actionWord = ($status == 'active') ? 'restored' : 'archived';
            $_SESSION['toast_announcement'] = ['msg' => "Announcement $actionWord successfully!", 'type' => 'success'];

            if($status == 'active') {
                header("Location: " . $archivePath);
            } else {
                header("Location: " . $redirectPath);
            }
            exit();

        } catch (PDOException $e) {
            die("Error updating status: " . $e->getMessage());
        }
    }

    // 2. FULL EDIT
    if (isset($_POST['id']) && isset($_POST['title'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $details = $_POST['details'];
        $location = $_POST['location'];
        $date = $_POST['date'];
        $time = $_POST['time'];

        $imageQuery = "";
        $params = [
            ':title' => $title, ':details' => $details, ':location' => $location,
            ':date' => $date, ':time' => $time, ':id' => $id
        ];

        if (!empty($_FILES["photo"]["name"])) {
            $filename = time() . "_" . basename($_FILES["photo"]["name"]);
            $target = "../uploads/announcements/" . $filename;
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target)) {
                $imageQuery = ", image = :image";
                $params[':image'] = $filename;
            }
        }

        try {
            $sql = "UPDATE announcements 
                    SET title = :title, details = :details, location = :location, 
                        date = :date, time = :time $imageQuery
                    WHERE announcement_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            // FIX: Scoped Session Key
            $_SESSION['toast_announcement'] = ['msg' => 'Announcement updated successfully!', 'type' => 'success'];
            header("Location: " . $redirectPath);
            exit(); 

        } catch (PDOException $e) {
            die("Error updating record: " . $e->getMessage());
        }
    }
}
?>