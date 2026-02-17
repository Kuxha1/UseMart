<?php
session_start();

include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
}else{

    $user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["pay_now"])) {
    $conn->begin_transaction(); // Start transaction

    try {
        // Get cart items
        $sql = "SELECT products.id AS product_id, products.price, products.user_id AS seller_id 
                FROM cart 
                JOIN products ON cart.product_id = products.id 
                WHERE cart.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Get payment details from form
            $cardholder_name = $_POST['card-name'];
            $card_number = $_POST['card-number'];
            $expiry_date = $_POST['expiry'];
            $cvv = $_POST['cvv'];
            $billing_address = $_POST['billing-address'];
            $total_price = 0; // Initialize total price

            // Hash card details for security
            $card_number_hash = password_hash($card_number, PASSWORD_BCRYPT);
            $cvv_hash = password_hash($cvv, PASSWORD_BCRYPT);

            while ($row = $result->fetch_assoc()) {
                $product_id = $row["product_id"];
                $price = $row["price"];
                $seller_id = $row["seller_id"];
                $total_price += $price; // Calculate total price

                // Insert into orders table
                $order_sql = "INSERT INTO orders (user_id, product_id, price) VALUES (?, ?, ?)";
                $order_stmt = $conn->prepare($order_sql);
                $order_stmt->bind_param("iid", $user_id, $product_id, $price);
                $order_stmt->execute();

                // Insert into sales table
                $sales_sql = "INSERT INTO sales (seller_id, buyer_id, product_id, price) VALUES (?, ?, ?, ?)";
                $sales_stmt = $conn->prepare($sales_sql);
                $sales_stmt->bind_param("iiid", $seller_id, $user_id, $product_id, $price);
                $sales_stmt->execute();
            }

            // Insert into payments table
            $payment_sql = "INSERT INTO payments (user_id, cardholder_name, card_number_hash, expiry_date, cvv_hash, billing_address, total_price) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $payment_stmt = $conn->prepare($payment_sql);
            $payment_stmt->bind_param("isssssd", $user_id, $cardholder_name, $card_number_hash, $expiry_date, $cvv_hash, $billing_address, $total_price);
            $payment_stmt->execute();

            // Clear the cart
            $delete_cart_sql = "DELETE FROM cart WHERE user_id = ?";
            $delete_cart_stmt = $conn->prepare($delete_cart_sql);
            $delete_cart_stmt->bind_param("i", $user_id);
            $delete_cart_stmt->execute();

            $conn->commit(); // Commit transaction

            echo "<script>alert('Order and Payment processed successfully!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('No items in the cart.'); window.location.href='cart.php';</script>";
        }
    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        echo "<script>alert('Transaction failed: " . $e->getMessage() . "');</script>";
    } finally {
        $stmt->close();
        $conn->close();
    }
}
    
    // Fetch cart items for display
    $sql = "SELECT products.id AS product_id, products.product_name, products.price, products.image_1 
            FROM cart 
            JOIN products ON cart.product_id = products.id 
            WHERE cart.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cart_items = [];
    $total_price = 0;
    
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_price += $row['price'];
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart page</title>
    <link rel="stylesheet" href="style/cart-style.css">

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
            <p>Please log in or register to access your Cart .</p>
            <button onclick="redirectToLogin()" class="login-button">Login / Register</button>
            <br><br><br><br><br><br><br>
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

<div class="split">

<!-- Cart Section -->
<div class="split">
    <!-- Left Side: Shopping Cart -->
    <div class="List-container">
        <h2>Your Shopping Cart</h2><br>
        <?php if (!empty($cart_items)): ?>
            <div class="cart-container">
                <?php foreach ($cart_items as $row): ?>
                <div class="cart-item" onclick="window.location.href='product_details.php?id=<?php echo $row['product_id']; ?>'">
                    <img src="uploads/<?php echo !empty($row['image_1']) ? htmlspecialchars($row['image_1']) : 'default.jpg'; ?>" alt="Product Image">
                        <div class="cart-details">
                            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                            <p><strong>Price:</strong> <?php echo htmlspecialchars($row['price']); ?>rs</p>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $row['product_id']; ?>)">&#10005;</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: white; font-size:32px;">Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <!-- Right Side: Price Summary -->
    <div class="pricelist-container">
        <p >Your Total</p>
        <?php if (!empty($cart_items)): ?>
            <?php foreach ($cart_items as $row): ?>
                <div class="price-item">
                    <span><?php echo htmlspecialchars($row['product_name']); ?> price:-</span>
                    <span><?php echo number_format($row['price'], 2); ?>rs</span>
                </div>
            <?php endforeach; ?>

            <!-- Divider -->
            <div class="price-divider"></div>

            <!-- Total Price -->
            <div class="total-price">
                TOTAL:- <?php echo number_format($total_price, 2); ?> Rs
            </div>

            <button class="pay-btn" type="button" onclick="openPaymentModal()">Payment</button> 
            
            
        <?php else: ?>
            <p style="text-align: center; color: white; font-size:32px;">No items in cart.</p>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closePaymentModal()">&times;</span>
        <h2>Payment Details</h2><br><br>

        <form class="payment-form"  method="post" action="Cart.php"> 
            <label for="card-name">Cardholder Name</label>
            <input type="text" id="card-name" name="card-name" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">

            <label for="card-number">Card Number</label>
            <input type="text" id="card-number" name="card-number" required pattern="\d{16}" title="Enter a 16-digit card number" maxlength="16">

            <label for="expiry">Expiry Date</label>
            <input type="month" id="expiry" name="expiry" required>

            <label for="cvv">CVV</label>
            <input type="password" id="cvv" name="cvv" required pattern="\d{3}" title="Enter a 3-digit CVV" maxlength="3">

            <label for="billing-address">Billing Address</label>
            <input type="text" id="billing-address" name="billing-address" required>

            <div class="total-price">
                TOTAL:- <?php echo number_format($total_price, 2); ?> Rs
            </div>

            <button class="payNow" type="submit" name="pay_now">Pay Now</button>
            <button class="CloseNow" type="button" onclick="closePaymentModal()">Close</button>
        </form>
    </div>
</div>


<?php } ?>
<script>

// Function to open the payment modal
function openPaymentModal() {
    document.getElementById('paymentModal').style.display = 'flex'; // Show modal
}

// Function to close the payment modal
function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none'; // Hide modal
}

// Close modal when clicking outside the modal content
window.onclick = function(event) {
    let modal = document.getElementById('paymentModal');
    if (event.target === modal) {
        closePaymentModal();
    }
};

function removeFromCart(productId) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "remove_from_cart.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert(xhr.responseText);
            window.location.reload(); // Reload cart page
        }
    };

    xhr.send("product_id=" + productId);
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

    </script>
</body>
</html>
