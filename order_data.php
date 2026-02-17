<?php
include 'db_connect.php'; // Database connection

// Weekly Orders (last 5 weeks)
$weekly_orders = [];
$weekly_labels = [];

$weekly_sql = "
    SELECT YEARWEEK(ordered_at, 1) as week, DATE_FORMAT(MIN(ordered_at), '%d %b') as week_start, COUNT(*) as count
    FROM orders
    WHERE ordered_at >= DATE_SUB(CURDATE(), INTERVAL 5 WEEK)
    GROUP BY week
    ORDER BY week ASC
";
$weekly_result = $conn->query($weekly_sql);
if ($weekly_result && $weekly_result->num_rows > 0) {
    while ($row = $weekly_result->fetch_assoc()) {
        $weekly_labels[] = $row['week_start'];
        $weekly_orders[] = (int)$row['count'];
    }
}

// Step 1: Prepare fixed 12-month range from Jan to Dec 2025
$monthly_orders = [];
$monthly_labels = [];

for ($month = 1; $month <= 12; $month++) {
    $date = DateTime::createFromFormat('!m Y', "$month 2025");
    $label = $date->format('M Y'); // e.g., Jan 2025
    $monthly_labels[] = $label;
    $monthly_orders[$label] = 0;
}

// Step 2: Query actual order data for 2025
$monthly_sql = "
    SELECT DATE_FORMAT(ordered_at, '%b %Y') as month, COUNT(*) as count
    FROM orders
    WHERE YEAR(ordered_at) = 2025
    GROUP BY month
    ORDER BY MIN(ordered_at) ASC
";
$monthly_result = $conn->query($monthly_sql);

if ($monthly_result && $monthly_result->num_rows > 0) {
    while ($row = $monthly_result->fetch_assoc()) {
        if (isset($monthly_orders[$row['month']])) {
            $monthly_orders[$row['month']] = (int)$row['count'];
        }
    }
}

// Convert to labels and data arrays
$monthlyOrdersLabels = array_keys($monthly_orders);
$monthlyOrdersData = array_values($monthly_orders);


// Handle DELETE request
if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']); // Sanitize input
    $delete_sql = "DELETE FROM orders WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo "<script>alert('Order deleted successfully!'); window.location.href='order_data.php';</script>";
    } else {
        echo "<script>alert('Error deleting order.');</script>";
    }

    $stmt->close();
}

// Handle status update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// Handle download request
if (isset($_GET['download'])) {
    $filename = "orders_data.txt";
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");

    // Fetch orders data
    $sql = "SELECT orders.id, 
                   users.fullname AS buyer_name, 
                   products.product_name, 
                   orders.price, 
                   orders.status, 
                   orders.ordered_at
            FROM orders
            JOIN users ON orders.user_id = users.id
            JOIN products ON orders.product_id = products.id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $separator = str_repeat("-", 90) . "\n";
        fwrite($output, "Orders Data\n");
        fwrite($output, $separator);
        fwrite($output, "ID | Buyer Name | Product Name | Price | Status | Ordered At\n");
        fwrite($output, $separator);

        while ($row = $result->fetch_assoc()) {
            $line = sprintf("%-5s | %-12s | %-15s | %-8.2f | %-10s | %s\n", 
                $row["id"], 
                $row["buyer_name"], 
                $row["product_name"], 
                $row["price"], 
                $row["status"], 
                $row["ordered_at"]
            );
            fwrite($output, $line);
        }
        fwrite($output, $separator);
    } else {
        fwrite($output, "No orders data available.\n");
    }

    fclose($output);
    exit;
}

// Fetch orders data for display
$sql = "SELECT orders.id, 
               users.fullname AS buyer_name, 
               products.product_name, 
               orders.price, 
               orders.status, 
               orders.ordered_at
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN products ON orders.product_id = products.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders Data</title>
    <link rel="stylesheet" href="style/table_data.css">
    <div class="header">
        <div class="usemart">
            <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToAdmin()">
            <h1 style="margin-top:9px; margin-left:10px;">Admin Dashboard</h1>
        </div>
        <button class="logout-button" onclick="logout()">Log Out</button>
    </div> 
</head>
<body>
    <div class="sidebar">
        <a href="admin_report.php">Reports</a>
        <a href="order_data.php">Orders Data</a>
        <a href="sells_data.php">Sells Data</a>
        <a href="user_data.php">Users Data</a>
        <a href="product_data.php">Products Data</a>
        <a href="post_data.php">Posts Data</a>
        <a href="cart_data.php">Users Cart Data</a>
        <a href="wishlist_data.php">Wishlist Data</a>
        <a href="Payment_data.php">Payment data</a>
    </div>

    <div class="Heading">
        <div class="h">Orders Data</div>
        <button class="downloadData-btn" onclick="document.getElementById('exportForm').submit();">Download Data</button>
        <form id="exportForm" method="GET" style="display:none;">
            <input type="hidden" name="download" value="1">
        </form>
    </div>

    <div class="Chart" style="width: 1300px; margin-left: 220px;">
    <canvas id="ordersChart" height="100"></canvas>
    </div>


    <table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Buyer Name</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Status</th>
            <th>Ordered At</th>
            <th>Actions</th>
        </tr>
    </thead>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['buyer_name']}</td>
                        <td>{$row['product_name']}</td>
                        <td>{$row['price']} Rs</td>
                        <td>
                            <select id='status_{$row['id']}' onchange='updateStatus({$row['id']})'>
                                <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                <option value='Shipped' " . ($row['status'] == 'Shipped' ? 'selected' : '') . ">Shipped</option>
                                <option value='Delivered' " . ($row['status'] == 'Delivered' ? 'selected' : '') . ">Delivered</option>
                            </select>
                        </td>
                        <td>{$row['ordered_at']}</td>
                        <td>
                            <a href='order_data.php?delete={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this order?\")'>
                                <button class='delete'>Delete</button>
                            </a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No orders data found</td></tr>";
        }
        ?>
    </table>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function logout() {
            window.location.href = 'logout.php';
        }

        function redirectToAdmin(){
            window.location.href = 'admin_report.php';
        }

        function updateStatus(orderId) {
            var newStatus = document.getElementById('status_' + orderId).value;
            $.post('order_data.php', { update_status: 1, order_id: orderId, new_status: newStatus }, function(response) {
                if (response.trim() === "success") {
                    alert("Order status updated successfully!");
                    location.reload();
                } else {
                    alert("Failed to update status.");
                }
            });
        }
   

    const monthlyOrdersLabels = <?php echo json_encode($monthlyOrdersLabels); ?>;
    const monthlyOrdersData = <?php echo json_encode($monthlyOrdersData); ?>;
    const weeklyOrdersLabels = <?php echo json_encode($weekly_labels); ?>;
    const weeklyOrdersData = <?php echo json_encode($weekly_orders); ?>;

    let isMonthly = true;

    const ctx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyOrdersLabels,
            datasets: [{
                label: 'Monthly Orders',
                data: monthlyOrdersData,
                borderColor: 'rgb(255, 255, 255)',
                backgroundColor: 'rgba(15, 65, 92, 0.6)',
                tension: 0.3,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            onClick: function(evt, elements, chart) {
                const title = chart.chartArea;
                const mouseX = evt.offsetX;
                const mouseY = evt.offsetY;

                if (
                    mouseX >= title.left &&
                    mouseX <= title.right &&
                    mouseY <= chart.chartArea.top
                ) {
                    toggleOrderChart(chart);
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Orders Overview',
                    color: 'white',
                    font: { size: 20 }
                },
                legend: {
                    labels: {
                        color: 'white'
                    },
                    onClick: function (e, legendItem, legend) {
                        isMonthly = !isMonthly;

                        const chart = legend.chart;
                        chart.data.labels = isMonthly ? monthlyOrdersLabels : weeklyOrdersLabels;
                        chart.data.datasets[0].label = isMonthly ? 'Monthly Orders (Rs)' : 'Weekly Orders(Rs)';
                        chart.data.datasets[0].data = isMonthly ? monthlyOrdersData : weeklyOrdersData;
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
                    ticks: {
                        color: 'white'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: 'white',
                        callback: function(value) {
                            return value + ' orders';
                        }
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });

    function toggleOrderChart(chart) {
        isMonthly = !isMonthly;
        chart.data.labels = isMonthly ? monthlyOrdersLabels : weeklyOrdersLabels;
        chart.data.datasets[0].label = isMonthly ? 'Monthly Orders' : 'Weekly Orders';
        chart.data.datasets[0].data = isMonthly ? monthlyOrdersData : weeklyOrdersData;
        chart.options.plugins.title.text = isMonthly
            ? 'Monthly Orders Overview (click to switch)'
            : 'Weekly Orders Overview (click to switch)';
        chart.update();
    }
</script>


</body>
</html>

<?php $conn->close(); ?>
