<?php
include 'db.php';

$success = false;

$vehicleName = $_GET['name'] ?? '';
$vehicleImage = $_GET['image'] ?? '';
$vehicleType = $_GET['type'] ?? '';
$dailyRate = $_GET['dailyRate'] ?? 0;
$weeklyRate = $_GET['weeklyRate'] ?? 0;
$monthlyRate = $_GET['monthlyRate'] ?? 0;
$vehicleId = $_GET['vehicleId'] ?? '';
$subscriptionId = $_GET['subscriptionId'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $subscription_id = $_POST['subscription_id'] ?? null;
    $start_date = $_POST['pickup-date'];
    $end_date = $_POST['return-date'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if (empty($vehicle_id)) {
        die("Error: vehicle_id is empty. Cannot proceed.");
    }

    // Calculate rental days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;

    $total_cost = 0;

    if (!empty($subscription_id)) {
        $sub_sql = "SELECT duration_days, price FROM `car rental`.`subscription` WHERE subscription_id = ?";
        $stmt = $conn->prepare($sub_sql);
        $stmt->bind_param("i", $subscription_id);
        $stmt->execute();
        $sub_result = $stmt->get_result();

        if ($sub_result->num_rows > 0) {
            $sub = $sub_result->fetch_assoc();
            $cycles = ceil($days / $sub['duration_days']);
            $total_cost = $cycles * $sub['price'];
        }

        $stmt->close();
    } else {
        $veh_sql = "SELECT costperday FROM `car rental`.`vehicle` WHERE vehicle_id = ?";
        $stmt = $conn->prepare($veh_sql);
        $stmt->bind_param("i", $vehicle_id);
        $stmt->execute();
        $veh_result = $stmt->get_result();

        if ($veh_result->num_rows > 0) {
            $veh = $veh_result->fetch_assoc();
            $total_cost = $days * $veh['costperday'];
        } else {
            die("Error: vehicle_id not found in database.");
        }

        $stmt->close();
    }

    // Insert rental record — handle NULL for subscription_id
    if (!empty($subscription_id)) {
        $insert_sql = "INSERT INTO `car rental`.`rental table` (user_id, vehicle_id, subscription_id, start_date, end_date, total_cost)
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiissi", $user_id, $vehicle_id, $subscription_id, $start_date, $end_date, $total_cost);
    } else {
        $insert_sql = "INSERT INTO `car rental`.`rental table` (user_id, vehicle_id, subscription_id, start_date, end_date, total_cost)
                       VALUES (?, ?, NULL, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iisss", $user_id, $vehicle_id, $start_date, $end_date, $total_cost);
    }

    if ($stmt->execute()) {
        $success = true;
        echo "Booking successful!";
    } else {
        echo "Insert failed: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
