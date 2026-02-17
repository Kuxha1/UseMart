<?php
include 'db_connect.php'; // Database connection

// Handle DELETE request
if (isset($_GET['delete'])) {
    $payment_id = intval($_GET['delete']); // Sanitize input
    $delete_sql = "DELETE FROM payments WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $payment_id);

    if ($stmt->execute()) {
        echo "<script>alert('Payment deleted successfully!'); window.location.href='payment_data.php';</script>";
    } else {
        echo "<script>alert('Error deleting payment!');</script>";
    }

    $stmt->close();
}

// Handle download request
if (isset($_GET['download'])) {
    $filename = "payment_data.txt";
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");

    // Fetch payment data
    $sql = "SELECT payments.id, users.fullname, payments.cardholder_name, 
                   payments.billing_address, payments.total_price, payments.payment_date 
            FROM payments
            JOIN users ON payments.user_id = users.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $separator = str_repeat("-", 80) . "\n";
        fwrite($output, "Payment Data\n");
        fwrite($output, $separator);
        fwrite($output, "ID  | User Name   | Cardholder Name | Billing Address | Total Price | Payment Date\n");
        fwrite($output, $separator);

        while ($row = $result->fetch_assoc()) {
            $line = sprintf("%-4s | %-12s | %-16s | %-18s | %-10s | %s\n", 
                $row["id"], 
                $row["fullname"], 
                $row["cardholder_name"], 
                $row["billing_address"], 
                number_format($row["total_price"], 2) . " Rs", 
                $row["payment_date"]
            );
            fwrite($output, $line);
        }
        fwrite($output, $separator);
    } else {
        fwrite($output, "No payment data available.\n");
    }

    fclose($output);
    exit;
}

// Fetch payment data for displaying on the admin page
$sql = "SELECT payments.id, users.fullname, payments.cardholder_name, 
               payments.billing_address, payments.total_price, payments.payment_date 
        FROM payments
        JOIN users ON payments.user_id = users.id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Data</title>
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
        <a href="sells_data.php">Sales Data</a>
        <a href="user_data.php">Users Data</a>
        <a href="product_data.php">Products Data</a>
        <a href="post_data.php">Posts Data</a>
        <a href="cart_data.php">Users Cart Data</a>
        <a href="wishlist_data.php">Wishlist Data</a>
        <a href="payment_data.php">Payment Data</a>
    </div>

    <div class="Heading">
        <div class="h">Payment Data</div>
        <button class="downloadData-btn" onclick="document.getElementById('exportForm').submit();">Download Data</button>
        <form id="exportForm" method="GET" style="display:none;">
            <input type="hidden" name="download" value="1">
        </form>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Cardholder Name</th>
                <th>Billing Address</th>
                <th>Total Price</th>
                <th>Payment Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['fullname']}</td>
                        <td>{$row['cardholder_name']}</td>
                        <td>{$row['billing_address']}</td>
                        <td>" . number_format($row['total_price'], 2) . " Rs</td>
                        <td>{$row['payment_date']}</td>
                        <td>
                            <a href='payment_data.php?delete={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this payment?\")'>
                                <button class='delete'>Delete</button>
                            </a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No payment data found</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <script>
    function logout() {
        window.location.href = 'logout.php';
    }

    function redirectToAdmin(){
        window.location.href = 'admin_report.php';
    }
    </script>
</body>
</html>
<?php $conn->close(); ?>
