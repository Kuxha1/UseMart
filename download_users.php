<?php
include 'db_connect.php';

// Fetch user data from the database
$result = $conn->query("SELECT id, fullname, email, address, contact_no, description FROM users");

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Prepare the text file content
$data = "ID\tFull Name\tEmail\tAddress\tContact No\tDescription\n";
while ($row = $result->fetch_assoc()) {
    $data .= "{$row['id']}\t{$row['fullname']}\t{$row['email']}\t{$row['address']}\t{$row['contact_no']}\t{$row['description']}\n";
}

// Set headers to force download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="users_data.txt"');

// Output the data
echo $data;
exit;
?>
