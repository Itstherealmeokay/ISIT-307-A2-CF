<?php
session_start();

// Check if user is logged in, else redirect to login
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];

include('db_config.php');
$sql = "SELECT * FROM ChargingLocations";
$result = $conn->query($sql);

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
    <hr>
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

        $conn->close();
        ?>

    </table>
    <div class="button-container">
    <button onclick="window.location.href='create_location.php';">Add Charging Location</button>
    <button onclick="window.location.href='logout.php';">Logout</button>
    </div>
    </body>
    </html>