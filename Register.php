<?php
include "db_connect.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $contact_no = trim($_POST['contact_no']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt password

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('Error: User already exists with this email!'); window.location.href='register.php';</script>";
    } else {
        // Prepare and execute SQL statement
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, address, contact_no, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $address, $contact_no, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
    
    $check_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color:#2c3e50;
            
        }
        header img{
            height: 500px;
            width: 500px;
            margin-right: 100px;
        }
        .register-container {
            background-color: #16C47F;
            padding: 25px;
            border-radius: 10px;
            position: relative;
            border: 3px solid white;
            width: 40%;
        }
        .cancel-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: white;
        }
        .cancel-button:hover {
            transform: scale(1.1);
        }
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background: none;
            border: none;
            font-size: 30px;
            cursor: pointer;
            color: white;
        }
        .back-button:hover {
            transform: scale(1.1);

        }


        .input-field {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .register-button {
            background-color: #2c3e50;
            width: 48%;
            padding: 10px;
            margin-top: 10px;
            border: 5px solid white;
            border-radius: 15px;
            cursor: pointer;
            font-size: 20px;
            color: white;

        }
        .register-button:hover {
            background-color: hsl(0, 0%, 100%);
            color: #2c3e50 ;
            border: 5px solid #2c3e50;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.8);
        }    
        h2 {
            color: white;
            text-align: center;
        }
    </style>
    <header><img src="resources/A.png" alt="" height="500px" width="500px" onclick="redirectToHome()"></header>
</head>
<body>
<div class="register-container">
        <button class="back-button" onclick="redirectToLogin()">&#8678;</button>
        <button class="cancel-button" onclick="redirectToHome()">&times;</button>
        <h2>Sign-In/Register</h2>
        
        <form action="register.php" method="POST">
            <input type="text" name="fullname" class="input-field" placeholder="Fullname.." required>
            <input type="email" name="email" class="input-field" placeholder="Email..." required>
            <input type="text" name="address" class="input-field" placeholder="Address">
            <input type="text" name="contact_no" class="input-field" placeholder="Mobile No.." required>
            <input type="password" name="password" class="input-field" placeholder="Password..." required>
            <center>
            <div>
                <button type="submit" class="register-button">Register</button>
            </div>
            </center>
        </form>
    </div>
    <script>
        function redirectToLogin() {
            window.location.href = 'login.php';;
        }
        function redirectToHome() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
