<?php
include('db_config.php');

$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$user_type = $_POST['user_type'];

$sql = "INSERT INTO Users (name, phone, email, user_type)
        VALUES ('$name', '$phone', '$email', '$user_type')";

if ($conn->query($sql) === TRUE) {
    echo "New user registered successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
