<?php
// Set up the database connection parameters
$host = 'localhost';        
$username = 'root';         
$password = '';             
$dbname = 'city_ev_chargers'; 

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




?>
