<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "ngo") {
    header("location: index.php");
    exit;
}

include "config.php";

$ngo_id = $_SESSION["id"];

// ===============================
// Fetch all cases for this NGO
// ===============================
$query = "
    SELECT 
        c.case_id, c.case_status, c.assigned_at, c.closed_at,
        u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone, u.issue_type,
        t.full_name AS team_member_name, t.role AS team_member_role
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN team_members t ON c.team_id = t.team_id
    WHERE c.ngo_id = ?
    ORDER BY c.assigned_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $ngo_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NGO Dashboard</title>
<link rel="stylesheet" href="../CSS/style.css">
<style>
.dashboard { max-width: 1200px; margin: 30px auto; padding: 20px; }
h2 { margin-bottom: 20px; }
.card { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.card h3 { margin-top: 0; margin-bottom: 10px; color: #007BFF; }
.card p { margin: 4px 0; }
.actions a { display: inline-block; margin: 4px 4px 0 0; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px; color: #fff; }
.sessions { background: #28a745; }
.progress { background: #ffc107; color: #000; }
.skills { background: #17a2b8; }
.no-cases { font-style: italic; color: #555; margin-top: 20px; }
</style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="dashboard">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["email"]); ?> (NGO)</h2>

    <?php if(mysqli_num_rows($result) > 0) { 
        while($case = mysqli_fetch_assoc($result)) { ?>
        <div class="card">
            <h3>Case ID: <?php echo $case['case_id']; ?> (<?php echo $case['case_status']; ?>)</h3>
            <p><strong>User:</strong> <?php echo htmlspecialchars($case['user_name']); ?> | <?php echo htmlspecialchars($case['user_email']); ?> | <?php echo htmlspecialchars($case['user_phone']); ?></p>
            <p><strong>Issue:</strong> <?php echo $case['issue_type']; ?></p>
            <p><strong>Assigned Team Member:</strong> <?php echo htmlspecialchars($case['team_member_name']); ?> (<?php echo $case['team_member_role']; ?>)</p>
            <p><strong>Assigned At:</strong> <?php echo $case['assigned_at']; ?> | <strong>Closed At:</strong> <?php echo $case['closed_at'] ? $case['closed_at'] : '-'; ?></p>
            <div class="actions">
                <a href="case_details.php?case_id=<?php echo $case['case_id']; ?>" class="sessions">View Details</a>
            </div>
        </div>
    <?php } } else { ?>
        <p class="no-cases">No cases assigned to your NGO yet.</p>
    <?php } ?>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
