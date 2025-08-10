<?php
session_start();

// Check if user is logged in, else redirect to login
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];

include('db_config.php');
$sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
        FROM ChargingLocations cl
        LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
        GROUP BY cl.location_id";
$result = $conn->query($sql);

//Filter by full and empty locations
if (isset($_GET['full_locations'])) {
    $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
            FROM ChargingLocations cl
            LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
            GROUP BY cl.location_id
            HAVING COUNT(cs.session_id) = cl.num_stations";
    $result = $conn->query($sql);
} elseif (isset($_GET['empty_locations'])) {
    $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
            FROM ChargingLocations cl
            LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
            GROUP BY cl.location_id
            HAVING COUNT(cs.session_id) < cl.num_stations";
    $result = $conn->query($sql);
}

?>
<DOCTYPE html>
    <html>
    <head>
        <title>Admin Dashboard</title>
        <style>
           div.button-container {
               margin-top: 20px;
               justify-content: space-between;
               
           }
        </style>
    </head>
    <body>
        <h1>Welcome, Admin! <?php echo $name; ?></h1>     
         <!-- Table to display charging locations -->
    <h2>Charging Locations</h2>
    <form action="admin_dash.php" method="GET">
        <label for="full_locations">Filter by Full Locations:</label>
        <input type="checkbox" name="full_locations" value="1">
        <label for="empty_locations">Filter by Available Locations:</label>
        <input type="checkbox" name="empty_locations" value="1">
        <input type="submit" value="Filter">
    </form>
    <hr>
    <table border="1">
        <tr>
            <th>Location ID</th>
            <th>Description</th>
            <th>Stations in Use
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
                        <td>" . $row["active_sessions"] . "</td>
                        <td>" . $row["num_stations"] . "</td>
                        <td>" . $row["cost_per_hour"] . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No charging locations found.</td></tr>";
        }

        $conn->close();
        ?>

    </table>
    <div class="button-container">
    <button onclick="window.location.href='create_location.php';">Add Charging Location</button>
    <button onclick="window.location.href='edit_location.php';">Edit Charging Location</button>
    <button onclick="window.location.href='logout.php';">Logout</button>
    </div>
    </body>
    </html>