<?php
include('config.php');
session_start();

$email = $password = $role = "";
$email_err = $password_err = $role_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"];

    if (empty($email)) $email_err = "Please enter your email.";
    if (empty($password)) $password_err = "Please enter your password.";
    if (empty($role)) $role_err = "Please select your role.";

    if (empty($email_err) && empty($password_err) && empty($role_err)) {

        // ===============================
        // If USER selected
        // ===============================
        if ($role == "user") {

            $sql = "SELECT user_id, email, password, is_verified 
                    FROM users WHERE email = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {

                mysqli_stmt_bind_result($stmt, $id, $email_db, $hashed_password, $is_verified);
                mysqli_stmt_fetch($stmt);

                if (password_verify($password, $hashed_password)) {
                    if ($is_verified == 0) {
                        $email_err = "Account not verified by admin.";
                    } else {
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email_db;
                        $_SESSION["role"] = "user";

                        header("Location: dashboard.php");
                        exit();
                    }
                } else $password_err = "Incorrect password.";
            } else $email_err = "No user account found.";

            mysqli_stmt_close($stmt);
        }

        // ===============================
        // If NGO selected
        // ===============================
        elseif ($role == "ngo") {

            $sql = "SELECT ngo_id, email, password, is_verified 
                    FROM ngos WHERE email = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {

                mysqli_stmt_bind_result($stmt, $id, $email_db, $hashed_password, $is_verified);
                mysqli_stmt_fetch($stmt);

                if (password_verify($password, $hashed_password)) {
                    if ($is_verified == 0) {
                        $email_err = "NGO account not verified by admin.";
                    } else {
                        $_SESSION["id"] = $id;
                        $_SESSION["email"] = $email_db;
                        $_SESSION["role"] = "ngo";

                        header("Location: ngo/ngo_dashboard.php");
                        exit();
                    }
                } else $password_err = "Incorrect password.";
            } else $email_err = "No NGO account found.";

            mysqli_stmt_close($stmt);
        }

        // ===============================
        // If TEAM MEMBER selected
        // ===============================
        elseif ($role == "team_member") {

            $sql = "SELECT team_id, email, password, role 
                    FROM team_members WHERE email = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {

                mysqli_stmt_bind_result($stmt, $id, $email_db, $hashed_password, $member_role);
                mysqli_stmt_fetch($stmt);

                if (password_verify($password, $hashed_password)) {
                    $_SESSION["id"] = $id;
                    $_SESSION["email"] = $email_db;
                    $_SESSION["role"] = "team_member";
                    $_SESSION["member_role"] = $member_role;

                    header("Location: team/team_dashboard.php");
                    exit();
                } else $password_err = "Incorrect password.";
            } else $email_err = "No team member account found.";

            mysqli_stmt_close($stmt);
        }

        // ===============================
        // If COUNSELOR selected
        // ===============================
        elseif ($role == "counselor") {

            $sql = "SELECT counselor_id, email, password, name 
                    FROM counselors WHERE email = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {

                mysqli_stmt_bind_result($stmt, $id, $email_db, $hashed_password, $counselor_name);
                mysqli_stmt_fetch($stmt);

                if (password_verify($password, $hashed_password)) {
                    $_SESSION["id"] = $id;
                    $_SESSION["email"] = $email_db;
                    $_SESSION["name"] = $counselor_name;
                    $_SESSION["role"] = "counselor";

                    // Redirect to counselor dashboard
                    header("Location: counselor/counselor_dashboard.php");
                    exit();
                } else $password_err = "Incorrect password.";
            } else $email_err = "No counselor account found.";

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="./CSS/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div>
    <h2>Login</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div>
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <div style="color:red;"><?php echo $email_err; ?></div>
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" required>
            <div style="color:red;"><?php echo $password_err; ?></div>
        </div>

        <div>
            <label>Login As</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="user" <?php if($role=="user") echo "selected"; ?>>User</option>
                <option value="ngo" <?php if($role=="ngo") echo "selected"; ?>>NGO</option>
                <option value="team_member" <?php if($role=="team_member") echo "selected"; ?>>Team Member</option>
                <option value="counselor" <?php if($role=="counselor") echo "selected"; ?>>Counselor</option>
            </select>
            <div style="color:red;"><?php echo $role_err; ?></div>
        </div>

        <div>
            <input type="submit" value="Login">
        </div>

    </form>
</div>

</body>
</html>