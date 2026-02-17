<?php 
session_start();
$conn = new mysqli("localhost", "root", "085279", "project");

include "db_connect.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to list a product.");
}

$user_id = $_SESSION['user_id']; // ✅ Get user ID from session


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true); // ✅ Create uploads folder if missing
    }

    $images = ["", "", "", "", ""]; // Stores image paths
    $documents = ["", ""]; // Stores document paths

    // Handle image uploads (Up to 5 images)
    for ($i = 0; $i < 6; $i++) {
        $image_key = "image_" . ($i + 1); // Match form input name
        if (!empty($_FILES[$image_key]["name"])) {
            $file_ext = pathinfo($_FILES[$image_key]["name"], PATHINFO_EXTENSION);
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $file_name = time() . "_image_" . ($i + 1) . "." . $file_ext;
                $image_path = $upload_dir . $file_name;
                move_uploaded_file($_FILES[$image_key]["tmp_name"], $image_path);
                $images[$i] = $file_name; // Save only the filename
            }
        }
    }

    // Handle document uploads (Up to 2 documents)
    for ($i = 0; $i < 2; $i++) {
        $doc_key = "document_" . ($i + 1);
        if (!empty($_FILES[$doc_key]["name"])) {
            $file_ext = pathinfo($_FILES[$doc_key]["name"], PATHINFO_EXTENSION);
            if (in_array($file_ext, ['pdf', 'doc', 'docx'])) {
                $file_name = time() . "_doc_" . ($i + 1) . "." . $file_ext;
                $doc_path = $upload_dir . $file_name;
                move_uploaded_file($_FILES[$doc_key]["tmp_name"], $doc_path);
                $documents[$i] = $file_name; // Save only the filename
            }
        }
    }

    // Insert into database with category
    $stmt = $conn->prepare("INSERT INTO products 
    (user_id, product_name, category, price, description, image_1, image_2, image_3, image_4, image_5, document_1, document_2, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("issdssssssss", 
    $user_id, $product_name, $category, $price, $description, 
    $images[0], $images[1], $images[2], $images[3], $images[4], 
    $documents[0], $documents[1]);

    if ($stmt->execute()) {
        echo "<script>alert('Product listed successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error listing product. Please try again.');</script>";
    }

    $stmt->close();
}

$conn->close();
?>
