<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_POST["sender_id"] ?? 0;
    $receiver_id = $_POST["receiver_id"] ?? 0;

    if ($sender_id > 0 && $receiver_id > 0) {
        $stmt = $conn->prepare("
            SELECT chats.message, chats.timestamp, users.fullname, users.id 
            FROM chats 
            JOIN users ON chats.sender_id = users.id
            WHERE (chats.sender_id = ? AND chats.receiver_id = ?) 
               OR (chats.sender_id = ? AND chats.receiver_id = ?)
            ORDER BY chats.timestamp ASC
        ");
        $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $class = ($row["id"] == $sender_id) ? "sent" : "received";
                echo "<div class='message $class'><b>" . htmlspecialchars($row["fullname"]) . ":</b> " . htmlspecialchars($row["message"]) . "</div>";
            }
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Invalid chat request";
    }
}
?>
