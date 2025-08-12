<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

include('classes.php');
include('db_config.php');

$db = new Database($host, $username, $password, $dbname);
$chargingSession = new ChargingSession($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = $_POST['description']; 
    $num_stations = $_POST['num_stations']; 
    $cost_per_hour = $_POST['cost_per_hour']; 

    $result = $chargingSession->addLocation($description, $num_stations, $cost_per_hour);
    if ($result) {
        echo "Location added successfully";
    } else {
        echo "Error adding location: " . $db->error;
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Add Charging Location</title>
    </head>
    <body>
        <h2>Add Charging Location</h2>
        <form action="create_location.php" method="POST">
            <label for="description">Description:</label><br>
            <input type="text" name="description" required><br><br>

            <label for="num_stations">Number of Stations:</label><br>
            <input type="number" name="num_stations" required><br><br>

            <label for="cost_per_hour">Cost per Hour:</label><br>
            <input type="number" step="0.01" name="cost_per_hour" required><br><br>

            <input type="submit" name="add_location" value="Add Location">
        </form>
        <button onclick="window.location.href='admin_dash.php';">Back</button>
    </body>
</html>