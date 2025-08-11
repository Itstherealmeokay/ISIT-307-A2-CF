<?php
session_start();

include('db_config.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  
$location_id = $_GET['location_id'];  
$user_type = $_SESSION['user_type'];

if ($user_type == 'admin') {
    $user_id = $_GET['user_id'];
}



// Check if the location has available stations
$query = "SELECT num_stations FROM ChargingLocations WHERE location_id = '$location_id'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$num_stations = $row['num_stations'];

// Check the number of currently used stations at this location
$used_query = "SELECT COUNT(*) AS used FROM ChargingSessions WHERE location_id = '$location_id' AND check_out_time IS NULL";
$used_result = $conn->query($used_query);
$used_row = $used_result->fetch_assoc();
$used_stations = $used_row['used'];

// If there is an available station, allow check-in
if ($used_stations < $num_stations) {
    // Insert new check-in record
    $check_in_time = $_GET['check_in_time'];
    $insert_query = "INSERT INTO ChargingSessions (user_id, location_id, check_in_time) VALUES ('$user_id', '$location_id', '$check_in_time')";
    
    if ($conn->query($insert_query) === TRUE) {
        echo "You have successfully checked in!";
        if ($user_type == 'admin') {
            echo "<a href='admin_dash.php'>Go back to admin dashboard</a>";
        } else {
            echo "<a href='user_dash.php'>Go back to user dashboard</a>";
        }
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "No available stations at this location.";
    echo "<br>";
    if ($user_type == 'admin') {
        echo "<a href='admin_dash.php'>Go back to admin dashboard</a>";
    } else {
        echo "<a href='user_dash.php'>Go back to user dashboard</a>";
    }
    
}

$conn->close();
?>
