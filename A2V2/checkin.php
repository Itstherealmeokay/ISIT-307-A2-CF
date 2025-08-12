<?php
session_start();

if (!isset($_SESSION['email'])) {
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
    $user_id = $_GET['user_id'];
    $check_in_time = $_GET['check_in_time'];
    $chargingSession->checkIn($user_id, $location_id, $check_in_time);
}
//use assoc
$price_hour =  $chargingLocation->getPrice_Per_Hour($location_id)->fetch_assoc()['cost_per_hour'];



?>

<!DOCTYPE html>
<html>
<head>
    <title>Check In</title>
</head>
<body>
    <p>Check In successful!</p>
    <p>Price per hour: $<?php echo $price_hour; ?></p>
    <?php
    if ($_SESSION['user_type'] == 'admin') {
        echo "<a href='admin_dash.php'>Go to Admin Dashboard</a>";    
    } else {
        echo "<a href='user_dash.php'>Go to User Dashboard</a>";    
    }
    ?>
</body>
</html>
    