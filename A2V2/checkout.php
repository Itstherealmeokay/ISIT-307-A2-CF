<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = $_GET['session_id'];  // Get session ID from URL
$user_type = $_SESSION['user_type'];

// Include necessary classes
include('classes.php');
include('db_config.php');

// Create the Database object
$db = new Database($host, $username, $password, $dbname);

// Create ChargingSession object
$chargingSession = new ChargingSession($db);

// Ensure that the session ID exists
if (isset($session_id)) {
    // Get the current session's check-in time and cost per hour
    $session_result = $chargingSession->getActiveSessions($user_id);

    if ($session_result->num_rows > 0) {
        $session_row = $session_result->fetch_assoc();
        
        // Get check-in time from session
        $check_in_time = $session_row['check_in_time'];
        $location_id = $session_row['location_id'];

        // Assuming you're passing the check-out time from the form
        if (isset($_GET['check_out_time'])) {
            $check_out_time = $_GET['check_out_time'];
            $check_out_time = $db->escape_string($check_out_time);
            
            // Calculate the total cost based on check-in and check-out times
            $total_cost = $chargingSession->calculateTotalCost($session_id);

            // Update the session with the check-out time and total cost
            $chargingSession->checkOut($session_id, $check_out_time, $total_cost);

            echo "Successfully checked out!";
        }
    } else {
        echo "No active sessions found for the user.";
    }
} else {
    echo "Session ID is missing.";
}

$db->close();  // Close the database connection
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>
<body>
    <p>Checkout successful!</p><br>
    <p>Paid: $<?php echo $total_cost, 2; ?></p>

    <a href="user_dash.php">Back to Dashboard</a>
</body>
</html>
