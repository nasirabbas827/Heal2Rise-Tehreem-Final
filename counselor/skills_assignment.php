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

// --- CREATE: Assign New Skill ---
if (isset($_POST['assign_skill'])) {
    $skill_name = mysqli_real_escape_string($conn, $_POST['skill_name']);
    $status = $_POST['skill_status'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $sql = "INSERT INTO skill_development (case_id, skill_name, skill_status, remarks) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $case_id, $skill_name, $status, $remarks);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Skill assigned successfully!";
    } else {
        $message = "Error assigning skill: " . mysqli_error($conn);
    }
}

// --- DELETE: Remove Skill ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM skill_development WHERE skill_id = $del_id");
    header("Location: skills_assignment.php?case_id=$case_id&msg=deleted");
}

// --- READ: Fetch skills for this case ---
$query = "SELECT * FROM skill_development WHERE case_id = ? ORDER BY updated_at DESC";
$stmt_fetch = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt_fetch, "i", $case_id);
mysqli_stmt_execute($stmt_fetch);
$skills = mysqli_stmt_get_result($stmt_fetch);

// Fetch User Name for Header
$user_res = mysqli_query($conn, "SELECT u.full_name FROM cases c JOIN users u ON c.user_id = u.user_id WHERE c.case_id = $case_id");
$user_data = mysqli_fetch_assoc($user_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skill Development | Heal2Rise</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div  >
    <h2>Skill Development: <?php echo htmlspecialchars($user_data['full_name']); ?></h2>
    
    <?php if($message) echo "<p style='color:green;'>$message</p>"; ?>

    <div >
        <h3>Assign New Skill/Task</h3>
        <form method="POST">
            <div style="margin-bottom: 10px;">
                <label><b>Skill/Task Name:</b></label><br>
                <input type="text" name="skill_name" placeholder="e.g. Deep Breathing, Journaling..." required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 10px;">
                <label><b>Initial Status:</b></label><br>
                <select name="skill_status" style="width: 100%; padding: 8px;">
                    <option value="Learning">Learning</option>
                    <option value="Practicing">Practicing</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <div style="margin-bottom: 10px;">
                <label><b>Counselor Remarks:</b></label><br>
                <textarea name="remarks" rows="3" placeholder="Instructions for the user..." style="width: 100%; padding: 8px;"></textarea>
            </div>

            <button type="submit" name="assign_skill" style="background: #007BFF; color: white; border: none; padding: 10px 20px; cursor: pointer;">Assign to User</button>
        </form>
    </div>

    <h3>Current Progress</h3>
    <table border="1" width="100%" cellpadding="10" style="border-collapse: collapse;">
        <thead>
            <tr style="background: #333; color: #fff;">
                <th>Skill Name</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Last Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($skills) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($skills)): ?>
                <tr>
                    <td><b><?php echo htmlspecialchars($row['skill_name']); ?></b></td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 4px; background: 
                            <?php 
                                if($row['skill_status'] == 'Completed') echo '#d4edda';
                                elseif($row['skill_status'] == 'Practicing') echo '#fff3cd';
                                else echo '#d1ecf1';
                            ?>;">
                            <?php echo $row['skill_status']; ?>
                        </span>
                    </td>
                    <td><?php echo nl2br(htmlspecialchars($row['remarks'])); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['updated_at'])); ?></td>
                    <td>
                        <a href="?case_id=<?php echo $case_id; ?>&delete_id=<?php echo $row['skill_id']; ?>" 
                           style="color: red; text-decoration: none;" 
                           onclick="return confirm('Remove this skill assignment?')">Remove</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" align="center">No skills assigned yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>