<?php
include 'db_connect.php'; // Database connection

// Monthly sales data
$monthly_sales = array_fill_keys(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], 0);

$sales_sql = "SELECT price, sold_at FROM sales";
$sales_result = $conn->query($sales_sql);

if ($sales_result && $sales_result->num_rows > 0) {
    while ($sale = $sales_result->fetch_assoc()) {
        if (!empty($sale['sold_at'])) {
            $month_index = (int)date('n', strtotime($sale['sold_at'])) - 1; // 0 to 11
            $months = array_keys($monthly_sales);
            $month_name = $months[$month_index];
            $monthly_sales[$month_name] += (float)$sale['price'];
        }
    }
}


// Weekly sales data
$weekly_sales_data = [];
$weekly_sales_labels = [];

$current_month = date('n');
$current_year = date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

for ($start_day = 1; $start_day <= $days_in_month; $start_day += 7) {
    $end_day = min($start_day + 6, $days_in_month);
    $weekly_sales_labels[] = "{$start_day}-{$end_day} " . date('M');
    $weekly_sales_data[] = 0; // initialize sales
}

$sales_sql_weekly = "SELECT price, sold_at FROM sales 
                     WHERE MONTH(sold_at) = $current_month AND YEAR(sold_at) = $current_year";
$sales_result_weekly = $conn->query($sales_sql_weekly);

if ($sales_result_weekly && $sales_result_weekly->num_rows > 0) {
    while ($sale = $sales_result_weekly->fetch_assoc()) {
        $day = (int)date('j', strtotime($sale['sold_at']));
        $week_index = floor(($day - 1) / 7); // 0-based index
        if (isset($weekly_sales_data[$week_index])) {
            $weekly_sales_data[$week_index] += (float)$sale['price'];
        }
    }
}



// Handle DELETE request
if (isset($_GET['delete'])) {
    $sale_id = intval($_GET['delete']); // Sanitize input
    $delete_sql = "DELETE FROM sales WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $sale_id);

    if ($stmt->execute()) {
        echo "<script>alert('Sale deleted successfully!'); window.location.href='sells_data.php';</script>";
    } else {
        echo "<script>alert('Error deleting sale.');</script>";
    }

    $stmt->close();
}

// Handle download request
if (isset($_GET['download'])) {
    $filename = "sells_data.txt";
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");

    // Fetch sales data with related names
    $sql = "SELECT sales.id, 
                   seller.fullname AS seller_name, 
                   buyer.fullname AS buyer_name,
                   products.product_name, 
                   sales.price, 
                   sales.sold_at
            FROM sales
            JOIN users AS seller ON sales.seller_id = seller.id
            JOIN users AS buyer ON sales.buyer_id = buyer.id
            JOIN products ON sales.product_id = products.id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $separator = str_repeat("-", 80) . "\n";
        fwrite($output, "Sales Data\n");
        fwrite($output, $separator);
        fwrite($output, "ID | Seller Name | Buyer Name | Product Name | Price | Sold At\n");
        fwrite($output, $separator);

        while ($row = $result->fetch_assoc()) {
            $line = sprintf("%-5s | %-12s | %-12s | %-15s | %-8.2f | %s\n", 
                $row["id"], 
                $row["seller_name"], 
                $row["buyer_name"], 
                $row["product_name"], 
                $row["price"], 
                $row["sold_at"]
            );
            fwrite($output, $line);
        }
        fwrite($output, $separator);
    } else {
        fwrite($output, "No sales data available.\n");
    }

    fclose($output);
    exit;
}

// Fetch sales data for displaying
$sql = "SELECT sales.id, 
               seller.fullname AS seller_name, 
               buyer.fullname AS buyer_name,
               products.product_name, 
               sales.price, 
               sales.sold_at
        FROM sales
        JOIN users AS seller ON sales.seller_id = seller.id
        JOIN users AS buyer ON sales.buyer_id = buyer.id
        JOIN products ON sales.product_id = products.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Data</title>
    <link rel="stylesheet" href="style/table_data.css">

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
            <div class="h">Sales data</div>
            <button class="downloadData-btn" onclick="document.getElementById('exportForm').submit();">Download Data</button>
            <form id="exportForm" method="GET" style="display:none;">
                <input type="hidden" name="download" value="1">
            </form>
    </div>

    <div class="Chart" style="width: 1300px; margin-left: 220px;">
    <canvas id="salesChart" height="100"></canvas>
</div>


    <table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Seller Name</th>
            <th>Buyer Name</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Sold At</th>
            <th>Actions</th>
        </tr>
</thead>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['seller_name']}</td>
                        <td>{$row['buyer_name']}</td>
                        <td>{$row['product_name']}</td>
                        <td>{$row['price']} Rs</td>
                        <td>{$row['sold_at']}</td>
                         <td>
                            <a href='sells_data.php?delete={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this sale?\")'>
                                <button class='delete'>Delete</button>
                            </a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No sales data found</td></tr>";
        }
        ?>
    </table>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function logout() {
    window.location.href = 'logout.php';
}

function redirectToadmin(){
    window.location.href = 'admin_report.php';
}


    const monthlySalesLabels = <?php echo json_encode(array_keys($monthly_sales)); ?>;
    const monthlySalesData = <?php echo json_encode(array_values($monthly_sales)); ?>;
    console.log("Labels:", monthlySalesLabels);
    console.log("Data:", monthlySalesData);
    const weeklySalesLabels = <?php echo json_encode($weekly_sales_labels); ?>;
    const weeklySalesData = <?php echo json_encode($weekly_sales_data); ?>;


    let isMonthly = true;

    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlySalesLabels,
            datasets: [{
                label: 'Monthly Sales (Rs)',
                data: monthlySalesData,
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
        // Detect if the title was clicked
        const title = chart.chartArea;
        const mouseX = evt.offsetX;
        const mouseY = evt.offsetY;

        if (
            mouseX >= title.left &&
            mouseX <= title.right &&
            mouseY <= chart.chartArea.top // click above the chart (where the title is)
        ) {
            toggleChart(chart);
        }
        },
        plugins: {
            title: {
                display: true,
                text: 'Sales Overview',
                color: 'white',
                font: { size: 20 }
            },
            legend: {
                labels: {
                    color: 'white'
                },
                onClick: function (e, legendItem, legend) {
                    // Toggle between monthly and weekly
                    isMonthly = !isMonthly;

                    const chart = legend.chart;
                    chart.data.labels = isMonthly ? monthlySalesLabels : weeklySalesLabels;
                    chart.data.datasets[0].label = isMonthly ? 'Monthly Sales (Rs)' : 'Weekly Sales (Rs)';
                    chart.data.datasets[0].data = isMonthly ? monthlySalesData : weeklySalesData;
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
                    color: 'rgba(255, 255, 255, 0.1)' // optional
                }
            },
            y: {
                ticks: {
                    color: 'white',
                    callback: function(value) {
                        return value + ' Rs';
                    }
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)' // optional
                }
            }
        }
    }
});

function toggleChart(chart) {
    isMonthly = !isMonthly;

    chart.data.labels = isMonthly ? monthlySalesLabels : weeklySalesLabels;
    chart.data.datasets[0].label = isMonthly ? 'Monthly Sales (Rs)' : 'Weekly Sales (Rs)';
    chart.data.datasets[0].data = isMonthly ? monthlySalesData : weeklySalesData;

    chart.options.plugins.title.text = isMonthly
        ? 'Monthly Sales Overview (click to switch)'
        : 'Weekly Sales Overview (click to switch)';

    chart.update();
}
</script>

</body>
</html>

<?php $conn->close(); ?>
