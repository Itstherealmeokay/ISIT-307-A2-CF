<?php
session_start();

// Check if user is logged in, else redirect to login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];
include('db_config.php');

?>

<DOCTYPE html>
    <html>
    <head>
        <title>User Dashboard</title>
        <style>
            .table-container {
            margin-bottom: 20px;
        }
        </style>
    </head>
    <body>
        <?php
        $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
                FROM ChargingLocations cl
                LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
                GROUP BY cl.location_id
                HAVING COUNT(cs.session_id) < cl.num_stations";
        $result = $conn->query($sql);
        ?>
        <h1>Welcome, User! <?php echo $name; ?></h1>
        <div class="table-container">
            <table border="1">
                <tr>
                    <th>Location ID</th>
                    <th>Description</th>
                    <th>Number of Stations</th>
                    <th>Cost per Hour</th>
                </tr>

                <?php
                    // Display all charging locations
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row["location_id"] . "</td>
                                    <td>" . $row["description"] . "</td>
                                    <td>" . ($row["num_stations"] - $row["active_sessions"]) . "</td>
                                    <td>" . $row["cost_per_hour"] . "</td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No charging locations found.</td></tr>";
                    }

                ?>

            </table>
        </div>

        <form action="check_in.php" method="GET">
        <label for="location_id">Select Location:</label>
        <select name="location_id" required>
            <?php
            // Query the database again for the dropdown
            $dropdown_query = "SELECT * FROM ChargingLocations";
            $dropdown_result = $conn->query($dropdown_query);

            if ($dropdown_result->num_rows > 0) {
                while ($row = $dropdown_result->fetch_assoc()) {
                    echo "<option value='" . $row['location_id'] . "'>" . $row['description'] . "</option>";
                }
            }
            ?>
        </select>
        <input type="submit" value="Check-in">
        </form>

        <?php
        // Check In Sessions
        $query = "SELECT cs.location_id, cl.description, cs.check_in_time, cs.session_id
                    FROM ChargingSessions cs
                    JOIN ChargingLocations cl ON cs.location_id = cl.location_id
                    WHERE cs.check_out_time IS NULL";
                    
        $result = $conn->query($query);
        ?>
        <h2>Check-in Sessions</h2>
        <div class="table-container">
            <table border="1">
                <tr>
                    <th>Location</th>
                    <th>Check-in Time</th>
                    <th>Check-out</th>
                </tr>

                <?php
                // Display all check-in sessions
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
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
        </div>
        
        <?php
        // Check Out Sessions   
        $query = "SELECT cs.location_id, cl.description, cs.check_in_time, cs.check_out_time, cs.total_cost
                    FROM ChargingSessions cs
                    JOIN ChargingLocations cl ON cs.location_id = cl.location_id
                    WHERE cs.check_out_time IS NOT NULL";
                    
        $result = $conn->query($query);
        ?>

        <h2>Check-out Sessions</h2>
        <div class="table-container">
            <table border="1">
                <tr>
                    <th>Location</th>
                    <th>Check-in Time</th>
                    <th>Check-out Time</th>
                    <th>Total Cost</th>
                </tr>

                <?php
                // Display all check-out sessions
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["description"] . "</td>
                                <td>" . $row["check_in_time"] . "</td>
                                <td>" . $row["check_out_time"] . "</td>
                                <td>" . $row["total_cost"] . "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No check-out sessions found.</td></tr>";
                }
                ?>
            </table>
        </div>


        <button><a href="logout.php">Logout</a></button>




    </body>
    </html>