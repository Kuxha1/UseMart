<?php
// Include database connection
session_start();
include "db_connect.php";

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<p>No product ID provided!</p>");
}


$product_id = intval($_GET['id']); // Securely get product ID

// Fetch product details
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Check if product exists
if (!$product) {
    die("<p>No product found!</p>");
}

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['report_error'] = "You must be logged in to report a product.";
    } else {
        $reporter_id = $_SESSION['user_id']; // Ensure user is logged in
        $reported_product_id = $product_id;
        $description = trim($_POST['description']);

        if (!empty($description)) {
            $insert_query = "INSERT INTO reports (reporter_id, reported_product_id, description) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iis", $reporter_id, $reported_product_id, $description);
            if ($stmt->execute()) {
                $_SESSION['report_success'] = "Product has been reported successfully.";
            } else {
                $_SESSION['report_error'] = "Failed to submit report. Try again.";
            }
        } else {
            $_SESSION['report_error'] = "Please provide a valid report description.";
        }
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: product_details.php?id=" . $product_id);
    exit();
}


$seller_id = $product['user_id']; // Make sure $product is properly fetched
$loggedInUserId = $_SESSION['user_id'] ?? null; // Get logged-in user ID

$is_logged_in = isset($_SESSION['user_id']);
$is_own_product = $is_logged_in && $_SESSION['user_id'] == $product['user_id']; // Now using $product safely


$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist page</title>
    <link rel="stylesheet" href="style/prodetails-style.css">

    <div class="header">
        <div class="usemart">
       <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToHome()">
       <h1 style="margin-top:9px; margin-left:10px;">UseMart</h1>
       </div>

       <form action="product_search.php" method="GET">
       <div class="search-container">
       <input type="text" name="query" placeholder="Search for products..." required>
           <button type="submit" class="search-icon">
               <img src="resources/icons/Search.png" alt="Search" class="icon">
           </button>
       </div>
       </form>
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

<div class="split">


        <div class="doc-image-container">


             <!-- Slideshow Container -->
        <div class="slideshow-container">
            <?php 
            $images = [
                $product['image_1'], 
                $product['image_2'], 
                $product['image_3'], 
                $product['image_4'], 
                $product['image_5']
            ];
            $imageIndex = 0;
            foreach ($images as $image) {
                if (!empty($image)) {
                    echo "<div class='slide ".($imageIndex === 0 ? "active" : "")."'>
                            <img src='uploads/" . htmlspecialchars($image) . "' alt='Product Image'>
                          </div>";
                    $imageIndex++;
                }
            }
            ?>
            <button class="prev" onclick="prevSlide()">&#10094;</button>
            <button class="next" onclick="nextSlide()">&#10095;</button>
        </div>
           
   </div>
 

    <div class="info-container">

        <div class="info-head">
        <h2 class="name"><?php echo htmlspecialchars($product['product_name']); ?></h2>
         
        </div>
                
                <h2><strong>Price:</strong> <?php echo htmlspecialchars($product['price']); ?> Rs</h2>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                <p ><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>

                <button class="buy-now">BUY NOW</button>
                <!-- Add to Cart Button -->
<?php if (!$is_logged_in): ?>
    <button onclick="showLoginAlert()" class="add-to-cart">ADD TO CART</button>
<?php elseif ($is_own_product): ?>
    <button onclick="showOwnProductAlert()" class="add-to-cart">ADD TO CART</button>
<?php else: ?>
    <button type="button" class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>, <?php echo $_SESSION['user_id'] ?? 'null'; ?>, <?php echo $product['user_id']; ?>)">
    ADD TO CART
    </button>

<?php endif; ?>

            <!-- Wishlist Button -->
<?php if (!$is_logged_in): ?>
    <button onclick="showLoginAlert()" class="doc-button">WISHLIST</button>
<?php elseif ($is_own_product): ?>
    <button onclick="showOwnProductAlert()" class="doc-button">WISHLIST</button>
<?php else: ?>
    <button type="button" class="doc-button" onclick="addToWishlist(<?php echo $product['id']; ?>, <?php echo $product['user_id']; ?>, <?php echo $_SESSION['user_id'] ?? 'null'; ?>)">
    WISHLIST
    </button>

<?php endif; ?>

            

           <!-- Product Bill Button -->
<?php if (!empty($product['document_1'])): ?>
    <button class="doc-button" onclick="openPDF('<?php echo htmlspecialchars('uploads/' . $product['document_1']); ?>')">
        PRODUCT BILL
    </button>
<?php else: ?>
    <button class="doc-button" disabled>PRODUCT BILL (Not Available)</button>
<?php endif; ?>

<!-- Other Document Button -->
<?php if (!empty($product['document_2'])): ?>
    <button class="doc-button" onclick="openPDF('<?php echo htmlspecialchars('uploads/' . $product['document_2']); ?>')">
        OTHER DOCUMENT
    </button>
<?php else: ?>
    <button class="doc-button" disabled>OTHER DOCUMENT (Not Available)</button>
<?php endif; ?>

            <div>
            <button class="message-button" onclick="viewProfile(<?php echo $seller_id; ?>)">SELLER PROFILE</button>
            <button class="report-button" onclick="openReportModal()">REPORT</button>
            </div>
            
        </div>
</div>
<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Report Product</h3><br>
        <form method="POST">
            <textarea name="description" placeholder="Describe the issue" rows="5" cols="50" required></textarea><br>
            <button type="submit" name="submit_report" class="sub-rep-button">Submit Report</button>
        </form>
    </div>
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
                    <td><a href="t&c.html">Terms & Conditions</a></td>
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

    <script>
 // Function to open the modal
 function openReportModal() {
        document.getElementById('reportModal').style.display = 'flex';
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById('reportModal').style.display = 'none';
    }

    // Close modal when clicking outside the modal content
    window.onclick = function(event) {
        let modal = document.getElementById('reportModal');
        if (event.target === modal) {  // Check if clicked outside the modal content
            closeModal();
        }
    };
        let index = 0;
        const slides = document.querySelectorAll('.slide');

        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            index = (n + slides.length) % slides.length;
            slides[index].classList.add('active');
        }

        function nextSlide() {
            showSlide(index + 1);
        }

        function prevSlide() {
            showSlide(index - 1);
        }
        function showLoginAlert() {
            alert("You must login to perform this action.");
        }

        function showOwnProductAlert() {
            alert("You cannot add your own product to your cart/wishlist.");
        }

        function redirectToLogin() {
            window.location.href = "login.php";
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
        function redirectToDetails() {
            window.location.href = 'user_details.php';
        }

        function viewProfile(sellerId) {
    let loggedInUserId = <?= json_encode($loggedInUserId) ?>; // Get the logged-in user ID

    if (sellerId == loggedInUserId) {
        alert("You are viewing your own profile!"); // Optional alert
        return; // Prevent redirection
    }

    window.location.href = "user_profile.php?user_id=" + sellerId;
}

function addToWishlist(productId, ownerId, userId) {
    if (!userId || userId === "null") {
        alert("Please log in to add items to your wishlist.");
        return;
    }

    if (ownerId == userId) {  
        alert("Error: You cannot add your own product to your wishlist.");
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "wishlist_handler.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText);  // Display response message
        }
    };

    xhr.send("product_id=" + productId);
}


function addToCart(productId, userId, sellerId) { 
    if (!userId || userId === "null") {
        alert("Please log in to add items to your cart.");
        return;
    }

    if (userId == sellerId) {
        alert("Error: You cannot add your own product to your cart.");
        return;
    }

    // AJAX request to add item to cart
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "cart_handler.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert(xhr.responseText); // Display server response in an alert
        }
    };

    xhr.send("product_id=" + productId);
}

function openPDF(pdfUrl) {
    window.open(pdfUrl, '_blank');
}



    </script>
    <!-- Display Success or Error Message -->
<?php if (isset($_SESSION['report_success'])): ?>
    <script>
        alert("<?php echo $_SESSION['report_success']; ?>");
    </script>
    <?php unset($_SESSION['report_success']); // Clear message after showing ?>
<?php endif; ?>

<?php if (isset($_SESSION['report_error'])): ?>
    <script>
        alert("<?php echo $_SESSION['report_error']; ?>");
    </script>
    <?php unset($_SESSION['report_error']); // Clear message after showing ?>
<?php endif; /**
 * Project Name: UseMart – Second-Hand Goods Marketplace
 * Author: Kushal Mistry
 * Copyright (c) 2026 Kushal Mistry
 * All rights reserved.
 */?>

</body>
</html>