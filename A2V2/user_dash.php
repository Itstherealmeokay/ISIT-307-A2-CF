<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'user') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];


include('classes.php');


include('db_config.php');

$db = new Database($host, $username, $password, $dbname);

$chargingLocation = new ChargingLocation($db);
$chargingSession = new ChargingSession($db);

if (isset($_GET['search_location'])) {
    $location_result = $chargingLocation->searchLocations($_GET['search_location']);
} else {
    $location_result = $chargingLocation->getAvailableLocations();
}


$active_sessions = $chargingSession->getActiveSessions($user_id);

$completed_sessions = $chargingSession->getCheckoutSessions($user_id);

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        .table-container {
            margin-bottom: 20px;
        }

        .checkin-container {
            margin: 20px;
            padding: 10px;
            border: 2px solid #ccc;
            display: flex;
            flex-direction: column;
            line-height: 2.5;
            justify-content: space-between;

        }

        div.container {
            margin: 20px;
            padding: 10px;
            justify-content: space-between;
            border: 2px solid #ccc;
        }

        div.logout-container {
            margin: 20px;
            justify-content: space-between;
    
        }

        .logout-button {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        
    </style>
</head>
<body>

<h1>Welcome, User! <?php echo $name; ?></h1>

<div class="container">
    <h2> Available Charging Locations</h2>

    <form action="user_dash.php" method="GET">
        <input type="text" name="search_location" placeholder="Search Location">
        <input type="submit" value="Search">
    </form>

    <hr>
    <div class="table-container">
        <table border="1">
            <tr>
                <th>Location ID</th>
                <th>Description</th>
                <th>Stations in Use</th>
                <th>Number of Stations</th>
                <th>Cost per Hour</th>
            </tr>

            <?php
            // Display all available charging locations
            if ($location_result->num_rows > 0) {
                while($row = $location_result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["location_id"] . "</td>
                            <td>" . $row["description"] . "</td>
                            <td>" . $row["active_sessions"] . "</td>
                            <td>" . $row["num_stations"] . "</td>
                            <td>" . $row["cost_per_hour"] . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No charging locations found.</td></tr>";
            }
            ?>

        </table>
    </div>
</div>
<div class="checkin-container">
    <b>Check-in Session</b>
    <form action="checkin.php" method="GET">
        <label for="location_id">Select Location:</label>
        <select name="location_id" required>
            <?php
            $dropdown_result = $chargingLocation->getAvailableLocations();
            if ($dropdown_result->num_rows > 0) {
                while($row = $dropdown_result->fetch_assoc()) {
                    echo "<option value='" . $row["location_id"] . "'>" . $row["description"] . "</option>";
                }
            }
            ?>
        </select><br>
        <label for="check_in_time">Enter Check-In Date and Time:</label>
        <input type="datetime-local" name="check_in_time" required>
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="submit" value="Check-in">
    </form>
</div>

<div class="container">
    <h2>Active Check-in Sessions</h2>
    <div class="table-container">
        <table border="1">
            <tr>
                <th>Location</th>
                <th>Check-in Time</th>
                <th>Check-out</th>
            </tr>

            <?php
            // Display active sessions
            $user_id = $_SESSION['user_id'];
            $timezone = new DateTimeZone('Asia/Singapore');
            
            if ($active_sessions->num_rows > 0) {
                while($row = $active_sessions->fetch_assoc()) {
                    
                    echo "<tr>
                            <td>" . $row["description"] . "</td>
                            <td>" . $row["check_in_time"] . "</td>
                            <td><a href='checkout.php?session_id=" . $row["session_id"] . "&user_id=" . $user_id . "&check_out_time=" . date('Y-m-d H:i:s') . "'>Check-out</a></td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No active sessions found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>
<div class="container">
    <h2>Completed Check-out Sessions</h2>
    <div class="table-container">
        <table border="1">
            <tr>
                <th>Location</th>
                <th>Check-in Time</th>
                <th>Check-out Time</th>
                <th>Total Cost</th>
            </tr>

            <?php
            // Display completed sessions
            // set the timezone to Asia/Singapore
            date_default_timezone_set('Asia/Singapore');
            if ($completed_sessions->num_rows > 0) {
                while($row = $completed_sessions->fetch_assoc()) {
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
        </table>
    </div>
</div>

<div class="logout-container">
    <button onclick="window.location.href='logout.php';" class="logout-button">Logout</button></button>
</div>

</body>
</html>
