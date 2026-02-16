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

// Handle status update
if (isset($_POST['update_case'])) {
    $case_id = intval($_POST['case_id']);
    $case_status = $_POST['case_status'];
    $closed_at = !empty($_POST['closed_at']) ? $_POST['closed_at'] : NULL;

    $stmt = $conn->prepare("UPDATE cases SET case_status=?, closed_at=? WHERE case_id=?");
    $stmt->bind_param("ssi", $case_status, $closed_at, $case_id);

    if ($stmt->execute()) {
        $successMsg = "Case updated successfully.";
    } else {
        $errorMsg = "Error updating case: " . $conn->error;
    }
    $stmt->close();
}

// Search logic
$searchQuery = "";
if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
    $search = mysqli_real_escape_string($conn, trim($_GET["search"]));
    $searchQuery = "WHERE u.full_name LIKE '%$search%' OR n.ngo_name LIKE '%$search%' OR t.full_name LIKE '%$search%'";
}

// Fetch Cases
$query = "
    SELECT c.case_id, u.full_name AS user_name, n.ngo_name, t.full_name AS team_name, t.role AS team_role,
           c.case_status, c.assigned_at, c.closed_at
    FROM cases c
    JOIN users u ON c.user_id = u.user_id
    JOIN ngos n ON c.ngo_id = n.ngo_id
    JOIN team_members t ON c.team_id = t.team_id
    $searchQuery
    ORDER BY c.case_id DESC
";
$cases = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Cases | Heal2Rise</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        /* Container & Typography */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; }
        .container { width: 95%; max-width: 1200px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9rem; }
        th { background-color: #34495e; color: #ffffff; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; vertical-align: middle; }
        tr:hover { background-color: #f9f9f9; }

        /* Status Badges */
        .status-pill { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
        .status-Assigned { background: #e3f2fd; color: #0d47a1; }
        .status-Ongoing { background: #fff3e0; color: #e65100; }
        .status-Rehabilitation { background: #f3e5f5; color: #7b1fa2; }
        .status-Completed { background: #e8f5e9; color: #2e7d32; }
        .status-Closed { background: #ffebee; color: #c62828; }

        /* Form Elements inside Table */
        .inline-form { display: flex; align-items: center; gap: 5px; }
        select.status-select, input.date-input {
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.85rem;
            outline: none;
        }
        select.status-select:focus { border-color: #007BFF; }

        /* Buttons */
        .btn { padding: 7px 12px; border:none; border-radius:4px; cursor:pointer; font-size: 0.85rem; font-weight: 600; transition: 0.3s; }
        .btn-update { background:#2ecc71; color:#fff; }
        .btn-update:hover { background:#27ae60; }
        .btn-view { background:#3498db; color:#fff; text-decoration: none; display: inline-block; }
        .btn-view:hover { background:#2980b9; }

        /* Search Bar */
        .search-box { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px; }
        .btn-search { background: #34495e; color: white; padding: 8px 15px; border-radius: 4px; border: none; cursor: pointer; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container">
    <h2>Heal2Rise | Case Management</h2>

    <?php if ($successMsg) echo "<div style='padding:10px; background:#d4edda; color:#155724; border-radius:4px; margin-bottom:15px;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='padding:10px; background:#f8d7da; color:#721c24; border-radius:4px; margin-bottom:15px;'>$errorMsg</div>"; ?>

    <div style="display:flex; justify-content: space-between; align-items: center;">
        <form method="get">
            <input type="text" name="search" class="search-box" placeholder="Search User, NGO, or Team..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn-search">Search</button>
        </form>
        <a href="admin_add_case.php" style="text-decoration:none; color:#3498db; font-weight:bold;">+ Assign New Case</a>
    </div>

    <?php if (mysqli_num_rows($cases) > 0) { ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>NGO</th>
                <th>Team Member</th>
                <th>Status Management</th>
                <th>History</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($case = mysqli_fetch_assoc($cases)) { ?>
            <tr>
                <td><strong>#<?php echo $case['case_id']; ?></strong></td>
                <td><?php echo htmlspecialchars($case['user_name']); ?></td>
                <td><?php echo htmlspecialchars($case['ngo_name']); ?></td>
                <td>
                    <?php echo htmlspecialchars($case['team_name']); ?> 
                    <small style="color:#777;"><?php echo $case['team_role']; ?></small>
                </td>
                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                        <select name="case_status" class="status-select status-<?php echo $case['case_status']; ?>">
                            <?php
                            $statuses = ['Assigned','Ongoing','Rehabilitation','Completed','Closed'];
                            foreach($statuses as $status) {
                                $selected = ($status == $case['case_status']) ? "selected" : "";
                                echo "<option value='$status' $selected>$status</option>";
                            }
                            ?>
                        </select>
                        <input type="date" name="closed_at" class="date-input" title="Closing Date"
                               value="<?php echo $case['closed_at'] ? date('Y-m-d', strtotime($case['closed_at'])) : ''; ?>">
                        <button type="submit" name="update_case" class="btn btn-update">Update</button>
                    </form>
                </td>
                <td>
                    <small>
                        <b>Assigned:</b> <?php echo date('M d, Y', strtotime($case['assigned_at'])); ?> 
                        <b>Closed:</b> <?php echo $case['closed_at'] ? date('M d, Y', strtotime($case['closed_at'])) : "-"; ?>
                    </small>
                </td>
                <td>
                    <a href="case_details.php?case_id=<?php echo $case['case_id']; ?>" class="btn btn-view">View Logs</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php } else { echo "<p style='text-align:center; padding:20px;'>No active cases found in the system.</p>"; } ?>
</div>
</body>
</html>