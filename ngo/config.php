<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "", "H2rTdatabase");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>