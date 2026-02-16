<?php
session_start();
include "config.php";

// ===============================
// Check Admin Login
// ===============================
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

$successMsg = "";
$errorMsg = "";

// ===============================
// Handle Add
// ===============================
if (isset($_POST["add_team"])) {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $role = $_POST["role"];
    $phone = trim($_POST["phone"]);

    $stmt = mysqli_prepare($conn, 
        "INSERT INTO team_members (full_name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssss", $full_name, $email, $password, $role, $phone);

    if (mysqli_stmt_execute($stmt)) {
        $successMsg = "Team member added successfully.";
    } else {
        $errorMsg = "Failed to add member. Email may already exist.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Team Member</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <h2>Add Team Member</h2>

    <?php if ($successMsg) echo "<div class='success-msg'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div class='error-msg'>$errorMsg</div>"; ?>

    <form method="post" class="team-form">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" placeholder="Enter full name" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="Counselor">Counselor</option>
                <option value="Psychiatrist">Psychiatrist</option>
                <option value="Social Worker">Social Worker</option>
                <option value="Monitor">Monitor</option>
            </select>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" placeholder="Enter phone number">
        </div>

        <button type="submit" name="add_team">Add Member</button>
    </form>

    <div class="back-link">
        <a href="admin_team_list.php" style="display:inline-block; margin:20px; padding:8px 15px; background-color:#28a745; color:#fff; text-decoration:none; border-radius:5px; font-weight:500; transition: background-color 0.3s, transform 0.2s;">Back to Team Members</a>
    </div>
</div>

</body>
</html>
