<?php
session_start();
include "config.php";

// Check Admin Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

$successMsg = "";
$errorMsg = "";

// Handle Full Update
if (isset($_POST['full_update'])) {
    $case_id = intval($_POST['case_id']);
    $ngo_id = !empty($_POST['ngo_id']) ? intval($_POST['ngo_id']) : NULL;
    $team_id = !empty($_POST['team_id']) ? intval($_POST['team_id']) : NULL;
    $case_status = $_POST['case_status'];
    $closed_at = !empty($_POST['closed_at']) ? $_POST['closed_at'] : NULL;

    $stmt = $conn->prepare("UPDATE cases SET ngo_id=?, team_id=?, case_status=?, closed_at=? WHERE case_id=?");
    $stmt->bind_param("iissi", $ngo_id, $team_id, $case_status, $closed_at, $case_id);

    if ($stmt->execute()) {
        $successMsg = "Case #$case_id updated successfully!";
    } else {
        $errorMsg = "Update failed: " . $conn->error;
    }
    $stmt->close();
}

// Fetch NGOs and Team Members
$ngos_list = mysqli_query($conn, "SELECT ngo_id, ngo_name FROM ngos ORDER BY ngo_name ASC");
$team_list = mysqli_query($conn, "SELECT team_id, full_name, role FROM team_members ORDER BY full_name ASC");

// Search logic
$searchQuery = "";
if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
    $search = mysqli_real_escape_string($conn, trim($_GET["search"]));
    $searchQuery = "WHERE u.full_name LIKE '%$search%' OR n.ngo_name LIKE '%$search%' OR c.category LIKE '%$search%'";
}

$query = "
    SELECT c.*, u.full_name AS user_name, n.ngo_name, t.full_name AS team_name
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN ngos n ON c.ngo_id = n.ngo_id
    LEFT JOIN team_members t ON c.team_id = t.team_id
    $searchQuery
    ORDER BY FIELD(c.case_status, 'Pending', 'Assigned', 'Ongoing', 'Rehabilitation', 'Completed', 'Closed'), c.case_id DESC
";
$cases = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Management | Heal2Rise Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/style.css">

    <style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --dark: #5a5c69;
            --light: #f8f9fc;
        }

        body { font-family: 'Nunito', sans-serif; background-color: var(--light); color: #333; margin: 0; }
        .main-content { padding: 40px; max-width: 1400px; margin: auto; }
        
        h1 { font-weight: 700; color: var(--dark); margin-bottom: 5px; }
        .subtitle { color: var(--secondary); margin-bottom: 30px; font-size: 0.9rem; }

        /* Search Bar Styling */
        .search-box { background: white; padding: 10px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; gap: 10px; align-items: center; width: fit-content; }
        .search-box input { border: 1px solid #ddd; padding: 10px 15px; border-radius: 8px; width: 300px; outline: none; }
        .btn-search { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }

        /* Table Styling */
        .card { background: white; border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); overflow: hidden; padding: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fdfdfd; padding: 15px; font-size: 0.75rem; text-transform: uppercase; color: var(--secondary); border-bottom: 2px solid #edf0f5; text-align: left; }
        td { padding: 20px 15px; border-bottom: 1px solid #edf0f5; vertical-align: middle; }
        tr:hover { background-color: #fcfdfe; }

        /* Status Badges */
        .badge { padding: 5px 10px; border-radius: 5px; font-size: 0.7rem; font-weight: 800; display: inline-block; text-transform: uppercase; margin-bottom: 8px; }
        .status-Pending { background: #ffeeba; color: #856404; }
        .status-Assigned { background: #cce5ff; color: #004085; }
        .status-Ongoing { background: #d1ecf1; color: #0c5460; }
        .status-Completed { background: #d4edda; color: #155724; }
        .status-Closed { background: #f8d7da; color: #721c24; }

        /* Action Row Controls */
        .edit-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        select, input[type="date"] { background: #fff; border: 1px solid #d1d3e2; border-radius: 6px; padding: 8px; font-size: 0.85rem; color: #6e707e; outline: none; }
        select:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25); }
        
        .btn-save { background: var(--success); color: white; border: none; padding: 9px 15px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 5px; }
        .btn-save:hover { background: #169b6b; transform: translateY(-1px); }

        .view-btn { height: 40px; width: 40px; border-radius: 50%; background: var(--info); color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; }
        .view-btn:hover { background: #2a96a5; transform: scale(1.1); }

        /* Messages */
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid; }
        .alert-success { background: #d4edda; color: #155724; border-left-color: #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left-color: #dc3545; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div>
            <h1>Case Management Center</h1>
            <p class="subtitle">Assign NGOs, manage team leads, and monitor patient rehabilitation progress.</p>
        </div>
        <form method="get" class="search-box">
            <i class="fa fa-search" style="color: #ccc;"></i>
            <input type="text" name="search" placeholder="Search ID, name, or NGO..." value="<?php echo @$_GET['search']; ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>

    <?php if ($successMsg) echo "<div class='alert alert-success'><i class='fa fa-check-circle'></i> $successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div class='alert alert-error'><i class='fa fa-exclamation-circle'></i> $errorMsg</div>"; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th width="15%">Case & Issue</th>
                    <th width="15%">Patient</th>
                    <th width="62%">Assignment & Management</th>
                    <th width="8%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($case = mysqli_fetch_assoc($cases)) { ?>
                <tr>
                    <td>
                        <span class="badge status-<?php echo $case['case_status']; ?>"><?php echo $case['case_status']; ?></span><br>
                        <strong style="font-size: 1.1rem;">#<?php echo $case['case_id']; ?></strong><br>
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary);"><?php echo $case['category']; ?></span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="height: 35px; width: 35px; border-radius: 50%; background: #eaecf4; display: flex; align-items: center; justify-content: center; color: var(--secondary);">
                                <i class="fa fa-user"></i>
                            </div>
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($case['user_name']); ?></span>
                        </div>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                            <div class="edit-row">
                                <select name="ngo_id" style="width: 180px;">
                                    <option value="">-- No NGO --</option>
                                    <?php mysqli_data_seek($ngos_list, 0); 
                                          while($n = mysqli_fetch_assoc($ngos_list)) {
                                              $sel = ($n['ngo_id'] == $case['ngo_id']) ? "selected" : "";
                                              echo "<option value='{$n['ngo_id']}' $sel>{$n['ngo_name']}</option>";
                                          } ?>
                                </select>

                                <select name="team_id" style="width: 180px;">
                                    <option value="">-- No Team --</option>
                                    <?php mysqli_data_seek($team_list, 0); 
                                          while($t = mysqli_fetch_assoc($team_list)) {
                                              $sel = ($t['team_id'] == $case['team_id']) ? "selected" : "";
                                              echo "<option value='{$t['team_id']}' $sel>{$t['full_name']} ({$t['role']})</option>";
                                          } ?>
                                </select>

                                <select name="case_status">
                                    <?php
                                    $statuses = ['Pending','Assigned','Ongoing','Rehabilitation','Completed','Closed'];
                                    foreach($statuses as $st) {
                                        $sel = ($st == $case['case_status']) ? "selected" : "";
                                        echo "<option value='$st' $sel>$st</option>";
                                    }
                                    ?>
                                </select>

                                <button type="submit" name="full_update" class="btn-save">
                                    <i class="fa fa-save"></i> Apply
                                </button>
                            </div>
                            
                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 0.75rem; font-weight: 600; color: var(--secondary);">Closing Date:</span>
                                <input type="date" name="closed_at" value="<?php echo $case['closed_at'] ? date('Y-m-d', strtotime($case['closed_at'])) : ''; ?>">
                                <?php if($case['assigned_at']): ?>
                                    <span style="font-size: 0.7rem; color: #b7b9cc; font-style: italic;">Created: <?php echo date('M d, Y', strtotime($case['assigned_at'])); ?></span>
                                <?php endif; ?>
                            </div>
                        </form>
                    </td>
                    <td style="text-align: center;">
                        <a href="case_details.php?case_id=<?php echo $case['case_id']; ?>" class="view-btn" title="View Logs">
                            <i class="fa fa-external-link-alt"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>