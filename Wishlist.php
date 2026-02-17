<?php
session_start();

include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
}else{

    $user_id = $_SESSION['user_id'];

    // **Part 1: Fetch Wishlist Items Before Closing Statement**
    // Fetch wishlist items with product details

    $query = "SELECT p.id, p.product_name, p.price, p.image_1, p.image_2
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ?";


$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$wishlist_items = [];
while ($row = $result->fetch_assoc()) {
    $wishlist_items[$row['id']]['product_name'] = $row['product_name'];
    $wishlist_items[$row['id']]['price'] = $row['price'];
    
    // Ensure both images are stored correctly
    $wishlist_items[$row['id']]['images'] = [];
    if (!empty($row['image_1'])) {
        $wishlist_items[$row['id']]['images'][] = "uploads/" . $row['image_1'];
    }
    if (!empty($row['image_2'])) {
        $wishlist_items[$row['id']]['images'][] = "uploads/" . $row['image_2'];
    }
}


    $stmt->close(); // Close statement after fetching the data
    
    // **Part 2: Add to Wishlist (Only if product_id is given)**
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
    
        // Validate Product ID
        $check_owner_stmt = $conn->prepare("SELECT user_id FROM products WHERE id = ?");
        $check_owner_stmt->bind_param("i", $product_id);
        $check_owner_stmt->execute();
        $owner_result = $check_owner_stmt->get_result();
        $product = $owner_result->fetch_assoc();
        $check_owner_stmt->close();
    
        if (!$product) {
            die("Error: Product not found.");
        }
    
        // Prevent user from adding their own product
        if ($product['user_id'] == $user_id) {
            die("Error: You cannot add your own product to your wishlist.");
        }
    
        // Check if the product is already in the wishlist
        $check_wishlist_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check_wishlist_stmt->bind_param("ii", $user_id, $product_id);
        $check_wishlist_stmt->execute();
        $check_wishlist_stmt->store_result();
    
        if ($check_wishlist_stmt->num_rows > 0) {
            die("Error: Product is already in your wishlist.");
        }
        $check_wishlist_stmt->close();
    
        // Add product to wishlist
        $insert_stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $user_id, $product_id);
    
        if ($insert_stmt->execute()) {
            echo "Product added to wishlist successfully!";
        } else {
            echo "Error adding product to wishlist.";
        }
    
        $insert_stmt->close();
    }
    
    $conn->close();


    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist page</title>
    <link rel="stylesheet" href="style/wishlist-style.css">

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
    <?php
    if (!isset($_SESSION['user_id'])) { ?>

        <div style="text-align: center; padding: 50px;">
            <h2>You are not logged in</h2>
            <p>Please log in or register to access your Wishlist .</p>
            <button onclick="redirectToLogin()" class="login-button">Login / Register</button>
            <br><br><br><br><br><br>
        </div>
        <?php } else { ?>

            
<div class="wishlist-container">
    <h1>Wishlist</h1>
    <?php if (!empty($wishlist_items)): ?>
        <?php foreach ($wishlist_items as $product_id => $product): ?>
            <div class="wishlist-item" onclick="window.location.href='product_details.php?id=<?php echo $product_id; ?>'">
                <div class="product-images">
                <?php 
                    // Show only 2 images per product
                    $count = 0;
                    foreach ($product['images'] as $image): 
                        if ($count < 2): ?>
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Product Image" width="200px">
                        <?php 
                        $count++;
                        endif;
                    endforeach; 
                    ?>
                </div>
                <strong class="product-details"><?php echo htmlspecialchars($product['product_name']); ?></strong>
                <p class="product-details">Price: <?php echo number_format($product['price'], 2); ?> Rs</p>
                <div class="buttons">
                <button class="add-to-cart" onclick="addToCart(<?php echo $product_id; ?>)">
                    ADD TO CART</button>
                <button class="buy-now">BUY NOW</button>
                </div>
                <button class="remove-btn" onclick="removeFromWishlist(<?php echo $product_id; ?>)">&#10005;</button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; color: white; font-size:32px;">Your wishlist is empty.</p><br><br><br><br><br><br><br><br><br><br>
    <?php endif; ?>
</div>



    <?php } ?>

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

    <script>
        function removeFromWishlist(productId) {
        if (confirm("Are you sure you want to remove this product from your wishlist?")) {
            fetch(`remove_wishlist.php?product_id=${productId}`, { method: "GET" })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                })
                .catch(error => console.error("Error removing product:", error));
        }
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

        var userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

        function addToCart(productId) { 
    if (typeof userId === 'undefined' || userId === null) {
        alert("Please log in to add items to cart.");
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "cart_handler.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            let response = JSON.parse(xhr.responseText);
            alert(response.message);
        }
    };

    xhr.send("product_id=" + productId);
}




    </script>
</body>
</html>
