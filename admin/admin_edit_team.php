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

// ===============================
// Get Team ID
// ===============================
if (!isset($_GET["id"])) {
    header("Location: admin_team_list.php");
    exit();
}

$team_id = intval($_GET["id"]);

// ===============================
// Fetch Team Member
// ===============================
$stmt = mysqli_prepare($conn, "SELECT * FROM team_members WHERE team_id=?");
mysqli_stmt_bind_param($stmt, "i", $team_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$member) {
    header("Location: admin_team_list.php");
    exit();
}

// ===============================
// Handle Update
// ===============================
$successMsg = "";
$errorMsg = "";

if (isset($_POST["update_team"])) {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];
    $phone = trim($_POST["phone"]);

    $stmt = mysqli_prepare($conn, "UPDATE team_members SET full_name=?, email=?, role=?, phone=? WHERE team_id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $email, $role, $phone, $team_id);

    if (mysqli_stmt_execute($stmt)) {
        $successMsg = "Team member updated successfully.";
    } else {
        $errorMsg = "Failed to update member. Email may already exist.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Team Member</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <h2>Edit Team Member</h2>

    <?php if ($successMsg) echo "<div class='success-msg'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div class='error-msg'>$errorMsg</div>"; ?>

    <form method="post" class="team-form">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($member['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="Counselor" <?php if($member['role']=="Counselor") echo "selected"; ?>>Counselor</option>
                <option value="Psychiatrist" <?php if($member['role']=="Psychiatrist") echo "selected"; ?>>Psychiatrist</option>
                <option value="Social Worker" <?php if($member['role']=="Social Worker") echo "selected"; ?>>Social Worker</option>
                <option value="Monitor" <?php if($member['role']=="Monitor") echo "selected"; ?>>Monitor</option>
            </select>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>">
        </div>

        <button type="submit" name="update_team">Update Member</button>
    </form>

    <div class="back-link">
        <a href="admin_team_list.php" style="display:inline-block; margin:20px; padding:8px 15px; background-color:#28a745; color:#fff; text-decoration:none; border-radius:5px; font-weight:500; transition: background-color 0.3s, transform 0.2s;">Back to Team Members</a>
    </div>
</div>

</body>
</html>
