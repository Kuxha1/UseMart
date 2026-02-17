<?php
include 'db_connect.php'; // Database connection

// Run the query and check for errors
$result = $conn->query("SELECT * FROM users");

if (!$result) {
    die("Query failed: " . $conn->error); // Show SQL error message
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    
    $user = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $contact_no = $_POST['contact_no'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, address=?, contact_no=?, description=? WHERE id=?");
    $stmt->bind_param("sssssi", $fullname, $email, $address, $contact_no, $description, $id);

    if ($stmt->execute()) {
        echo "User updated successfully!";
    } else {
        echo "Error updating user.";
    }

    $stmt->close();
    $conn->close();
    header("Location: user_data.php"); // Redirect back
    exit();
}    
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="style/table_data.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Data</title>
    <div class="header">
        <div class="usemart">
       <img src="resources/A.png" alt="Logo" class="logo" onclick="redirectToadmin()">
       <h1 style="margin-top:9px; margin-left:10px;">Admin Dashboard</h1>
       </div>
       <button class="logout-button" onclick="logout()">Log Out</button>
    </div>   
    <style>
        /* Modal Form Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #2c3e50;
            padding: 20px;
            border-radius: 8px;
            width: 40%;
            position: relative;
            text-align: center;
            color: white;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #16C47F;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #139c66;
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
            <div class="h">All Users</div>
            <button class="downloadData-btn" onclick="downloadUserData()">Download Data</button>
        </div>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Profile</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Address</th>
            <th>Contact No</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr id="user-<?php echo $row['id']; ?>">
            <td><?php echo $row['id']; ?></td>
            <td>
                <img src="<?php echo !empty($row['profile_photo']) ? $row['profile_photo'] : 'resources/pfp.png'; ?>" 
                     width="50" height="50">
            </td>
            <td><?php echo $row['fullname']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['address']; ?></td>
            <td><?php echo $row['contact_no']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <button class="Edit" onclick="editUser(<?php echo $row['id']; ?>)">Edit</button>
                <button class="delete" onclick="deleteUser(<?php echo $row['id']; ?>)">Delete</button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit User</h2>
        <form method="POST" action="">
            <input type="hidden" name="id" id="editId">
            Full Name: <input type="text" name="fullname" id="editFullname"><br>
            Email: <input type="email" name="email" id="editEmail"><br>
            Address: <input type="text" name="address" id="editAddress"><br>
            Contact No: <input type="text" name="contact_no" id="editContact"><br>
            Description: <input type="text" name="description" id="editDescription"><br>
            <button type="submit">Update data</button>
        </form>
    </div>
</div>

<script>
function logout() {
    window.location.href = 'logout.php';
}

function redirectToadmin(){
    window.location.href = 'admin_report.php';
}

function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user?")) {
        // Send AJAX request to delete the user
        fetch('delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${userId}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data); // Show success or error message
            let row = document.getElementById(`user-${userId}`);
            if (row) row.remove(); // Remove the row from UI
        })
        .catch(error => console.error("Error:", error));
    }
}

// Function to open the edit modal and populate fields
function editUser(userId) {
    // Get user data from the table row
    let row = document.getElementById(`user-${userId}`);
    let fullname = row.children[2].innerText;
    let email = row.children[3].innerText;
    let address = row.children[4].innerText;
    let contact_no = row.children[5].innerText;
    let description = row.children[6].innerText;

    // Populate modal fields
    document.getElementById('editId').value = userId;
    document.getElementById('editFullname').value = fullname;
    document.getElementById('editEmail').value = email;
    document.getElementById('editAddress').value = address;
    document.getElementById('editContact').value = contact_no;
    document.getElementById('editDescription').value = description;

    // Show modal
    document.getElementById('editModal').style.display = 'flex';
}

// Function to close modal
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
// Close modal when clicking outside of it
window.onclick = function(event) {
    let modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
}
// Ensure modal is hidden when page loads
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('editModal').style.display = 'none';
});

function downloadUserData() {
    window.location.href = 'download_users.php';
}

</script>


</body>
</html>