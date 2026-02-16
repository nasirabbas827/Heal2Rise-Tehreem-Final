<?php
session_start();
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "ngo") {
    header("location: index.php");
    exit;
}

include "config.php";

$ngoId = $_SESSION["id"];
$success = $error = "";

// Fetch NGO data
$stmt = $conn->prepare("SELECT ngo_name, email, phone, address, specialization FROM ngos WHERE ngo_id = ?");
$stmt->bind_param("i", $ngoId);
$stmt->execute();
$result = $stmt->get_result();
$ngo = $result->fetch_assoc();
$stmt->close();

if (!$ngo) {
    die("NGO not found.");
}

// Update profile if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ngo_name       = trim($_POST["ngo_name"]);
    $email          = trim($_POST["email"]);
    $phone          = trim($_POST["phone"]);
    $address        = trim($_POST["address"]);
    $specialization = $_POST["specialization"];

    if (!empty($ngo_name) && !empty($email) && !empty($phone) && !empty($address) && !empty($specialization)) {
        $stmt = $conn->prepare("UPDATE ngos SET ngo_name=?, email=?, phone=?, address=?, specialization=? WHERE ngo_id=?");
        $stmt->bind_param("sssssi", $ngo_name, $email, $phone, $address, $specialization, $ngoId);

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            // Refresh local data
            $ngo["ngo_name"]       = $ngo_name;
            $ngo["email"]          = $email;
            $ngo["phone"]          = $phone;
            $ngo["address"]        = $address;
            $ngo["specialization"] = $specialization;
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
<title>Update NGO Profile</title>
<link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<h2>Update NGO Profile</h2>

<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label>NGO Name:</label> 
    <input type="text" name="ngo_name" value="<?= htmlspecialchars($ngo['ngo_name']) ?>" required>  

    <label>Email:</label> 
    <input type="email" name="email" value="<?= htmlspecialchars($ngo['email']) ?>" required>  

    <label>Phone:</label> 
    <input type="text" name="phone" value="<?= htmlspecialchars($ngo['phone']) ?>" required>  

    <label>Address:</label> 
    <textarea name="address" rows="3" required><?= htmlspecialchars($ngo['address']) ?></textarea>  

    <label>Specialization:</label> 
    <select name="specialization" required>
        <option value="">Select Specialization</option>
        <option value="Depression" <?php if($ngo['specialization']=="Depression") echo "selected"; ?>>Depression</option>
        <option value="Family Issues" <?php if($ngo['specialization']=="Family Issues") echo "selected"; ?>>Family Issues</option>
        <option value="Marital Issues" <?php if($ngo['specialization']=="Marital Issues") echo "selected"; ?>>Marital Issues</option>
        <option value="General Counseling" <?php if($ngo['specialization']=="General Counseling") echo "selected"; ?>>General Counseling</option>
        <option value="Rehabilitation" <?php if($ngo['specialization']=="Rehabilitation") echo "selected"; ?>>Rehabilitation</option>
    </select>  

    <button type="submit">Update Profile</button>
</form>

</body>
</html>

<?php mysqli_close($conn); ?>
