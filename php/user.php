<?php
// This function fetches user info from the database based on the user's ID
function getUserById($id, $conn){
    try {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() == 1){
            return $user;  // Return user data if found
        } else {
            return false;  // Return false if no user is found
        }
    } catch (PDOException $e) {
        // Return a message if there is an error in the database operation
        return "Error: " . $e->getMessage();
    }
}
?>