<?php
session_start();
include "../config.php";

// 1. Security Access Control
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "counselor") {
    header("location: ../index.php");
    exit;
}

$counselor_id = $_SESSION["id"];
$counselor_name = isset($_SESSION["name"]) ? $_SESSION["name"] : "Counselor";

// 2. Fetch cases recommended to THIS counselor
$query = "
    SELECT c.case_id, u.full_name AS user_name, c.category, c.case_status, c.assigned_at
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.recommended_counselor_id = ?
    ORDER BY c.case_id DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $counselor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselor Dashboard | Heal2Rise</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #6c5ce7; --secondary: #a29bfe; --success: #00b894; --warning: #fdcb6e; --info: #0984e3; }
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .dashboard-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .welcome-card { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 20px rgba(108, 92, 231, 0.2); }
        
        .case-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        .case-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid var(--primary); transition: transform 0.2s; }
        .case-card:hover { transform: translateY(-5px); }
        
        .case-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .case-id { font-weight: bold; color: #636e72; font-size: 0.9rem; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        
        .user-info h3 { margin: 5px 0; color: #2d3436; }
        .category-tag { color: var(--primary); font-size: 0.85rem; font-weight: 600; }
        
        .action-group { display: flex; gap: 10px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; }
        .btn-dash { flex: 1; text-align: center; padding: 8px 5px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: 0.3s; }
        
        .btn-details { background: var(--info); color: white; }
        .btn-sessions { background: var(--success); color: white; }
        .btn-skills { background: var(--warning); color: #2d3436; }
        .btn-dash:hover { opacity: 0.85; }
        
        .no-data { text-align: center; padding: 50px; background: white; border-radius: 12px; color: #b2bec3; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="dashboard-container">
    <div class="welcome-card">
        <h1>Welcome, Dr. <?php echo htmlspecialchars($counselor_name); ?></h1>
        <p>You have <b><?php echo mysqli_num_rows($result); ?></b> active cases recommended to your care.</p>
    </div>

    <div class="case-grid">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="case-card">
                    <div class="case-header">
                        <span class="case-id">CASE #<?php echo $row['case_id']; ?></span>
                        <span class="status-badge" style="background: #dfe6e9; color: #2d3436;">
                            <?php echo $row['case_status']; ?>
                        </span>
                    </div>

                    <div class="user-info">
                        <span class="category-tag"><?php echo $row['category']; ?></span>
                        <h3><?php echo htmlspecialchars($row['user_name']); ?></h3>
                        <small style="color: #b2bec3;">Assigned: <?php echo date('M d, Y', strtotime($row['assigned_at'])); ?></small>
                    </div>

                    <div class="action-group">
                        <a href="case_view.php?id=<?php echo $row['case_id']; ?>" class="btn-dash btn-details">
                            <i class="fa fa-eye"></i> Details
                        </a>
                        
                        <a href="sessions_manage.php?case_id=<?php echo $row['case_id']; ?>" class="btn-dash btn-sessions">
                            <i class="fa fa-calendar-check"></i> Sessions
                        </a>
                        
                        <a href="skills_assignment.php?case_id=<?php echo $row['case_id']; ?>" class="btn-dash btn-skills">
                            <i class="fa fa-lightbulb"></i> Skills
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data" style="grid-column: 1 / -1;">
                <i class="fa fa-folder-open fa-3x"></i>
                <p>No patients have been recommended to you yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>