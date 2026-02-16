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
// Handle Add Session
// ===============================
$successMsg = "";
$errorMsg = "";

if (isset($_POST['add_session'])) {
    $session_date = $_POST['session_date'];
    $notes = $_POST['notes'];
    $next_session_date = $_POST['next_session_date'] ?: NULL;

    $stmt_insert = mysqli_prepare($conn, "INSERT INTO counseling_sessions (case_id, session_date, notes, next_session_date) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert, "isss", $case_id, $session_date, $notes, $next_session_date);

    if (mysqli_stmt_execute($stmt_insert)) {
        $successMsg = "Session added successfully.";
    } else {
        $errorMsg = "Failed to add session.";
    }
    mysqli_stmt_close($stmt_insert);
}

// ===============================
// Handle Delete Session
// ===============================
if (isset($_GET['delete'])) {
    $session_id = intval($_GET['delete']);
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM counseling_sessions WHERE session_id = ? AND case_id = ?");
    mysqli_stmt_bind_param($stmt_delete, "ii", $session_id, $case_id);
    if (mysqli_stmt_execute($stmt_delete)) {
        $successMsg = "Session deleted successfully.";
    } else {
        $errorMsg = "Failed to delete session.";
    }
    mysqli_stmt_close($stmt_delete);
}

// ===============================
// Fetch Sessions for this case
// ===============================
$stmt_fetch = mysqli_prepare($conn, "SELECT * FROM counseling_sessions WHERE case_id = ? ORDER BY session_date DESC");
mysqli_stmt_bind_param($stmt_fetch, "i", $case_id);
mysqli_stmt_execute($stmt_fetch);
$result_sessions = mysqli_stmt_get_result($stmt_fetch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions for Case <?php echo $case_id; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>Sessions for Case ID: <?php echo $case_id; ?></h2>

    <?php if ($successMsg) echo "<div style='color:green;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='color:red;'>$errorMsg</div>"; ?>

    <h3>Add New Session</h3>
    <form method="post">
        <label>Session Date & Time:</label> 
        <input type="datetime-local" name="session_date" required>  

        <label>Notes:</label> 
        <textarea name="notes" rows="4" cols="50"></textarea>  

        <label>Next Session Date (optional):</label> 
        <input type="datetime-local" name="next_session_date">  

        <input type="submit" name="add_session" value="Add Session">
    </form>

    <h3>Existing Sessions</h3>

    <?php if(mysqli_num_rows($result_sessions) > 0) { ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Session Date</th>
            <th>Notes</th>
            <th>Next Session</th>
            <th>Actions</th>
        </tr>
        <?php while($session = mysqli_fetch_assoc($result_sessions)) { ?>
        <tr>
            <td><?php echo $session['session_id']; ?></td>
            <td><?php echo $session['session_date']; ?></td>
            <td><?php echo htmlspecialchars($session['notes']); ?></td>
            <td><?php echo $session['next_session_date'] ?: '-'; ?></td>
            <td>
                <a href="?case_id=<?php echo $case_id; ?>&delete=<?php echo $session['session_id']; ?>" onclick="return confirm('Delete this session?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } else { ?>
        <p>No sessions recorded yet.</p>
    <?php } ?>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt_case);
mysqli_stmt_close($stmt_fetch);
mysqli_close($conn);
?>
