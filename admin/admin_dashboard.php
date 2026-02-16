<?php
session_start();
include "config.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Fetch totals
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_ngos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ngos"))['total'];
$total_team = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM team_members"))['total'];
$total_cases = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM cases"))['total'];

// Fetch recent 10 cases with details
$cases_query = "
    SELECT c.case_id, u.full_name AS user_name, n.ngo_name, t.full_name AS team_name, c.case_status, c.assigned_at, c.closed_at
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN ngos n ON c.ngo_id = n.ngo_id
    JOIN team_members t ON c.team_id = t.team_id
    ORDER BY c.assigned_at DESC
    LIMIT 10
";
$cases_result = mysqli_query($conn, $cases_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Heal2Rise</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        body { background:#f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#333; margin:0; padding:0;}
        .container { max-width:1200px; margin:30px auto; padding:20px; }
        h1 { color:#2c3e50; margin-bottom:20px; }
        .stats { display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
        .stat-card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); text-align:center; }
        .stat-card h2 { font-size:32px; color:#007BFF; margin-bottom:5px; }
        .stat-card p { font-size:14px; color:#555; }
        .cases-table { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { border:1px solid #ddd; padding:10px; text-align:left; }
        th { background:#007BFF; color:#fff; }
        tr:hover { background:#f1f1f1; }
        @media(max-width:768px){ .stats{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container">
    <h1>Admin Dashboard</h1>

    <div class="stats">
        <div class="stat-card">
            <h2><?php echo $total_users; ?></h2>
            <p>Total Users</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $total_ngos; ?></h2>
            <p>Total NGOs</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $total_team; ?></h2>
            <p>Team Members</p>
        </div>
        <div class="stat-card">
            <h2><?php echo $total_cases; ?></h2>
            <p>Total Cases</p>
        </div>
    </div>

    <div class="cases-table">
        <h2>Recent Cases</h2>
        <table>
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>User</th>
                    <th>NGO</th>
                    <th>Team Member</th>
                    <th>Status</th>
                    <th>Assigned At</th>
                    <th>Closed At</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($cases_result) > 0): ?>
                    <?php while($case = mysqli_fetch_assoc($cases_result)): ?>
                        <tr>
                            <td><?php echo $case['case_id']; ?></td>
                            <td><?php echo htmlspecialchars($case['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($case['ngo_name']); ?></td>
                            <td><?php echo htmlspecialchars($case['team_name']); ?></td>
                            <td><?php echo $case['case_status']; ?></td>
                            <td><?php echo $case['assigned_at']; ?></td>
                            <td><?php echo $case['closed_at'] ? $case['closed_at'] : '-'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;color:red;">No cases found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>
