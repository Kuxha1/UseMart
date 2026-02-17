<?php

/**
 * Project Name: UseMart – Second-Hand Goods Marketplace
 * Author: Kushal Mistry
 * Copyright (c) 2026 Kushal Mistry
 * All rights reserved.
 */

session_start();

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if the email and password match the hardcoded admin credentials
    if ($email === "admin@gmail.com" && $password === "admin") {
        $_SESSION['user_id'] = 0; // Admin doesn't have a user ID in DB
        $_SESSION['fullname'] = "Admin";
        $_SESSION['is_admin'] = true; // Mark user as admin

        echo "<script>alert('Admin login successful!'); window.location.href='admin_report.php';</script>";
        exit();
    }


    // Check user login from database
    $stmt = $conn->prepare("SELECT id, fullname, password FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $fullname, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['fullname'] = $fullname;

            echo "<script>alert('Login successful!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Incorrect password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User not found. Please register!'); window.location.href='register.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
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
        .login-container {
            background-color: #16C47F;
            padding: 25px;
            border-radius: 10px;
            position: relative;
            border: 3px solid white;

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
        .input-field {
            width: 100%;
            padding: 10px;
            margin: 10px 0px 0px ;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .button {
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
        .register-button {
            margin-left: 10px;
        }
        .button:hover {
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
</head>
<header><img src="resources/A.png" alt="" height="500px" width="500px" onclick="redirectToHome()"></header>
<body>
<div class="login-container">
        <button class="cancel-button" onclick="redirectToHome()">&times;</button>
        <h2>Login/Register</h2>
        
        <form action="login.php" method="POST">
            <input type="email" name="email" class="input-field" placeholder="Email..." required>
            <input type="password" name="password" class="input-field" placeholder="Password..." required>
            <div>
                <button type="submit" class="button login-button">Login</button>
                <button type="button" class="button register-button" onclick="redirectToRegister()">Register</button>
            </div>
        </form>
    </div>
    <script>
        function redirectToHome() {
            window.location.href = 'index.php';
        }
        function redirectToRegister() {
            window.location.href = 'Register.php';
        }
    </script>
</body>
</html>
