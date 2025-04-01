<?php
include './db_conn.php';

// Pickup Request
function pickupRequest($conn) {
    $status = checkRequestStatus($_POST['pick_id'], $conn);
    if ($status !== 'Approved') {
        updateRequestStatus($_POST['pick_id'], 'Pickup', $conn);
    }
}
?>