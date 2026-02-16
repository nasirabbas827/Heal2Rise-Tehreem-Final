<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "team_member") {
    header("location: index.php");
    exit;
}

include "config.php";

$team_id = $_SESSION["id"];
$success = $error = "";

// ===============================
// Fetch team member data
// ===============================
$stmt = $conn->prepare("SELECT full_name, email, phone, role FROM team_members WHERE team_id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    die("Team member not found.");
}

// ===============================
// Update profile if form submitted
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email     = trim($_POST["email"]);
    $phone     = trim($_POST["phone"]);
    $role      = $_POST["role"];

    if (!empty($full_name) && !empty($email) && !empty($phone) && !empty($role)) {
        $stmt = $conn->prepare("UPDATE team_members SET full_name=?, email=?, phone=?, role=? WHERE team_id=?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $role, $team_id);

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh local data
            $member["full_name"] = $full_name;
            $member["email"]     = $email;
            $member["phone"]     = $phone;
            $member["role"]      = $role;
            $_SESSION["member_role"] = $role;
        } else {
            $error = "Error updating profile: " . $conn->error;
        }

        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/style.css">
    <title>Team Member Profile</title>
</head>
<body>

<?php include('navbar.php'); ?>

<h2>Update Profile</h2>

<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label>Full Name:</label> 
    <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required>  

    <label>Email:</label> 
    <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required>  

    <label>Phone:</label> 
    <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>" required>  

    <label>Role:</label> 
    <select name="role" required>
        <option value="Counselor" <?php if($member['role']=="Counselor") echo "selected"; ?>>Counselor</option>
        <option value="Psychiatrist" <?php if($member['role']=="Psychiatrist") echo "selected"; ?>>Psychiatrist</option>
        <option value="Social Worker" <?php if($member['role']=="Social Worker") echo "selected"; ?>>Social Worker</option>
        <option value="Monitor" <?php if($member['role']=="Monitor") echo "selected"; ?>>Monitor</option>
    </select>  

    <button type="submit">Update Profile</button>
</form>

</body>
</html>

<?php mysqli_close($conn); ?>
