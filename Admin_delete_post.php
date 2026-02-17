<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $post_id = intval($_POST['id']);

    // Fetch post images before deleting
    $stmt = $conn->prepare("SELECT image_1, image_2 FROM community_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();

    if ($post) {
        // Delete images from the server
        $files = [$post['image_1'], $post['image_2']];
        foreach ($files as $file) {
            if (!empty($file) && file_exists("uploads/" . $file)) {
                unlink("uploads/" . $file);
            }
        }

        // Delete post from database
        $stmt = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute()) {
            echo "Post deleted successfully!";
        } else {
            echo "Error deleting post.";
        }
        $stmt->close();
    } else {
        echo "Post not found.";
    }
}

$conn->close();
?>
