<?php
include 'db_connect.php'; // Database connection


// Handle download request
if (isset($_GET['download'])) {
    $filename = "reports_data.txt";
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");

    // Fetch reports with relevant details
    $sql = "SELECT reports.id, 
                   reporter.fullname AS reporter_name, 
                   COALESCE(reported_user.fullname, 'N/A') AS reported_user_name,
                   COALESCE(products.product_name, 'N/A') AS reported_product_name,
                   COALESCE(posts.topic, 'N/A') AS reported_post_topic,
                   COALESCE(orders.id, 'N/A') AS reported_order_id,
                   reports.description, reports.status, reports.created_at
            FROM reports
            JOIN users AS reporter ON reports.reporter_id = reporter.id
            LEFT JOIN users AS reported_user ON reports.reported_user_id = reported_user.id
            LEFT JOIN products ON reports.reported_product_id = products.id
            LEFT JOIN community_posts AS posts ON reports.reported_post_id = posts.id
            LEFT JOIN orders ON reports.reported_order_id = orders.id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $separator = str_repeat("-", 100) . "\n";
        fwrite($output, "Reports Data\n");
        fwrite($output, $separator);
        fwrite($output, "ID | Reporter | Reported User | Product | Post Topic | Order ID | Status | Created At\n");
        fwrite($output, $separator);

        while ($row = $result->fetch_assoc()) {
            $line = sprintf(
                "%-5s | %-10s | %-12s | %-15s | %-15s | %-8s | %-10s | %s\n",
                $row["id"], 
                $row["reporter_name"], 
                $row["reported_user_name"], 
                $row["reported_product_name"], 
                $row["reported_post_topic"], 
                $row["reported_order_id"],
                $row["status"], 
                $row["created_at"]
            );
            fwrite($output, $line);
        }
        fwrite($output, $separator);
    } else {
        fwrite($output, "No reports available.\n");
    }

    fclose($output);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="style\admin_dash.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Report Panel</title>
    <div class="header">
        <div class="usemart">
       <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToadmin()">
       <h1 style="margin-top:9px; margin-left:10px;">Admin Dashboard</h1>
       </div>
       <button class="logout-button" onclick="logout()">Log Out</button>
    </div>   
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

<div class="Heading">
    <div class="h">Reports Data</div>
    <button class="download" onclick="document.getElementById('exportForm').submit();">Download Data</button>
    <form id="exportForm" method="GET" style="display:none;">
        <input type="hidden" name="download" value="1">
    </form>
</div>   
 
<div class="content">
    <div class="tabs">
    <button class="tab-button active" onclick="showTab('all-reports')">ALL REPORTS</button>
    <button class="tab-button" onclick="showTab('reported-orders')">REPORTED ORDERS</button>
    <button class="tab-button" onclick="showTab('reported-users')">REPORTED USERS</button>
    <button class="tab-button" onclick="showTab('reported-posts')">REPORTED POSTS</button>
    <button class="tab-button" onclick="showTab('reported-products')">REPORTED PRODUCTS</button>
</div>
        <?php
        include "db_connect.php";
        ?>
<div id="all-reports" class="tab-content active"> 
<h2>Reported User</h2><br>
    <?php
    $result = $conn->query("SELECT u.id, u.fullname, u.email, u.address, u.contact_no, u.profile_photo, r.description, r.status, r.id AS report_id
                            FROM reports r 
                            JOIN users u ON r.reported_user_id = u.id 
                            WHERE r.reported_user_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='User-report-card'>";

        // Check if the user has a profile photo, otherwise use a default image
        $profilePhoto = !empty($row['profile_photo']) ? "" . $row['profile_photo'] : "resources/pfp.png";
        
        echo "<img src='" . $profilePhoto . "' alt='User'>";
        echo "<div class='User-report-details'>";
        echo "<strong>Report: " . $row['description'] . "</strong><br>";
        // Show Current Status
        echo "<strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span><br>";


        echo "Name: " . $row['fullname'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Address: " . $row['address'] . "<br>";
        echo "Mobile: " . $row['contact_no'] . "<br>";

        

        // Dropdown to Update Status
        echo "Update Status: <select class='status-dropdown' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>";
        echo "<option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>";
        echo "<option value='reviewed' " . ($row['status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>";
        echo "<option value='resolved' " . ($row['status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>";
        echo "</select>";
        echo "<br>";

        echo "</div>";
        
        // Delete Report Button
        echo "<button class='userdelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        
        echo "</div>";
    }
?>

<h2>Reported Orders</h2><br>
    <?php
    $result = $conn->query("SELECT o.id AS order_id, p.product_name, o.price, o.status AS tracking_status, 
                                   o.ordered_at, r.id AS report_id, r.description, r.status AS report_status
                            FROM reports r 
                            JOIN orders o ON r.reported_order_id = o.id 
                            JOIN products p ON o.product_id = p.id
                            WHERE r.reported_order_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='order-report-card'>";

        echo "<div class='order-report-details'>";
        echo "<strong>Report Reason:</strong> " . $row['description'] . "  ";
        // **Show Current Report Status**
        echo "<strong>Report Status:</strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['report_status']) . "</span><br>";

        echo "<p><strong>Order ID:</strong> " . $row['order_id'] . "  ";
        echo "<strong>Product Name:</strong> " . $row['product_name'] . "  ";
        echo "<strong>Price:</strong> " . number_format($row['price'], 2) . " Rs</p>";
        echo "<p><strong>Tracking Status:</strong> " . ucfirst($row['tracking_status']) . "</p>";
        echo "<p><strong>Ordered At:</strong> " . $row['ordered_at'] . "</p>";
      
        // Status dropdown
        echo "<select class='status-update' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>
        <option value='pending' " . ($row['report_status'] == 'pending' ? 'selected' : '') . ">Pending</option>
        <option value='reviewed' " . ($row['report_status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>
        <option value='resolved' " . ($row['report_status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>
        </select><br>";    
        echo "</div>";
        echo "<button class='userdelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        echo "</div>";
}?>


<h2>Reported Posts</h2><br>
    <?php
    $result = $conn->query("SELECT cp.id, cp.topic, cp.content, cp.image_1, cp.image_2, r.id AS report_id, r.description, r.status
                            FROM reports r 
                            JOIN community_posts cp ON r.reported_post_id = cp.id 
                            WHERE r.reported_post_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='post-report-card'>";
        echo "<div class='post-report-details'>";
        echo "<p><strong>Report Reason: </strong>" . $row['description'] . "</p>";
        // **Show Current Status**
        echo "<p><strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span></p>";

        echo "<strong>Topic: </strong>" . $row['topic'] . " ";
        echo "<p><strong>Content: </strong>" . $row['content'] . "</p>";
        
        // Status dropdown
        echo "<strong>Status: </strong> <select class='status-dropdown' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>";
        echo "<option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>";
        echo "<option value='reviewed' " . ($row['status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>";
        echo "<option value='resolved' " . ($row['status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>";
        echo "</select>";
        echo "<br>";

        // Image Buttons
        echo "<div class='image-gallery'>";
        if (!empty($row['image_1'])) {
            echo "<button onclick='viewImage(\"" . $row['image_1'] . "\")'>Image 1</button>";
        }
        if (!empty($row['image_2'])) {
            echo "<button onclick='viewImage(\"" . $row['image_2'] . "\")'>Image 2</button>";
        }
        echo "</div>";

        echo "</div>";

        // Delete button (aligned to the right)
        echo "<button class='post-delete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        echo "</div>";
    }
?>

</div>

<div id="reported-orders" class="tab-content"> 
    <h2>Reported Orders</h2><br>
    <?php
    $result = $conn->query("SELECT o.id AS order_id, p.product_name, o.price, o.status AS tracking_status, 
                                   o.ordered_at, r.id AS report_id, r.description, r.status AS report_status
                            FROM reports r 
                            JOIN orders o ON r.reported_order_id = o.id 
                            JOIN products p ON o.product_id = p.id
                            WHERE r.reported_order_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='order-report-card'>";

        echo "<div class='order-report-details'>";
        echo "<strong>Report Reason:</strong> " . $row['description'] . "  ";
        // **Show Current Report Status**
        echo "<strong>Report Status:</strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['report_status']) . "</span><br>";

        echo "<p><strong>Order ID:</strong> " . $row['order_id'] . "  ";
        echo "<strong>Product Name:</strong> " . $row['product_name'] . "  ";
        echo "<strong>Price:</strong> " . number_format($row['price'], 2) . " Rs</p>";
        echo "<p><strong>Tracking Status:</strong> " . ucfirst($row['tracking_status']) . "</p>";
        echo "<p><strong>Ordered At:</strong> " . $row['ordered_at'] . "</p>";
      
        // Status dropdown
        echo "<select class='status-update' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>
        <option value='pending' " . ($row['report_status'] == 'pending' ? 'selected' : '') . ">Pending</option>
        <option value='reviewed' " . ($row['report_status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>
        <option value='resolved' " . ($row['report_status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>
        </select><br>";    
        echo "</div>";
        echo "<button class='userdelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        echo "</div>";
    }
    ?>
</div>


<div id="reported-users" class="tab-content">
    <h2>Reported Users</h2><br>
    <?php
    $result = $conn->query("SELECT u.id, u.fullname, u.email, u.address, u.contact_no, u.profile_photo, r.description, r.status, r.id AS report_id
                            FROM reports r 
                            JOIN users u ON r.reported_user_id = u.id 
                            WHERE r.reported_user_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='User-report-card'>";

        
        // Check if the user has a profile photo, otherwise use a default image
        $profilePhoto = !empty($row['profile_photo']) ? "" . $row['profile_photo'] : "resources/pfp.png";
        
        echo "<img src='" . $profilePhoto . "' alt='User'>";
        echo "<div class='User-report-details'>";
        echo "<strong>Report: " . $row['description'] . "</strong><br>";
        // Show Current Status
        echo "<strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span><br>";


        echo "Name: " . $row['fullname'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Address: " . $row['address'] . "<br>";
        echo "Mobile: " . $row['contact_no'] . "<br>";

        

        // Dropdown to Update Status
        echo "Update Status: <select class='status-dropdown' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>";
        echo "<option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>";
        echo "<option value='reviewed' " . ($row['status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>";
        echo "<option value='resolved' " . ($row['status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>";
        echo "</select>";
        echo "<br>";

        echo "</div>";
        
        // Delete Report Button
        echo "<button class='userdelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        
        echo "</div>";
    }
    ?>
</div>

        
<div id="reported-posts" class="tab-content">
    <h2>Reported Posts</h2><br>
    <?php
    $result = $conn->query("SELECT cp.id, cp.topic, cp.content, cp.image_1, cp.image_2, r.id AS report_id, r.description, r.status
                            FROM reports r 
                            JOIN community_posts cp ON r.reported_post_id = cp.id 
                            WHERE r.reported_post_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='post-report-card'>";

        echo "<div class='post-report-details'>";
        echo "<p><strong>Report Reason: </strong>" . $row['description'] . "</p>";
        // **Show Current Status**
        echo "<p><strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span></p>";
        echo "<strong>Topic: </strong>" . $row['topic'] . " ";
        echo "<p><strong>Content: </strong>" . $row['content'] . "</p>";
        
        // Status dropdown
        echo "<strong>Status: </strong> <select class='status-dropdown' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>";
        echo "<option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>";
        echo "<option value='reviewed' " . ($row['status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>";
        echo "<option value='resolved' " . ($row['status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>";
        echo "</select>";
        echo "<br>";

        // Image Buttons
        echo "<div class='image-gallery'>";
        if (!empty($row['image_1'])) {
            echo "<button onclick='viewImage(\"" . $row['image_1'] . "\")'>Image 1</button>";
        }
        if (!empty($row['image_2'])) {
            echo "<button onclick='viewImage(\"" . $row['image_2'] . "\")'>Image 2</button>";
        }
        echo "</div>";

        echo "</div>";

        // Delete button (aligned to the right)
        echo "<button class='post-delete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        echo "</div>";
    }
    ?>
</div>

<div id="reported-orders" class="tab-content"> 
<h2>Reported Orders</h2><br>
    <?php
    $result = $conn->query("SELECT o.id AS order_id, p.product_name, o.price, o.status AS tracking_status, 
                                   o.ordered_at, r.id AS report_id, r.description, r.status AS report_status
                            FROM reports r 
                            JOIN orders o ON r.reported_order_id = o.id 
                            JOIN products p ON o.product_id = p.id
                            WHERE r.reported_order_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='order-report-card'>";

        echo "<div class='order-report-details'>";
        echo "<p><strong>Order ID:</strong> " . $row['order_id'] . "  ";
        echo "<strong>Product Name:</strong> " . $row['product_name'] . "  ";
        echo "<strong>Price:</strong> " . number_format($row['price'], 2) . " Rs</p>";
        echo "<p><strong>Tracking Status:</strong> " . ucfirst($row['tracking_status']) . "</p>";
        echo "<p><strong>Ordered At:</strong> " . $row['ordered_at'] . "</p>";
        
        // Report details
        echo "<p><strong>Report Reason:</strong> " . $row['description'] . "</p>";

        // **Show Current Report Status**
        echo "<p><strong>Report Status:</strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['report_status']) . "</span></p>";

        // Status dropdown
        echo "Update Status: <select class='status-dropdown' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>";
        echo "<option value='pending' " . ($row['report_status'] == 'pending' ? 'selected' : '') . ">Pending</option>";
        echo "<option value='reviewed' " . ($row['report_status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>";
        echo "<option value='resolved' " . ($row['report_status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>";
        echo "</select>";
        echo "<br>";

        echo "</div>";
        echo "<button class='order-report-delete' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        echo "</div>";
    }
    ?>
</div>

<div id="reported-products" class="tab-content">
<h2>Reported Products</h2><br>
    <?php
        $result = $conn->query("SELECT p.*, r.id AS report_id, r.description AS report_reason, r.status 
        FROM reports r 
        JOIN products p ON r.reported_product_id = p.id
        WHERE r.reported_product_id IS NOT NULL");

    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='pro-report-card'>";
        

        echo "<div class='pro-report-details'>";
        echo "<strong>Name of Product: </strong>" . $row['product_name'] . " ";
        echo "<strong>Price: </strong>" . $row['price'] . "rs ";
        echo "<strong>Category: </strong>" . $row['category'] . " ";
        echo "<strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span><br>";
        echo "<p><strong>Product Description:</strong> " . $row['description'] . "</p>";
        echo "<p><strong>Report Reason:</strong> " . $row['report_reason'] . "</p>";  // Report description
        echo "<button class='view-btn' onclick='toggleDetails(" . $row['id'] . ")'>View Details</button>";
        echo "<select class='status-update' onchange='updateStatus(" . $row['report_id'] . ", this.value)'>
                <option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                <option value='reviewed' " . ($row['status'] == 'reviewed' ? 'selected' : '') . ">Reviewed</option>
                <option value='resolved' " . ($row['status'] == 'resolved' ? 'selected' : '') . ">Resolved</option>
              </select>";    
        echo "</div>";

        // Delete Button (Moved to right corner)
        echo "<div class='delete-container'>";
        echo "<button class='prodelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        echo "</div>";
        

         // Hidden images and documents section
         echo "<div id='details-" . $row['id'] . "' class='hidden-details' style='display:none;'>";
         echo "<div class='image-gallery'>";
         for ($i = 1; $i <= 5; $i++) {
             if (!empty($row["image_$i"])) {
                 echo "<button onclick='viewImage(\"uploads/" . $row["image_$i"] . "\")'>Image $i</button>";
             }
         }

         // Display Bill & Documents
         if (!empty($row['document_1'])) {
            echo "<button onclick='viewDocument(\"uploads/" . $row['document_1'] . "\")'>Bill</button>";
        }
        if (!empty($row['document_2'])) {
            echo "<button onclick='viewDocument(\"uploads/" . $row['document_2'] . "\")'>Document</button>";
        }
        echo "</div>";
        echo "</div>"; // Close details div
        echo "</div>"; // Close report-card div
}?>
        </div>
</div>
<?php $conn->close(); ?>
</div>

 <script>

    document.addEventListener("DOMContentLoaded", function () {
    showTab('all-reports'); // Set "ALL REPORTS" as default
    });

    function showTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });

    // Remove 'active' class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show the selected tab
    document.getElementById(tabId).style.display = 'block';

    // Add 'active' class to the clicked button
    document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
}

        
        function logout() {
            window.location.href = 'logout.php';
        }
        function redirectToadmin(){
            window.location.href = 'admin_report.php';
        }

        function deleteReport(userId) {
            if (confirm("Are you sure you want to delete this report?")) {
                window.location.href = "delete_report.php?id=" + userId;
            }
        }

        function toggleDetails(productId) {
        var detailsDiv = document.getElementById("details-" + productId);
        detailsDiv.style.display = detailsDiv.style.display === "none" ? "block" : "none";
    }

    function viewImage(imageUrl) {
        window.open(imageUrl, "_blank");
    }

    function viewDocument(docUrl) {
        window.open(docUrl, "_blank");
    }


    function deleteReport(reportId) {
    if (confirm("Are you sure you want to delete this report?")) {
        fetch('delete_report.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${reportId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`report-${reportId}`).remove();
                alert("Report deleted successfully!");
            } else {
                alert("Failed to delete report. Please try again.");
            }
        })
        .catch(error => console.error("Error deleting report:", error));
    }
}


function updateStatus(reportId, status) {
    console.log(`Updating report ${reportId} to status ${status}`); // Debug log

    fetch('update_report_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(reportId)}&status=${encodeURIComponent(status)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server response:", data); // Log response
        if (data.success) {
            let statusElement = document.querySelector(`#status-${reportId}`);
            if (statusElement) {
                statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            }
            alert("Status updated successfully!");
        } else {
            alert("Failed to update status: " + (data.error ? data.error : "Unknown error"));
        }
    })
    .catch(error => console.error("Error updating status:", error));
}



    </script>
</body>
</html>
