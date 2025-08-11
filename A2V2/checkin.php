<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'user') {
    header("Location: login.php");
    exit();
}

include('classes.php');
include('db_config.php');

$db = new Database($host, $username, $password, $dbname);
$chargingLocation = new ChargingLocation($db);
$chargingSession = new ChargingSession($db);

if (isset($_GET['location_id'])) {
    $location_id = $_GET['location_id'];
    $user_id = $_SESSION['user_id'];
    $check_in_time = $_GET['check_in_time'];

    //if admin user_id = Get
    if ($_SESSION['user_type'] == 'admin') {
        $user_id = $_GET['user_id'];
    }
    $chargingSession->checkIn($user_id, $location_id, $check_in_time);
}

$price_hour = $chargingLocation->getPrice_Per_Hour($location_id); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check In</title>
</head>
<body>
    <p>Check In successful!</p><br>
    <p>Price Per Hour : $<?php echo $price_hour; ?></p>

    <a href="user_dash.php">Back to Dashboard</a>
</body>
</html>
    