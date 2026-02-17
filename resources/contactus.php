<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - UseMart</title>
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
            padding: 1rem 2rem;
            background-color: #16C47F;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .usemart {
            display: flex;
            align-items: center;
        }
        .logo {
            height: 40px;
            margin-right: 10px;
        }
        .usemart span {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }
        .search-container {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border-radius: 25px;
            padding: 0.5rem 1rem;
            width: 400px;
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
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #2c3e50;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            font-size: 28px;
        }
        .contact-details p {
            line-height: 1.6;
            font-size: 16px;
        }
        .contact-details a {
            color: #16C47F;
            text-decoration: none;
            font-weight: bold;
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
  .icon {
            width: 18px;
            height: 18px;
        }
         .header-buttons {
            display: flex;
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="usemart">
            <img src="resources/A.png" alt="Logo" class="logo">
            <span>UseMart</span>
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
                <img alt="User" class="icon">
            </button>
            <button class="icon-button">
                <img src="resources/icons/Vector-5.png" alt="Menu" class="icon">
            </button>
        </div>
    </div>
    <div class="container">
        <h1>Contact Us</h1>
        <div class="info">
            <p>If you have any questions, concerns, or feedback, feel free to reach out to us. We're here to help!</p>
        </div>
        <div class="contact-details">
            <p><strong>Company Name:</strong> UseMart Pvt Ltd</p>
            <p><strong>Address:</strong> 123 Market Street, Ahmedabad, Gujarat, India</p>
            <p><strong>Phone:</strong> +91 98765 43210</p>
            <p><strong>Email:</strong> <a href="mailto:support@usemart.com">support@usemart.com</a></p>
            <p><strong>Business Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM</p>
            <p><strong>Follow Us:</strong></p>
            <p>
                <a href="#">Instagram</a> |
                <a href="#">Facebook</a> |
                <a href="#">Twitter</a> |
                <a href="#">LinkedIn</a>
            </p>
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
</footer>
</body>
</html>
