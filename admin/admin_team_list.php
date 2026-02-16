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
// Handle Delete
// ===============================
if (isset($_GET["delete"])) {
    $team_id = intval($_GET["delete"]);

    $stmt = mysqli_prepare($conn, "DELETE FROM team_members WHERE team_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $team_id);

    if (mysqli_stmt_execute($stmt)) {
        $successMsg = "Team member deleted successfully.";
    } else {
        $errorMsg = "Failed to delete member.";
    }

    mysqli_stmt_close($stmt);
}

// ===============================
// Search Logic
// ===============================
$searchQuery = "";
if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
    $search = mysqli_real_escape_string($conn, trim($_GET["search"]));
    $searchQuery = "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%'";
}

// ===============================
// Fetch Team Members
// ===============================
$members = mysqli_query($conn, "SELECT * FROM team_members $searchQuery ORDER BY team_id DESC");

if (mysqli_num_rows($members) === 0) {
    $errorMsg = "No team members found.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Team Members</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>All Team Members</h2>

    <?php if ($successMsg) echo "<div style='color:green;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='color:red;'>$errorMsg</div>"; ?>

    <form method="get">
        <input type="text" name="search" placeholder="Search by Name or Email"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit">Search</button>
    </form>

     <a href="admin_add_team.php" style="display:inline-block; margin:20px; padding:8px 15px; background-color:#28a745; color:#fff; text-decoration:none; border-radius:5px; font-weight:500; transition: background-color 0.3s, transform 0.2s;">+ Add New Team Member</a>
      

    <?php if (mysqli_num_rows($members) > 0) { ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Phone</th>
            <th>Actions</th>
        </tr>

        <?php while ($member = mysqli_fetch_assoc($members)) { ?>
        <tr>
            <td><?php echo $member['team_id']; ?></td>
            <td><?php echo htmlspecialchars($member['full_name']); ?></td>
            <td><?php echo htmlspecialchars($member['email']); ?></td>
            <td><?php echo $member['role']; ?></td>
            <td><?php echo htmlspecialchars($member['phone']); ?></td>
            <td>
                <a href="admin_edit_team.php?id=<?php echo $member['team_id']; ?>">Edit</a> |
                <a href="?delete=<?php echo $member['team_id']; ?>" onclick="return confirm('Are you sure to delete this member?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
</div>

</body>
</html>
