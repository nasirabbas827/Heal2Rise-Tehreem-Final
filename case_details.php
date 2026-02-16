<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

include "config.php";

$case_id = intval($_GET['case_id']);
$user_id = $_SESSION["id"];
$role = $_SESSION["role"];

// ===============================
// Check if user or team member has access to this case
// ===============================
if ($role == "user") {
    $query = "SELECT * FROM cases WHERE case_id=? AND user_id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $case_id, $user_id);
} elseif ($role == "team_member") {
    $query = "SELECT * FROM cases WHERE case_id=? AND team_id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $case_id, $user_id);
} else {
    die("Access denied.");
}

mysqli_stmt_execute($stmt);
$case_result = mysqli_stmt_get_result($stmt);
$case = mysqli_fetch_assoc($case_result);
mysqli_stmt_close($stmt);

if (!$case) {
    die("Case not found or access denied.");
}

// ===============================
// Fetch related user, NGO, team member
// ===============================
$query = "
    SELECT u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone,
           n.ngo_name, n.email AS ngo_email, n.phone AS ngo_phone,
           t.full_name AS team_name, t.role AS team_role, t.email AS team_email
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN ngos n ON c.ngo_id = n.ngo_id
    JOIN team_members t ON c.team_id = t.team_id
    WHERE c.case_id=?
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $case_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$details = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// ===============================
// Fetch sessions
// ===============================
$sessions = mysqli_query($conn, "SELECT * FROM counseling_sessions WHERE case_id=$case_id ORDER BY session_date DESC");

// ===============================
// Fetch progress updates
// ===============================
$progress = mysqli_query($conn, "SELECT p.*, t.full_name AS updated_by_name 
                                 FROM progress_updates p
                                 JOIN team_members t ON p.updated_by=t.team_id
                                 WHERE p.case_id=$case_id ORDER BY p.created_at DESC");

// ===============================
// Fetch skills
// ===============================
$skills = mysqli_query($conn, "SELECT * FROM skill_development WHERE case_id=$case_id ORDER BY updated_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Case Details</title>
<link rel="stylesheet" href="./CSS/style.css">
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

    <div class="card">
        <h3>Case #<?php echo $case['case_id']; ?> Details</h3>
        <p><strong>Status:</strong> <?php echo $case['case_status']; ?></p>
        <p><strong>Assigned At:</strong> <?php echo $case['assigned_at']; ?></p>
        <p><strong>Closed At:</strong> <?php echo $case['closed_at'] ? $case['closed_at'] : '-'; ?></p>
    </div>

    <div class="card">
        <h3>User Info</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($details['user_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($details['user_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($details['user_phone']); ?></p>
    </div>

    <div class="card">
        <h3>NGO Info</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($details['ngo_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($details['ngo_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($details['ngo_phone']); ?></p>
    </div>

    <div class="card">
        <h3>Team Member Info</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($details['team_name']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($details['team_role']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($details['team_email']); ?></p>
    </div>

    <div class="card">
        <h3>Counseling Sessions</h3>
        <?php if(mysqli_num_rows($sessions) > 0) { ?>
        <table class="table">
            <tr>
                <th>Date</th>
                <th>Notes</th>
                <th>Next Session</th>
            </tr>
            <?php while($s = mysqli_fetch_assoc($sessions)) { ?>
            <tr>
                <td><?php echo $s['session_date']; ?></td>
                <td><?php echo htmlspecialchars($s['notes']); ?></td>
                <td><?php echo $s['next_session_date'] ? $s['next_session_date'] : '-'; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
            <p>No sessions recorded yet.</p>
        <?php } ?>
    </div>

    <div class="card">
        <h3>Progress Updates</h3>
        <?php if(mysqli_num_rows($progress) > 0) { ?>
        <table class="table">
            <tr>
                <th>Date</th>
                <th>Updated By</th>
                <th>Note</th>
                <th>Level</th>
            </tr>
            <?php while($p = mysqli_fetch_assoc($progress)) { ?>
            <tr>
                <td><?php echo $p['created_at']; ?></td>
                <td><?php echo htmlspecialchars($p['updated_by_name']); ?></td>
                <td><?php echo htmlspecialchars($p['progress_note']); ?></td>
                <td><?php echo $p['progress_level']; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
            <p>No progress updates yet.</p>
        <?php } ?>
    </div>

    <div class="card">
        <h3>Skills Development</h3>
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
                <td><?php echo htmlspecialchars($sk['skill_name']); ?></td>
                <td><?php echo $sk['skill_status']; ?></td>
                <td><?php echo htmlspecialchars($sk['remarks']); ?></td>
                <td><?php echo $sk['updated_at']; ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
            <p>No skills tracked yet.</p>
        <?php } ?>
    </div>

</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
