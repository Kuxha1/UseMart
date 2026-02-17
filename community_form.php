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
$user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;
$filter = isset($_GET['filter']) ? $_GET['filter'] : "all"; // Default filter

// Query to fetch posts
if ($user_id) {
    if ($filter == "my_posts") {
        // Show only the logged-in user's posts
        $sql = "SELECT community_posts.*, users.fullname, users.profile_photo FROM community_posts 
                JOIN users ON community_posts.user_id = users.id
                WHERE community_posts.user_id = ? ORDER BY community_posts.created_at DESC";
    } else {
        // Show all posts except the user's own
        $sql = "SELECT community_posts.*, users.fullname, users.profile_photo FROM community_posts 
                JOIN users ON community_posts.user_id = users.id
                WHERE community_posts.user_id != ? ORDER BY community_posts.created_at DESC";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    // If user is not logged in, show all posts
    $sql = "SELECT community_posts.*, users.fullname, users.profile_photo FROM community_posts 
            JOIN users ON community_posts.user_id = users.id ORDER BY community_posts.created_at DESC";
    
    $stmt = $conn->prepare($sql);
}

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['report_error'] = "You must be logged in to report a post.";
    } else {
        $reporter_id = $_SESSION['user_id']; // The user who is reporting
        $reported_post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0; // Ensure post ID is set
        $description = trim($_POST['description']);

        if ($reported_post_id > 0 && !empty($description)) {
            $insert_query = "INSERT INTO reports (reporter_id, reported_post_id, description) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iis", $reporter_id, $reported_post_id, $description);

            if ($stmt->execute()) {
                $_SESSION['report_success'] = "Post has been reported successfully.";
            } else {
                $_SESSION['report_error'] = "Failed to submit report. Try again.";
            }
        } else {
            $_SESSION['report_error'] = "Invalid post ID or empty description.";
        }
    }

    // Redirect to prevent form resubmission
    header("Location: community_form.php");
    exit();
}


$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style/Cf-style.css">
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

<div class="head">
    <div class="title">
    <p>Community Form</p>
    </div>
    <button class="Addpost-button" onclick="checkLogin()">Add Post</button>
</div>



<div class="container">
        <div class="button-group">
            <button class="UPbutton" onclick="filterPosts('all')">USERS POSTS</button>
            <button class="MPbutton" onclick="checkLoginPost()">MY POST</button>
        </div>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="posts">
            <div class="post">
    <?php if (!empty($row['profile_photo'])): ?>
        <img class='profile-photo' src='<?= htmlspecialchars($row['profile_photo'] ?: 'resources/images/pfp.png') ?>' 
            alt='Profile' onclick="viewProfile('<?= $row['user_id'] ?>')">
    <?php else: ?>
        <img class='profile-photo' src='<?= htmlspecialchars($row['profile_photo'] ?: 'resources/pfp.png') ?>' 
            alt='Profile' onclick="viewProfile(<?= isset($row['user_id']) ? htmlspecialchars($row['user_id']) : 'null' ?>)">
    <?php endif; ?>

    <div class="post-content">
        <div class="name-date">
            <strong><?= htmlspecialchars($row['fullname']) ?></strong>
            <p style="margin-left: auto;"><?= htmlspecialchars(date("d M Y", strtotime($row['created_at']))) ?></p>
        </div>
        <h3><?= htmlspecialchars($row['topic']) ?></h3>
        <div class="post-images">
            <?php if (!empty($row['image_1'])): ?>
                <img class='post-image' src="<?= htmlspecialchars($row['image_1']) ?>" alt="Post Image">
            <?php endif; ?>

            <?php if (!empty($row['image_2'])): ?>
                <img class='post-image' src="<?= htmlspecialchars($row['image_2']) ?>" alt="Post Image">
            <?php endif; ?>
        </div>

        <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
    </div>

    <?php if ($filter == "my_posts"): ?>
        <!-- Delete Button for User's Posts -->
        <form action="delete_post.php" method="POST">
            <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
            <button type="submit" class="rep-del-button">Delete Post</button>
        </form>
    <?php else: ?>
        <!-- Report Button for Other Users' Posts -->
        <button class="rep-del-button" onclick="openReportModal(<?php echo $row['id']; ?>)">Report Post</button>
    <?php endif; ?>
</div>
</div><br>
<?php endwhile; ?>
<?php else: ?>
    <p style="text-align: center; color: white; font-size: 32px;">No posts found.</p><br><br><br><br><br>
<?php endif; ?>
</div>

<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Report Post</h3><br>
        <form method="POST">
            <input type="hidden" name="post_id" id="reportedPostId"> <!-- Store Post ID -->
            <textarea name="description" placeholder="Describe the issue" rows="5" cols="50" required></textarea><br>
            <button type="submit" name="submit_report" class="sub-rep-button">Submit Report</button>
        </form>
    </div>
</div>


 <!-- Background Blur Overlay -->
<div id="overlay"></div>
<!-- Close Button (Top-Left Corner) -->
<button class="close-button" onclick="closeForm()">&times;</button>
<!-- Pop-up Form -->
<div class="form-popup" id="postForm">
    <div class="form-content">
        <h2>Create a Post</h2>
        <form action="upload_post.php" method="POST" enctype="multipart/form-data">
            
        <!-- Slideshow Container -->
            <div class="slideshow-container">
                <div class="slides">
                    <img id="preview1" src="" alt="Preview Image 1">
                    <img id="preview2" src="" alt="Preview Image 2">
                </div>
                <button class="prev" onclick="changeSlide(-1)">&#10094;</button>
                <button class="next" onclick="changeSlide(1)">&#10095;</button>
            </div>

            <label>Upload Image 1:</label>
            <input type="file" name="image_1" accept="image/*" onchange="previewImage(event, 0)">
            
            <label>Upload Image 2:</label>
            <input type="file" name="image_2" accept="image/*" onchange="previewImage(event, 1)">

            <input type="text" name="topic" placeholder="Enter topic" required>
            <textarea name="content" id="content" maxlength="300" placeholder="Content (max 300 words):" required></textarea>
            <p id="word-count">0/300 words</p>

            <button type="submit" class="upload-post-button">Post</button>
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

function viewProfile(userId) {
    let loggedInUserId = "<?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null' ?>";

    if (userId === loggedInUserId) {
        alert("You are viewing your own profile!");
        return;
    }

    window.location.href = "user_profile.php?user_id=" + userId;
}

    function checkLogin() {
    <?php if ($user_id) { ?>
        openForm();
    <?php } else { ?>
        window.location.href = "login.php"; // Redirect to login page
    <?php } ?>
    }

    function checkLoginPost() {
    <?php if ($user_id) { ?>
        filterPosts('my_posts')
    <?php } else { ?>
        window.location.href = "login.php"; // Redirect to login page
    <?php } ?>
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

    function filterPosts(filter) {
        window.location.href = "community_form.php?filter=" + filter;
    }

    let currentSlide = 0;

function previewImage(event, index) {
    const reader = new FileReader();
    reader.onload = function () {
        document.getElementById(index === 0 ? 'preview1' : 'preview2').src = reader.result;
        document.getElementById(index === 0 ? 'preview1' : 'preview2').classList.add('active');
    };
    reader.readAsDataURL(event.target.files[0]);
}

function changeSlide(step) {
    let images = document.querySelectorAll(".slides img");
    images[currentSlide].classList.remove("active");

    currentSlide += step;
    if (currentSlide >= images.length) currentSlide = 0;
    if (currentSlide < 0) currentSlide = images.length - 1;

    images[currentSlide].classList.add("active");
}

function openForm() {
    document.getElementById("postForm").style.display = "block";
    document.getElementById("overlay").style.display = "block";
    document.querySelector(".close-button").style.display = "block"; // Show close button
}

function closeForm() {
    document.getElementById("postForm").style.display = "none";
    document.getElementById("overlay").style.display = "none";
    document.querySelector(".close-button").style.display = "none"; // Hide close button
}

        // Close form if user clicks outside it
window.onclick = function(event) {
    var overlay = document.getElementById("overlay");
    var postForm = document.getElementById("postForm");

    if (event.target == overlay) {
        closeForm();
    }
};



    document.getElementById("content").addEventListener("input", function () { 
        let words = this.value.trim().split(/\s+/).filter(word => word.length > 0);
        document.getElementById("word-count").textContent = words.length + "/300 words";
    });


function openReportModal(postId) {
    document.getElementById('reportedPostId').value = postId; // Set post ID
    document.getElementById('reportModal').style.display = 'flex'; // Show modal
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
<?php
// Close statement and connection
$stmt->close();
$conn->close();
?>   