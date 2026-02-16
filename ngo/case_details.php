<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "ngo") {
    header("location: index.php");
    exit;
}

include "config.php";

$case_id = intval($_GET['case_id']);
$ngo_id = $_SESSION["id"];

// ===============================
// Fetch case main details
// ===============================
$query = "
    SELECT 
        c.case_id, c.case_status, c.assigned_at, c.closed_at,
        u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone, u.issue_type, u.description AS user_description,
        t.full_name AS team_member_name, t.role AS team_member_role
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN team_members t ON c.team_id = t.team_id
    WHERE c.case_id = ? AND c.ngo_id = ?
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $case_id, $ngo_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$case = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$case) {
    die("Case not found or you don't have access.");
}

// ===============================
// Fetch Counseling Sessions
// ===============================
$sessions = mysqli_query($conn, "SELECT * FROM counseling_sessions WHERE case_id = $case_id ORDER BY session_date DESC");

// ===============================
// Fetch Progress Updates
// ===============================
$progress = mysqli_query($conn, "SELECT p.*, t.full_name AS updated_by_name FROM progress_updates p JOIN team_members t ON p.updated_by = t.team_id WHERE case_id = $case_id ORDER BY created_at DESC");

// ===============================
// Fetch Skill Development
// ===============================
$skills = mysqli_query($conn, "SELECT * FROM skill_development WHERE case_id = $case_id ORDER BY updated_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Case Details - Case <?php echo $case_id; ?></title>
<link rel="stylesheet" href="../CSS/style.css">
<style>
.container { max-width: 1000px; margin: 30px auto; padding: 20px; }
.card { background:#fff; padding:20px; margin-bottom:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.card h3 { margin-top:0; margin-bottom:10px; color:#007BFF; }
.card p { margin:4px 0; }
</style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <h2>Case Details - Case ID <?php echo $case['case_id']; ?></h2>
    
    <div class="card">
        <h3>Main Info</h3>
        <p><strong>Status:</strong> <?php echo $case['case_status']; ?></p>
        <p><strong>Assigned At:</strong> <?php echo $case['assigned_at']; ?> | <strong>Closed At:</strong> <?php echo $case['closed_at'] ? $case['closed_at'] : '-'; ?></p>
        <p><strong>User:</strong> <?php echo htmlspecialchars($case['user_name']); ?> (<?php echo $case['user_email']; ?> | <?php echo $case['user_phone']; ?>)</p>
        <p><strong>Issue:</strong> <?php echo $case['issue_type']; ?></p>
        <p><strong>Description:</strong> <?php echo $case['user_description']; ?></p>
        <p><strong>Team Member:</strong> <?php echo htmlspecialchars($case['team_member_name']); ?> (<?php echo $case['team_member_role']; ?>)</p>
    </div>

    <div class="card">
        <h3>Counseling Sessions</h3>
        <?php if(mysqli_num_rows($sessions) > 0) { ?>
        <table class="table">
            <tr>
                <th>Session Date</th>
                <th>Notes</th>
                <th>Next Session</th>
            </tr>
            <?php while($s = mysqli_fetch_assoc($sessions)) { ?>
            <tr>
                <td><?php echo $s['session_date']; ?></td>
                <td><?php echo $s['notes']; ?></td>
                <td><?php echo $s['next_session_date'] ? $s['next_session_date'] : '-'; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { echo "<p>No sessions added yet.</p>"; } ?>
    </div>

    <div class="card">
        <h3>Progress Updates</h3>
        <?php if(mysqli_num_rows($progress) > 0) { ?>
        <table class="table">
            <tr>
                <th>Updated By</th>
                <th>Note</th>
                <th>Level</th>
                <th>Created At</th>
            </tr>
            <?php while($p = mysqli_fetch_assoc($progress)) { ?>
            <tr>
                <td><?php echo $p['updated_by_name']; ?></td>
                <td><?php echo $p['progress_note']; ?></td>
                <td><?php echo $p['progress_level']; ?></td>
                <td><?php echo $p['created_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { echo "<p>No progress updates yet.</p>"; } ?>
    </div>

    <div class="card">
        <h3>Skill Development</h3>
        <?php if(mysqli_num_rows($skills) > 0) { ?>
        <table class="table">
            <tr>
                <th>Skill Name</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Updated At</th>
            </tr>
            <?php while($sk = mysqli_fetch_assoc($skills)) { ?>
            <tr>
                <td><?php echo $sk['skill_name']; ?></td>
                <td><?php echo $sk['skill_status']; ?></td>
                <td><?php echo $sk['remarks']; ?></td>
                <td><?php echo $sk['updated_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { echo "<p>No skill entries yet.</p>"; } ?>
    </div>

</div>

</body>
</html>

<?php mysqli_close($conn); ?>
