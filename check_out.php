<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = $_GET['session_id'];  // Get session ID from the URL
$user_type = $_SESSION['user_type'];
include('db_config.php');


$check_out_time = date('Y-m-d H:i:s');
$update_query = "UPDATE ChargingSessions SET check_out_time = '$check_out_time' WHERE session_id = '$session_id' AND user_id = '$user_id' AND check_out_time IS NULL";

if ($conn->query($update_query) === TRUE) {

    $cost_query = "SELECT cl.cost_per_hour, cs.check_in_time FROM ChargingSessions cs 
                   JOIN ChargingLocations cl ON cs.location_id = cl.location_id
                   WHERE cs.session_id = '$session_id'";

    $cost_result = $conn->query($cost_query);

    if ($cost_result->num_rows > 0) {
        $cost_row = $cost_result->fetch_assoc();
        $cost_per_hour = $cost_row['cost_per_hour'];
        $check_in_time = $cost_row['check_in_time'];

        $duration = strtotime($check_out_time) - strtotime($check_in_time);
        $hours = floor($duration / 3600);
        $total_cost = $cost_per_hour * $hours;

        $update_cost_query = "UPDATE ChargingSessions SET total_cost = '$total_cost' WHERE session_id = '$session_id'";
        $conn->query($update_cost_query);
    }

    echo "Check-out successful.";
    if ($user_type === 'user') {
        echo "<br><a href='user_dash.php'>Go back to user dashboard</a>";
    } else {
        echo "<br><a href='admin_dash.php'>Go back to admin dashboard</a>";
    }
} else {
    echo "Error: " . $update_query . "<br>" . $conn->error;
}

$conn->close();
?>
