<?php
session_start();
include "config.php";

// Check Admin Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// 1. Fetch Summary Totals
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_ngos  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ngos"))['total'];
$total_team  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM team_members"))['total'];
$total_cases = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM cases"))['total'];

// 2. Count Cases by Status for Analytics
$pending_cases = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM cases WHERE case_status='Pending'"))['total'];
$ongoing_cases = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM cases WHERE case_status='Ongoing'"))['total'];

// 3. Fetch Recent 10 Cases (Using LEFT JOIN to show cases even if NGO/Team isn't assigned yet)
$cases_query = "
    SELECT c.case_id, u.full_name AS user_name, n.ngo_name, t.full_name AS team_name, 
           c.case_status, c.assigned_at, c.category
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN ngos n ON c.ngo_id = n.ngo_id
    LEFT JOIN team_members t ON c.team_id = t.team_id
    ORDER BY c.assigned_at DESC
    LIMIT 10
";
$cases_result = mysqli_query($conn, $cases_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Heal2Rise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --dark: #5a5c69;
            --light-bg: #f8f9fc;
        }

        body { background: var(--light-bg); font-family: 'Nunito', sans-serif; margin: 0; }
        .dashboard-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            background: #fff; padding: 25px; border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 5px solid var(--primary);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { color: var(--secondary); font-size: 0.8rem; text-transform: uppercase; margin: 0; }
        .stat-card .value { font-size: 1.8rem; font-weight: 700; color: #333; margin: 10px 0; }
        
        /* Color variants for stats */
        .stat-ngo { border-left-color: var(--success); }
        .stat-team { border-left-color: var(--warning); }
        .stat-pending { border-left-color: var(--danger); }

        /* Table Card */
        .content-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 25px; }
        .content-card h2 { font-size: 1.2rem; color: var(--dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #fcfcfc; color: var(--secondary); font-size: 0.85rem; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #f4f4f4; font-size: 0.9rem; }
        
        /* Badges */
        .badge { padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .bg-pending { background: #ffeeba; color: #856404; }
        .bg-ongoing { background: #cce5ff; color: #004085; }
        .bg-completed { background: #d4edda; color: #155724; }
        
        .action-link { color: var(--primary); text-decoration: none; font-weight: 600; }
        .action-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="dashboard-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h1>Admin Control Center</h1>
        <p style="color: #888;">System Date: <?php echo date('d M, Y'); ?></p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><i class="fa fa-users"></i> Total Patients</h3>
            <div class="value"><?php echo number_format($total_users); ?></div>
        </div>
        <div class="stat-card stat-ngo">
            <h3><i class="fa fa-building"></i> Active NGOs</h3>
            <div class="value"><?php echo number_format($total_ngos); ?></div>
        </div>
        <div class="stat-card stat-team">
            <h3><i class="fa fa-user-shield"></i> Experts/Teams</h3>
            <div class="value"><?php echo number_format($total_team); ?></div>
        </div>
        <div class="stat-card stat-pending">
            <h3><i class="fa fa-clock"></i> New Requests</h3>
            <div class="value"><?php echo number_format($pending_cases); ?></div>
        </div>
    </div>

    <div class="content-card">
        <h2><i class="fa fa-history"></i> Latest Case Assignments</h2>
        <table>
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Patient Name</th>
                    <th>Issue Category</th>
                    <th>Assigned NGO</th>
                    <th>Team Lead</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($case = mysqli_fetch_assoc($cases_result)): ?>
                <tr>
                    <td><strong>#<?php echo $case['case_id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($case['user_name']); ?></td>
                    <td><small><?php echo $case['category']; ?></small></td>
                    <td><?php echo $case['ngo_name'] ? htmlspecialchars($case['ngo_name']) : '<em style="color:#ccc;">Not Assigned</em>'; ?></td>
                    <td><?php echo $case['team_name'] ? htmlspecialchars($case['team_name']) : '<em style="color:#ccc;">Pending</em>'; ?></td>
                    <td>
                        <?php 
                            $status_class = "bg-ongoing";
                            if($case['case_status'] == 'Pending') $status_class = "bg-pending";
                            if($case['case_status'] == 'Completed') $status_class = "bg-completed";
                        ?>
                        <span class="badge <?php echo $status_class; ?>">
                            <?php echo $case['case_status']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="admin_case_list.php" class="action-link">Manage</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="admin_case_list.php" style="color: var(--primary); font-size: 0.9rem; text-decoration: none;">View All System Cases <i class="fa fa-arrow-right"></i></a>
        </div>
    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>