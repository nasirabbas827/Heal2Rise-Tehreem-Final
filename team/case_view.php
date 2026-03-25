<?php
session_start();
include "../config.php";

// Access Control: Team Member Only
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "team_member") {
    header("location: ../index.php");
    exit;
}

$case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$team_id = $_SESSION["id"];

if ($case_id == 0) {
    header("location: team_dashboard.php");
    exit;
}

// 1. Fetch Master Case Data (Joining Users, NGOs, and Counselors)
$query = "
    SELECT c.*, 
           u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone, u.gender, u.issue_type,
           n.ngo_name, n.email AS ngo_email,
           co.name AS counselor_name, co.specialization AS counselor_spec
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN ngos n ON c.ngo_id = n.ngo_id
    LEFT JOIN counselors co ON c.recommended_counselor_id = co.counselor_id
    WHERE c.case_id = ? AND c.team_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $case_id, $team_id);
mysqli_stmt_execute($stmt);
$case = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$case) {
    die("Access Denied: You are not the supervisor for this case.");
}

// 2. Fetch Clinical Logs
$sessions = mysqli_query($conn, "SELECT * FROM counseling_sessions WHERE case_id = $case_id ORDER BY session_date DESC");
$skills = mysqli_query($conn, "SELECT * FROM skill_development WHERE case_id = $case_id ORDER BY updated_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Audit #<?php echo $case_id; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .header-flex { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h3 { margin-top: 0; font-size: 1.1rem; color: #2c3e50; border-bottom: 1px solid #f4f4f4; padding-bottom: 8px; }
        .status-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; background: #e8f4fd; color: #2980b9; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.95rem; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #fcfcfc; color: #666; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <div class="header-flex">
        <div>
            <h2 style="margin:0;">Detailed Case Review: #<?php echo $case_id; ?></h2>
            <p style="color:#777; margin:5px 0;">Assigned on <?php echo date('M d, Y', strtotime($case['assigned_at'])); ?></p>
        </div>
        <a href="team_dashboard.php" style="text-decoration:none; color:#3498db;">← Back to Panel</a>
    </div>

    <div class="info-grid">
        <div class="card">
            <h3><i class="fa fa-user"></i> Patient Profile</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($case['user_name']); ?> (<?php echo $case['gender']; ?>)</p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($case['user_email']); ?> | <?php echo $case['user_phone']; ?></p>
            <p><strong>Category:</strong> <span class="status-pill"><?php echo $case['category']; ?></span></p>
            <p><strong>Patient Description:</strong><br>
               <small style="color:#555;"><?php echo nl2br(htmlspecialchars($case['description'])); ?></small></p>
        </div>

        <div class="card">
            <h3><i class="fa fa-users"></i> Support Network</h3>
            <p><strong>Assigned NGO:</strong><br> <?php echo $case['ngo_name'] ? htmlspecialchars($case['ngo_name']) : "No NGO Linked"; ?></p>
            <hr style="border:0; border-top:1px solid #f4f4f4; margin:10px 0;">
            <p><strong>Lead Counselor:</strong><br> 
               <?php if($case['counselor_name']): ?>
                   Dr. <?php echo htmlspecialchars($case['counselor_name']); ?> (<?php echo $case['counselor_spec']; ?>)
               <?php else: ?>
                   <span style="color:#e67e22;">Waiting for Assignment</span>
               <?php endif; ?>
            </p>
            <p><strong>Current Case Status:</strong> <span class="status-pill" style="background:#2ecc71; color:white;"><?php echo $case['case_status']; ?></span></p>
        </div>
    </div>

    <div class="card" style="margin-bottom:25px;">
        <h3>Counseling Logs</h3>
        <?php if(mysqli_num_rows($sessions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Session Notes</th>
                        <th>Follow-up Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = mysqli_fetch_assoc($sessions)): ?>
                    <tr>
                        <td><strong><?php echo date('M d, Y', strtotime($s['session_date'])); ?></strong></td>
                        <td><?php echo nl2br(htmlspecialchars($s['notes'])); ?></td>
                        <td><?php echo $s['next_session_date'] ? date('M d, Y', strtotime($s['next_session_date'])) : 'Not Set'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="padding:10px; background:#f9f9f9; color:#999; text-align:center;">No counseling sessions have been logged for this patient yet.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Task & Skill Progress</h3>
        <?php if(mysqli_num_rows($skills) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Assigned Skill</th>
                        <th>Status</th>
                        <th>Counselor Remarks</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($sk = mysqli_fetch_assoc($skills)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($sk['skill_name']); ?></strong></td>
                        <td><span class="status-pill"><?php echo $sk['skill_status']; ?></span></td>
                        <td><?php echo htmlspecialchars($sk['remarks']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($sk['updated_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="padding:10px; background:#f9f9f9; color:#999; text-align:center;">No skill development tasks have been assigned yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>