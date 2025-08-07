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

$query = "SELECT * FROM ChargingLocations";
$result = $conn->query($query);



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
        $query = "SELECT cs.location_id, cl.description, cs.check_in_time
                    FROM ChargingSessions cs
                    JOIN ChargingLocations cl ON cs.location_id = cl.location_id";
                    
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
                                <td><a href='check_out.php'>Check Out</a></td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No check-in sessions found.</td></tr>";
                }
                ?>
            </table>
        </div>

        <button><a href="logout.php">Logout</a></button>




    </body>
    </html>