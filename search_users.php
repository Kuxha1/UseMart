<?php
include "db_connect.php";
session_start();

$user_id = $_SESSION["user_id"];
$query = $_GET["query"];

// Fetch users who have chat history OR match the search query
$users = $conn->query("
    SELECT id, fullname, profile_photo 
    FROM users 
    WHERE (id IN (
        SELECT sender_id FROM chats WHERE receiver_id = $user_id
        UNION 
        SELECT receiver_id FROM chats WHERE sender_id = $user_id
    ) OR fullname LIKE '%$query%') 
    AND id != $user_id
");

if ($users->num_rows > 0) {
    while ($row = $users->fetch_assoc()) {
        $profile_photo = !empty($row['profile_photo']) ? $row['profile_photo'] : 'resources/pfp.png';
        echo "<div class='user' onclick=\"selectUser('{$row['fullname']}', '{$profile_photo}', '{$row['id']}')\">";
        echo "<img src='{$profile_photo}' alt='User'> <h3>{$row['fullname']}</h3>";
        echo "</div>";
    }
} else {
    echo "<p>No users found. Search to start a conversation.</p>";
}
?>
