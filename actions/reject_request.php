<?php
include './db_conn.php';

// Reject Request
function rejectRequest($conn) {
    $status = checkRequestStatus($_POST['reject_id'], $conn);
    if ($status !== 'Approved') {
        updateRequestStatus($_POST['reject_id'], 'Rejected', $conn);
    }
}
?>