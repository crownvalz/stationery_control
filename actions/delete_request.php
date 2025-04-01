<?php
include './db_conn.php';

// Delete Request
function deleteRequest($conn) {
    $status = checkRequestStatus($_POST['delete_id'], $conn);
    if ($status !== 'Approved') {
        $stmt = $conn->prepare("DELETE FROM requests WHERE id = :id");
        $stmt->bindParam(':id', $_POST['delete_id'], PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>