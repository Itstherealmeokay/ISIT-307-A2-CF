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
    <style>
        

        .checkin-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 30px;
            text-align: center;
            width: 80%;
            max-width: 400px;
            margin: 0 auto;
        }

        .checkin-container p {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
        }

        .checkin-container p:first-child {
            font-weight: bold;
            font-size: 22px;
        }

        .checkin-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .checkin-container a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="checkin-container">
        <p>Check In successful!</p>
        <p>Price per hour: $<?php echo $price_hour; ?></p>
        <?php
        if ($_SESSION['user_type'] == 'admin') {
            echo "<a href='admin_dash.php'>Go to Admin Dashboard</a>";    
        } else {
            echo "<a href='user_dash.php'>Go to User Dashboard</a>";    
        }
        ?>
    </div>
</body>
</html>

    