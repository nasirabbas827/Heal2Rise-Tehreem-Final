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
// Handle Add Skill
// ===============================
$successMsg = "";
$errorMsg = "";

if (isset($_POST['add_skill'])) {
    $skill_name = trim($_POST['skill_name']);
    $skill_status = $_POST['skill_status'];
    $remarks = trim($_POST['remarks']);

    $stmt_insert = mysqli_prepare($conn, "INSERT INTO skill_development (case_id, skill_name, skill_status, remarks) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert, "isss", $case_id, $skill_name, $skill_status, $remarks);

    if (mysqli_stmt_execute($stmt_insert)) {
        $successMsg = "Skill added successfully.";
    } else {
        $errorMsg = "Failed to add skill.";
    }
    mysqli_stmt_close($stmt_insert);
}

// ===============================
// Handle Delete Skill
// ===============================
if (isset($_GET['delete'])) {
    $skill_id = intval($_GET['delete']);
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM skill_development WHERE skill_id = ? AND case_id = ?");
    mysqli_stmt_bind_param($stmt_delete, "ii", $skill_id, $case_id);
    if (mysqli_stmt_execute($stmt_delete)) {
        $successMsg = "Skill deleted successfully.";
    } else {
        $errorMsg = "Failed to delete skill.";
    }
    mysqli_stmt_close($stmt_delete);
}

// ===============================
// Fetch Skills for this case
// ===============================
$stmt_fetch = mysqli_prepare($conn, "SELECT * FROM skill_development WHERE case_id = ? ORDER BY updated_at DESC");
mysqli_stmt_bind_param($stmt_fetch, "i", $case_id);
mysqli_stmt_execute($stmt_fetch);
$result_skills = mysqli_stmt_get_result($stmt_fetch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Development for Case <?php echo $case_id; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>Skill Development for Case ID: <?php echo $case_id; ?></h2>

    <?php if ($successMsg) echo "<div style='color:green;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='color:red;'>$errorMsg</div>"; ?>

    <h3>Add New Skill</h3>
    <form method="post">
        <label>Skill Name:</label> 
        <input type="text" name="skill_name" required>  

        <label>Skill Status:</label> 
        <select name="skill_status" required>
            <option value="Learning">Learning</option>
            <option value="Practicing">Practicing</option>
            <option value="Completed">Completed</option>
        </select>  

        <label>Remarks:</label> 
        <textarea name="remarks" rows="3" cols="50"></textarea>  

        <input type="submit" name="add_skill" value="Add Skill">
    </form>

    <h3>Existing Skills</h3>

    <?php if(mysqli_num_rows($result_skills) > 0) { ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Skill Name</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Last Updated</th>
            <th>Actions</th>
        </tr>
        <?php while($skill = mysqli_fetch_assoc($result_skills)) { ?>
        <tr>
            <td><?php echo $skill['skill_id']; ?></td>
            <td><?php echo htmlspecialchars($skill['skill_name']); ?></td>
            <td><?php echo $skill['skill_status']; ?></td>
            <td><?php echo htmlspecialchars($skill['remarks']); ?></td>
            <td><?php echo $skill['updated_at']; ?></td>
            <td>
                <a href="?case_id=<?php echo $case_id; ?>&delete=<?php echo $skill['skill_id']; ?>" onclick="return confirm('Delete this skill?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } else { ?>
        <p>No skills recorded yet.</p>
    <?php } ?>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt_case);
mysqli_stmt_close($stmt_fetch);
mysqli_close($conn);
?>
