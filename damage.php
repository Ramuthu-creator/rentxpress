<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car rental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$user_id = $_POST['userId'];
$vehicle_id = $_POST['model'];
$report_date = $_POST['Report-Date'];
$description = $_POST['description'];

// Insert into damage_report table
$sql = "INSERT INTO damage_report (user_id, vehicle_id, report_date, description)
        VALUES ('$user_id', '$vehicle_id', '$report_date', '$description')";

if ($conn->query($sql) === TRUE) {
    echo "Damage report submitted successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
