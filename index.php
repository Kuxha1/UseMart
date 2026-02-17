<?php

/**
 * Project Name: UseMart – Second-Hand Goods Marketplace
 * Author: Kushal Mistry
 * Copyright (c) 2026 Kushal Mistry
 * All rights reserved.
 */

session_start();
include "db_connect.php";

$sql = "SELECT id, product_name, price, image_1 FROM products";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style/home-style.css">
    <title>Homepage</title>
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


<div class="banner">
        <img src="resources/pro-img/banner.png" alt="UseMart Banner">
        <div class="banner-content">
            <h2>UseMart is an innovative platform dedicated to the buying and selling of second-hand goods.</h2>
            <div class="buttons">
                <a href="Profile.php" class="btn">SELL NOW</a>
                <a href="product_search.php" class="btn">SHOP NOW</a>
            </div>
        </div>
    </div>
    
    
    <br>
    <br>
    <p style="text-align: center; color: #2c3e50; font-size: 34px;">Explore Popular Categories<p>
    <div class="categories">
    <a href="product_search.php?category=Electronics" class="category">
        <img src="resources/images/1 (1).png">
        <p>Electronics</p>
    </a>
    <a href="product_search.php?category=Home-Furniture" class="category">
        <img src="resources/images/1 (6).png">
        <p>Furniture</p>
    </a>
    <a href="product_search.php?category=Clothing" class="category">
        <img src="resources/images/1 (3).png">
        <p>Clothes</p>
    </a>
    <a href="product_search.php?category=Books" class="category">
        <img src="resources/images/1 (7).png">
        <p>Books</p>
    </a>
    <a href="product_search.php?category=Toys" class="category">
        <img src="resources/images/1 (4).png">
        <p>Toys</p>
    </a>
    <a href="product_search.php?category=Tools" class="category">
        <img src="resources/images/1 (9).png">
        <p>Tools</p>
    </a>
    <a href="product_search.php?category=Vehicle parts" class="category">
        <img src="resources/images/1 (5).png">
        <p>Vehicle parts</p>
    </a>
    <a href="product_search.php?category=Beauty products" class="category">
        <img src="resources/images/1 (8).png">
        <p>Beauty</p>
    </a>
</div>

<div class="product-section">
    <p class="product-head">Products You May Like</p>
    <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
    <div class="slideshow-container">
        <div class="product-slider">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-card" onclick="window.location.href='product_details.php?id=<?php echo $row['id']; ?>'">
                    <div class="product-image" >
                        <img src="uploads/<?php echo htmlspecialchars($row['image_1']); ?>" alt="Product Image">
                    </div>
                    <p class="product-name"><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></p>
                    <p class="product-price">Price: <?php echo htmlspecialchars($row['price']); ?>rs</p>
                    <button class="buy-now" >BUY NOW</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <button class="next" onclick="moveSlide(1)">&#10095;</button>
</div>

    <div class="info-section">
    <p class="product-head">UseMart Services</p>
        <div class="info-box">
            <img src="resources/pro-img/Start.png" alt="Person typing on laptop">
            <div class="info-text">
                <p>Whatever you're into, it's here. Turn a wrench, get a tech upgrade, and find everything you love at an affordable price on UseMart.</p>
                <button class="info-btn" onclick="redirectToSearch()">Start Here</button>
            </div>
        </div>

        <div class="info-box">
            <div class="info-text">
                <p>Share with the community what you're looking for or notify them about your early listing on UseMart.</p>
                <button class="Share-info-btn" onclick="redirectToMedia()">Share Here</button>
            </div>
            <img src="resources/pro-img/Share.png" alt="Person using a tablet">
        </div>

        <div class="info-box">
            <img src="resources/pro-img/Chat.png" alt="Business handshake">
            <div class="info-text">
                <p>Build your trust and spread your network with customers through a real-time integrated chat feature in UseMart.</p>
                <button class="info-btn" onclick="redirectToChat()">Chat Now</button>
            </div>
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
                    <td><a href="https://www.instagram.com/">Insta</a></td>
                    <td><a href="#">Buyer/Customer</a></td>
                    <td><a href="t&c.html">Terms & Conditions</a></td>
                    <td><a href="seller.php">Seller Info</a></td>
                </tr>
                <tr>
                    <td><a href="https://x.com/?lang=en">X</a></td>
                    <td><a href="seller.php">Seller</a></td>
                    <td><a href="#">Developers</a></td>
                    <td><a href="contactus.php">Contact Us</a></td>
                </tr>
                <tr>
                    <td><a href="https://www.facebook.com/">Facebook</a></td>
                    <td><a href="#">Bugs</a></td>
                    <td><a href="AboutUS.php">Company Info</a></td>
                    <td></td>
                </tr>
                <tr>
                    <td><a href="https://www.reddit.com/">Reddit</a></td>
                    <td><a href="AboutUS.php">Others</a></td>
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
        function redirectToSearch() {
            window.location.href = 'product_search.php';
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

        let index = 0;
        function moveSlide(direction) {
            const slider = document.getElementById("productSlider");
            const totalProducts = document.querySelectorAll(".product-card").length;
            const productWidth = document.querySelector(".product-card").offsetWidth + 20;
            index += direction;

            if (index < 0) index = totalProducts - 1;
            if (index >= totalProducts) index = 0;

            slider.style.transform = `translateX(${-index * productWidth}px)`;
        }let currentIndex = 0;

function moveSlide(direction) {
    const slider = document.querySelector(".product-slider");
    const totalSlides = document.querySelectorAll(".product-card").length;
    const visibleSlides = 3; // Number of visible slides

    currentIndex += direction;

    if (currentIndex < 0) {
        currentIndex = totalSlides - visibleSlides;
    } else if (currentIndex > totalSlides - visibleSlides) {
        currentIndex = 0;
    }

    const offset = -currentIndex * (180 + 20); // Width of card + margin
    slider.style.transform = `translateX(${offset}px)`;
}

// Auto slide every 3 seconds
setInterval(() => {
    moveSlide(4);
}, 3000);


    </script>
    
</body>
</html>