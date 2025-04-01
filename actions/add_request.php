<?php
include './db_conn.php';

// Add Request
function addRequest($conn) {
    if (!empty($_POST['item_name']) && intval($_POST['quantity']) > 0) {
        $stmt = $conn->prepare("INSERT INTO requests (item_name, quantity) VALUES (:item_name, :quantity)");
        $stmt->bindParam(':item_name', $_POST['item_name'], PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $_POST['quantity'], PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>