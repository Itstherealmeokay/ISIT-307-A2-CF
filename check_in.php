<?php
session_start();

include('db_config.php');

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  
$location_id = $_GET['location_id'];  



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
    $check_in_time = date('Y-m-d H:i:s');
    $insert_query = "INSERT INTO ChargingSessions (user_id, location_id, check_in_time) VALUES ('$user_id', '$location_id', '$check_in_time')";
    
    if ($conn->query($insert_query) === TRUE) {
        echo "You have successfully checked in!";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "No available stations at this location.";
    
}

$conn->close();
?>

<DOCTYPE html>
    <html>
    <head>
        <title>Check In</title>
    </head>
    <body>
        <br>
        <button onclick="window.location.href='user_dash.php';">Back</button>
    </body>
    </html>