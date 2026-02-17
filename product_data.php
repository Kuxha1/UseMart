<?php
include 'db_connect.php'; // Database connection

// Fetch all products
$result = $conn->query("SELECT * FROM products");

if (!$result) {
    die("Query failed: " . $conn->error);
}
// Handle AJAX requests for fetching images
if (isset($_GET['action']) && $_GET['action'] == 'get_images' && isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $query = $conn->prepare("SELECT image_1, image_2, image_3, image_4, image_5 FROM products WHERE id = ?");
    $query->bind_param("i", $product_id);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    
    // Prepend "uploads/" to each image path
    $images = array_filter([
        !empty($result['image_1']) ? 'uploads/' . $result['image_1'] : null,
        !empty($result['image_2']) ? 'uploads/' . $result['image_2'] : null,
        !empty($result['image_3']) ? 'uploads/' . $result['image_3'] : null,
        !empty($result['image_4']) ? 'uploads/' . $result['image_4'] : null,
        !empty($result['image_5']) ? 'uploads/' . $result['image_5'] : null
    ]);
    
    echo json_encode($images);
    exit;
}


// Handle deleting all images
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_images') {
    $product_id = $_POST['id'];
    $update = $conn->prepare("UPDATE products SET image_1 = NULL, image_2 = NULL, image_3 = NULL, image_4 = NULL, image_5 = NULL WHERE id = ?");
    $update->bind_param("i", $product_id);
    
    if ($update->execute()) {
        echo "All images deleted successfully!";
    } else {
        echo "Error deleting images.";
    }
    exit;
}

// Handle deleting all documents
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_documents') {
    $product_id = $_POST['id'];
    $update = $conn->prepare("UPDATE products SET document_1 = NULL, document_2 = NULL WHERE id = ?");
    $update->bind_param("i", $product_id);
    
    if ($update->execute()) {
        echo "All documents deleted successfully!";
    } else {
        echo "Error deleting documents.";
    }
    exit;
}

// Update product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $product_name = $_POST['product_name'];
    $category = $_POST['category']; // Added category retrieval
    $price = $_POST['price'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE products SET product_name=?, category=?, price=?, description=? WHERE id=?");
    $stmt->bind_param("ssdsi", $product_name, $category, $price, $description, $id); // Corrected binding order

    if ($stmt->execute()) {
        echo "Product updated successfully!";
    } else {
        echo "Error updating product.";
    }

    $stmt->close();
    $conn->close();
    header("Location: product_data.php");
    exit();
}
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style/table_data.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Data</title>
    <div class="header">
        <div class="usemart">
       <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToadmin()">
       <h1 style="margin-top:9px; margin-left:10px;">Admin Dashboard</h1>
       </div>
       <button class="logout-button" onclick="logout()">Log Out</button>
    </div>
    <style>
        .modal, .slideshow {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }
        .modal-content, .slideshow-content {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            width: 40%;
            position: relative;
            text-align: center;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
        .image-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-container img {
            max-width: 100%;
            max-height: 400px;
        }
        .nav-btn {
            background: #555;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }
        .nav-btn:hover {
            background: #777;
        }
        .delete-btn {
            background: red;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
    </style>  
</head>
<body>
<div class="sidebar">
    <a href="admin_report.php">Reports</a>
        <a href="order_data.php">Orders Data</a>
        <a href="sells_data.php">sells Data</a>
        <a href="user_data.php">Users Data</a>
        <a href="product_data.php">Products Data</a>
        <a href="post_data.php">Posts Data</a>
        <a href="cart_data.php">Users cart Data</a>
        <a href="wishlist_data.php">Wishlist data</a>
        <a href="Payment_data.php">Payment data</a>
    </div>   

<!-- Heading Section -->
<div class="Heading">
            <div class="h">All Products</div>
            <button class="downloadData-btn" onclick="downloadProductsData()">Download Data</button>
        </div>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Category</th>
            <th>Description</th>
            <th>Images</th>
            <th>Documents</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr id="product-<?php echo $row['id']; ?>">
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['product_name']; ?></td>
            <td><?php echo $row['price']; ?></td>
            <td><?php echo $row['category']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <button onclick="openSlideshow(<?php echo $row['id']; ?>)">View Images</button>
                <button class="delete-btn" onclick="deleteAllImages(<?php echo $row['id']; ?>)">Delete All Images</button>
            </td>
            <td>
                <?php
                if (!empty($row["document_1"])) echo "<a href='uploads/{$row["document_1"]}' target='_blank'>Document 1</a><br>";
                if (!empty($row["document_2"])) echo "<a href='uploads/{$row["document_2"]}' target='_blank'>Document 2</a><br>";
                ?>
                <button class="delete-btn" onclick="deleteAllDocuments(<?php echo $row['id']; ?>)">Delete All Documents</button>
            </td>
            <td>
                <button class="Edit" onclick="editProduct(<?php echo $row['id']; ?>)">Edit</button>
                <button class="delete" onclick="deleteProduct(<?php echo $row['id']; ?>)">Delete</button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Edit Product Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Product</h2>
        <form method="POST" action="">
            <input type="hidden" name="id" id="editId">
            Product Name: <input type="text" name="product_name" id="editProductName"><br>
            Price: <input type="number" step="0.01" name="price" id="editPrice"><br>
            Category: <input type="text" name="category" id="editProductcategory"><br>
            Description: <input type="text" name="description" id="editDescription"><br>
            <button type="submit">Update</button>
        </form>
    </div>
</div>

<!-- Image Slideshow Modal -->
<div id="slideshowModal" class="slideshow">
    <div class="slideshow-content">
        <span class="close" onclick="closeSlideshow()">&times;</span>
        <h2>Product Images</h2>
        <div class="image-container">
            <button class="nav-btn" onclick="prevImage()">&#10094;</button>
            <img id="slideshowImage" src="" alt="Image">
            <button class="nav-btn" onclick="nextImage()">&#10095;</button>
        </div>
    </div>
</div>

<script>
    
function logout() {
    window.location.href = 'logout.php';
}

function redirectToadmin(){
    window.location.href = 'admin_report.php';
}


function editProduct(productId) {
    let row = document.getElementById(`product-${productId}`);
    let productName = row.children[1].innerText;
    let price = row.children[2].innerText;
    let category = row.children[3].innerText; // Fixed typo: innerTsxt → innerText
    let description = row.children[4].innerText;

    document.getElementById('editId').value = productId;
    document.getElementById('editProductName').value = productName;
    document.getElementById('editPrice').value = price;
    document.getElementById('editProductcategory').value = category; // Fixed incorrect variable assignment
    document.getElementById('editDescription').value = description;

    document.getElementById('editModal').style.display = 'flex';
}


function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    let modal = document.getElementById('editModal');
    let slideshowModal = document.getElementById('slideshowModal');

    // Close Edit Modal when clicking outside
    if (event.target === modal) {
        closeEditModal();
    }

    // Close Slideshow when clicking outside
    if (event.target === slideshowModal) {
        closeSlideshow();
    }
};

function deleteProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        fetch('delete_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${productId}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            let row = document.getElementById(`product-${productId}`);
            if (row) row.remove();
        })
        .catch(error => console.error("Error:", error));
    }
}

function deleteAllImages(productId) {
    fetch('product_data.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_images&id=${productId}`
    }).then(response => response.text()).then(alert);
}

function deleteAllDocuments(productId) {
    fetch('product_data.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_documents&id=${productId}`
    }).then(response => response.text()).then(alert);
}


let images = [];
let currentIndex = 0;

function openSlideshow(productId) {
    fetch(`product_data.php?action=get_images&id=${productId}`)
    .then(response => response.json())
    .then(data => {
        if (data.length > 0) {
            images = data;
            currentIndex = 0;
            document.getElementById("slideshowImage").src = images[currentIndex];
            document.getElementById("slideshowModal").style.display = 'flex';
        } else {
            alert("No images available!");
        }
    });
}


function closeSlideshow() {
    document.getElementById("slideshowModal").style.display = 'none';
}

function prevImage() {
    if (currentIndex > 0) {
        currentIndex--;
        document.getElementById("slideshowImage").src = images[currentIndex];
    }
}

function nextImage() {
    if (currentIndex < images.length - 1) {
        currentIndex++;
        document.getElementById("slideshowImage").src = images[currentIndex];
    }
}

function downloadProductsData() {
    window.location.href = 'download_products.php';
}

</script>

</body>
</html>
