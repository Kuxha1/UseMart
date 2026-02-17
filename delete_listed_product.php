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

// Remove product from all wishlists
$wishlist_delete_stmt = $conn->prepare("DELETE FROM wishlist WHERE product_id = ?");
$wishlist_delete_stmt->bind_param("i", $product_id);
$wishlist_delete_stmt->execute();
$wishlist_delete_stmt->close();

// Remove product from all carts
$cart_delete_stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
$cart_delete_stmt->bind_param("i", $product_id);
$cart_delete_stmt->execute();
$cart_delete_stmt->close();

// Delete the product from the database
$delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$delete_stmt->bind_param("i", $product_id);

if ($delete_stmt->execute()) {
    echo "Product deleted successfully, and it has been removed from all wishlists!";
} else {
    echo "Error deleting product. Please try again.";
}

$delete_stmt->close();
$conn->close();
?>
