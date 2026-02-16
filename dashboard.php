<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "user") {
    header("location: index.php");
    exit;
}

include "config.php";

$user_id = $_SESSION["id"];

// ===============================
// Fetch all cases for this user
// ===============================
$query = "
    SELECT 
        c.case_id,
        c.case_status,
        c.assigned_at,
        c.closed_at,
        n.ngo_name,
        t.full_name AS team_member_name,
        t.role AS team_member_role
    FROM cases c
    JOIN ngos n ON c.ngo_id = n.ngo_id
    JOIN team_members t ON c.team_id = t.team_id
    WHERE c.user_id = ?
    ORDER BY c.assigned_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<link rel="stylesheet" href="./CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["email"]); ?></h2>
    <h3>Your Cases</h3>

    <?php if(mysqli_num_rows($result) > 0) { ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Case ID</th>
            <th>NGO</th>
            <th>Team Member</th>
            <th>Role</th>
            <th>Status</th>
            <th>Assigned At</th>
            <th>Closed At</th>
            <th>Actions</th>
        </tr>

        <?php while($case = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $case['case_id']; ?></td>
            <td><?php echo htmlspecialchars($case['ngo_name']); ?></td>
            <td><?php echo htmlspecialchars($case['team_member_name']); ?></td>
            <td><?php echo htmlspecialchars($case['team_member_role']); ?></td>
            <td><?php echo $case['case_status']; ?></td>
            <td><?php echo $case['assigned_at']; ?></td>
            <td><?php echo $case['closed_at'] ? $case['closed_at'] : '-'; ?></td>
            <td>
                <a href="case_details.php?case_id=<?php echo $case['case_id']; ?>">View Details</a>
            </td>
        </tr>
        <?php } ?>

    </table>
    <?php } else { ?>
        <p>You have no cases assigned yet.</p>
    <?php } ?>
</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
