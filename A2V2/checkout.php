<?php
session_start();


if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['user_id'];
$session_id = $_GET['session_id'];  
$user_type = $_SESSION['user_type'];


include('classes.php');
include('db_config.php');

$db = new Database($host, $username, $password, $dbname);

$chargingSession = new ChargingSession($db);


if (isset($session_id)) {
    $session_result = $chargingSession->getActiveSessions($user_id);

    if ($session_result->num_rows > 0) {
        $session_row = $session_result->fetch_assoc();
        
        $check_in_time = $session_row['check_in_time'];
        $location_id = $session_row['location_id'];

        date_default_timezone_set('Asia/Singapore');
        
        $check_out_time = date('Y-m-d H:i:s');

        $total_cost = $chargingSession->calculateTotalCost($session_id);

        $chargingSession->checkOut($session_id, $check_out_time, $total_cost);

        echo "Successfully checked out!";
    } else {
        echo "No active sessions found for the user.";
    }
} else {
    echo "Session ID is missing.";
}

$db->close(); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <style>
        .checkout-container {
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

        .checkout-container p {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
        }

        .checkout-container p:first-child {
            font-weight: bold;
            font-size: 22px;
        }

        .checkout-container a {
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

        .checkout-container a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <p>Checkout successful!</p>
        <p>Total Cost: $<?php echo number_format($total_cost, 2); ?></p>

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
