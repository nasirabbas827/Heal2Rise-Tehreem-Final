<?php
include('config.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name   = trim($_POST["full_name"]);
    $email       = trim($_POST["email"]);
    $password    = trim($_POST["password"]);
    $phone       = trim($_POST["phone"]);
    $gender      = $_POST["gender"];
    $issue_type  = $_POST["issue_type"];
    $description = trim($_POST["description"]);

    // ==========================
    // Check if email exists
    // ==========================
    $sql_email_check = "SELECT user_id FROM users WHERE email = ?";
    $stmt_email = mysqli_prepare($conn, $sql_email_check);
    mysqli_stmt_bind_param($stmt_email, "s", $email);
    mysqli_stmt_execute($stmt_email);
    mysqli_stmt_store_result($stmt_email);
    $email_count = mysqli_stmt_num_rows($stmt_email);
    mysqli_stmt_close($stmt_email);

    // ==========================
    // Check if phone exists
    // ==========================
    $sql_phone_check = "SELECT user_id FROM users WHERE phone = ?";
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

        // Insert user
        $sql_insert = "INSERT INTO users 
            (full_name, email, password, phone, gender, issue_type, description, status, is_verified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 0)";

        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param(
            $stmt_insert,
            "sssssss",
            $full_name,
            $email,
            $hashed_password,
            $phone,
            $gender,
            $issue_type,
            $description
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
    <title>User Registration</title>
    <link rel="stylesheet" href="./CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>User Registration</h2>

    <?php if (!empty($message)) echo $message; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div>
            <label>Full Name</label>
            <input type="text" name="full_name" required>
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
            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div>
            <label>Issue Type</label>
            <select name="issue_type" required>
                <option value="">Select Issue</option>
                <option value="Depression">Depression</option>
                <option value="Family Issues">Family Issues</option>
                <option value="Marital Issues">Marital Issues</option>
                <option value="Hopelessness">Hopelessness</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div>
            <label>Describe Your Situation</label>
            <textarea name="description" rows="4" required></textarea>
        </div>

        <div>
            <input type="submit" value="Register">
        </div>

    </form>
</div>

</body>
</html>
