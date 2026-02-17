<?php
session_start();
include "db_connect.php";

$user_id = $_SESSION['user_id'] ?? null;

// Initialize filters
$search_query = isset($_GET['query']) ? htmlspecialchars(trim($_GET['query'])) : '';
$min_price = isset($_GET['min_price']) ? filter_var($_GET['min_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
$max_price = isset($_GET['max_price']) ? filter_var($_GET['max_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 9999999;
$categories = isset($_GET['category']) ? array_map('htmlspecialchars', (array) $_GET['category']) : [];

// Build SQL query dynamically
$sql = "SELECT * FROM products WHERE price BETWEEN ? AND ?";
$params = [$min_price, $max_price];
$param_types = "dd";

// Apply search query filter
if (!empty($search_query)) {
    $sql .= " AND (product_name LIKE ? OR description LIKE ? )";
    $search_wildcard = "%$search_query%";
    $params[] = $search_wildcard;
    $params[] = $search_wildcard;
    $param_types .= "ss";
}

// Apply category filter (if selected)
if (!empty($categories)) {
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $sql .= " AND category IN ($placeholders)";
    $params = array_merge($params, $categories);
    $param_types .= str_repeat("s", count($categories));
}

// Prepare and execute statement
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/product_search-style.css">
    <title>Search product page</title>
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
<!-- Left Section - Search and Filters -->
<div class="filter">
    <form method="GET" action="">
        <p class="title">Filter Items</p>
        
        <div class="price-range">
            <p style="color: white; font-size: 25px">Price Range</p>
            <div class="price-inputs">
                <input type="number" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>" min="0">
                <span style="color: white; font-size: 20px">to</span>
                <input type="number" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>" min="0">
            </div>
        </div>

        <div class="categories">
            <p class="filter-title" style="color: white; font-size: 25px;">Categories</p>
            <?php
            $available_categories = ["Electronics", "Clothing", "Home-Furniture", "Sports-Fitness", "Vehicle parts", "Books", "Beauty products", "Jewelers", "Toys", "Tools", "Others"];
            foreach ($available_categories as $category) {
                $isChecked = in_array($category, $categories) ? 'checked' : '';
                echo '<div class="category-item">
                        <input type="checkbox" name="category[]" value="' . htmlspecialchars($category) . '" ' . $isChecked . '> ' . htmlspecialchars($category) . '
                      </div>';
            }
            ?>
        </div>
        
        <center>
            <button class="filter-button" type="submit">Filter Products</button>
        </center>
    </form>
</div>

<!-- Right section - Search Results -->
<div class="product-list">
<div class="product-header">    
    <h2 class="Search-title">
        Search Results for "<?php echo $search_query ?: 'All Products'; ?>"
    </h2>
</div>

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="product-card" onclick="productDetailPage(<?php echo $row['id']; ?>)">
            <div class="product-image">
                <img src="uploads/<?php echo !empty($row['image_1']) ? htmlspecialchars($row['image_1']) : 'default.jpg'; ?>" alt="Product Image">
            </div>
            <div class="product-details">
                <h2><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></h2>
                <p><strong>Price:</strong> <?php echo htmlspecialchars($row['price']); ?>rs</p>
                <p class="product-desc">
                    <?php echo htmlspecialchars($row['description']); ?>
                </p>
                <div class="product-actions">
                    <button class="buy-now">BUY NOW</button>
                    <button class="add-to-cart"
                        onclick="addToCart(<?php echo $row['id']; ?>, <?php echo $_SESSION['user_id'] ?? 'null'; ?>, <?php echo $row['user_id']; ?>)">
                    ADD TO CART
                </button>
                </div>
            </div>
            <div class="wishlist" 
            onclick="addToWishlist(<?php echo $row['id']; ?>, <?php echo $row['user_id']; ?>, <?php echo $_SESSION['user_id'] ?? 'null'; ?>)">&#10084;</div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No products found.</p>
<?php endif; ?>



</div>
</div>
</div>       
    <script>
        function productDetailPage(productId) {
        window.location.href = 'product_details.php?id=' + productId;
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
        
        function addToWishlist(productId, ownerId, userId) {

            if (!userId || userId === "null") {
        alert("Please log in to add items to your wishlist.");
        return;
        }
        if (ownerId == userId) {  // Prevent adding own product
        alert("Error: You cannot add your own product to your wishlist.");
        return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "wishlist_handler.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                alert(xhr.responseText);  // Show response from the server
            } else {
                alert("Error: Unable to add to wishlist.");
            }
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

    // Proceed with AJAX request to add item to cart
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "cart_handler.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert(xhr.responseText); // Show response from server
        }
    };

    xhr.send("product_id=" + productId + "&user_id=" + userId);
}



    </script>
</body>
</html>
