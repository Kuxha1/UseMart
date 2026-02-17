<?php
include 'db_connect.php'; // Database connection

// Fetch all posts
$result = $conn->query("SELECT * FROM community_posts");

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style/table_data.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Data</title>
    <div class="header">
        <div class="usemart">
            <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToAdmin()">
            <h1 style="margin-top:9px; margin-left:10px;">Admin Dashboard</h1>
        </div>
        <button class="logout-button" onclick="logout()">Log Out</button>
    </div>   

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            width: 40%;
            position: relative;
            text-align: center;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
        .image-preview {
            max-width: 100%;
            height: auto;
        }
    </style>   
</head>
<body>
<div class="sidebar">
    <a href="admin_report.php">Reports</a>
        <a href="order_data.php">Orders Data</a>
        <a href="sells_data.php">sells Data</a>
        <a href="user_data.php">Users Data</a>
        <a href="product_data.php">Products Data</a>
        <a href="post_data.php">Posts Data</a>
        <a href="cart_data.php">Users cart Data</a>
        <a href="wishlist_data.php">Wishlist data</a>
        <a href="Payment_data.php">Payment data</a>
    </div>

    <!-- Heading Section -->
    <div class="Heading">
            <div class="h">All Posts</div>
            <button class="downloadData-btn" onclick="downloadPostsData()">Download Data</button>
        </div>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Topic</th>
                <th>Content</th>
                <th>Images</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr id="post-<?php echo $row['id']; ?>">
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['topic']); ?></td>
                <td><?php echo htmlspecialchars($row['content']); ?></td>
                <td>
                    <?php if (!empty($row['image_1']) || !empty($row['image_2'])): ?>
                        <button onclick="viewImage('<?php echo $row['image_1']; ?>', '<?php echo $row['image_2']; ?>')">View Images</button>
                    <?php else: ?>
                        No Images
                    <?php endif; ?>
                </td>
                <td>
                    <button class="delete" onclick="deletePost(<?php echo $row['id']; ?>)">Delete</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" class="image-preview" src="" alt="Post Image">
            <br>
            <button id="prevImage" onclick="prevImage()">Prev</button>
            <button id="nextImage" onclick="nextImage()">Next</button>
        </div>
    </div>

    <script>
        function logout() {
            window.location.href = 'logout.php';
        }
        function redirectToAdmin() {
            window.location.href = 'admin_report.php';
        }

        // Delete post function
        function deletePost(postId) {
            if (confirm("Are you sure you want to delete this post?")) {
                fetch("Admin_delete_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + postId
                })
                .then(response => response.text())
                .then(data => {
                    alert(data); 
                    location.reload();
                })
                .catch(error => console.error("Error:", error));
            }
        }

        let images = [];
        let currentIndex = 0;

        function viewImage(image1, image2) {
            images = [];
            if (image1) images.push("" + image1);
            if (image2) images.push("" + image2);

            if (images.length > 0) {
                document.getElementById('modalImage').src = images[0];
                currentIndex = 0;
                document.getElementById('imageModal').style.display = 'flex';
            }
        }

        function prevImage() {
            if (currentIndex > 0) {
                currentIndex--;
                document.getElementById('modalImage').src = images[currentIndex];
            }
        }

        function nextImage() {
            if (currentIndex < images.length - 1) {
                currentIndex++;
                document.getElementById('modalImage').src = images[currentIndex];
            }
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal if clicked outside
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                closeImageModal();
            }
        }

        function downloadPostsData() {
    window.location.href = 'download_posts.php';
}

    </script>

</body>
</html>
