<?php
session_start();
include "db_connect.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in.");
}

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    die("Error: Invalid product ID.");
}

// Ensure the user owns this product before deleting
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $product_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Error: Product not found.");
}

// Delete from database
$delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$delete_stmt->bind_param("i", $product_id);

if ($delete_stmt->execute()) {
    echo "Product deleted successfully!";
} else {
    echo "Error deleting product. Please try again.";
}
?>
