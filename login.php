<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db_config.php');
    
    $email = $_POST['email'];
    
    $sql = "SELECT * FROM Users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $_SESSION['email'] = $email;
        $_SESSION['user_type'] = $row['user_type']; 
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];

        if ($row['user_type'] == 'admin') {
            header("Location: admin_dash.php");
        } else {
            header("Location: user_dash.php");
        }
    } else {
        echo "Invalid email.";
    }
    
    $conn->close();
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
