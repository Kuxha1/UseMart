<?php
session_start();
include "db_connect.php";

header('Content-Type: text/plain'); // Send plain text response

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;

if (!$product_id) {
    echo "Invalid product ID.";
    exit();
}

// Check if the product exists and get the owner ID
$stmt = $conn->prepare("SELECT user_id FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($seller_id);
$stmt->fetch();
$stmt->close();

if (!$seller_id) {
    echo "Product not found.";
    exit();
}

// Prevent user from adding their own product
if ($seller_id == $user_id) {
    echo "You cannot add your own product to your cart.";
    exit();
}

// Check if the product is already in the cart
$check_stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
$check_stmt->bind_param("ii", $user_id, $product_id);
$check_stmt->execute();
$check_stmt->store_result();
$already_exists = $check_stmt->num_rows;
$check_stmt->close();

if ($already_exists > 0) {
    echo "Item is already in your cart.";
    exit();
}

// Add product to cart
$insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, added_at) VALUES (?, ?, NOW())");
$insert_stmt->bind_param("ii", $user_id, $product_id);

if ($insert_stmt->execute()) {
    echo "Item added to cart.";
} else {
    echo "Failed to add item to cart.";
}

$insert_stmt->close();
$conn->close();
exit();
