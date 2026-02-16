<?php
session_start();
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "user") {
    header("location: index.php");
    exit;
}

include "config.php";

$userId = $_SESSION["id"];
$success = $error = "";

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, phone, gender, issue_type, description FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// Update profile if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name   = trim($_POST["full_name"]);
    $email       = trim($_POST["email"]);
    $phone       = trim($_POST["phone"]);
    $gender      = $_POST["gender"];
    $issue_type  = $_POST["issue_type"];
    $description = trim($_POST["description"]);

    if (!empty($full_name) && !empty($email) && !empty($phone) && !empty($gender) && !empty($issue_type)) {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, gender=?, issue_type=?, description=? WHERE user_id=?");
        $stmt->bind_param("ssssssi", $full_name, $email, $phone, $gender, $issue_type, $description, $userId);

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh local data
            $user["full_name"]   = $full_name;
            $user["email"]       = $email;
            $user["phone"]       = $phone;
            $user["gender"]      = $gender;
            $user["issue_type"]  = $issue_type;
            $user["description"] = $description;
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "All fields except description are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="./CSS/style.css">
<title>Update Profile</title>
</head>
<body>

<?php include('navbar.php'); ?>

<h2>Update Profile</h2>

<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label>Full Name:</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>  

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>  

    <label>Phone:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>  

    <label>Gender:</label>
    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
        <option value="Other" <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
    </select>

    <label>Issue Type:</label>
    <select name="issue_type" required>
        <option value="">Select Issue Type</option>
        <option value="Depression" <?= $user['issue_type'] == 'Depression' ? 'selected' : '' ?>>Depression</option>
        <option value="Family Issues" <?= $user['issue_type'] == 'Family Issues' ? 'selected' : '' ?>>Family Issues</option>
        <option value="Marital Issues" <?= $user['issue_type'] == 'Marital Issues' ? 'selected' : '' ?>>Marital Issues</option>
        <option value="Hopelessness" <?= $user['issue_type'] == 'Hopelessness' ? 'selected' : '' ?>>Hopelessness</option>
        <option value="Other" <?= $user['issue_type'] == 'Other' ? 'selected' : '' ?>>Other</option>
    </select>

    <label>Description:</label>
    <textarea name="description"><?= htmlspecialchars($user['description']) ?></textarea>

    <button type="submit">Update Profile</button>
</form>

</body>
</html>

<?php mysqli_close($conn); ?>
