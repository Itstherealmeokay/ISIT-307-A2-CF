<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

include('db_config.php');

// Get the user ID 
$user_id = $_GET['user_id'];

$sql = "SELECT * FROM Users WHERE user_id = '$user_id'";
$result = $conn->query($sql);

$session_sql = "SELECT cl.description, cs.session_id, cs.check_out_time, cs.check_in_time FROM ChargingSessions cs
                JOIN ChargingLocations cl ON cs.location_id = cl.location_id
                WHERE cs.user_id = '$user_id' AND cs.check_out_time IS NULL";
$session_result = $conn->query($session_sql);

$location_sql = "SELECT * FROM ChargingLocations";
$location_result = $conn->query($location_sql);

?>

<DOCTYPE html>
    <html>
    <head>
        <title>User Details</title>
    </head>
    <body>
        <h2>User Details</h2>
        <table border="1">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>User Type</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["user_id"] . "</td>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["email"] . "</td>
                            <td>" . $row["user_type"] . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No users found.</td></tr>";
            }
            ?>
        </table>
        <br>
        <form action="check_in.php" method="GET">
            <label for="location_id">Select Location:</label>
            <select name="location_id" id="location_id">
                <?php
                if ($location_result->num_rows > 0) {
                    while($row = $location_result->fetch_assoc()) {
                        echo "<option value='" . $row["location_id"] . "'>" . $row["description"] . "</option>";
                    }
                } else {
                    echo "<option value=''>No locations found.</option>";
                }
                ?>

            </select><br>
            <label for="check_in_time">Enter Check-In Date and Time:</label>
            <input type="datetime-local" name="check_in_time" required>
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="submit" value="Check-in">

        <h2>Check-in Sessions</h2>
        <table border="1">
            <tr>
                <th>Location</th>
                <th>Check-in Time</th>
                <th>Check-out</th>
            </tr>
            <?php
            if ($session_result->num_rows > 0) {
                while($row = $session_result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["description"] . "</td>
                            <td>" . $row["check_in_time"] . "</td>
                            <td><a href='check_out.php?session_id=" . $row["session_id"] . "'>Check Out</a></td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No check-in sessions found.</td></tr>";
            }
            ?>
        </table>

        <button onclick="window.location.href='admin_dash.php';">Back to Admin Dashboard</button>
    </body>
    </html>