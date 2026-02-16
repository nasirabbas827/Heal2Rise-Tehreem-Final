<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "team_member") {
    header("location: index.php");
    exit;
}

include "config.php";

$team_id = $_SESSION["id"];

// ===============================
// Fetch assigned cases
// ===============================
$query = "
    SELECT c.case_id, u.full_name AS user_name, n.ngo_name, c.case_status, c.assigned_at, c.closed_at
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN ngos n ON c.ngo_id = n.ngo_id
    WHERE c.team_id = ?
    ORDER BY c.assigned_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $team_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Team Member Dashboard</title>
<link rel="stylesheet" href="../CSS/style.css">
<style>
    .dashboard { max-width: 1100px; margin: 30px auto; padding: 20px; }
    h2 { margin-bottom: 20px; }
    .card { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #007BFF; color: #fff; }
    tr:hover { background: #f1f1f1; }
    .btn { display: inline-block; padding: 6px 12px; margin: 2px; border-radius: 4px; color: #fff; text-decoration: none; font-size: 14px; }
    .btn-sessions { background: #28a745; }
    .btn-progress { background: #ffc107; color: #000; }
    .btn-skills { background: #17a2b8; }
    .no-cases { font-style: italic; color: #555; margin-top: 20px; }
</style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="dashboard">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["email"]); ?> (<?php echo htmlspecialchars($_SESSION["member_role"]); ?>)</h2>

    <?php if(mysqli_num_rows($result) > 0) { ?>
    <div class="card">
        <table>
            <tr>
                <th>Case ID</th>
                <th>User</th>
                <th>NGO</th>
                <th>Status</th>
                <th>Assigned At</th>
                <th>Closed At</th>
                <th>Actions</th>
            </tr>

            <?php while($case = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $case['case_id']; ?></td>
                <td><?php echo htmlspecialchars($case['user_name']); ?></td>
                <td><?php echo htmlspecialchars($case['ngo_name']); ?></td>
                <td><?php echo $case['case_status']; ?></td>
                <td><?php echo $case['assigned_at']; ?></td>
                <td><?php echo $case['closed_at'] ? $case['closed_at'] : '-'; ?></td>
                <td>
                    <a href="sessions.php?case_id=<?php echo $case['case_id']; ?>" class="btn btn-sessions">Sessions</a>
                    <a href="progress.php?case_id=<?php echo $case['case_id']; ?>" class="btn btn-progress">Progress</a>
                    <a href="skills.php?case_id=<?php echo $case['case_id']; ?>" class="btn btn-skills">Skills</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php } else { ?>
        <p class="no-cases">No cases assigned to you yet.</p>
    <?php } ?>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
