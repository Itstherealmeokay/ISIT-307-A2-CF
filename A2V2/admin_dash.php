<?php
session_start();

// Check if user is logged in, else redirect to login
if (!isset($_SESSION['email']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];

// Include necessary classes
include('classes.php');

// Include database config to get the connection details
include('db_config.php');

// Create the Database object
$db = new Database($host, $username, $password, $dbname);

// Create ChargingLocation and User objects
$chargingLocation = new ChargingLocation($db);
$user = new User($db);

// Default filter for locations
$filter = '';
if (isset($_GET['full_locations'])) {
    $filter = 'full';
} elseif (isset($_GET['empty_locations'])) {
    $filter = 'empty';
}

// Handle location search
if (isset($_GET['search_location'])) {
    $search_location = $_GET['search_location'];
    $location_result = $chargingLocation->searchLocations($search_location);
} else {
    $location_result = $chargingLocation->getLocations($filter);
}

// Handle user search
if (isset($_GET['search_user'])) {
    $search_user = $_GET['search_user'];
    $user_result = $user->getUsers($search_user);
} else {
    $user_result = $user->getUsers();
}
if (isset($_GET['active_sessions'])) {
    $user_result = $user->filterIfCheckedIn(); 
}
// Close the database connection
$db->close();
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

        .logout-button {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        div.logout-container {
            margin: 20px;
            justify-content: space-between;
            color: red;
        }

        div.charging-locations-container {
            margin: 20px;
            padding: 10px;
            justify-content: space-between;
            border: 2px solid #ccc;
        }

        div.users-container {
            margin: 20px;
            padding: 10px;
            justify-content: space-between;
            border: 2px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Welcome, Admin! <?php echo $name; ?></h1>     

    <div class="charging-locations-container">
        <h2>Charging Locations</h2>
        <form action="admin_dash.php" method="GET">
            <label for="full_locations">Filter by Full Locations:</label>
            <input type="checkbox" name="full_locations" value="1">
            <br><label for="empty_locations">Filter by Available Locations:</label>
            <input type="checkbox" name="empty_locations" value="1">
            <br><br><input type="submit" value="Filter">
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
                while ($row = $location_result->fetch_assoc()) {
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
    </div>


    <div class="users-container">      
        <!-- User List -->
        <h2>User List</h2>
        <form action="admin_dash.php" method="GET">
            <label for="active_sessions">Filter by Active Sessions:</label>
            <input type="checkbox" name="active_sessions" value="1">
            <input type="submit" value="Filter">
        </form><br>

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
                while ($row = $user_result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["user_id"] . "</td>
                            <td>" . $row["name"] . "</td>
                            <td>" . $row["email"] . "</td>
                            <td>" . $row["user_type"] . "</td>
                            <td><a href='user_details.php?user_id=" . $row["user_id"] . "'>Details</a></td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No users found.</td></tr>";
            }
            ?>

        </table>

        <div class="button-container">
            <button onclick="window.location.href='register.php';">Add User</button>
        </div>
    </div>

    <div class="logout-container">
        <button onclick="window.location.href='logout.php';" class="logout-button">Logout</button>
    </div>
</body>
</html>
