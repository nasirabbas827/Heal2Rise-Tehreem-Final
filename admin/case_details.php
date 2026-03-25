<?php
session_start();
include "config.php";

// 1. Security Check for Admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

if (!isset($_GET['case_id'])) {
    die("Case ID is required.");
}

$case_id = intval($_GET['case_id']);

// 2. Comprehensive Query using your provided tables
$query = "
    SELECT c.*, 
           u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone, u.gender, u.issue_type, u.description AS user_desc,
           n.ngo_name, n.email AS ngo_email, n.phone AS ngo_phone, n.specialization AS ngo_spec,
           t.full_name AS team_name, t.role AS team_role,
           co.name AS counselor_name, co.email AS counselor_email, co.phone AS counselor_phone
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN ngos n ON c.ngo_id = n.ngo_id
    LEFT JOIN team_members t ON c.team_id = t.team_id
    LEFT JOIN counselors co ON c.recommended_counselor_id = co.counselor_id
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

// 3. Fetch Related Activity Logs
$sessions = mysqli_query($conn, "SELECT * FROM counseling_sessions WHERE case_id = $case_id ORDER BY session_date DESC");
$skills = mysqli_query($conn, "SELECT * FROM skill_development WHERE case_id = $case_id ORDER BY updated_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Case #<?php echo $case['case_id']; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .container{
            margin:20px;
        }
        .grid-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .card { flex: 1; background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; color: #333; border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        .status-header { background: #333; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <div class="status-header">
        <span><strong>CASE ID: #<?php echo $case['case_id']; ?></strong></span>
        <span>STATUS: <strong><?php echo strtoupper($case['case_status']); ?></strong></span>
    </div>

    <div class="grid-row">
        <div class="card">
            <h3>Patient Profile</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($case['user_name']); ?> (<?php echo $case['gender']; ?>)</p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($case['user_email']); ?> | <?php echo htmlspecialchars($case['user_phone']); ?></p>
            <p><strong>Reported Issue:</strong> <?php echo $case['issue_type']; ?></p>
            <p><strong>Description:</strong><br><em><?php echo nl2br(htmlspecialchars($case['user_desc'])); ?></em></p>
        </div>

        <div class="card">
            <h3>Assigned Counselor</h3>
            <?php if($case['counselor_name']): ?>
                <p><strong>Name:</strong> Dr. <?php echo htmlspecialchars($case['counselor_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($case['counselor_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($case['counselor_phone']); ?></p>
            <?php else: ?>
                <p style="color: red;">No Counselor assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid-row">
        <div class="card">
            <h3>NGO Details</h3>
            <?php if($case['ngo_name']): ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($case['ngo_name']); ?></p>
                <p><strong>Specialization:</strong> <?php echo $case['ngo_spec']; ?></p>
                <p><strong>NGO Email:</strong> <?php echo htmlspecialchars($case['ngo_email']); ?></p>
            <?php else: ?>
                <p>No NGO assigned.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Internal Supervisor</h3>
            <?php if($case['team_name']): ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($case['team_name']); ?></p>
                <p><strong>Role:</strong> <?php echo $case['team_role']; ?></p>
                <p><strong>Assigned On:</strong> <?php echo date('M d, Y', strtotime($case['assigned_at'])); ?></p>
            <?php else: ?>
                <p>No team member assigned.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Session History</h3>
        <?php if(mysqli_num_rows($sessions) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Notes</th>
                    <th>Next Session</th>
                </tr>
            </thead>
            <tbody>
                <?php while($s = mysqli_fetch_assoc($sessions)): ?>
                <tr>
                    <td><?php echo date('M d, Y - h:i A', strtotime($s['session_date'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($s['notes'])); ?></td>
                    <td><?php echo $s['next_session_date'] ? date('M d, Y', strtotime($s['next_session_date'])) : '-'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No sessions recorded.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Skill Development Progress</h3>
        <?php if(mysqli_num_rows($skills) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Skill Name</th>
                    <th>Status</th>
                    <th>Counselor Remarks</th>
                    <th>Last Update</th>
                </tr>
            </thead>
            <tbody>
                <?php while($k = mysqli_fetch_assoc($skills)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($k['skill_name']); ?></strong></td>
                    <td><?php echo $k['skill_status']; ?></td>
                    <td><?php echo htmlspecialchars($k['remarks']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($k['updated_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No skills assigned.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
<?php mysqli_close($conn); ?>