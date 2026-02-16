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
// Handle NGO Update
// ===============================
if (isset($_POST["update_ngo"])) {

    $ngo_id = intval($_POST["ngo_id"]);
    $specialization = $_POST["specialization"];
    $is_verified = intval($_POST["is_verified"]);

    $stmt = mysqli_prepare($conn, 
        "UPDATE ngos SET specialization = ?, is_verified = ? WHERE ngo_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sii", $specialization, $is_verified, $ngo_id);

    if (mysqli_stmt_execute($stmt)) {
        $successMsg = "NGO updated successfully.";
    } else {
        $errorMsg = "Failed to update NGO.";
    }

    mysqli_stmt_close($stmt);
}

// ===============================
// Search Logic
// ===============================
$searchQuery = "";
if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
    $search = mysqli_real_escape_string($conn, trim($_GET["search"]));
    $searchQuery = "WHERE ngo_name LIKE '%$search%' OR email LIKE '%$search%'";
}

// ===============================
// Fetch NGOs
// ===============================
$ngos = mysqli_query($conn, 
    "SELECT * FROM ngos $searchQuery ORDER BY ngo_id DESC"
);

if (mysqli_num_rows($ngos) === 0) {
    $errorMsg = "No NGOs found.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - View NGOs</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>All Registered NGOs</h2>

    <?php if ($successMsg) echo "<div style='color:green;'>$successMsg</div>"; ?>
    <?php if ($errorMsg) echo "<div style='color:red;'>$errorMsg</div>"; ?>

    <form method="get">
        <input type="text" name="search" placeholder="Search by NGO Name or Email"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit">Search</button>
    </form>

    <?php if (mysqli_num_rows($ngos) > 0) { ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>NGO Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Specialization</th>
                <th>Verified</th>
                <th>Update</th>
            </tr>

            <?php while ($ngo = mysqli_fetch_assoc($ngos)) { ?>
            <tr>
                <td><?php echo $ngo['ngo_id']; ?></td>
                <td><?php echo htmlspecialchars($ngo['ngo_name']); ?></td>
                <td><?php echo htmlspecialchars($ngo['email']); ?></td>
                <td><?php echo htmlspecialchars($ngo['phone']); ?></td>
                <td><?php echo htmlspecialchars($ngo['address']); ?></td>

                <form method="post">
                    <td>
                        <select name="specialization">
                            <option value="Depression" <?php if($ngo['specialization']=="Depression") echo "selected"; ?>>Depression</option>
                            <option value="Family Issues" <?php if($ngo['specialization']=="Family Issues") echo "selected"; ?>>Family Issues</option>
                            <option value="Marital Issues" <?php if($ngo['specialization']=="Marital Issues") echo "selected"; ?>>Marital Issues</option>
                            <option value="General Counseling" <?php if($ngo['specialization']=="General Counseling") echo "selected"; ?>>General Counseling</option>
                            <option value="Rehabilitation" <?php if($ngo['specialization']=="Rehabilitation") echo "selected"; ?>>Rehabilitation</option>
                        </select>
                    </td>

                    <td>
                        <select name="is_verified">
                            <option value="0" <?php if($ngo['is_verified']==0) echo "selected"; ?>>No</option>
                            <option value="1" <?php if($ngo['is_verified']==1) echo "selected"; ?>>Yes</option>
                        </select>
                    </td>

                    <td>
                        <input type="hidden" name="ngo_id" value="<?php echo $ngo['ngo_id']; ?>">
                        <button type="submit" name="update_ngo">Update</button>
                    </td>
                </form>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>
</div>

</body>
</html>
