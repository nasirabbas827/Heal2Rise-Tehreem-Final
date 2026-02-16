<nav>
        <ul>
            <li><a href="dashboard.php">Heal2Rise</a></li>
            <?php
            if (isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
                echo '<li><a href="dashboard.php">Home</a></li>';
                echo '<li><a href="update_profile.php">Profile</a></li>';
                echo '<li><a href="logout.php">Logout</a></li>';
            } else {
                echo '<li><a href="ngo_register.php">Ngo Register</a></li>';
                echo '<li><a href="register.php">User Register</a></li>';
                echo '<li><a href="login.php">Login</a></li>';
                echo '<li><a href="adminlogin.php">Admin Login</a></li>';
            }
            ?>
        </ul>
    </nav>