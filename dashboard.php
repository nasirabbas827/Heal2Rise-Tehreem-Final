<?php
session_start();

// 1. Access Control
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "user") {
    header("location: index.php");
    exit;
}

include "config.php";
$user_id = $_SESSION["id"];
$message = "";
$msg_type = "";

// ===============================
// Handle CRUD: Create Case (Insert)
// ===============================
if (isset($_POST['submit_case'])) {
    $category = $_POST['category']; // Matches ENUM: 'Depression','Family Issues', etc.
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $insert_sql = "INSERT INTO cases (user_id, category, description, case_status) VALUES (?, ?, ?, 'Pending')";
    $stmt_ins = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt_ins, "iss", $user_id, $category, $description);
    
    if (mysqli_stmt_execute($stmt_ins)) {
        $message = "Case submitted successfully! An admin will assign an NGO shortly.";
        $msg_type = "success";
    } else {
        $message = "Error submitting case. Please try again.";
        $msg_type = "error";
    }
    mysqli_stmt_close($stmt_ins);
}

// ===============================
// Handle CRUD: Delete Case
// ===============================
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Security: Only delete if it belongs to user AND is still 'Pending'
    $del_sql = "DELETE FROM cases WHERE case_id = ? AND user_id = ? AND case_status = 'Pending'";
    $stmt_del = mysqli_prepare($conn, $del_sql);
    mysqli_stmt_bind_param($stmt_del, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);
    header("Location: dashboard.php?msg=deleted");
    exit;
}

// ===============================
// Fetch all cases for this user
// ===============================
$query = "
    SELECT 
        c.case_id,
        c.category,
        c.case_status,
        c.assigned_at,
        c.description,
        n.ngo_name,
        t.full_name AS team_member_name
    FROM cases c
    LEFT JOIN ngos n ON c.ngo_id = n.ngo_id
    LEFT JOIN team_members t ON c.team_id = t.team_id
    WHERE c.user_id = ?
    ORDER BY c.case_id DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Heal2Rise</title>
    <link rel="stylesheet" href="./CSS/style.css">
 
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["email"]); ?></h2>
    <hr>

    <?php if($message): ?>
        <div class="alert <?php echo $msg_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h3>Report a New Issue</h3>
        <form action="" method="POST">
            <div style="margin-bottom: 15px;">
                <label><b>Nature of Issue:</b></label><br>
                <select name="category" required style="width: 100%; padding: 8px; margin-top: 5px;">
                    <option value="" disabled selected>Select a category</option>
                    <option value="Depression">Depression</option>
                    <option value="Family Issues">Family Issues</option>
                    <option value="Marital Issues">Marital Issues</option>
                    <option value="Hopelessness">Hopelessness</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label><b>Describe your situation:</b></label><br>
                <textarea name="description" required placeholder="Tell us how we can help..." style="width: 100%; height: 100px; padding: 8px; margin-top: 5px; box-sizing: border-box;"></textarea>
            </div>
            
            <button type="submit" name="submit_case" class="btn-submit">Submit My Case</button>
        </form>
    </div>

    <h3>Your History</h3>
    <?php if(mysqli_num_rows($result) > 0) { ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Assigned NGO</th>
                <th>Status</th>
                <th>Date Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($case = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td>#<?php echo $case['case_id']; ?></td>
                <td><?php echo htmlspecialchars($case['category']); ?></td>
                <td>
                    <?php 
                        if ($case['ngo_name']) {
                            echo "<b>" . htmlspecialchars($case['ngo_name']) . "</b><br>";
                            echo "<small>Member: " . htmlspecialchars($case['team_member_name']) . "</small>";
                        } else {
                            echo "<i style='color: #6c757d;'>Verification in progress...</i>";
                        }
                    ?>
                </td>
                <td>
                    <span class="status-badge <?php echo $case['case_status']; ?>">
                        <?php echo $case['case_status']; ?>
                    </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($case['assigned_at'])); ?></td>
                <td>
                    <a href="case_details.php?case_id=<?php echo $case['case_id']; ?>">View</a>
                    
                    <?php if($case['case_status'] == 'Pending') { ?>
                        | <a href="edit_case.php?id=<?php echo $case['case_id']; ?>" class="btn-edit">Edit</a>
                        | <a href="dashboard.php?delete_id=<?php echo $case['case_id']; ?>" 
                             class="btn-del" onclick="return confirm('Delete this request?')">Delete</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php } else { ?>
        <p style="color: #6c757d;">You haven't submitted any cases yet.</p>
    <?php } ?>
</div>

</body>
</html>