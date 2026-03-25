<?php
session_start();
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "team_member") {
    header("location: index.php");
    exit;
}

include "config.php";
$team_id = $_SESSION["id"];
$message = "";

// 1. Handle Counselor Recommendation
if (isset($_POST['recommend_counselor'])) {
    $case_id = intval($_POST['case_id']);
    $counselor_id = intval($_POST['counselor_id']);
    
    $update_sql = "UPDATE cases SET recommended_counselor_id = ? WHERE case_id = ? AND team_id = ?";
    $stmt_up = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt_up, "iii", $counselor_id, $case_id, $team_id);
    mysqli_stmt_execute($stmt_up);
    $message = "Counselor updated successfully!";
}

// 2. Handle Closure Request (Setting status to Completed)
if (isset($_POST['request_closure'])) {
    $case_id = intval($_POST['case_id']);
    $update_status = "UPDATE cases SET case_status = 'Completed' WHERE case_id = ? AND team_id = ?";
    $stmt_cl = mysqli_prepare($conn, $update_status);
    mysqli_stmt_bind_param($stmt_cl, "ii", $case_id, $team_id);
    if(mysqli_stmt_execute($stmt_cl)) {
        $message = "Case #$case_id marked as Completed!";
    }
}

// Fetch all Counselors for the dropdown
$c_list = mysqli_query($conn, "SELECT counselor_id, name, specialization FROM counselors");
$counselors_array = [];
while($c = mysqli_fetch_assoc($c_list)) { $counselors_array[] = $c; }

// Fetch assigned cases
$query = "
    SELECT c.case_id, u.full_name AS user_name, n.ngo_name, c.case_status, c.assigned_at, 
           c.category, c.recommended_counselor_id, co.name AS counselor_name
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN ngos n ON c.ngo_id = n.ngo_id
    LEFT JOIN counselors co ON c.recommended_counselor_id = co.counselor_id
    WHERE c.team_id = ?
    ORDER BY c.assigned_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $team_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Dashboard | Heal2Rise</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard { max-width: 1250px; margin: 30px auto; padding: 20px; }
        .status-Ongoing { background: #e3f2fd; color: #1976d2; }
        .status-Completed { background: #d4edda; color: #155724; }
        .status-Pending { background: #fff3cd; color: #856404; }
        
        .btn-manage { background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; float: right; }
        .btn-view { background: #6c757d; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-close-req { background: #e67e22; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        
        .recommend-box { display: flex; gap: 5px; align-items: center; }
        select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 13px; }
        .btn-save { background: #27ae60; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
        .alert { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="dashboard">
    <a href="manage_counselors.php" class="btn-manage"><i class="fa fa-user-md"></i> Counselor Directory</a>
    <h2>Team Panel: <?php echo htmlspecialchars($_SESSION["email"]); ?></h2>

    <?php if($message) echo "<div class='alert'>$message</div>"; ?>

    <div class="card">
        <h3>Cases Management</h3>
        <table width="100%" cellpadding="10">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th>ID & Category</th>
                    <th>User / NGO</th>
                    <th>Status</th>
                    <th>Counselor Assignment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($case = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td>
                        <strong>#<?php echo $case['case_id']; ?></strong><br>
                        <small class="badge"><?php echo $case['category']; ?></small>
                    </td>
                    <td>
                        <i class="fa fa-user"></i> <?php echo htmlspecialchars($case['user_name']); ?><br>
                        <i class="fa fa-university"></i> <?php echo htmlspecialchars($case['ngo_name']); ?>
                    </td>
                    <td>
                        <span class="badge status-<?php echo $case['case_status']; ?>">
                            <?php echo $case['case_status']; ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" class="recommend-box">
                            <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                            <select name="counselor_id">
                                <option value="">-- Assign Counselor --</option>
                                <?php foreach($counselors_array as $c) { 
                                    $selected = ($case['recommended_counselor_id'] == $c['counselor_id']) ? "selected" : "";
                                    echo "<option value='{$c['counselor_id']}' $selected>{$c['name']}</option>";
                                } ?>
                            </select>
                            <button type="submit" name="recommend_counselor" class="btn-save">
                                <i class="fa fa-check"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div>
                            <a href="case_view.php?id=<?php echo $case['case_id']; ?>" class="btn-view" title="View Details">
                                <i class="fa fa-eye"></i> Details
                            </a>

                            <?php if($case['case_status'] != 'Completed' && $case['case_status'] != 'Closed'): ?>
                                <form method="POST" onsubmit="return confirm('Mark this case as Completed for Admin review?')">
                                    <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                                    <button type="submit" name="request_closure" class="btn-close-req">
                                        <i class="fa fa-flag-checkered"></i> Finish
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>