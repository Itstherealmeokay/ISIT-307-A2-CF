<?php
session_start();

// Include the autoloader or class files
include('classes.php');

// Include database config to get the connection details
include('db_config.php');

// Create the Database object
$db = new Database($host, $username, $password, $dbname);

// Create the User object
$user = new User($db);

// Handle the login process when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Use the User class to log the user in
    $result = $user->login($email);

    if ($result->num_rows > 0) {
        // Fetch the user data
        $row = $result->fetch_assoc();

        // Start a session for the user
        $user->startSession($row);

        // Redirect to the appropriate dashboard based on user type
        if ($row['user_type'] == 'admin') {
            header("Location: admin_dash.php");
        } else {
            header("Location: user_dash.php");
        }
    } else {
        echo "Invalid email.";
    }

    $db->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <label for="email">Email:</label><br>
        <input type="email" name="email" required><br><br>

        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="register_form.html">Register</a></p>
</body>
</html>
