<?php
include('config.php');

// Start session
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are set
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Retrieve entered username and password from form
        $entered_username = $_POST['username'];
        $entered_password = $_POST['password'];

        // Prepare SQL statement to select admin with entered username
        $sql = "SELECT * FROM admin WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $entered_username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if admin with entered username exists
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            // Verify entered password against password from database
            if ($entered_password === $admin['password']) {
                // Store admin credentials in session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $entered_username;
                // Redirect to admin dashboard
                header("Location: ./admin/admin_dashboard.php");
                exit();
            } else {
                // If password doesn't match, display error message
                echo "<p style='color: red;'>Invalid password.</p>";
            }
        } else {
            // If username doesn't exist, display error message
            echo "<p style='color: red;'>Invalid username.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="./CSS/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>
    <h2>Admin Login</h2>
    <form action="adminlogin.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required> 
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required> 
        <input type="submit" value="Login">
    </form>
</body>
</html>
