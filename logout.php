<?php
session_start();

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proctored_quiz";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update is_logged_in flag based on the role
if (isset($_SESSION['role']) && isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'candidate') {
        $conn->query("UPDATE candidates SET is_logged_in = 0 WHERE id = " . $_SESSION['user_id']);
    } elseif ($_SESSION['role'] == 'instructor') {
        $conn->query("UPDATE instructor SET is_logged_in = 0 WHERE instructor_id = " . $_SESSION['user_id']);
    }
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
