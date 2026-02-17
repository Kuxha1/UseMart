<?php
include 'db_connect.php'; // Database connection

// Handle DELETE request
if (isset($_GET['delete'])) {
    $cart_id = intval($_GET['delete']); // Sanitize input
    $delete_sql = "DELETE FROM cart WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        echo "<script>alert('Item deleted successfully!'); window.location.href='cart_data.php';</script>";
    } else {
        echo "<script>alert('Error deleting item!');</script>";
    }

    $stmt->close();
}

// Handle download request
if (isset($_GET['download'])) {
    $filename = "cart_data.txt";
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $output = fopen("php://output", "w");

    // Fetch cart data with product and user names
    $sql = "SELECT cart.id, users.fullname, products.product_name, cart.added_at 
            FROM cart
            JOIN users ON cart.user_id = users.id
            JOIN products ON cart.product_id = products.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $separator = str_repeat("-", 60) . "\n";
        fwrite($output, "Cart Data\n");
        fwrite($output, $separator);
        fwrite($output, "ID | User Name    | Product Name      | Added At\n");
        fwrite($output, $separator);

        while ($row = $result->fetch_assoc()) {
            $line = sprintf("%-5s | %-12s | %-16s | %s\n", 
                $row["id"], 
                $row["fullname"], 
                $row["product_name"], 
                $row["added_at"]
            );
            fwrite($output, $line);
        }
        fwrite($output, $separator);
    } else {
        fwrite($output, "No cart data available.\n");
    }

    fclose($output);
    exit;
}

// Fetch cart data for displaying on the admin page
$sql = "SELECT cart.id, users.fullname, products.product_name, cart.added_at 
        FROM cart
        JOIN users ON cart.user_id = users.id
        JOIN products ON cart.product_id = products.id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Data</title>
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
        <a href="cart_data.php">Users Cart Data</a>
        <a href="wishlist_data.php">Wishlist Data</a>
        <a href="Payment_data.php">Payment data</a>
    </div>

    <div class="Heading">
            <div class="h">Cart Data</div>
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
                <th>Product</th>
                <th>Added At</th>
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
                        <td>{$row['product_name']}</td>
                        <td>{$row['added_at']}</td>
                        <td>
                            <a href='cart_data.php?delete={$row['id']}' onclick='return confirm(\"Are you sure?\")'>
                                <button class='delete'>Delete</button>
                            </a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No cart data found</td></tr>";
        }
        ?>
        </tbody>
    </table>
    <script>
function logout() {
    window.location.href = 'logout.php';
}

function redirectToadmin(){
    window.location.href = 'admin_report.php';
}
    </script>
</body>
</html>
<?php $conn->close(); ?>