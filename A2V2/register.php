<?php

session_start();

include('classes.php');
include('db_config.php');

$db = new Database($host, $username, $password, $dbname);
$user = new User($db);
$is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $user_type = $_POST['user_type'];

    $message = $user->register($name, $email, $phone, $user_type);
    echo $message;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Form</title>
    <style>
        form {
            border: 1px solid #ccc;
            padding: 20px;
            width: 300px;
            box-sizing: border-box;
        }

         label {
            display: block;
            margin-bottom: 8px;
            margin-top: 8px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Register Form</h1>
    <form action="register.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="user_type">User Type:</label>
        <select id="user_type" name="user_type" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
        <br>
        <input type="submit" value="Register">
    </form><br>
<?php
if ($is_admin) {
    echo '<button onclick="window.location.href=\'admin_dash.php\';">Back to Admin Dashboard</button>';
} 

else {
        echo '<button onclick="window.location.href=\'login.php\';">Login</button>';
}
?>
</body>
</html>