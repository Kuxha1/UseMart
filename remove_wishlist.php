<?php
session_start();
include "db_connect.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in.");
}

$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    die("Error: Invalid product ID.");
}

$user_id = $_SESSION['user_id'];

// Delete product from wishlist
$stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);

if ($stmt->execute()) {
    echo "Product removed from wishlist successfully!";
} else {
    echo "Error removing product from wishlist.";
}

$stmt->close();
$conn->close();
?>
