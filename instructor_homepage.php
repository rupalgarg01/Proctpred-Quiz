<?php
// Start the session at the beginning of the page
session_start();

// Check if the instructor is logged in by verifying if the session variable for instructor ID is set
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Homepage</title>
    <link rel="stylesheet" href="./assets/css/instructor_homepage.css">
    <style>
        .status-message {
            margin: 20px auto;
            padding: 10px 20px;
            text-align: center;
            font-size: 18px;
            border-radius: 5px;
            width: 80%;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <h1>Quiz<span>Eye</span></h1>
            </div>
            <nav>
                <ul id="nav-links">
                    <li><a href="#hero">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="logout.php" class="btn login-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    

    <section class="hero">
        <div class="hero-content">
            <h2>Welcome to the Instructor Hub!</h2>
            <p>Your place to create, manage, and track quizzes effortlessly.</p>
        </div>
        <div class="hero-image">
            <img src="https://www.pngarts.com/files/7/Virtual-Meeting-Transparent-Images.png" alt="Quiz Hero">
        </div>
    </section>
    <?php
    // Check for a status message
    if (isset($_GET['status'])) {
        $status = htmlspecialchars($_GET['status']); // Secure against XSS
        echo "<div id='statusMessage' class='status-message status-success'>{$status}</div>";
    }
    ?>

    <section id="features" class="features-section">
        <a href="quizcr.html" style="text-decoration: none; ">
            <div class="feature" style="cursor: pointer; ">
                <img src="https://ditchthattextbook.com/wp-content/uploads/2022/03/image5.png" 
                alt="Create Icon" style="width: 120px; height: 100px;">
                <h3>Create Quizzes</h3>
                <p>Design interactive quizzes with ease.</p>
            </div>
        </a>
        <a href="quiz_magmt.php" style="text-decoration: none;">
            <div class="feature" style="cursor: pointer;">
                <img src="https://static.vecteezy.com/system/resources/thumbnails/014/432/663/small_2x/business-work-success-idea-achievement-analysis-concept-businessman-clerk-manager-holding-technology-gear-in-hands-illustration-png.png"
                    alt="Manage Icon" style="width: 120px; height: 100px;">
                <h3>Manage Participants</h3>
                <p>Track and manage participants effortlessly.</p>
            </div>
        </a>
        
        <div class="feature">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSmJeWDxOyHLG9EE3Ymtf8SESsCfv9QRmNfKA&s"
                alt="Reports Icon">
            <h3>View Reports</h3>
            <p>Analyze performance with detailed reports.</p>
        </div>
        <div class="feature">
            <img src="https://cdn-icons-png.flaticon.com/512/7400/7400834.png" alt="Leaderboard Icon">
            <h3>Leaderboard</h3>
            <p>Showcase top performers and encourage competition.</p>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <h2>Contact Us</h2>
        <p style="font-size:20px; color:black;">If you encounter any issues or have questions, feel free to reach out to us directly via email:</p>
        <p style="font-size:24px;"><a href="mailto:anshishubhipr@gmail.com" class="contact-email">anshishubhipr@gmail.com</a></p>
    </section>

    <footer>
        <p>&copy; 2024 QuizEye. All rights reserved.</p>
    </footer>

    <script>
        // Remove the status message after 5 seconds
        setTimeout(() => {
            const statusMessage = document.getElementById('statusMessage');
            if (statusMessage) {
                statusMessage.style.transition = 'opacity 0.5s ease';
                statusMessage.style.opacity = '0';
                setTimeout(() => {
                    statusMessage.remove();
                }, 500);
            }
        }, 9000);
    </script>
</body>

</html>
