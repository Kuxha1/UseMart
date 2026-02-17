<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About UseMart</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #1E3823;
            color: white;
            overflow-x: hidden;
        }
        /* Header Styles */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background-color: #16C47F;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo {
            height: 60px;
            cursor: pointer;
        }
        .usemart {
            display: flex;
            align-items: center;
            color: white;
        }
        .usemart h1 {
            margin-left: 10px;
        }
        .search-container {
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
            border-radius: 25px;
            padding: 0.5rem 1rem;
            width: 400px;
            border: 1px solid #e0e0e0;
        }
        .search-container input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            padding: 0.5rem;
            font-size: 1rem;
        }
        .search-icon {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }
        .search-icon img {
            width: 20px;
            height: 20px;
        }
        .header-buttons {
            display: flex;
            gap: 20px;
        }
        .icon-button {
            color: #ffffff;
            font-size: 16px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            transition: transform 0.2s;
        }
        .icon-button:hover {
            transform: scale(1.1);
        }
        .icon {
            width: 20px;
            height: 20px;
        }

        /* About Section */
        .container2 {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px;
            background: #1E3823;
        }
        .text-section {
            width: 50%;
            padding-right: 30px;
        }
        .text-section h2 {
            font-size: 36px;
            font-weight: bold;
            color: #A1E6A2;
        }
        .text-section p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .image-section {
            width: 50%;
            text-align: center;
        }
        .image-section img {
            width: 350px;
            border-radius: 10px;
        }

        /* Who We Are Section */
        .who-we-are {
            display: flex;
            align-items: center;
            padding: 50px;
            background: white;
            color: black;
            flex-direction: row-reverse;
        }
        .who-we-are .text-content {
            width: 50%;
            padding-left: 30px;
        }
        .who-we-are h2 {
            font-size: 36px;
            font-weight: bold;
            color: #1E3823;
        }
        .who-we-are .highlight {
            color: #A1E6A2;
        }
        .who-we-are p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .who-we-are .image-container {
            width: 50%;
            display: flex;
            justify-content: center;
        }
        .who-we-are .image-container img {
            width: 350px;
            height: 350px;
            border-radius: 50%;
            object-fit: cover;
        }
         .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #ffffff;
            font-size: 16px;
        }
        .footer {
    margin-top: 20px;
    background-color: #2c3e50;
    color: white;
    padding: 3rem 2rem;
}

.footer-table {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.footer-table th {
    text-align: left;
    padding-bottom: 1.5rem;
    font-size: 1.2rem;
}

.footer-table td {
    padding: 0.5rem 0;
}

.footer-table a {
    color: #bdc3c7;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-table a:hover {
    color: white;
}
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="usemart">
        <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToHome()">
        <h1>UseMart</h1>
    </div>
    <form action="product_search.php" method="GET">
        <div class="search-container">
            <input type="text" name="query" placeholder="Search for products..." required>
            <button type="submit" class="search-icon">
                <img src="resources/icons/Search.png" alt="Search">
            </button>
        </div>
    </form>
    <div class="header-buttons">
        <button class="icon-button" onclick="redirectToCommunity()">Community</button>
        <button class="icon-button" onclick="redirectToChat()">Chat</button>
        <button class="icon-button" onclick="redirectToWish()">Wishlist</button>
        <button class="icon-button" onclick="redirectToCart()">Cart</button>
        <button class="icon-button" onclick="redirectToProfile()">
            <img src="resources/icons/Vector-4.png" alt="User" class="icon">
        </button>
        <button class="icon-button">
            <img src="resources/icons/Vector-5.png" alt="Menu" class="icon">
        </button>
    </div>
</div>

<!-- About UseMart Section -->
<div class="container2"> 
    <div class="text-section">
        <h2>About Use Mart</h2>
        <p>We are a global leader in facilitating trade.</p>
        <p>We build leading marketplace ecosystems enabled by tech, powered by trust, and loved by customers.</p>
    </div>
    <div class="image-section">
        <img src="resources/ss1.png" alt="About Use Mart">
    </div>
</div>

<!-- Who We Are Section -->
<div class="who-we-are">
    <div class="text-content">
        <h2><span class="highlight">Who we are</span> and what we do</h2>
        <p>Serving hundreds of millions of people every month, we help people buy and sell cars, find housing, buy and sell household goods, and much more.</p>
        <p>Our well-loved consumer brands offer safe, smart, and convenient trading platforms and services for our customers.</p>
    </div>
    <div class="image-container">
        <img src="resources/ss2.png" alt="Who we are">
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
            <td><a href="https://www.instagram.com/neel_0530/" target="_blank">Insta</a></td>
            <td><a href="#">Buyer/Customer</a></td>
            <td><a href="t&c.php">Terms & Conditions</a></td>
            <td><a href="seller.php">Seller Info</a></td>
        </tr>
        <tr>
            <td><a href="https://x.com/i/flow/login?redirect_after_login=%2Fi%2Fflow%2Flogin" target="_blank">X</a></td>
            <td><a href="#">Seller</a></td>
            <td><a href="#">Developers</a></td>
            <td><a href="contactus.php">Contact Us</a></td>
        </tr>
        <tr>
            <td><a href="https://www.facebook.com/" target="_blank">Facebook</a></td>
            <td><a href="#">Bugs</a></td>
            <td><a href="AboutUS.php">Company Info</a></td>
            <td></td>
        </tr>
        <tr>
            <td><a href="https://www.reddit.com/?rdt=42984" target="_blank">Reddit</a></td>
            <td><a href="#">Others</a></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td><a href="https://discord.com/" target="_blank">Discord</a></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

<script>
    function redirectToHome() {
        window.location.href = "index.php";
    }
    function redirectToCommunity() {
        window.location.href = "community_form.php";
    }
    function redirectToChat() {
        window.location.href = "Chat.php";
    }
    function redirectToWish() {
        window.location.href = "Wishlist.php";
    }
    function redirectToCart() {
        window.location.href = "Cart.php";
    }
    function redirectToProfile() {
        window.location.href = "profile.php";
    }
</script>

</body>
</html>
