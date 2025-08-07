<?php
// Set up the database connection parameters
$host = 'localhost';        // or IP address of the server
$username = 'root';         // your MySQL username
$password = '';             // your MySQL password
$dbname = 'city_ev_chargers'; // name of your database

// Create the connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check if connection was successful
//if ($conn->connect_error) {
  //  die("Connection failed: " . $conn->connect_error);
//} else {
 //   echo "Connected successfully!";
//}
?>
