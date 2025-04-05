<?php
session_start();

// Initialize error message
$error_message = "";

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

// Handle login request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Atomic Check and Login for Candidates
    $candidate_sql = "SELECT * FROM candidates WHERE email = '$email'";
    $candidate_result = $conn->query($candidate_sql);

    if ($candidate_result->num_rows > 0) {
        $row = $candidate_result->fetch_assoc();
        if ($password == $row['pwd']) {
            if ($row['is_logged_in'] == 1) {
                $_SESSION['error_message'] = "Candidate is already logged in from another session.";
                header("Location: login.php");
                exit;
            } else {
                // Update login status and initialize session
                $conn->query("UPDATE candidates SET is_logged_in = 1 WHERE email = '$email'");
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['role'] = 'candidate';

                header("Location: candidate_homepage.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Invalid password!";
            header("Location: login.php");
            exit;
        }
    }

    // Atomic Check and Login for Instructors
    $instructor_sql = "SELECT * FROM instructor WHERE email = '$email'";
    $instructor_result = $conn->query($instructor_sql);

    if ($instructor_result->num_rows > 0) {
        $row = $instructor_result->fetch_assoc();
        if ($password == $row['pwd']) {
            if ($row['is_logged_in'] == 1) {
                $_SESSION['error_message'] = "Instructor is already logged in from another session.";
                header("Location: login.php");
                exit;
            } else {
                // Update login status and initialize session
                $conn->query("UPDATE instructor SET is_logged_in = 1 WHERE email = '$email'");
                $_SESSION['user_id'] = $row['instructor_id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['role'] = 'instructor';

                header("Location: instructor_homepage.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Invalid password!";
            header("Location: login.php");
            exit;
        }
    }

    // If no user found
    $_SESSION['error_message'] = "No user found with this email!";
    header("Location: login.php");
    exit;
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/login.css">
    <style>
        /* Error box styling */
        .error-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffdddd; /* Light red background */
            border-left: 6px solid #f44336; /* Red border */
            color: #d8000c; /* Red text */
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 4px;
            font-size: 16px;
        }

        .error-box .close {
            color: #d8000c;
            cursor: pointer;
            font-size: 20px;
            margin-left: 16px;
        }

        .error-box .close:hover {
            color: black;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left-side">
            <img src="https://www.indlearn.com/assets/frontend/default-new/image/login-security.gif" alt="Sign up and start learning" class="image">
        </div>
        <div class="right-side">
            <h2>
                <p class="welcome-text">Welcome, Back! 👋</p>
            </h2>

            <!-- Display Error Message if Set -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-box" id="errorBox">
                    <?php echo $_SESSION['error_message']; ?>
                    <span class="close" onclick="closeErrorBox()">&times;</span>
                </div>
                <?php unset($_SESSION['error_message']); // Clear the error message ?>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="Your Email">Email</label>
                    <div class="icon-container">
                        <i class="fa-solid fa-user"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="icon-container">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your valid password" required>
                    </div>
                </div>
                <p class="forg_pwd"> <a href="forgot_password.php">Forgot password?</a></p>
                <button type="submit" class="btn">Log in</button>
                <p class="dont_acc">Don't have an account? <a href="signup.php">Signup</a></p>
            </form>
        </div>
    </div>

    <script>
        // Function to close the error box
        function closeErrorBox() {
            const errorBox = document.getElementById('errorBox');
            if (errorBox) {
                errorBox.style.display = 'none';
            }
        }
    </script>
</body>
</html>