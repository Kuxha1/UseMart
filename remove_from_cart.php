<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    echo "Error: Not logged in";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;

if ($product_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    if ($stmt->execute()) {
        echo "Product removed from cart!";
    } else {
        echo "Error: Could not remove product.";
    }
} else {
    echo "Error: Product ID missing.";
}
?>
