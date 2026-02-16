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

// Fetch Users, NGOs, and Team Members for dropdowns
$users = mysqli_query($conn, "SELECT user_id, full_name FROM users ORDER BY full_name ASC");
$ngos = mysqli_query($conn, "SELECT ngo_id, ngo_name FROM ngos ORDER BY ngo_name ASC");
$team_members = mysqli_query($conn, "SELECT team_id, full_name, role FROM team_members ORDER BY full_name ASC");

// ===============================
// Handle Add Case
// ===============================
if (isset($_POST["add_case"])) {
    $user_id = intval($_POST["user_id"]);
    $ngo_id = intval($_POST["ngo_id"]);
    $team_id = intval($_POST["team_id"]);
    $case_status = $_POST["case_status"];

    $stmt = mysqli_prepare($conn, 
        "INSERT INTO cases (user_id, ngo_id, team_id, case_status) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiis", $user_id, $ngo_id, $team_id, $case_status);

    if (mysqli_stmt_execute($stmt)) {
        $successMsg = "Case assigned successfully.";
    } else {
        $errorMsg = "Failed to assign case.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Case</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>Assign New Case</h2>

    <?php if ($successMsg) echo "<div style='color:green;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='color:red;'>$errorMsg</div>"; ?>

    <form method="post">
        <label>User:</label> 
        <select name="user_id" required>
            <option value="">Select User</option>
            <?php while($user = mysqli_fetch_assoc($users)) { ?>
                <option value="<?php echo $user['user_id']; ?>">
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </option>
            <?php } ?>
        </select>  

        <label>NGO:</label> 
        <select name="ngo_id" required>
            <option value="">Select NGO</option>
            <?php while($ngo = mysqli_fetch_assoc($ngos)) { ?>
                <option value="<?php echo $ngo['ngo_id']; ?>">
                    <?php echo htmlspecialchars($ngo['ngo_name']); ?>
                </option>
            <?php } ?>
        </select>  

        <label>Team Member:</label> 
        <select name="team_id" required>
            <option value="">Select Team Member</option>
            <?php while($team = mysqli_fetch_assoc($team_members)) { ?>
                <option value="<?php echo $team['team_id']; ?>">
                    <?php echo htmlspecialchars($team['full_name'] . " (" . $team['role'] . ")"); ?>
                </option>
            <?php } ?>
        </select>  

        <label>Case Status:</label> 
        <select name="case_status" required>
            <option value="Assigned">Assigned</option>
            <option value="Ongoing">Ongoing</option>
            <option value="Rehabilitation">Rehabilitation</option>
            <option value="Completed">Completed</option>
            <option value="Closed">Closed</option>
        </select>  

        <button type="submit" name="add_case">Assign Case</button>
    </form>

     
    <a href="admin_case_list.php" style="display:inline-block; margin:20px; padding:8px 15px; background-color:#28a745; color:#fff; text-decoration:none; border-radius:5px; font-weight:500; transition: background-color 0.3s, transform 0.2s;">View All Cases</a>
</div>

</body>
</html>
