<?php
require_once "db_connect.php";

// Check if Request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. STATUS UPDATE (Archive/Restore)
    // Check kung may ID at STATUS pero WALANG TITLE (ibig sabihin status update lang to)
    if (isset($_POST['id']) && isset($_POST['status']) && !isset($_POST['title'])) {
        $id = $_POST['id'];
        $status = $_POST['status'];

        try {
            $sql = "UPDATE announcements SET status = :status WHERE announcement_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':status' => $status, ':id' => $id]);
            
            // Redirect based on status
            if($status == 'active') {
                // RESTORE ACTION:
                // Binago ko ito from '?restored=true' to '?success=restored'
                // para basahin ito ng JavaScript sa Archive page.
                header("Location: ../pages/admin/admin_announcement_archive.php?success=restored");
            } else {
                // ARCHIVE ACTION:
                // Ito okay lang na 'archived=true' kasi yun ang nasa Main Page JS mo.
                header("Location: ../pages/admin/admin_announcement.php?archived=true");
            }
            exit();

        } catch (PDOException $e) {
            die("Error updating status: " . $e->getMessage());
        }
    }

    // 2. FULL EDIT (Form Submit)
    if (isset($_POST['id']) && isset($_POST['title'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $details = $_POST['details'];
        $location = $_POST['location'];
        $date = $_POST['date'];
        $time = $_POST['time'];

        // Handle Image Upload
        $imageQuery = "";
        $params = [
            ':title' => $title,
            ':details' => $details,
            ':location' => $location,
            ':date' => $date,
            ':time' => $time,
            ':id' => $id
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
                    SET title = :title, 
                        details = :details, 
                        location = :location, 
                        date = :date, 
                        time = :time 
                        $imageQuery
                    WHERE announcement_id = :id";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            header("Location: ../pages/admin/admin_announcement.php?success=updated");
            exit(); 

        } catch (PDOException $e) {
            die("Error updating record: " . $e->getMessage());
        }
    }
}
?>