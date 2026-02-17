<?php
session_start();
require 'db_connect.php';

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']); // Sanitize input

    // Fetch user's community posts
    $post_query = $conn->prepare("SELECT topic, content, image_1, image_2, created_at FROM community_posts WHERE user_id = ?");
    $post_query->bind_param("i", $user_id);
    $post_query->execute();
    $posts_result = $post_query->get_result();

    // Fetch seller details
    $query = $conn->prepare("SELECT fullname, email, contact_no, description, profile_photo FROM users WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("User not found.");
    }

    // Fetch seller's products
    $product_query = $conn->prepare("SELECT id, product_name, price, description, image_1, created_at FROM products WHERE user_id = ?");
    $product_query->bind_param("i", $user_id);
    $product_query->execute();
    $products_result = $product_query->get_result();

    // Handle report submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['report_error'] = "You must be logged in to report a user.";
    } else {
        $reporter_id = $_SESSION['user_id']; // The user who is reporting
        $reported_user_id = $user_id; // The user being reported
        $description = trim($_POST['description']);

        if (!empty($description)) {
            $insert_query = "INSERT INTO reports (reporter_id, reported_user_id, description) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iis", $reporter_id, $reported_user_id, $description);

            if ($stmt->execute()) {
                $_SESSION['report_success'] = "User has been reported successfully.";
            } else {
                $_SESSION['report_error'] = "Failed to submit report. Try again.";
            }
        } else {
            $_SESSION['report_error'] = "Please provide a valid report description.";
        }
    }

    // Redirect to prevent resubmission
    header("Location: user_profile.php?user_id=" . $user_id);
    exit();
}


} else {
    die("No user ID provided.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbox page</title>
    <link rel="stylesheet" href="style/U_profile-style.css">
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
        <div class="user-info">

            <div class="info">
                <div class="user-avatar">
                    <img src="<?php echo htmlspecialchars($user['profile_photo'] ?: 'resources/pfp.png');?>" class="profile-photo" alt="Profile Picture">
                </div>
                <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
            </div>
            <div class="align">
            <div class="info2">
                <p class="bio"><?php echo htmlspecialchars($user['description']); ?></p>
                <div class="actions">
                <button class="btn message" onclick="openChat(<?= htmlspecialchars($user_id) ?>)">SEND MESSAGE</button>
                <button class="btn report" onclick="openReportModal()">REPORT USER</button>
                </div>
            </div>
            </div>
        </div>

        <div class="content">
        <div class="posts-section">
        <h3>POSTS</h3>
        <?php if ($posts_result->num_rows > 0): ?>
        <?php while ($post = $posts_result->fetch_assoc()): ?>
            <div class="post">
            <div class="flex">
            <h4><?php echo htmlspecialchars($post['topic']); ?></h4>
            <small style="margin-left: auto;"><?php echo date("d M Y", strtotime($post['created_at'])); ?></small>
            </div>
                <div class="post-images">
                    <?php if (!empty($post['image_1'])): ?>
                        <img src="<?php echo htmlspecialchars($post['image_1']); ?>" alt="Post Image" class="image-placeholder">
                    <?php endif; ?>
                    <?php if (!empty($post['image_2'])): ?>
                        <img src="<?php echo htmlspecialchars($post['image_2']); ?>" alt="Post Image" class="image-placeholder">
                    <?php endif; ?>
                    
                </div>
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <h1 color="white">No posts available.</h1>
    <?php endif; ?>
</div>

            <!-- SELLER'S PRODUCTS -->
    <div class="items-section">
        <h3>Items on Sale</h3>
        <?php if ($products_result->num_rows > 0): ?>
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <div class="item" onclick="window.location.href='product_details.php?id=<?php echo $product['id']; ?>'">
                    <img src="uploads/<?php echo htmlspecialchars($product['image_1']); ?>" alt="Product Image" width="300px" height="100px">
                    <div class="item-details">
                        <div class="flex">
                        <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                        <small style="margin-left: auto;"><?php echo date("d M Y", strtotime($product['created_at'])); ?></small>
                        </div>
                        <p>Price: <?php echo htmlspecialchars($product['price']); ?> Rs</p>
                        <small>Description: <?php echo htmlspecialchars($product['description']); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <h1 color="white">No products listed by this seller.</h1>
        <?php endif; ?>
    </div>
    </div>

        </div>

        <!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <b style="font-family: 20px;">Report User</b><br><br>
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
    if (event.target === modal) {
        closeModal();
    }
};

        function openChat(userId) {
            window.location.href = "Chat.php?receiver_id=" + userId;
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
<?php if (isset($_SESSION['report_success'])): ?>
    <script>
        alert("<?php echo $_SESSION['report_success']; ?>");
    </script>
    <?php unset($_SESSION['report_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['report_error'])): ?>
    <script>
        alert("<?php echo $_SESSION['report_error']; ?>");
    </script>
    <?php unset($_SESSION['report_error']); ?>
<?php endif; ?>

</body>
</html>
