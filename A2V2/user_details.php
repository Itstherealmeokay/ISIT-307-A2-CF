<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}


include('classes.php');
include('db_config.php');

$db = new Database($host, $username, $password, $dbname);
$user = new User($db);
$chargingLocation = new ChargingLocation($db);
$chargingSession = new ChargingSession($db);

$user_id = $_GET['user_id'];

$user_result = $user->getUserbyId($user_id);

$active_sessions = $chargingSession->getActiveSessions($user_id);
$completed_sessions = $chargingSession->getCheckoutSessions($user_id);
$location = $chargingLocation->getAvailableLocations();

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
    <style>
         div.container {
            margin: 20px;
            padding: 10px;
            justify-content: space-between;
            border: 2px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Details</h2>
        <table border="1">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
            </tr>
            <?php
            if ($user_result->num_rows > 0) {
                while ($row = $user_result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["user_id"] . "</td>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["email"] . "</td>
                            <td>" . $row["phone"] . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No user found.</td></tr>";
            }
            ?>
        
        </table><br>

        <form action="checkin.php" method = "GET">
            <label for="location_id">Select Location:</label>
            <select name="location_id" required>
                <?php
                if ($location->num_rows > 0) {
                    while ($row = $location->fetch_assoc()) {
                        echo "<option value='" . $row['location_id'] . "'>" . $row['description'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No locations available</option>";
                }
                ?>
            </select><br><br>
            <label for="check_in_time">Enter Check-In Date and Time:</label>
            <input type="datetime-local" name="check_in_time" required>
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="submit" value="Check-in">
        </form>
    </div>

    <div class="container">
        <h2>Active Sessions</h2>
        <table border="1">
            <tr>
                <th>Location</th>
                <th>Check-In Time</th>
                <th>Check-Out Time</th>
            </tr>
            <?php
            if ($active_sessions->num_rows > 0) {
                while ($row = $active_sessions->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["description"] . "</td>
                            <td>" . $row["check_in_time"] . "</td>
                            <td><a href='checkout.php?session_id=" . $row["session_id"] . "&user_id=" . $user_id . "&check_out_time=" . date('Y-m-d H:i:s') . "'>Check-out</a></td>
                            
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No active sessions found.</td></tr>";
            }
            ?>
        </table>
    </div>

    <div class="container">
        <h2>Completed Sessions</h2>
        <table border="1">
            <tr>
                <th>Location</th>
                <th>Check-In Time</th>
                <th>Check-Out Time</th>
                <th>Total Cost</th>
            </tr>
            <?php
            if ($completed_sessions->num_rows > 0) {
                while ($row = $completed_sessions->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["description"] . "</td>
                            <td>" . $row["check_in_time"] . "</td>
                            <td>" . $row["check_out_time"] . "</td>
                            <td>" . $row["total_cost"] . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No completed sessions found.</td></tr>";
            }
            ?>
        </table><br>
    </div>

    <button onclick="window.location.href='admin_dash.php';" style = "margin-left:20px">Back to Dashboard</button>
</body>
</html>