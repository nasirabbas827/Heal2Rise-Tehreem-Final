<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "team_member") {
    header("location: index.php");
    exit;
}

include "config.php";

$team_id = $_SESSION["id"];
$case_id = isset($_GET['case_id']) ? intval($_GET['case_id']) : 0;

// ===============================
// Check if the case belongs to this team member
// ===============================
$stmt_case = mysqli_prepare($conn, "SELECT * FROM cases WHERE case_id = ? AND team_id = ?");
mysqli_stmt_bind_param($stmt_case, "ii", $case_id, $team_id);
mysqli_stmt_execute($stmt_case);
$result_case = mysqli_stmt_get_result($stmt_case);
if (mysqli_num_rows($result_case) === 0) {
    die("You do not have permission to access this case.");
}

// ===============================
// Handle Add Progress Update
// ===============================
$successMsg = "";
$errorMsg = "";

if (isset($_POST['add_progress'])) {
    $progress_note = $_POST['progress_note'];
    $progress_level = $_POST['progress_level'];

    $stmt_insert = mysqli_prepare($conn, "INSERT INTO progress_updates (case_id, updated_by, progress_note, progress_level) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert, "iiss", $case_id, $team_id, $progress_note, $progress_level);

    if (mysqli_stmt_execute($stmt_insert)) {
        $successMsg = "Progress update added successfully.";
    } else {
        $errorMsg = "Failed to add progress update.";
    }
    mysqli_stmt_close($stmt_insert);
}

// ===============================
// Handle Delete Progress Update
// ===============================
if (isset($_GET['delete'])) {
    $progress_id = intval($_GET['delete']);
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM progress_updates WHERE progress_id = ? AND case_id = ?");
    mysqli_stmt_bind_param($stmt_delete, "ii", $progress_id, $case_id);
    if (mysqli_stmt_execute($stmt_delete)) {
        $successMsg = "Progress update deleted successfully.";
    } else {
        $errorMsg = "Failed to delete progress update.";
    }
    mysqli_stmt_close($stmt_delete);
}

// ===============================
// Fetch Progress Updates
// ===============================
$stmt_fetch = mysqli_prepare($conn, "SELECT * FROM progress_updates WHERE case_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt_fetch, "i", $case_id);
mysqli_stmt_execute($stmt_fetch);
$result_progress = mysqli_stmt_get_result($stmt_fetch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Updates for Case <?php echo $case_id; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>Progress Updates for Case ID: <?php echo $case_id; ?></h2>

    <?php if ($successMsg) echo "<div style='color:green;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='color:red;'>$errorMsg</div>"; ?>

    <h3>Add New Progress Update</h3>
    <form method="post">
        <label>Progress Note:</label> 
        <textarea name="progress_note" rows="4" cols="50" required></textarea>  

        <label>Progress Level:</label> 
        <select name="progress_level" required>
            <option value="Low">Low</option>
            <option value="Moderate">Moderate</option>
            <option value="Good">Good</option>
            <option value="Excellent">Excellent</option>
        </select>  

        <input type="submit" name="add_progress" value="Add Progress">
    </form>

    <h3>Existing Progress Updates</h3>

    <?php if(mysqli_num_rows($result_progress) > 0) { ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Note</th>
            <th>Level</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php while($progress = mysqli_fetch_assoc($result_progress)) { ?>
        <tr>
            <td><?php echo $progress['progress_id']; ?></td>
            <td><?php echo htmlspecialchars($progress['progress_note']); ?></td>
            <td><?php echo $progress['progress_level']; ?></td>
            <td><?php echo $progress['created_at']; ?></td>
            <td>
                <a href="?case_id=<?php echo $case_id; ?>&delete=<?php echo $progress['progress_id']; ?>" onclick="return confirm('Delete this progress update?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } else { ?>
        <p>No progress updates recorded yet.</p>
    <?php } ?>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt_case);
mysqli_stmt_close($stmt_fetch);
mysqli_close($conn);
?>
