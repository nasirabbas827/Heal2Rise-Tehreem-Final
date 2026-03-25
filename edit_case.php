<?php
session_start();
include "config.php";

// 1. Access Control
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "user") {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$case_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$message = "";

if (!$case_id) {
    header("location: dashboard.php");
    exit;
}

// 2. Fetch existing data (Security check: must belong to user and be Pending)
$fetch_sql = "SELECT category, description, case_status FROM cases WHERE case_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $fetch_sql);
mysqli_stmt_bind_param($stmt, "ii", $case_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$case_data = mysqli_fetch_assoc($result);

// Redirect if case doesn't exist or is already assigned/ongoing
if (!$case_data || $case_data['case_status'] !== 'Pending') {
    header("location: dashboard.php?error=not_editable");
    exit;
}

// 3. Handle Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_category = $_POST['category'];
    $new_description = mysqli_real_escape_string($conn, $_POST['description']);

    // Extra layer of security: Ensure we only update if it's still Pending
    $update_sql = "UPDATE cases SET category = ?, description = ? WHERE case_id = ? AND user_id = ? AND case_status = 'Pending'";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssii", $new_category, $new_description, $case_id, $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
        header("location: dashboard.php?msg=updated");
        exit;
    } else {
        $message = "Error updating record. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Case #<?php echo $case_id; ?> | Heal2Rise</title>
    <link rel="stylesheet" href="./CSS/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container">
    <div class="header-area">
        <h2>Edit Case #<?php echo $case_id; ?></h2>
        <p style="color: #666; font-size: 0.9rem;">Update your issue details below.</p>
    </div>

    <?php if($message) echo "<p style='color:red; background: #fff5f5; padding: 10px; border-radius: 4px;'>$message</p>"; ?>

    <form action="" method="POST">
        <div style="margin-bottom: 20px;">
            <label><b>Nature of Issue:</b></label>
            <select name="category" required>
                <option value="Depression" <?php echo ($case_data['category'] == 'Depression') ? 'selected' : ''; ?>>Depression</option>
                <option value="Family Issues" <?php echo ($case_data['category'] == 'Family Issues') ? 'selected' : ''; ?>>Family Issues</option>
                <option value="Marital Issues" <?php echo ($case_data['category'] == 'Marital Issues') ? 'selected' : ''; ?>>Marital Issues</option>
                <option value="Hopelessness" <?php echo ($case_data['category'] == 'Hopelessness') ? 'selected' : ''; ?>>Hopelessness</option>
                <option value="Other" <?php echo ($case_data['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label><b>Description:</b></label>
            <textarea name="description" required style="height: 180px;"><?php echo htmlspecialchars($case_data['description']); ?></textarea>
        </div>

        <div style="margin-top: 30px;">
            <button type="submit" class="btn-update">Save Changes</button>
            <a href="dashboard.php" class="cancel-link">Discard Changes</a>
        </div>
    </form>
</div>

</body>
</html>