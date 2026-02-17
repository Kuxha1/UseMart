<?php

/**
 * Project Name: UseMart – Second-Hand Goods Marketplace
 * Author: Kushal Mistry
 * Copyright (c) 2026 Kushal Mistry
 * All rights reserved.
 */

session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
}else{

    $user_id = $_SESSION['user_id'];
    $success_msg = "";
    $error_msg = "";
    
    // Fetch user details
    $sql = "SELECT fullname, email, contact_no, address, description, profile_photo FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Handle profile update
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $contact_no = trim($_POST['contact_no']);
        $address = trim($_POST['address']);
        $description = trim($_POST['description']);
        $profile_photo = $user['profile_photo']; // Default to existing photo
    
        // Profile photo upload handling
        if (!empty($_FILES["profile_photo"]["name"])) {
            $target_dir = "uploads/";
            
            // Ensure 'uploads/' directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create directory if not exists
            }
            
            $file_name = time() . "_" . basename($_FILES["profile_photo"]["name"]);
            $target_file = $target_dir . $file_name;
    
            // Check if the file was uploaded successfully
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                $profile_photo = $target_file;
            } else {
                echo "<p style='color:red;'>Error: Failed to upload profile photo.</p>";
            }
        }
    
        // Check if password change is requested
        if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET fullname = ?, email = ?, contact_no = ?, address = ?, description = ?, profile_photo = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("sssssssi", $fullname, $email, $contact_no, $address, $description, $profile_photo, $hashed_password, $user_id);
            } else {
                echo "<script>alert('Passwords do not match.');</script>";
                exit();
            }
        } else {
            $update_sql = "UPDATE users SET fullname = ?, email = ?, contact_no = ?, address = ?, description = ?, profile_photo = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssssssi", $fullname, $email, $contact_no, $address, $description, $profile_photo, $user_id);
        }
    
        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully!');</script>";
        } else {
            echo "<script>alert('Error updating profile. Please try again.');</script>";
        }

}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/profile-style.css">
    <title>Profile Page</title>
    <div class="header">
         <div class="usemart">
        <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToHome()">
        <h1 style="margin-top:9px; margin-left:10px;">UseMart</h1>
        </div>
        <div class="icons">
        <button class="icon-button" onclick="redirectToMedia()">Community</button>
            <button class="icon-button" onclick="redirectToChat()">Chat</button>
            <button class="icon-button" onclick="redirectToWish()">Wishlist</button>
            <button class="icon-button" onclick="redirectToCart()">Cart</button>
            <button class="icon-button" onclick="redirectToProfile()"><img src="resources/icons/Vector-4.png" alt="User" class="icon"></button>
            <button class="icon-button" onclick="redirectToDetails()"><img src="resources/icons/Vector-5.png" alt="Menu" class="icon"></button>
        </div>
    </div>

</head>
<body>

    <?php
    if (!isset($_SESSION['user_id'])) { ?>

        <div style="text-align: center; padding: 50px;">
            <h2>You are not logged in</h2>
            <p>Please log in or register to access your profile.</p>
            <button onclick="redirectToLogin()" class="login-button">Login / Register</button>
        </div>
        <footer class="footer">
            <table class="footer-table">
                <tr>
                    <th>Social Media</th>
                    <th>Report</th>
                    <th>About Us</th>
                    <th>Help & Contact</th>
                </tr>
                <tr>
                    <td><a href="#">Insta</a></td>
                    <td><a href="#">Buyer/Customer</a></td>
                    <td><a href="#">Terms & Conditions</a></td>
                    <td><a href="#">Seller Info</a></td>
                </tr>
                <tr>
                    <td><a href="#">X</a></td>
                    <td><a href="#">Seller</a></td>
                    <td><a href="#">Developers</a></td>
                    <td><a href="#">Contact Us</a></td>
                </tr>
                <tr>
                    <td><a href="#">Facebook</a></td>
                    <td><a href="#">Bugs</a></td>
                    <td><a href="#">Company Info</a></td>
                    <td></td>
                </tr>
                <tr>
                    <td><a href="#">Reddit</a></td>
                    <td><a href="#">Others</a></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><a href="#">Discord</a></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </footer>
        <?php } else { ?>

<!-- Left Section: Profile Details -->            
    <div class="split">       
    <div class="profile-container">
        <div class="centered">
            <h1 style="color:#2c3e50;">Personal Info</h1>
            <img src="<?php echo htmlspecialchars($user['profile_photo'] ?: 'resources/pfp.png'); ?>" class="profile-photo" id="profile-img">
            <h2 style="color:#2c3e50;"><?php echo htmlspecialchars($user['fullname']); ?></h2>
            <div class="profile-card">
                <p><strong><?php echo htmlspecialchars($user['description']); ?></strong></p><br>
                <p><strong>Gmail:</strong> <?php echo htmlspecialchars($user['email']); ?></p><br>
                <p><strong>Contact no:</strong> <?php echo htmlspecialchars($user['contact_no']); ?></p><br>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                <img src="resources/icons/Vector-6.png" alt="User" class="edit-button" onclick="openModal()" height="15px" width="15px" style="margin-left: 280px; margin-top: 10px;">
            </div>
            <button class="logout-button" onclick="logout()">Log Out</button>
        </div>
    </div>
    
    
        <!-- Edit Profile Modal -->
        <div id="editModal" class="editmodal">
        <div class="editmodal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="color: #fff; text-align: center;">Edit Profile</h2>
            <br>
            <form method="post" enctype="multipart/form-data">
                Profile Photo:<input type="file" name="profile_photo" class="file-input-field" accept="image/png,image/jpg"><br>

                Name:<input type="text" name="fullname" class="input-field" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

                Email:<input type="email" name="email" class="input-field" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                Contact No:<input type="text" name="contact_no" class="input-field" value="<?php echo htmlspecialchars($user['contact_no']); ?>" required>

                Address:<input type="text" name="address" class="input-field" value="<?php echo htmlspecialchars($user['address']); ?>" required>

                Description:<input type="text" name="description" class="input-field" value="<?php echo htmlspecialchars($user['description']); ?>" required>

                <!-- Change Password Section -->
                New Password (Leave blank to keep current password):<input type="password" name="new_password" class="input-field">

                Confirm New Password:<input type="password" name="confirm_password" class="input-field">

                <button type="submit" class="update-button">Update Profile</button>
            </form>
        </div>
    </div>

    
<!-- Right Section: Product Listing -->
<div class="product-container">
        <div class="product-header">
        <h2 class="listing-title">MY ITEM ON SELL</h2>
        <button class="sell-item-button" onclick="openProductModal()">Sell Item</button>
    </div>
    <br><br><br>
        <div class="product-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-item">
                    <img src="uploads/<?php echo $row['image']; ?>" alt="Product">
                    <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                    <p>Price: $<?php echo htmlspecialchars($row['price']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>


        <div class="product-list">
        <?php
        include "db_connect.php";

        $user_id = $_SESSION['user_id'];  

        // Fetch user's products from the database
        $query = "SELECT * FROM products WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()): ?>
                <div class="product-item" onclick="window.location.href='product_details.php?id=<?php echo $row['id']; ?>'">
                    <div class="product-image">
                    <?php
                    // Display the first available image
                    $images = [$row['image_1'], $row['image_2'], $row['image_3'], $row['image_4'], $row['image_5']];
                    $firstImage = '';

                    foreach ($images as $img) {
                    if (!empty($img)) {
                    $firstImage = "uploads/" . htmlspecialchars($img);
                    break; // Stop at the first non-empty image
                 }}?>
                 <img src="<?php echo $firstImage; ?>" alt="Product Image">
                    </div>
                    <div class="product-details">
                        <h2><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></h2>
                        <p><strong>Price:</strong> <?php echo htmlspecialchars($row['price']); ?>Rs</p>
                        <p><strong>Info:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                    </div>
                    <div class="product-actions">
                        <button class="delete-product-button" onclick="deletelistedProduct(<?php echo $row['id']; ?>)"><img src="resources/icons/Vector-7.png" style="height: 20px;width: 15px;"/></button>
                    </div>
                </div>
            <?php endwhile; else: ?>
            <p class="no-products">You haven't listed any products yet.</p>
        <?php endif; ?>

    </div>
</div>

<!-- Product Listing Modal -->
<div id="productModal" class="list-modal">
    <div class="list-modal-content">
        <span class="close" onclick="closeProductModal()">&times;</span>
        <center>
        <h2 style="color: #fff;">Upload Product Details</h2>
        <form action="list_product.php" method="POST" enctype="multipart/form-data">

            <!-- Slideshow for Image Preview -->
            <div class="slideshow-container">
                <button class="prev" onclick="changeSlide(-1)" type="button">&#10094;</button>
                <img id="slide-1" class="slide">
                <img id="slide-2" class="slide">
                <img id="slide-3" class="slide">
                <img id="slide-4" class="slide">
                <img id="slide-5" class="slide">
                <button class="next" onclick="changeSlide(1)" type="button">&#10095;</button>
            </div>

            <label style="color: #fff; font-size: 20px">Images (Max 5):</label><br>
            <input style="color: #fff;" type="file" name="image_1" accept="image/*" onchange="previewImage(event, 1)" required>
            <input style="color: #fff;" type="file" name="image_2" accept="image/*" onchange="previewImage(event, 2)" required>
            <input style="color: #fff;" type="file" name="image_3" accept="image/*" onchange="previewImage(event, 3)">
            <input style="color: #fff;" type="file" name="image_4" accept="image/*" onchange="previewImage(event, 4)">
            <input style="color: #fff;" type="file" name="image_5" accept="image/*" onchange="previewImage(event, 5)"><br><br>
            <table>
                <tr>
                    <td><input type="text" name="product_name" class="sell-input-field" placeholder="Enter Product name"></td>
                    <td><input type="number" name="price" class="sell-input-field" step="100" placeholder="Enter price"></td>
                    <td><select style="color: #2c3e50;" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Toys">Toys</option>
                        <option value="Home-Furniture">Home & Furniture</option>
                        <option value="Sports-Fitness">Sports & Fitness</option>
                        <option value="Books">Books</option>
                        <option value="Beauty products">Beauty products</option>
                        <option value="jewellery">jewellery</option>
                        <option value="Tools"> Tools</option>
                        <option value="Vehicle parts">Vehicle parts</option>
                        <option value="Others">Others</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td colspan="3"><textarea name="description"  placeholder="  Enter Item details" rows="3" cols="130"></textarea></td>
                </tr>
            </table>

            <label style="color: #fff; font-size: 20px">Item Documents (Max 2 - PDF/DOCX):</label>
            <input style="color: #fff;" type="file" name="document_1" accept=".pdf,.doc,.docx" required>
            <input style="color: #fff;" type="file" name="document_2" accept=".pdf,.doc,.docx"><br>

            <button class="upload-product" type="submit">List Product</button>
        </form>
        </center>
    </div>
</div>


    <?php } ?>
    <script>

        let currentSlide = 0;
        let imageCount = 0;


        function openProductModal() {
            document.getElementById('productModal').style.display = 'block';
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

         function openModal() {
            document.getElementById("editModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }

        // Close modal if user clicks outside it
        window.onclick = function(event) {
            var editModal = document.getElementById("editModal");
            var productModal = document.getElementById("productModal");

        if (event.target == editModal) {
            editModal.style.display = "none";
        }

        if (event.target == productModal) {
            productModal.style.display = "none";
        }
        }

        function redirectToHome() {
            window.location.href = 'index.php';
        }
        function redirectToMedia() {
            window.location.href = 'community_form.php';
        }
        function redirectToChat() {
            window.location.href = 'Chat.php';
        }
        function redirectToWish() {
            window.location.href = 'Wishlist.php';
        }
        function redirectToCart() {
            window.location.href = 'Cart.php';
        }
        function redirectToProfile() {
            window.location.href = 'Profile.php';
        } 
        function logout() {
            window.location.href = 'logout.php';
        } 
        function redirectToLogin() {
            window.location.href = "login.php";
        }
        function redirectToDetails() {
            window.location.href = 'user_details.php';
        }
        function validatePasswords() {
            var newPassword = document.querySelector('input[name="new_password"]').value;
            var confirmPassword = document.querySelector('input[name="confirm_password"]').value;
    
            if (newPassword !== "" && newPassword !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
        }
        return true;
        }
        //Listing Form with Image Slideshow Preview

        function previewImage(event, index) {
            const reader = new FileReader();
            reader.onload = function () {
                const slide = document.getElementById(`slide-${index}`);
                slide.src = reader.result;
                slide.style.display = "block";

                // Show slideshow container only when images are added
                document.querySelector(".slideshow-container").style.display = "block";

                imageCount = Math.max(imageCount, index + 1);
                currentSlide = index;
                showSlide(currentSlide);
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function changeSlide(step) {
            currentSlide += step;
            if (currentSlide >= imageCount) currentSlide = 1;
            if (currentSlide < 0) currentSlide = imageCount - 1;
            showSlide(currentSlide);
        }

        function showSlide(index) {
            const slides = document.querySelectorAll(".slide");
            slides.forEach((slide, i) => {
                slide.style.display = i === index ? "block" : "none";
            });
        }

        function deletelistedProduct(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        fetch(`delete_listed_product.php?id=${productId}`, { method: "GET" })
        .then(response => response.text())
        .then(data => {
            alert(data); // Show success/error message
            location.reload(); // Reload page to reflect changes
        })
        .catch(error => console.error("Error deleting product:", error));
    }
}



    </script>

</body>
</html>
