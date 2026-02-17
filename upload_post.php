<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $topic = htmlspecialchars($_POST["topic"]);
    $content = htmlspecialchars($_POST["content"]);

    // Validate content length (max 300 characters)
    if (strlen($content) > 3000) {
        die("Error: Content exceeds 300 characters. Please shorten your post.");
    }

    $image_1 = "";
    $image_2 = "";

    // Image Upload Handling
    if (!empty($_FILES["image_1"]["name"])) {
        $image_1 = "uploads/" . basename($_FILES["image_1"]["name"]);
        move_uploaded_file($_FILES["image_1"]["tmp_name"], $image_1);
    }

    if (!empty($_FILES["image_2"]["name"])) {
        $image_2 = "uploads/" . basename($_FILES["image_2"]["name"]);
        move_uploaded_file($_FILES["image_2"]["tmp_name"], $image_2);
    }

    // Insert into Database
    $sql = "INSERT INTO community_posts (user_id, topic, content, image_1, image_2) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $topic, $content, $image_1, $image_2);

    if ($stmt->execute()) {
        header("Location: community_form.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
