<?php
session_start();

// Check if user is logged in, else redirect to login
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];

include('db_config.php');

// Default SQL for fetching charging locations
$sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
        FROM ChargingLocations cl
        LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
        GROUP BY cl.location_id";

// Filter by full and empty locations
if (isset($_GET['full_locations'])) {
    $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
            FROM ChargingLocations cl
            LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
            GROUP BY cl.location_id
            HAVING COUNT(cs.session_id) = cl.num_stations";
} elseif (isset($_GET['empty_locations'])) {
    $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
            FROM ChargingLocations cl
            LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
            GROUP BY cl.location_id
            HAVING COUNT(cs.session_id) < cl.num_stations";
}

// Check if a location search is performed
if (isset($_GET['search_location'])) {
    $search_location = $_GET['search_location'];
    $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, COUNT(cs.session_id) AS active_sessions
            FROM ChargingLocations cl
            LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
            WHERE cl.description LIKE '%$search_location%'
            GROUP BY cl.location_id";
}

// Run the query for charging locations
$location_result = $conn->query($sql);

// Check if the query for locations was successful
if (!$location_result) {
    echo "Error executing query: " . $conn->error;
    exit();
}

// Users query for the user list section
$sql_users = "SELECT user_id, name, email, user_type FROM Users WHERE user_type = 'user'";

// Check if a user search is performed
if (isset($_GET['search_user'])) {
    $search_user = $_GET['search_user'];
    $sql_users = "SELECT user_id, name, email, user_type
                  FROM Users
                  WHERE name LIKE '%$search_user%' AND user_type = 'user'";
}

// Run the query for users
$user_result = $conn->query($sql_users);

// Check if the query for users was successful
if (!$user_result) {
    echo "Error executing user query: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        div.button-container {
            margin-top: 20px;
            justify-content: space-between;
        }

        div.logout-container {
            margin-top: 20px;
            justify-content: space-between;
            color: red;
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
    </form><br>

    <form action="admin_dash.php" method="GET">
        <input type="text" name="search_location" placeholder="Search Location">
        <input type="submit" value="Search">
    </form>
    <hr>

    <table border="1">
        <tr>
            <th>Location ID</th>
            <th>Description</th>
            <th>Stations in Use</th>
            <th>Number of Stations</th>
            <th>Cost per Hour</th>
        </tr>

        <?php
        // Display charging locations
        if ($location_result->num_rows > 0) {
            while($row = $location_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["location_id"] . "</td>
                        <td>" . $row["description"] . "</td>
                        <td>" . $row["active_sessions"] . " / " . $row["num_stations"] . "</td>
                        <td>" . $row["num_stations"] . "</td>
                        <td>" . $row["cost_per_hour"] . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No charging locations found.</td></tr>";
        }
        ?>

    </table>
    <div class="button-container">
        <button onclick="window.location.href='create_location.php';">Add Charging Location</button>
        <button onclick="window.location.href='edit_location.php';">Edit Charging Location</button>
    </div>

    <!-- User List -->
    <h2>User List</h2>
    <form action="admin_dash.php" method="GET">
        <input type="text" name="search_user" placeholder="Search User">
        <input type="submit" value="Search">
    </form>

    
    <hr>

    <table border="1">
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Details</th>
        </tr>

        <?php
        // Display users
        if ($user_result->num_rows > 0) {
            while($row = $user_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["user_id"] . "</td>
                        <td>" . $row["name"] . "</td>
                        <td>" . $row["email"] . "</td>
                        <td>" . $row["user_type"] . "</td>
                        <td><a href='user_details.php?user_id=" . $row["user_id"] . "'>Details</a></td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No users found.</td></tr>";
        }
        ?>

    </table>

    <div class="logout-container">
        <button onclick="window.location.href='logout.php';">Logout</button>
    </div>
</body>
</html>

<?php
$conn->close();  // Close the database connection
?>
