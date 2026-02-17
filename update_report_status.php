<?php
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['status'])) {
    $report_id = intval($_POST['id']);
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $report_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        error_log(print_r($stmt->error, true));
        echo json_encode(["success" => false]);
    }

    $stmt->close();
    $conn->close(); // Close the connection here
}
?>