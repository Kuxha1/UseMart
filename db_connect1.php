
<?php
$conn = new mysqli("localhost","root","085279","project");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>