<?php
session_start();
include "../config.php";

// Access Control
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "counselor") {
    header("location: ../index.php");
    exit;
}

$case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$counselor_id = $_SESSION["id"];

if ($case_id == 0) {
    header("location: counselor_dashboard.php");
    exit;
}

// ==========================================
// 1. Fetch Main Case, User, NGO, and Team Data
// ==========================================
$query = "
    SELECT c.*, 
           u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone, u.gender,
           n.ngo_name, n.email AS ngo_email,
           t.full_name AS team_member_name, t.role AS team_role
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN ngos n ON c.ngo_id = n.ngo_id
    LEFT JOIN team_members t ON c.team_id = t.team_id
    WHERE c.case_id = ? AND c.recommended_counselor_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $case_id, $counselor_id);
mysqli_stmt_execute($stmt);
$case_result = mysqli_stmt_get_result($stmt);
$case = mysqli_fetch_assoc($case_result);

// Redirect if case doesn't belong to this counselor
if (!$case) {
    header("location: counselor_dashboard.php");
    exit;
}

// ==========================================
// 2. Fetch Session Summary (Latest 3)
// ==========================================
$sessions_query = "SELECT * FROM counseling_sessions WHERE case_id = $case_id ORDER BY session_date DESC LIMIT 3";
$sessions_result = mysqli_query($conn, $sessions_query);

// ==========================================
// 3. Fetch Skills Summary
// ==========================================
$skills_query = "SELECT * FROM skill_development WHERE case_id = $case_id ORDER BY updated_at DESC";
$skills_result = mysqli_query($conn, $skills_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Details #<?php echo $case_id; ?> | Heal2Rise</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body >

<?php include('navbar.php'); ?>
<div style="margin:20px;" >

<div style="padding: 20px; max-width: 1100px; margin: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Detailed Case Report: #<?php echo $case_id; ?></h2>
        <a href="counselor_dashboard.php" style="text-decoration: none; color: #007BFF;">← Back to Dashboard</a>
    </div>

    <hr>

    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div style="flex: 2; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h3 style="margin-top:0; color: #333;">Patient Information</h3>
            <table width="100%" cellpadding="8">
                <tr><td><b>Full Name:</b></td><td><?php echo htmlspecialchars($case['user_name']); ?></td></tr>
                <tr><td><b>Contact:</b></td><td><?php echo htmlspecialchars($case['user_email']); ?> | <?php echo htmlspecialchars($case['user_phone']); ?></td></tr>
                <tr><td><b>Gender:</b></td><td><?php echo $case['gender']; ?></td></tr>
                <tr><td><b>Issue Category:</b></td><td><mark><?php echo $case['category']; ?></mark></td></tr>
                <tr><td><b>Status:</b></td><td><b><?php echo $case['case_status']; ?></b></td></tr>
            </table>
            
            <h4>Case Description</h4>
            <p style="background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #007BFF;">
                <?php echo nl2br(htmlspecialchars($case['description'])); ?>
            </p>
        </div>

        <div style="flex: 1; background: #f0f4f8; padding: 20px; border-radius: 8px; height: fit-content;">
            <h3 style="margin-top:0;">Support Network</h3>
            
            <p><b>Assigned NGO:</b><br>
            <?php echo $case['ngo_name'] ? htmlspecialchars($case['ngo_name']) : "No NGO Assigned"; ?></p>
            
            <p><b>Team Supervisor:</b><br>
            <?php echo $case['team_member_name'] ? htmlspecialchars($case['team_member_name']) . " (".$case['team_role'].")" : "Unassigned"; ?></p>
            
            <p><b>Case Started:</b><br>
            <?php echo date('M d, Y', strtotime($case['assigned_at'])); ?></p>

            <div style="margin-top: 20px;">
                <a href="sessions_manage.php?case_id=<?php echo $case_id; ?>" style="display: block; background: #28a745; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 5px; margin-bottom: 10px;">Log New Session</a>
                <a href="skills_assignment.php?case_id=<?php echo $case_id; ?>" style="display: block; background: #17a2b8; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 5px;">Manage Skills</a>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h3>Recent Counseling Sessions</h3>
        <table border="1" width="100%" cellpadding="10" style="border-collapse: collapse; background: white;">
            <tr style="background: #eee;">
                <th>Date</th>
                <th>Notes Summary</th>
                <th>Next Session</th>
            </tr>
            <?php if(mysqli_num_rows($sessions_result) > 0): ?>
                <?php while($s = mysqli_fetch_assoc($sessions_result)): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($s['session_date'])); ?></td>
                    <td><?php echo substr(htmlspecialchars($s['notes']), 0, 100) . '...'; ?></td>
                    <td><?php echo $s['next_session_date'] ? date('M d, Y', strtotime($s['next_session_date'])) : 'N/A'; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" align="center">No sessions recorded yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <div>
        <h3>Skill Development Progress</h3>
        <table border="1" width="100%" cellpadding="10" style="border-collapse: collapse; background: white;">
            <tr style="background: #eee;">
                <th>Skill</th>
                <th>Progress</th>
                <th>Remarks</th>
            </tr>
            <?php if(mysqli_num_rows($skills_result) > 0): ?>
                <?php while($sk = mysqli_fetch_assoc($skills_result)): ?>
                <tr>
                    <td><b><?php echo htmlspecialchars($sk['skill_name']); ?></b></td>
                    <td><?php echo $sk['skill_status']; ?></td>
                    <td><?php echo htmlspecialchars($sk['remarks']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" align="center">No skills assigned yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</div>

</body>
</html>