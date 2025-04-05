<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.html");
    exit; // Stop further execution of the script
}

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proctored_quiz";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize a variable to store the alert message
$alert_message = '';
$alert_type = ''; // Track whether it's success or error for styling
$redirect_to_login = false; // Flag for redirect

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check if the passwords match
    if ($new_password == $confirm_password) {
        // No password hashing, store password as plain text
        
        // First, check if the email exists in the 'candidates' table
        $sql = "SELECT * FROM candidates WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Email found in candidates, update the password
            $sql = "UPDATE candidates SET pwd = '$new_password' WHERE email = '$email'";
            if ($conn->query($sql) === TRUE) {
                $alert_message = "Password has been reset successfully for candidate!";
                $alert_type = "success"; // success message
                $redirect_to_login = true; // Set flag to redirect
            } else {
                $alert_message = "Error updating password for candidate: " . $conn->error;
                $alert_type = "error"; // error message
            }
        } else {
            // If not found in candidates, check in the 'instructor' table
            $sql = "SELECT * FROM instructor WHERE email = '$email'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Email found in instructors, update the password
                $sql = "UPDATE instructor SET pwd = '$new_password' WHERE email = '$email'";
                if ($conn->query($sql) === TRUE) {
                    $alert_message = "Password has been reset successfully for instructor!";
                    $alert_type = "success"; // success message
                    $redirect_to_login = true; // Set flag to redirect
                } else {
                    $alert_message = "Error updating password for instructor: " . $conn->error;
                    $alert_type = "error"; // error message
                }
            } else {
                $alert_message = "No user found with that email!";
                $alert_type = "orange"; // change this to orange for user not found
            }
        }
    } else {
        // Passwords don't match
        $alert_message = "Passwords do not match!";
        $alert_type = "error"; // error message
    }
}

// Close the connection
$conn->close();

// Redirect to login page if password reset was successful
if ($redirect_to_login) {
    echo "<script>
            alert('$alert_message');
            window.location.href = 'login.php'; // Replace with your login page URL
          </script>";
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="./assets/css/login.css">
    <style>
        /* Center the heading and add bottom margin */
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Style the label for email and password: make it bold, bigger, and add bottom margin */
        .input-group label {
            font-size: 1.1em;
            font-weight: 450;
            display: block;
            margin-bottom: 8px;
        }

        /* Add some margin to the input field */
        .input-group {
            margin-bottom: 20px;
        }

        /* Optional styling for the container for better layout */
        .container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }

        /* Style the submit button */
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #fa574e;
            color: white;
            border-color: black;
            -radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .btn:hover {
            background-color: #ebeff2;
            color: black;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.5); 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 400px;
            border-radius: 10px;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .success {
            color: green;
            font-size: 1.2em;
        }

        .error {
            color: red;
            font-size: 1.2em;
        }

        .orange {
            color: orange; /* New class for orange alert */
            font-size: 1.2em;
        }

        .btn-close {
            padding: 10px 20px;
            background-color: #fa574e;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
    <script>
        // Function to open the modal
        function openModal(message, type) {
            var modal = document.getElementById("myModal");
            var messageElem = document.getElementById("modal-message");
            var messageType = document.getElementById("modal-type");

            // Set the message and styling
            messageElem.textContent = message;
            messageType.className = type; // Ensure the type includes the new 'orange' class if applicable

            modal.style.display = "block"; // Show modal
        }

        // Function to close the modal
        function closeModal() {
            var modal = document.getElementById("myModal");
            modal.style.display = "none"; // Hide modal
        }

        // On page load, show modal if there's a message
        window.onload = function() {
            var message = "<?php echo $alert_message; ?>";
            var type = "<?php echo $alert_type; ?>";
            if (message && type !== "success") {
                openModal(message, type);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="right-side">
            <h2>Reset Password</h2>
            <form action="forgot_password.php" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="input-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your new password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                </div>
                <button type="submit" class="btn">Reset Password</button>
            </form>
        </div>
    </div>

    <!-- Modal for displaying messages -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modal-message"></p>
            <p id="modal-type" class=""></p>
            <button class="btn-close" onclick="closeModal()">Close</button>
        </div>
    </div>
</body>
</html>
