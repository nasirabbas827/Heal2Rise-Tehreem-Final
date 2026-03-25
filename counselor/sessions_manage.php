<?php
session_start();
include "../config.php";

// Access Control
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "counselor") {
    header("location: ../index.php");
    exit;
}

$case_id = isset($_GET['case_id']) ? intval($_GET['case_id']) : 0;
if ($case_id == 0) { header("location: counselor_dashboard.php"); exit; }

$message = "";

// --- CREATE ---
if (isset($_POST['add_session'])) {
    $s_date = $_POST['session_date'];
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $next_date = !empty($_POST['next_session_date']) ? $_POST['next_session_date'] : NULL;

    $sql = "INSERT INTO counseling_sessions (case_id, session_date, notes, next_session_date) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $case_id, $s_date, $notes, $next_date);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Session logged successfully!";
    } else {
        $message = "Error saving session.";
    }
}

// --- DELETE ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM counseling_sessions WHERE session_id = $del_id");
    header("Location: sessions_manage.php?case_id=$case_id&msg=deleted");
}

// --- READ ---
$query = "SELECT * FROM counseling_sessions WHERE case_id = ? ORDER BY session_date DESC";
$stmt_fetch = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt_fetch, "i", $case_id);
mysqli_stmt_execute($stmt_fetch);
$sessions = mysqli_stmt_get_result($stmt_fetch);

// Fetch User Name
$user_res = mysqli_query($conn, "SELECT u.full_name FROM cases c JOIN users u ON c.user_id = u.user_id WHERE c.case_id = $case_id");
$user_data = mysqli_fetch_assoc($user_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sessions | Heal2Rise</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div >
    <h2>Sessions for: <?php echo htmlspecialchars($user_data['full_name']); ?></h2>
    
    <?php if($message) echo "<p style='color:green;'>$message</p>"; ?>

        <h3>Log New Session</h3>
        <form method="POST">
            <div style="margin-bottom: 10px;">
                <label><b>Session Date:</b></label><br>
                <input type="datetime-local" name="session_date" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 10px;">
                <label><b>Notes:</b></label><br>
                <textarea name="notes" rows="4" required style="width: 100%; padding: 8px;"></textarea>
            </div>

            <div style="margin-bottom: 10px;">
                <label><b>Next Session Date (Optional):</b></label><br>
                <input type="datetime-local" name="next_session_date" style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" name="add_session" style="background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer;">Save Session</button>
        </form>
    </div>


    <h3>Session History</h3>
    <table border="1" width="100%" cellpadding="10" style="border-collapse: collapse;">
        <thead>
            <tr style="background: #333; color: #fff;">
                <th>ID</th>
                <th>Date & Time</th>
                <th>Counseling Notes</th>
                <th>Next Session</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($sessions) > 0): ?>
                <?php while($s = mysqli_fetch_assoc($sessions)): ?>
                <tr>
                    <td><?php echo $s['session_id']; ?></td>
                    <td><?php echo date('M d, Y - h:i A', strtotime($s['session_date'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($s['notes'])); ?></td>
                    <td>
                        <?php 
                        echo $s['next_session_date'] 
                            ? date('M d, Y - h:i A', strtotime($s['next_session_date'])) 
                            : '<em>Not scheduled</em>'; 
                        ?>
                    </td>
                    <td>
                        <a href="?case_id=<?php echo $case_id; ?>&delete_id=<?php echo $s['session_id']; ?>" 
                           style="color: red; text-decoration: none;" 
                           onclick="return confirm('Delete this record?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" align="center">No sessions found for this patient.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>