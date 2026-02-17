<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_id"])) {
    $post_id = $_POST["post_id"];
    $user_id = $_SESSION["user_id"];

    // Ensure the post belongs to the logged-in user
    $sql = "DELETE FROM community_posts WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);

    if ($stmt->execute()) {
        header("Location: community_form.php?filter=my_posts"); // Redirect to "My Posts"
        exit();
    } else {
        echo "Error deleting post.";
    }
}
?>
