<?php
session_start();
include "config.php";

// Security Check: Only allow NGO role
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "ngo") {
    header("location: ../index.php");
    exit;
}

$case_id = intval($_GET['case_id']);
$ngo_id = $_SESSION["id"];

// ==========================================
// 1. Fetch case, user, team, and counselor
// ==========================================
$query = "
    SELECT 
        c.case_id, c.case_status, c.assigned_at, c.closed_at, c.category,
        u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone, u.description AS user_description,
        t.full_name AS team_member_name, t.role AS team_member_role,
        co.name AS counselor_name, co.phone AS counselor_phone
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN team_members t ON c.team_id = t.team_id
    LEFT JOIN counselors co ON c.recommended_counselor_id = co.counselor_id
    WHERE c.case_id = ? AND c.ngo_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $case_id, $ngo_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$case = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$case) {
    die("Error: Case not found or access denied.");
}

// ==========================================
// 2. Fetch Related Data (Sessions, Skills)
// ==========================================
$sessions = mysqli_query($conn, "SELECT * FROM counseling_sessions WHERE case_id = $case_id ORDER BY session_date DESC");
$skills = mysqli_query($conn, "SELECT * FROM skill_development WHERE case_id = $case_id ORDER BY updated_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NGO Case View | #<?php echo $case_id; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .details-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); border-top: 3px solid #007BFF; }
        .card h3 { margin-top: 0; color: #007BFF; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-label { font-weight: bold; color: #555; }
        .status-badge { background: #eef2f7; padding: 5px 12px; border-radius: 4px; font-weight: bold; font-size: 0.9rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #f8f9fa; color: #333; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Case Audit: #<?php echo $case['case_id']; ?></h2>
        <span class="status-badge">Status: <?php echo $case['case_status']; ?></span>
    </div>

    <div class="details-grid">
        <div class="card">
            <h3>Patient & Issue Details</h3>
            <p><span class="info-label">Name:</span> <?php echo htmlspecialchars($case['user_name']); ?></p>
            <p><span class="info-label">Contact:</span> <?php echo htmlspecialchars($case['user_email']); ?> | <?php echo htmlspecialchars($case['user_phone']); ?></p>
            <p><span class="info-label">Category:</span> <?php echo $case['category']; ?></p>
            <p><span class="info-label">Description:</span><br>
               <small><?php echo nl2br(htmlspecialchars($case['user_description'])); ?></small></p>
        </div>

        <div class="card" style="border-top-color: #28a745;">
            <h3>Assigned Support</h3>
            <p><span class="info-label">Supervisor:</span><br> 
               <?php echo $case['team_member_name'] ? htmlspecialchars($case['team_member_name'])." (".$case['team_member_role'].")" : "Pending"; ?></p>
            <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
            <p><span class="info-label">Counselor:</span><br> 
               <?php echo $case['counselor_name'] ? "Dr. ".htmlspecialchars($case['counselor_name']) : "Not Assigned"; ?></p>
            <p><span class="info-label">Assigned At:</span><br> 
               <?php echo date('M d, Y', strtotime($case['assigned_at'])); ?></p>
        </div>
    </div>

    <div class="card">
        <h3>Clinical Session Logs</h3>
        <?php if(mysqli_num_rows($sessions) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Notes Summary</th>
                    <th>Next Appointment</th>
                </tr>
            </thead>
            <tbody>
                <?php while($s = mysqli_fetch_assoc($sessions)): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($s['session_date'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($s['notes'])); ?></td>
                    <td><?php echo $s['next_session_date'] ? date('M d, Y', strtotime($s['next_session_date'])) : '-'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No counseling sessions have been recorded yet.</p>
        <?php endif; ?>
    </div>

    <br>

    <div class="card">
        <h3>Skill Development & Progress</h3>
        <?php if(mysqli_num_rows($skills) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Skill Name</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php while($sk = mysqli_fetch_assoc($skills)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($sk['skill_name']); ?></strong></td>
                    <td><?php echo $sk['skill_status']; ?></td>
                    <td><?php echo htmlspecialchars($sk['remarks']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($sk['updated_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No skill development entries found.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>