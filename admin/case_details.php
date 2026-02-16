<?php
session_start();
include "config.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

if (!isset($_GET['case_id'])) {
    die("Case ID is required.");
}

$case_id = intval($_GET['case_id']);

// Fetch case info
$query = "
    SELECT c.*, u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone, u.issue_type, u.description AS user_desc,
           n.ngo_name, n.email AS ngo_email, n.phone AS ngo_phone, n.specialization,
           t.full_name AS team_name, t.role AS team_role
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN ngos n ON c.ngo_id = n.ngo_id
    JOIN team_members t ON c.team_id = t.team_id
    WHERE c.case_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $case_id);
$stmt->execute();
$result = $stmt->get_result();
$case = $result->fetch_assoc();
$stmt->close();

if (!$case) {
    die("Case not found.");
}

// Fetch counseling sessions
$sessions = mysqli_query($conn, "SELECT * FROM counseling_sessions WHERE case_id = $case_id ORDER BY session_date DESC");

// Fetch progress updates
$progresses = mysqli_query($conn, "SELECT p.*, t.full_name FROM progress_updates p JOIN team_members t ON p.updated_by = t.team_id WHERE p.case_id = $case_id ORDER BY created_at DESC");

// Fetch skills
$skills = mysqli_query($conn, "SELECT * FROM skill_development WHERE case_id = $case_id ORDER BY updated_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Case Details - <?php echo $case['case_id']; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .container { max-width:900px; margin:30px auto; padding:20px; }
        .card { background:#fff; padding:20px; margin-bottom:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        .card h3 { margin-top:0; margin-bottom:10px; color:#007BFF; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #ddd; padding:8px; text-align:left; }
        th { background:#007BFF; color:#fff; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h2>Case #<?php echo $case['case_id']; ?> Details</h2>

    <div class="card">
        <h3>User Info</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($case['user_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($case['user_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($case['user_phone']); ?></p>
        <p><strong>Issue:</strong> <?php echo $case['issue_type']; ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($case['user_desc']); ?></p>
    </div>

    <div class="card">
        <h3>NGO Info</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($case['ngo_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($case['ngo_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($case['ngo_phone']); ?></p>
        <p><strong>Specialization:</strong> <?php echo $case['specialization']; ?></p>
    </div>

    <div class="card">
        <h3>Team Member</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($case['team_name']); ?></p>
        <p><strong>Role:</strong> <?php echo $case['team_role']; ?></p>
    </div>

    <div class="card">
        <h3>Case Info</h3>
        <p><strong>Status:</strong> <?php echo $case['case_status']; ?></p>
        <p><strong>Assigned At:</strong> <?php echo $case['assigned_at']; ?></p>
        <p><strong>Closed At:</strong> <?php echo $case['closed_at'] ? $case['closed_at'] : '-'; ?></p>
    </div>

    <div class="card">
        <h3>Counseling Sessions</h3>
        <?php if(mysqli_num_rows($sessions) > 0) { ?>
        <table>
            <tr><th>Date</th><th>Notes</th><th>Next Session</th></tr>
            <?php while($s = mysqli_fetch_assoc($sessions)) { ?>
                <tr>
                    <td><?php echo $s['session_date']; ?></td>
                    <td><?php echo htmlspecialchars($s['notes']); ?></td>
                    <td><?php echo $s['next_session_date'] ? $s['next_session_date'] : '-'; ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php } else { echo "<p>No sessions yet.</p>"; } ?>
    </div>

    <div class="card">
        <h3>Progress Updates</h3>
        <?php if(mysqli_num_rows($progresses) > 0) { ?>
        <table>
            <tr><th>By</th><th>Note</th><th>Level</th><th>Date</th></tr>
            <?php while($p = mysqli_fetch_assoc($progresses)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['progress_note']); ?></td>
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
        <table>
            <tr><th>Skill</th><th>Status</th><th>Remarks</th><th>Updated At</th></tr>
            <?php while($k = mysqli_fetch_assoc($skills)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($k['skill_name']); ?></td>
                    <td><?php echo $k['skill_status']; ?></td>
                    <td><?php echo htmlspecialchars($k['remarks']); ?></td>
                    <td><?php echo $k['updated_at']; ?></td>
                </tr>
            <?php } ?>
        </table>
        <?php } else { echo "<p>No skills added yet.</p>"; } ?>
    </div>

</div>
</body>
</html>
<?php mysqli_close($conn); ?>
