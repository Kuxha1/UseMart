<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell on UseMart</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #50B498;
            color: white;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #16C47F;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo {
            height: 50px;
            cursor: pointer;
        }
        .usemart {
            display: flex;
            align-items: center;
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            margin-left: 10px;
        }
        .search-container {
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
            border-radius: 25px;
            padding: 5px 10px;
            width: 300px;
        }
        .search-container input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            padding: 5px;
            font-size: 14px;
        }
        .search-icon img {
            width: 18px;
            height: 18px;
        }
        .header-buttons {
            display: flex;
            gap: 15px;
        }
        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #ffffff;
            font-size: 16px;
        }
        .icon {
            width: 18px;
            height: 18px;
        }
        .container {
            max-width: 900px;
            margin: 80px auto 40px;
            background: #2c3e50;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
        }
        h2 {
            font-size: 22px;
            border-bottom: 2px solid white;
            padding-bottom: 5px;
            margin-top: 20px;
        }
        p {
            line-height: 1.6;
            font-size: 16px;
            margin: 10px 0;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin: 5px 0;
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
    <div class="header">
        <div class="usemart">
            <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToHome()">
            <span>UseMart</span>
        </div>
        <form action="product_search.php" method="GET">
            <div class="search-container">
                <input type="text" name="query" placeholder="Search for products..." required>
                <button type="submit" class="search-icon">
                    <img src="resources/icons/Search.png" alt="Search" class="icon">
                </button>
            </div>
        </form>
        <div class="header-buttons">
            <button class="icon-button" onclick="redirectToCommunity()">Community</button>
            <button class="icon-button" onclick="redirectToChat()">Chat</button>
            <button class="icon-button" onclick="redirectToWish()">Wishlist</button>
            <button class="icon-button" onclick="redirectToCart()">Cart</button>
            <button class="icon-button" onclick="redirectToProfile()">
                <img alt="User" class="icon">
            </button>
            <button class="icon-button">
                <img src="resources/icons/Vector-5.png" alt="Menu" class="icon">
            </button>
        </div>
    </div>
    <div class="container">
        <h1>Sell on UseMart</h1>
        <p>UseMart allows individuals and businesses to sell second-hand goods easily and securely. Whether you're an individual clearing out items or a small business selling refurbished products, UseMart provides the perfect platform.</p>
        
        <h2>Who Can Sell?</h2>
        <ul>
            <li><strong>Individuals:</strong> Sell items you no longer need.</li>
            <li><strong>Businesses:</strong> List refurbished or excess inventory.</li>
            <li><strong>Verified Sellers:</strong> Gain trust with a verified document.</li>
        </ul>
        
        <h2>How It Works</h2>
        <ul>
            <li><strong>Create an Account:</strong> Sign up as a user.</li>
            <li><strong>List Your Products:</strong> Upload images, set prices, and add descriptions.</li>
            <li><strong>Marketing:</strong> Adverties you product in community form.</li>
            <li><strong>Connect with Buyers:</strong> Chat with potential buyers and finalize sales.</li>
            <li><strong>Secure Payments:</strong> Use our safe transaction system.</li>
        </ul>
        
        <h2>Why Sell on UseMart?</h2>
        <ul>
            <li><strong>Easy Listings:</strong> List your items for free in minutes.</li>
            <li><strong>Wide Reach:</strong> Connect with thousands of buyers.</li>
            <li><strong>Eco-Friendly:</strong> Promote sustainability by reselling goods.</li>
            <li><strong>Seller Protection:</strong> Secure transactions and fraud prevention.</li>
        </ul>
        
        <h2>Seller Guidelines</h2>
        <p>Ensure items are in good condition, communicate transparently, and follow UseMart’s policies to maintain trust and reliability.</p>
        <p><strong>Start selling today and turn your unused items into cash! 🚀</strong></p>
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
            <td><a href="#">Contact Us</a></td>
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
    </table>
</body>
</html>
