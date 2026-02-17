<?php
include 'db_connect.php'; // Database connection

// Fetch wishlist data
$result = $conn->query("SELECT w.id, u.fullname, p.product_name, w.added_at FROM wishlist w
                        JOIN users u ON w.user_id = u.id
                        JOIN products p ON w.product_id = p.id");

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Handle wishlist deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM wishlist WHERE id = $delete_id");
    header("Location: wishlist_data.php");
    exit();
}

// Function to generate and download wishlist data in TXT table format
function downloadWishlistData($conn) {
    $filename = "wishlist_data.txt";
    $file = fopen($filename, "w");
    
    $export_result = $conn->query("SELECT w.id, u.fullname, p.product_name, w.added_at FROM wishlist w
                                   JOIN users u ON w.user_id = u.id
                                   JOIN products p ON w.product_id = p.id");
    
    $separator = str_repeat("-", 70) . "\n";
    fwrite($file, $separator);
    fwrite($file, "| ID  | User Name           | Product Name       | Added At         |\n");
    fwrite($file, $separator);
    
    while ($row = $export_result->fetch_assoc()) {
        fwrite($file, sprintf("| %-4d | %-18s | %-18s | %-17s |\n",
            $row['id'], $row['fullname'], $row['product_name'], $row['added_at']));
    }
    
    fwrite($file, $separator);
    fclose($file);
    
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=$filename");
    readfile($filename);
    unlink($filename);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['export'])) {
    downloadWishlistData($conn);
}
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
            <div class="h">Wishlist data</div>
            <button class="downloadData-btn" onclick="document.getElementById('exportForm').submit();">Download Data</button>
            <form id="exportForm" method="POST" style="display:none;">
                <input type="hidden" name="export" value="1">
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
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['fullname']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['added_at']; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button class="delete" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
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
