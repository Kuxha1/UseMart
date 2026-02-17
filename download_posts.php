<?php
include 'db_connect.php'; // Ensure database connection

// Fetch all community posts
$result = $conn->query("SELECT * FROM community_posts");

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create file content
$data = "Community Posts Data:\n\n";
while ($row = $result->fetch_assoc()) {
    $data .= "ID: " . $row['id'] . "\n";
    $data .= "User ID: " . $row['user_id'] . "\n";
    $data .= "Topic: " . $row['topic'] . "\n";
    $data .= "Content: " . $row['content'] . "\n";
    $data .= "Created At: " . $row['created_at'] . "\n";
    
    // Add image paths if available
    if (!empty($row["image_1"])) {
        $data .= "Image 1: uploads/" . $row["image_1"] . "\n";
    }
    if (!empty($row["image_2"])) {
        $data .= "Image 2: uploads/" . $row["image_2"] . "\n";
    }

    $data .= "----------------------------\n";
}

// Set headers for file download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="community_posts.txt"');
echo $data;
exit;
?>
