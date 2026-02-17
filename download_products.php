<?php
include 'db_connect.php'; // Ensure database connection

// Fetch all product data
$result = $conn->query("SELECT * FROM products");

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create file content
$data = "Product Data:\n\n";
while ($row = $result->fetch_assoc()) {
    $data .= "ID: " . $row['id'] . "\n";
    $data .= "User ID: " . $row['user_id'] . "\n";
    $data .= "Name: " . $row['product_name'] . "\n";
    $data .= "Price: $" . $row['price'] . "\n";
    $data .= "Description: " . $row['description'] . "\n";
    $data .= "Category: " . $row['category'] . "\n";
    $data .= "Created At: " . $row['created_at'] . "\n";
    
    // Add image paths if available
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($row["image_$i"])) {
            $data .= "Image $i: uploads/" . $row["image_$i"] . "\n";
        }
    }

    // Add document paths if available
    for ($i = 1; $i <= 2; $i++) {
        if (!empty($row["document_$i"])) {
            $data .= "Document $i: uploads/" . $row["document_$i"] . "\n";
        }
    }

    $data .= "----------------------------\n";
}

// Set headers for download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="products_data.txt"');
echo $data;
exit;
?>
