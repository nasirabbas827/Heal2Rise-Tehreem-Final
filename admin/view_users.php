<?php
session_start();
include "config.php";

// ===============================
// Check Admin Login
// ===============================
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

$successMsg = "";
$errorMsg = "";

// ===============================
// Handle Status Update
// ===============================
if (isset($_POST["update_status"])) {

    $user_id = intval($_POST["user_id"]);
    $status = $_POST["status"];
    $is_verified = intval($_POST["is_verified"]);

    $stmt = mysqli_prepare($conn, 
        "UPDATE users SET status = ?, is_verified = ? WHERE user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sii", $status, $is_verified, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $successMsg = "User updated successfully.";
    } else {
        $errorMsg = "Failed to update user.";
    }

    mysqli_stmt_close($stmt);
}

// ===============================
// Search Logic
// ===============================
$searchQuery = "";
if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
    $search = mysqli_real_escape_string($conn, trim($_GET["search"]));
    $searchQuery = "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%'";
}

// ===============================
// Fetch Users
// ===============================
$users = mysqli_query($conn, 
    "SELECT * FROM users $searchQuery ORDER BY user_id DESC"
);

if (mysqli_num_rows($users) === 0) {
    $errorMsg = "No users found.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - View Users</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>All Registered Users</h2>

    <?php if ($successMsg) echo "<div style='color:green;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='color:red;'>$errorMsg</div>"; ?>

    <form method="get">
        <input type="text" name="search" placeholder="Search by Name or Email"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit">Search</button>
    </form>

    <?php if (mysqli_num_rows($users) > 0) { ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Issue Type</th>
                <th>Status</th>
                <th>Verified</th>
                <th>Update</th>
            </tr>

            <?php while ($user = mysqli_fetch_assoc($users)) { ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                <td><?php echo $user['gender']; ?></td>
                <td><?php echo $user['issue_type']; ?></td>

                <form method="post">
                    <td>
                        <select name="status">
                            <option value="Pending" <?php if($user['status']=="Pending") echo "selected"; ?>>Pending</option>
                            <option value="Assigned" <?php if($user['status']=="Assigned") echo "selected"; ?>>Assigned</option>
                            <option value="Under Treatment" <?php if($user['status']=="Under Treatment") echo "selected"; ?>>Under Treatment</option>
                            <option value="Recovered" <?php if($user['status']=="Recovered") echo "selected"; ?>>Recovered</option>
                            <option value="Closed" <?php if($user['status']=="Closed") echo "selected"; ?>>Closed</option>
                        </select>
                    </td>

                    <td>
                        <select name="is_verified">
                            <option value="0" <?php if($user['is_verified']==0) echo "selected"; ?>>No</option>
                            <option value="1" <?php if($user['is_verified']==1) echo "selected"; ?>>Yes</option>
                        </select>
                    </td>

                    <td>
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <button type="submit" name="update_status">Update</button>
                    </td>
                </form>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>

</body>
</html>
