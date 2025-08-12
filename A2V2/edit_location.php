<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

include('classes.php');
include('db_config.php');

$db = new Database($host, $username, $password, $dbname);
$chargingLocation = new ChargingLocation($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_location'])) {
    $location_id = $_POST['location_id'];
    $description = $_POST['description'];
    $num_stations = $_POST['num_stations'];
    $cost_per_hour = $_POST['cost_per_hour'];

    // Call the update method from ChargingLocation class
    if ($chargingLocation->updateLocation($location_id, $description, $num_stations, $cost_per_hour)) {
        echo "Charging location updated successfully.";
    } else {
        echo "Error updating location.";
    }
}

// Fetch all charging locations for the dropdown
$locations = $chargingLocation->getAllLocations();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Charging Location</title>
</head>
<body>
    <h2>Edit Charging Location</h2>
    <form action="edit_location.php" method="POST">
        <label for="location_id">Select Location:</label><br>
        <select name="location_id" required>
            <option value="">Select a location</option>
            <?php
            if ($locations->num_rows > 0) {
                while ($row = $locations->fetch_assoc()) {
                    echo '<option value="' . $row['location_id'] . '">' . $row['description'] . '</option>';
                }
            }
            ?>
        </select><br><br>

        <label for="description">Description:</label><br>
        <input type="text" name="description" placeholder="" required><br><br>

        <label for="num_stations">Number of Stations:</label><br>
        <input type="number" name="num_stations" required><br><br>

        <label for="cost_per_hour">Cost per Hour:</label><br>
        <input type="number" step="0.01" name="cost_per_hour" required><br><br>

        <input type="submit" name="update_location" value="Update Location">
    </form>
    <br>
    <button onclick="window.location.href='admin_dash.php';">Back to Admin Dashboard</button>
</body>
</html>