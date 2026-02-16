<?php
include('config.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $ngo_name       = trim($_POST["ngo_name"]);
    $email          = trim($_POST["email"]);
    $password       = trim($_POST["password"]);
    $phone          = trim($_POST["phone"]);
    $address        = trim($_POST["address"]);
    $specialization = $_POST["specialization"];

    // ==========================
    // Check if email already exists
    // ==========================
    $sql_email_check = "SELECT ngo_id FROM ngos WHERE email = ?";
    $stmt_email = mysqli_prepare($conn, $sql_email_check);
    mysqli_stmt_bind_param($stmt_email, "s", $email);
    mysqli_stmt_execute($stmt_email);
    mysqli_stmt_store_result($stmt_email);
    $email_count = mysqli_stmt_num_rows($stmt_email);
    mysqli_stmt_close($stmt_email);

    // ==========================
    // Check if phone already exists
    // ==========================
    $sql_phone_check = "SELECT ngo_id FROM ngos WHERE phone = ?";
    $stmt_phone = mysqli_prepare($conn, $sql_phone_check);
    mysqli_stmt_bind_param($stmt_phone, "s", $phone);
    mysqli_stmt_execute($stmt_phone);
    mysqli_stmt_store_result($stmt_phone);
    $phone_count = mysqli_stmt_num_rows($stmt_phone);
    mysqli_stmt_close($stmt_phone);

    if ($email_count > 0) {
        $message = "<div style='color:red;'>Email already exists.</div>";
    } elseif ($phone_count > 0) {
        $message = "<div style='color:red;'>Phone number already exists.</div>";
    } else {

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert NGO
        $sql_insert = "INSERT INTO ngos 
            (ngo_name, email, password, phone, address, specialization, is_verified) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";

        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param(
            $stmt_insert,
            "ssssss",
            $ngo_name,
            $email,
            $hashed_password,
            $phone,
            $address,
            $specialization
        );

        if (mysqli_stmt_execute($stmt_insert)) {
            $message = "<div style='color:green;'>Registration successful. Awaiting admin verification.</div>";
        } else {
            $message = "<div style='color:red;'>Registration failed. Please try again.</div>";
        }

        mysqli_stmt_close($stmt_insert);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>NGO Registration</title>
    <link rel="stylesheet" href="./CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>NGO Registration</h2>

    <?php if (!empty($message)) echo $message; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div>
            <label>NGO Name</label>
            <input type="text" name="ngo_name" required>
        </div>

        <div>
            <label>Phone</label>
            <input type="text" name="phone" required>
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Address</label>
            <textarea name="address" rows="3" required></textarea>
        </div>

        <div>
            <label>Specialization</label>
            <select name="specialization" required>
                <option value="">Select Specialization</option>
                <option value="Depression">Depression</option>
                <option value="Family Issues">Family Issues</option>
                <option value="Marital Issues">Marital Issues</option>
                <option value="General Counseling">General Counseling</option>
                <option value="Rehabilitation">Rehabilitation</option>
            </select>
        </div>

        <div>
            <input type="submit" value="Register NGO">
        </div>

    </form>
</div>

</body>
</html>
