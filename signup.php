<?php
session_start(); // Start the session

$servername = "localhost";
$username = "root";  // Default username for XAMPP is 'root'
$password = "";      // Default password for XAMPP is empty
$dbname = "proctored_quiz";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm-password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']); // Added gender field
    
    
    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match!";
        header("Location: signup.html"); // Redirect back to the signup page
        exit;
    }
    
    // Store the password as plain text (for demonstration purposes)
    $plain_password = $password;

    // Check role and set table accordingly
    if ($role == 'candidate') {
        // Prepare SQL query to insert data into 'candidates' table
        $sql = "INSERT INTO candidates (name, email, pwd,gender) VALUES ('$name', '$email', '$plain_password','$gender')";
    } elseif ($role == 'instructor') {
        // Prepare SQL query to insert data into 'instructors' table
        $sql = "INSERT INTO instructor (name, email, pwd,gender) VALUES ('$name', '$email', '$plain_password','$gender')";
    } else {
        $_SESSION['error_message'] = "Invalid role selected!";
        header("Location: signup.html"); // Redirect back to the signup page
        exit;
    }

    // Execute the query and check for errors
    if ($conn->query($sql) === TRUE) {
        // Get the last inserted ID
        $last_id = $conn->insert_id;

        // Set session variables for the logged-in user
        if ($role == 'candidate') {
            $_SESSION['user_id'] = $last_id; // Store the candidate ID
        } elseif ($role == 'instructor') {
            $_SESSION['user_id'] = $last_id; // Store the instructor ID
        }
        $_SESSION['name'] = $name;      // Store the user's name
        $_SESSION['email'] = $email;    // Store the user's email
        $_SESSION['role'] = $role; 
        $_SESSION['gender'] = $gender;      // Store the user's role

        // Redirect based on role
        if ($role == 'candidate') {
            header("Location: candidate_homepage.php");
        } elseif ($role == 'instructor') {
            header("Location: instructor_homepage.php");
        }
        exit;
    } else {
        
        header("Location: signup.php"); // Redirect back to the signup page
        exit;
    }
    
    // Close the connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/signup.css">
    <style>
        /* Increase the width of the select field */
        select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }
        .error-box {
    display: none; /* Hidden by default */
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #ffdddd;
    color: #d8000c;
    border: 1px solid #d8000c;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.close-btn {
    background: none;
    border: none;
    color: #d8000c;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    margin-left: 15px;
    line-height: 1;
}

.close-btn:hover {
    color: #a70000;
}

       
    </style>
</head>

<body>
    <div class="container">
        <div class="left-side">
            <img src="//cdna.artstation.com/p/assets/images/images/027/682/158/original/liz-gross-signup.gif?1592246526" alt="Sign up and start learning" class="image">
        </div>
        <div class="right-side">
            <div class="header-container">
               
            </div>
            <p id="mainh">Explore, learn, and grow with us. Enjoy a seamless and enriching educational journey. Let's begin!</p>
            <div id="error-box" class="error-box">
    <span id="error-message-text">
        <?php 
        if (isset($_SESSION['error_message'])) {
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']); // Clear the error message
        } 
        ?>
    </span>
    <button id="close-error-box" class="close-btn">X</button>
</div>
            <form action="signup.php" method="POST">
                <div class="input-group">
                    <label for="name">Name</label>
                    <div class="icon-container">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="icon-container">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="icon-container">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <div class="icon-container">
                        <i class="fa-solid fa-key"></i>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="gender">Gender</label>
                    <div class="icon-container">
                        <select id="gender" name="gender" required>
                            <option value="" disabled selected>Select your gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="input-group">
                    <label for="role">Select Role</label>
                    <div class="icon-container">
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Select your role</option>
                            <option value="candidate">Candidate</option>
                            <option value="instructor">Instructor</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">Sign Up</button>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </div>

    <!-- Hidden input field to store the error message -->
    <input type="hidden" id="error-message" value="<?php if(isset($_SESSION['error_message'])) { echo $_SESSION['error_message']; unset($_SESSION['error_message']); } ?>">

    <!-- Popup for error message -->
    <div id="error-popup" class="popup"></div>

    
</body>

    <script>
    // Check if an error message exists
    document.addEventListener("DOMContentLoaded", () => {
        const errorBox = document.getElementById("error-box");
        const closeErrorBox = document.getElementById("close-error-box");
        const errorMessage = document.getElementById("error-message-text").innerText;

        // Show the error box if there's an error message
        if (errorMessage.trim() !== "") {
            errorBox.style.display = "block";
        }

        // Close the error box when the close button is clicked
        closeErrorBox.addEventListener("click", () => {
            errorBox.style.display = "none";
        });
    });
</script>

</html>

