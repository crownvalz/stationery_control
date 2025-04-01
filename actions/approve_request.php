<?php
include './db_conn.php';

// Approve Request
function approveRequest($conn) {
    $status = checkRequestStatus($_POST['approve_id'], $conn);
    if ($status === 'Approved') {
        return; // If already approved, do nothing
    }

    // Fetch request details
    $request = $conn->prepare("SELECT status, item_name, quantity FROM requests WHERE id = :id");
    $request->bindParam(':id', $_POST['approve_id'], PDO::PARAM_INT);
    $request->execute();
    $requestData = $request->fetch(PDO::FETCH_ASSOC);

    // Fetch stock quantity
    $stockStmt = $conn->prepare("SELECT stock_quantity FROM stock WHERE item_name = :item_name");
    $stockStmt->bindParam(':item_name', $requestData['item_name'], PDO::PARAM_STR);
    $stockStmt->execute();
    $stock = $stockStmt->fetchColumn();

    // If stock is available, deduct and approve request
    if ($stock >= $requestData['quantity']) {
        $updateStockStmt = $conn->prepare("UPDATE stock SET stock_quantity = stock_quantity - :quantity WHERE item_name = :item_name");
        $updateStockStmt->bindParam(':quantity', $requestData['quantity'], PDO::PARAM_INT);
        $updateStockStmt->bindParam(':item_name', $requestData['item_name'], PDO::PARAM_STR);
        $updateStockStmt->execute();

        // Check if stock is now zero
        $checkStockStmt = $conn->prepare("SELECT stock_quantity FROM stock WHERE item_name = :item_name");
        $checkStockStmt->bindParam(':item_name', $requestData['item_name'], PDO::PARAM_STR);
        $checkStockStmt->execute();
        $updatedStock = $checkStockStmt->fetchColumn();

        if ($updatedStock == 0) {
            // Update status to 'Depleted'
            $updateStatusStmt = $conn->prepare("UPDATE stock SET stock_status = 'Depleted' WHERE item_name = :item_name");
            $updateStatusStmt->bindParam(':item_name', $requestData['item_name'], PDO::PARAM_STR);
            $updateStatusStmt->execute();
        }

        updateRequestStatus($_POST['approve_id'], 'Approved', $conn);
    } else {
        updateRequestStatus($_POST['approve_id'], 'Depleted', $conn);
    }
}
  ?>