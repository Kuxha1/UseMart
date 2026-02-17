<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_POST["sender_id"] ?? 0;
    $receiver_id = $_POST["receiver_id"] ?? 0;
    $message = trim($_POST["message"] ?? "");

    if (!empty($message) && $sender_id > 0 && $receiver_id > 0) {
        $stmt = $conn->prepare("INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);

        if ($stmt->execute()) {
            echo "Message sent successfully!";
        } else {
            echo "Database Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Invalid message data";
    }
}
?>
