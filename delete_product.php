<?php
include 'db_connect.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $product_id = intval($_POST['id']);

    // Fetch product images and documents before deleting from DB
    $stmt = $conn->prepare("SELECT image_1, image_2, image_3, image_4, image_5, document_1, document_2 FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Delete images and documents from server
        $files = [
            $product['image_1'], $product['image_2'], $product['image_3'], 
            $product['image_4'], $product['image_5'], $product['document_1'], 
            $product['document_2']
        ];

        foreach ($files as $file) {
            if (!empty($file) && file_exists("uploads/" . $file)) {
                unlink("uploads/" . $file); // Delete file
            }
        }

        // Delete product from database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            echo "Product deleted successfully!";
        } else {
            echo "Error deleting product.";
        }
        $stmt->close();
    } else {
        echo "Product not found.";
    }
}

$conn->close();
?>
