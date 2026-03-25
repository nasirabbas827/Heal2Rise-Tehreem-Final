<?php
session_start();
include "config.php";

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "team_member") {
    header("location: index.php");
    exit;
}

$message = "";

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM counselors WHERE counselor_id = $del_id");
    header("Location: manage_counselors.php?msg=deleted");
}

// Handle Add
if (isset($_POST['add_counselor'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $spec = $_POST['specialization'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure Hashing

    $sql = "INSERT INTO counselors (name, email, password, specialization) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $pass, $spec);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Counselor added successfully!";
    } else {
        $message = "Error: Email might already exist.";
    }
}

$counselors = mysqli_query($conn, "SELECT * FROM counselors ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Counselors</title>
    <link rel="stylesheet" href="../CSS/style.css">
    
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="container">
        <h2>Counselor Management</h2>

        <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
            <h3>Add New Counselor</h3>
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <select name="specialization" required>
                    <option value="Depression">Depression</option>
                    <option value="Family Issues">Family Issues</option>
                    <option value="Marital Issues">Marital Issues</option>
                    <option value="Hopelessness">Hopelessness</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <button type="submit" name="add_counselor" class="btn-add">Register Counselor</button>
        </form>

        <?php if($message) echo "<p style='color:green;'>$message</p>"; ?>

        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>Action</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($counselors)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo $row['specialization']; ?></td>
                <td><a href="?delete_id=<?php echo $row['counselor_id']; ?>" class="btn-del" onclick="return confirm('Are you sure?')">Delete</a></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>