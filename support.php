<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Assuming you have the user_id and role stored in the session
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // Assuming 'role' is stored in session
$home_url = ($user_role === 'candidate') ? 'candidate_homepage.php' : 'instructor_homepage.php';

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

// Prepare SQL based on user role
if ($user_role === 'candidate') {
    // If the user is a candidate, fetch the username from the candidates table
    $sql = "SELECT name FROM candidates WHERE id = ?";
} elseif ($user_role === 'instructor') {
    // If the user is an instructor, fetch the username from the instructor table
    $sql = "SELECT name FROM instructor WHERE instructor_id = ?";
} else {
    // Handle other roles if necessary
    die("Invalid role");
}

// Prepare statement
$stmt = $conn->prepare($sql);

// Check if prepare fails
if (!$stmt) {
    die("SQL prepare failed: " . $conn->error); // Display error if prepare fails
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support</title>
    
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/support.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <div class="container-fluid">
            <a class="navbar-brand nav-logo" href="#">
                Quiz<span>Eye</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <!-- Redirect to home page based on role -->
                        <a class="nav-link home" href="<?php echo $home_url; ?>">Home</a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user user-icon"></i>
                            <span class="user-name"><?php echo htmlspecialchars($username); ?></span>

                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Secondary Navigation Bar -->
    <nav class="secondary-nav">
        <a href="#faqs">FAQs</a>
        <a href="#guides">How-to Guides</a>
        <a href="#troubleshooting">Troubleshooting</a>
        <a href="#contact">Contact Us</a>
    </nav>

    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="text-and-search">
                <p>Help & Support</p>
                <input type="text" id="search-bar" placeholder="Search for help..." onkeyup="filterFAQs()">
            </div>
            <img src="https://ouch-cdn2.icons8.com/lF2yXl9ks5Rq40ODXZOc571mfoWR9F977Unpt8vDcC4/rs:fit:456:456/czM6Ly9pY29uczgu/b3VjaC1wcm9kLmFz/c2V0cy9zdmcvNjAz/LzEzYzYyYzdlLTQw/N2QtNGFiZC1iYmI3/LTVkZjIyYTE3OWI5/Mi5zdmc.png" alt="Help and Support" class="header-image">
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <main>
            <section id="faqs">
                <h2>Frequently Asked Questions</h2>
                <div class="faq">
                    <h3>How do I reset my password?</h3>
                    <p>Go to the 'Account Settings' page, click 'Forgot Password,' and follow the prompts.</p>
                </div>
                <div class="faq">
                    <h3>How do I fix microphone issues?</h3>
                    <p>Ensure your microphone is plugged in and working. You can test it under the system settings of your device.</p>
                </div>
            </section>

            <section id="guides">
                <h2>How-to Guides</h2>
                <div class="guide">
                    <h3>Step-by-Step: Transcribing Audio</h3>
                    <p>1. Upload your audio file.<br>2. Click the 'Start Transcription' button.<br>3. Wait for the system to process the audio.</p>
                </div>
                <div class="guide">
                    <h3>Setting up your account</h3>
                    <p>1. Go to the 'Sign Up' page.<br>2. Enter your details.<br>3. Verify your email and log in.</p>
                </div>
            </section>

            <section id="troubleshooting">
                <h2>Troubleshooting</h2>
                <div class="troubleshooting">
                    <h3>Can't hear audio during transcription</h3>
                    <p>Check your speaker settings and make sure the audio file format is supported by the system.</p>
                </div>
                <div class="troubleshooting">
                    <h3>Audio file is taking too long to upload</h3>
                    <p>Ensure your internet connection is stable and try uploading a smaller file if the problem persists.</p>
                </div>
            </section>

            <section id="contact">
                <h2>Contact Us</h2>
                <p>If you need further assistance, feel free to reach out to us via email at <a href="mailto:anshishubhipr@gmail.com">anshishubhipr@gmail.com</a>.</p>
            </section>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 QuizEye. All rights reserved.</p>
    </footer>

    <!-- Add Bootstrap JS and its dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
         function filterFAQs() {
            let input = document.getElementById("search-bar").value.toLowerCase();
            let faqs = document.querySelectorAll(".faq");
            faqs.forEach(function(faq) {
                let title = faq.querySelector("h3").textContent.toLowerCase();
                if (title.includes(input)) {
                    faq.style.display = "block";
                } else {
                    faq.style.display = "none";
                }
            });
        }
    </script>

</body>
</html>
