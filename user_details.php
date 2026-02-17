<?php

/**
 * Project Name: UseMart – Second-Hand Goods Marketplace
 * Author: Kushal Mistry
 * Copyright (c) 2026 Kushal Mistry
 * All rights reserved.
 */

session_start();

include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
}else{

$user_id = $_SESSION['user_id'];

$monthly_sales = [];
$weekly_sales = [];
$weekly_labels = [];

// --- Monthly Sales (Jan–Dec 2025) ---
for ($m = 1; $m <= 12; $m++) {
    $label = DateTime::createFromFormat('!m Y', "$m 2025")->format('M Y');
    $monthly_sales[$label] = 0.0;
}

$monthly_sql = "
    SELECT DATE_FORMAT(sold_at, '%b %Y') AS month, SUM(price) AS total
    FROM sales
    WHERE seller_id = $user_id AND YEAR(sold_at) = 2025
    GROUP BY month
";
$res = $conn->query($monthly_sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        if (isset($monthly_sales[$row['month']])) {
            $monthly_sales[$row['month']] = (float)$row['total'];
        }
    }
}

// --- Weekly Sales for current month ---
$current_month = date('n');
$current_year = date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

for ($start_day = 1; $start_day <= $days_in_month; $start_day += 7) {
    $end_day = min($start_day + 6, $days_in_month);
    $label = "$start_day-$end_day " . date('M');
    $weekly_labels[] = $label;
    $weekly_sales[] = 0.0;
}

$weekly_sql = "
    SELECT price, sold_at FROM sales
    WHERE seller_id = $user_id AND MONTH(sold_at) = $current_month AND YEAR(sold_at) = $current_year
";
$res_week = $conn->query($weekly_sql);
if ($res_week && $res_week->num_rows > 0) {
    while ($row = $res_week->fetch_assoc()) {
        $day = (int)date('j', strtotime($row['sold_at']));
        $week_index = floor(($day - 1) / 7);
        if (isset($weekly_sales[$week_index])) {
            $weekly_sales[$week_index] += (float)$row['price'];
        }
    }
}

// Send data to JavaScript
$monthlyLabels = array_keys($monthly_sales);
$monthlyData = array_values($monthly_sales);

// Handle order cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_order'])) {
    $order_id = intval($_POST['cancel_order']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    echo ($stmt->execute()) ? "success" : "error";
    $stmt->close();
    exit; // Stop further execution
}

// Handle TXT file download
if (isset($_GET['download_order'])) {
    $order_id = intval($_GET['download_order']);

    $stmt = $conn->prepare("SELECT orders.id, products.product_name, orders.price, orders.ordered_at, users.fullname 
                            FROM orders 
                            JOIN products ON orders.product_id = products.id 
                            JOIN users ON orders.user_id = users.id
                            WHERE orders.id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $invoice = "Order Invoice\n";
        $invoice .= "------------------------\n";
        $invoice .= "Order ID: " . $row['id'] . "\n";
        $invoice .= "Customer: " . $row['fullname'] . "\n";
        $invoice .= "Product: " . $row['product_name'] . "\n";
        $invoice .= "Price: " . number_format($row['price'], 2) . " Rs\n";
        $invoice .= "Ordered At: " . $row['ordered_at'] . "\n";
        
        // Set headers for file download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="Invoice_'.$order_id.'.txt"');
        echo $invoice;
        exit;
    }
}
    // Fetch user's orders
    $sql_orders = "SELECT orders.id, products.product_name, orders.price, orders.status, orders.ordered_at
                   FROM orders
                   JOIN products ON orders.product_id = products.id
                   WHERE orders.user_id = ?";
    $stmt_orders = $conn->prepare($sql_orders);
    $stmt_orders->bind_param("i", $user_id);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();
    

    if (isset($_GET['download_sale'])) {
        $sale_id = intval($_GET['download_sale']);
    
        $stmt = $conn->prepare("SELECT sales.id, products.product_name, sales.price, sales.sold_at, users.fullname AS buyer_name
                                FROM sales
                                JOIN products ON sales.product_id = products.id
                                JOIN users ON sales.buyer_id = users.id
                                WHERE sales.id = ?");
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            $invoice = "Sales Invoice\n";
            $invoice .= "------------------------\n";
            $invoice .= "Sale ID: " . $row['id'] . "\n";
            $invoice .= "Buyer: " . $row['buyer_name'] . "\n";
            $invoice .= "Product: " . $row['product_name'] . "\n";
            $invoice .= "Price: " . number_format($row['price'], 2) . " Rs\n";
            $invoice .= "Sold At: " . $row['sold_at'] . "\n";
            
            // Set headers for file download
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="Sales_Invoice_'.$sale_id.'.txt"');
            echo $invoice;
            exit;
        } else {
            echo "Sale not found.";
            exit;
        }
    }
    
    // Fetch user's sales
    $sql_sales = "SELECT sales.id, buyer.fullname AS buyer_name, products.product_name, sales.price, sales.sold_at
                  FROM sales
                  JOIN users AS buyer ON sales.buyer_id = buyer.id
                  JOIN products ON sales.product_id = products.id
                  WHERE sales.seller_id = ?";
    $stmt_sales = $conn->prepare($sql_sales);
    $stmt_sales->bind_param("i", $user_id);
    $stmt_sales->execute();
    $result_sales = $stmt_sales->get_result();
    
    if (isset($_POST['submit_report'])) {
    
        $reporter_id = $_SESSION['user_id']; // Assuming user is logged in
        $reported_order_id = $_POST['reported_order_id'];
        $description = mysqli_real_escape_string($conn, $_POST['description']);
    
        // Insert into reports table
        $query = "INSERT INTO reports (reporter_id, reported_order_id, description) VALUES ('$reporter_id', '$reported_order_id', '$description')";
    
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Report submitted successfully'); window.location.href='user_details.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error submitting report');</script>";
        }
    }

    // Fetch payments
$sql = "SELECT id, cardholder_name, billing_address, total_price, payment_date 
FROM payments 
WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_payments = $stmt->get_result();
    
    
    
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My details</title>
    <link rel="stylesheet" href="style/UserDetails-style.css">

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
    if (!isset($_SESSION['user_id'])) { 
    header("Location: Profile.php");
    exit(); } else { ?>
<div class="split">            
    <div class="sidebar">
        <a class="tab-button active" onclick="showTab('all-reports')">MY Reports</a>
        <a class="tab-button" onclick="showTab('Order-details')" href="#">Orders Details</a>
        <a class="tab-button" onclick="showTab('sells-details')" href="#">Sales Details</a>
        <a class="tab-button" onclick="showTab('Payment')" href="#">Payment details</a>
    </div>

<div class="data-box">
    <div id="all-reports" class="tab-content active">
        <?php
        $logged_in_user = $_SESSION['user_id']; // Assuming user ID is stored in session

        // Track if any reports exist
        $hasReports = false;


        // Reported Orders
    $result = $conn->query("SELECT o.id AS order_id, p.product_name, o.price, o.status AS tracking_status, 
    o.ordered_at, r.id AS report_id, r.description, r.status AS report_status
FROM reports r 
JOIN orders o ON r.reported_order_id = o.id 
JOIN products p ON o.product_id = p.id
WHERE r.reported_order_id IS NOT NULL AND r.reporter_id = $logged_in_user");

echo "<h2>Reported Orders</h2>";
if ($result->num_rows > 0) {
$hasReports = true;
while ($row = $result->fetch_assoc()) {
echo "<div id='report-" . $row['report_id'] . "' class='order-report-card'>";

echo "<div class='order-report-details'>";
// Report details
echo "<strong>Report Reason:</strong> " . $row['description'] . "    ";

// **Show Current Report Status**
echo "<strong>Report Status:</strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['report_status']) . "</span><br>";

echo "<p><strong>Order ID:</strong> " . $row['order_id'] . "  ";
echo "<strong>Product Name:</strong> " . $row['product_name'] . "  ";
echo "<strong>Price:</strong> " . number_format($row['price'], 2) . " Rs</p>";
echo "<p><strong>Tracking Status:</strong> " . ucfirst($row['tracking_status']) . "</p>";
echo "<p><strong>Ordered At:</strong> " . $row['ordered_at'] . "</p>";


echo "</div>";

echo "<button class='orderdelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
echo "</div>";
}
} else {
echo "<p>No orders have been reported by you.</p>";
}


// Reported Users
        $result = $conn->query("SELECT u.id, u.fullname, u.description AS user_bio, u.profile_photo, 
                                r.description AS report_description, r.status, r.id AS report_id
                        FROM reports r 
                        JOIN users u ON r.reported_user_id = u.id 
                        WHERE r.reported_user_id IS NOT NULL AND r.reporter_id = $logged_in_user");

echo "<h2>Reported Users</h2>";
if ($result->num_rows > 0) {
    $hasReports = true;
    while ($row = $result->fetch_assoc()) {
        echo "<div id='report-" . $row['report_id'] . "' class='User-report-card'>";
        $profilePhoto = !empty($row['profile_photo']) ? "" . $row['profile_photo'] : "resources/pfp.png";
        echo "<img src='" . $profilePhoto . "' alt='User'>";
        echo "<div class='User-report-details'>";
        echo "<strong>Report: " . $row['report_description'] . "</strong><br>";
        echo "<strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span><br>";
        echo "<b>Name: </b>" . $row['fullname'] . "  ";
        echo "<b>Bio: </b>" . (!empty($row['user_bio']) ? $row['user_bio'] : "No bio available") . "  ";
        echo "</div>";
        echo "<button class='userdelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
        echo "</div>";
    }
} else {
    echo "<p>No users have been reported by you.</p>";
}


        // Reported Posts
        $result = $conn->query("SELECT cp.id, cp.topic, cp.content, cp.image_1, cp.image_2, r.id AS report_id, r.description, r.status
                                FROM reports r 
                                JOIN community_posts cp ON r.reported_post_id = cp.id 
                                WHERE r.reported_post_id IS NOT NULL AND r.reporter_id = $logged_in_user");

        echo "<h2>Reported Posts</h2>";
        if ($result->num_rows > 0) {
            $hasReports = true;
            while ($row = $result->fetch_assoc()) {
                echo "<div id='report-" . $row['report_id'] . "' class='post-report-card'>";
                echo "<div class='post-report-details'>";
                echo "<p><strong>Report Reason: </strong>" . $row['description'] . "</p>";
                echo "<p><strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span></p>";
                echo "<strong>Topic: </strong>" . $row['topic'] . " ";
                echo "<p><strong>Content: </strong>" . $row['content'] . "</p>";
                echo "<div class='image-gallery'>";
                if (!empty($row['image_1'])) {
                    echo "<button onclick='viewImage(\"" . $row['image_1'] . "\")'>Image 1</button>";
                }
                if (!empty($row['image_2'])) {
                    echo "<button onclick='viewImage(\"" . $row['image_2'] . "\")'>Image 2</button>";
                }
                echo "</div>";
                echo "</div>";
                echo "<button class='userdelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
                echo "</div>";
            }
        } else {
            echo "<p>No posts have been reported by you.</p>";
        }

        // Reported Products
        $result = $conn->query("SELECT p.*, r.id AS report_id, r.description AS report_reason, r.status 
                                FROM reports r 
                                JOIN products p ON r.reported_product_id = p.id
                                WHERE r.reported_product_id IS NOT NULL AND r.reporter_id = $logged_in_user");

        echo "<h2>Reported Products</h2>";
        if ($result->num_rows > 0) {
            $hasReports = true;
            while ($row = $result->fetch_assoc()) {
                echo "<div id='report-" . $row['report_id'] . "' class='pro-report-card'>";
                echo "<div class='pro-report-details'>";
                echo "<strong>Report Reason:</strong> " . $row['report_reason'] . "  ";
                echo "<strong>Status: </strong> <span id='status-" . $row['report_id'] . "' class='status'>" . ucfirst($row['status']) . "</span><br>";
                echo "<strong>Name of Product: </strong>" . $row['product_name'] . " ";
                echo "<strong>Price: </strong>" . $row['price'] . "rs ";
                echo "<strong>Category: </strong>" . $row['category'] . " ";
                echo "<p><strong>Product Description:</strong> " . $row['description'] . "</p>";

                // Delete Button
                echo "<div class='delete-container'>";
                echo "<button class='prodelete-btn' onclick='deleteReport(" . $row['report_id'] . ")'>Delete Report</button>";
                echo "</div>";

                // Hidden details section
                echo "<div id='details-" . $row['id'] . "' class='hidden-details' style='display:none;'>";
                echo "<p><strong>Additional Product Details:</strong></p>";
                // Display product images
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

                echo "</div>";
                echo "<button class='view-btn' onclick='toggleDetails(" . $row['id'] . ")'>View Details</button>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No products have been reported by you.</p>";
        }

        // If no reports exist, show a general message
        if (!$hasReports) {
            echo "<h2>No reports found</h2>";
            echo "<p>You have not reported any users, posts, orders or products.</p>";
        }
        ?>
</div>

<div id="Order-details" class="tab-content">
<h2> My Orders</h2>
        <table border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product</th>
                <th>Price</th>
                <th>Status</th>
                <th>Ordered At</th>
                <th>Action</th>
            </tr>
        </thead>    
            <?php if ($result_orders->num_rows > 0): ?>
                <?php while ($row = $result_orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?> Rs</td>
                        <td><?php echo ucfirst($row['status']); ?></td>
                        <td><?php echo $row['ordered_at']; ?></td>
                        <td>
                            <button class="cancel-order" data-id="<?php echo $row['id']; ?>">Cancel</button>
                            <button class="download-invoice" data-id="<?php echo $row['id']; ?>">Download Bill</button>
                            <button class="btn-report" data-order-id="<?php echo $row['id']; ?>" onclick="openReportModal(this)">REPORT ORDER</button>
                        </td>         
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No orders found.</td></tr>
            <?php endif; ?>
        </table>
        </div>
    
<div id="sells-details" class="tab-content">
        <h2>My Sales</h2>
        <canvas id="userSalesChart" height="80px"></canvas>
        <table border="1">
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Buyer</th>
                <th>Product</th>
                <th>Price</th>
                <th>Sold At</th>
                <th>Action</th>
            </tr>
        </thead>    
            <?php if ($result_sales->num_rows > 0): ?>
                <?php while ($row = $result_sales->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['buyer_name']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?> Rs</td>
                        <td><?php echo $row['sold_at']; ?></td>
                        <td>
                        <button class="download-sale-invoice" data-id="<?php echo $row['id']; ?>">Download Invoice</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No sales found.</td></tr>
            <?php endif; ?>
        </table>
        </div>

        <div id="Payment" class="tab-content">
    <h2>My Payment Details</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Cardholder Name</th>
                <th>Billing Address</th>
                <th>Total Price</th>
                <th>Payment Date</th>
            </tr>
        </thead>    
        <?php if (isset($result_payments) && $result_payments->num_rows > 0): ?>
            <?php while ($row = $result_payments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['cardholder_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['billing_address']); ?></td>
                    <td><?php echo number_format($row['total_price'], 2); ?> Rs</td>
                    <td><?php echo $row['payment_date']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No payments found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <b style="font-size: 20px;">Report Order</b><br><br>
        <form method="POST">
            <input type="hidden" name="reported_order_id" id="reported_order_id">
            <textarea name="description" placeholder="Describe the issue" rows="5" cols="50" required></textarea><br>
            <button type="submit" name="submit_report" class="sub-rep-button">Submit Report</button>
        </form>
    </div>
</div>

    <?php } ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

    function openReportModal(button) {
        let orderId = button.getAttribute("data-order-id");
        document.getElementById('reported_order_id').value = orderId;
        document.getElementById('reportModal').style.display = 'flex';
    }

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

    function showTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });

    // Remove 'active' class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show the selected tab and mark button as active
    document.getElementById(tabId).style.display = 'block';
    document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
    }

    document.addEventListener("DOMContentLoaded", function () {
    showTab('all-reports'); // Default to "MY Reports"
});

function toggleDetails(productId) {
    let detailsDiv = document.getElementById('details-' + productId);
    
    if (detailsDiv) {
        // Toggle visibility
        if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
            detailsDiv.style.display = 'block';
        } else {
            detailsDiv.style.display = 'none';
        }
    } else {
        console.error("Details section not found for product ID: " + productId);
    }
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
document.addEventListener("DOMContentLoaded", function () {
    // Order Cancellation
    document.querySelectorAll(".cancel-order").forEach(button => {
        button.addEventListener("click", function () {
            let orderId = this.dataset.id;
            if (confirm("Are you sure you want to cancel this order?")) {
                fetch(window.location.href, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "cancel_order=" + orderId
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === "success") {
                        alert("Order canceled successfully.");
                        location.reload();
                    } else {
                        alert("Failed to cancel order.");
                    }
                });
            }
        });
    });

    // Download Invoice
    document.querySelectorAll(".download-invoice").forEach(button => {
        button.addEventListener("click", function () {
            let orderId = this.dataset.id;
            window.location.href = "?download_order=" + orderId;
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".download-sale-invoice").forEach(button => {
        button.addEventListener("click", function () {
            let saleId = this.getAttribute("data-id");
            if (saleId) {
                window.location.href = "user_details.php?download_sale=" + saleId;
            }
        });
    });
});


    const monthlyLabels = <?php echo json_encode($monthlyLabels); ?>;
    const monthlyData = <?php echo json_encode($monthlyData); ?>;
    const weeklyLabels = <?php echo json_encode($weekly_labels); ?>;
    const weeklyData = <?php echo json_encode($weekly_sales); ?>;

    let isMonthly = true;

    const ctx = document.getElementById('userSalesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Monthly Sales (Rs)',
                data: monthlyData,
                borderColor: 'white',
                backgroundColor: 'rgba(247, 247, 247, 0.31)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Sales Overview',
                    color: 'white',
                    font: { size: 18 }
                },
                legend: {
                    labels: { color: 'white' },
                    onClick: function(e, legendItem, legend) {
                        isMonthly = !isMonthly;
                        const chart = legend.chart;
                        chart.data.labels = isMonthly ? monthlyLabels : weeklyLabels;
                        chart.data.datasets[0].label = isMonthly ? 'Monthly Sales (Rs)' : 'Weekly Sales (Rs)';
                        chart.data.datasets[0].data = isMonthly ? monthlyData : weeklyData;
                        chart.options.plugins.title.text = isMonthly
                            ? 'Monthly Sales Overview (Click Legend to Toggle)'
                            : 'Weekly Sales Overview (Click Legend to Toggle)';
                        chart.update();
                    }
                },
                tooltip: {
                    bodyColor: 'white',
                    titleColor: 'white'
                }
            },
            scales: {
                x: {
                    ticks: { color: 'white' },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' }
                },
                y: {
                    ticks: {
                        color: 'white',
                        callback: value => value + ' Rs'
                    },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    beginAtZero: true
                }
            }
        }
    });

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
