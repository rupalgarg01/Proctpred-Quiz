<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proctored_quiz";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM candidates WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Error: User not found.";
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizEye Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.css">
    <link rel="stylesheet" href="assets/css/candidate_dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h1>Quiz<span>Eye</span></h1>
        </div>
        <ul class="sidebar-menu">
            <li data-target="profile-section-main"><i class="fas fa-user"></i>Profile</li>
            <li ><a href="candidate_homepage.php" style="color: white; text-decoration: none;"><i class="fas fa-adjust icon"></i>Home</a></li>
            <li data-target="quizzes-section"><i class="fas fa-clipboard-list"></i>My Quizzes</li>
            <li data-target="statistics-section"><i class="fas fa-chart-bar"></i>Statistics</li>
            <li data-target="faq-section"><i class="fas fa-question-circle"></i>FAQs</li>
            <li ><a href="support.php" style="color: white; text-decoration: none;"><i class="fas fa-envelope"></i>Contact</a></li>
            <li><a href="logout.php" style="color: white; text-decoration: none;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <div id="head" class="main-heading welcome-message">
            <h4>Welcome, <?php echo htmlspecialchars($user['name']); ?>😊</h4>
        </div>
        <i id="bell" class="fas fa-bell icon"></i>
        <i id="adjust" class="fas fa-adjust icon"></i>
        <div class="dropdown">
            <!-- User Icon -->
        <?php
        // Determine the user icon based on gender
        $userIcon = $user['gender'] === 'Female'
            ? "https://cdn1.iconfinder.com/data/icons/avatars-1-5/136/87-512.png"
            : "https://static.vecteezy.com/system/resources/previews/019/896/008/original/male-user-avatar-icon-in-flat-design-style-person-signs-illustration-png.png";
        ?>
        <img id="user-icon" src="<?php echo htmlspecialchars($userIcon); ?>" alt="User Icon" class="icon" style="width: 40px; height: 40px; border-radius: 50%; cursor: pointer;">
        </div>
    </div>

    <!-- Grid Container -->
    <div class="grid-container">
        <!-- Dashboard Content -->
        <div class="dashboard-content">

            <!-- Summary Blocks -->
            <div class="dashboard-summary">
                <div class="summary-block quizzes">
                    <h3>My Quizzes</h3>
                    <p>Total: 25</p>
                    <p>Upcoming: 3</p>
                </div>
                <div class="summary-block performance">
                    <h3>Performance</h3>
                    <p>Average Score: 85%</p>
                    <p>Best Score: 95%</p>
                </div>
                <div class="summary-block progress">
                    <h3>Progress</h3>
                    <p>Quizzes Completed: 20</p>
                    <p>Badges: 5</p>
                </div>
            </div>
    
            <!-- Chart Section -->
            <div class="chart-section">
                <h3>Your Performance Overview</h3>
                <canvas id="performanceChart"></canvas>
            </div>
    
            <!-- Visual Achievements Section -->
            <div class="visual-section">
                <div class="visual-block">
                    <h3>Current Streak</h3>
                    <p>5 Days</p>
                </div>
                <div class="visual-block">
                    <h3>Badges Earned</h3>
                    <p><i class="fas fa-medal"></i> Champion</p>
                    <p><i class="fas fa-star"></i> Fast Learner</p>
                </div>
            </div>
        </div>

        <!-- Calender -->
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>

        <!-- FAQ Section-->
        <div class="faq-content">
            <div id="faq-section" class="faq">
                <h2>FAQ Section</h2>
                <div class="accordion">
                    <div class="accordion-item">
                        <button class="accordion-title">
                            How do I access quizzes assigned to me?
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="accordion-content">
                            <p>Go to the "My Quizzes" section. All active quizzes will be listed there, and you can click on a quiz to start it.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <button class="accordion-title">
                            Can I attempt a quiz more than once?
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="accordion-content">
                            <p>It depends on the quiz settings defined by the admin.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <button class="accordion-title">
                            What is proctoring, and why is it used?
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="accordion-content">
                            <p>Proctoring is used to monitor quiz sessions via webcam or other tools to ensure fair play.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sections -->
    <div id="profile-section-main" class="section" style="position: relative; display: block; visibility: visible; padding: 10px; border: 1px solid #ccc;">
        <!-- Cross Button -->
        <button id="close-profile-btn" 
                style="position: absolute; top: 10px; right: 10px; background-color: white; color: #fa574e; border: none; font-size: 40px; cursor: pointer;">
            &times;
        </button>
        <h3>Your Profile</h3>
        <?php
        // Determine the profile picture based on gender
        $profileImage = $user['gender'] === 'Female'
            ? "https://cdn1.iconfinder.com/data/icons/avatars-1-5/136/87-512.png"
            : "https://static.vecteezy.com/system/resources/previews/019/896/008/original/male-user-avatar-icon-in-flat-design-style-person-signs-illustration-png.png";
        ?>
        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Picture" id="profile-picture" style="width: 100px; height: 100px; border-radius: 50%;">
        <p><strong>Name:</strong> <span id="profile-name"><?php echo htmlspecialchars($user['name']); ?></span></p>
        <p><strong>Email:</strong> <span id="profile-email"><?php echo htmlspecialchars($user['email']); ?></span></p>
        <p><strong>Gender:</strong> <span id="profile-gender"><?php echo htmlspecialchars($user['gender']); ?></span></p>
        <button id="edit-profile-btn" style="display: none;">Edit</button>
        <button id="save-profile-btn" style="display: none;">Save</button>
    </div>

    <div id="quizzes-section" class="section" style="position: relative; display: none; padding: 10px; border: 1px solid #ccc;">
    <!-- Close Button -->
    <button id="close-quizzes-btn" 
            style="position: absolute; top: 10px; right: 10px; background-color: white; color: #fa574e; border: none; font-size: 40px; cursor: pointer;">
        &times;
    </button>
        <h3>My Quizzes</h3>
        <table id="quizzes-table">
            <thead>
                <tr>
                    <th>S no.</th>
                    <th>Title</th>
                    <th>Uploaded On</th>
                    <th>Attempted On</th>
                    <th>Status</th>
                    <th>Max Marks</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Quiz 1</td>
                    <td>2024-11-01</td>
                    <td>2024-11-05</td>
                    <td>Attempted</td>
                    <td>30</td>
                    <td>25</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Quiz 2</td>
                    <td>2024-10-21</td>
                    <td></td>
                    <td>Not Attempted</td>
                    <td>30</td>
                    <td></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Quiz 3</td>
                    <td>2024-11-20</td>
                    <td>2024-11-28</td>
                    <td>Attempted</td>
                    <td>30</td>
                    <td>22</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="statistics-section" class="section" style="position: relative; display: none; padding: 10px; border: 1px solid #ccc;">
    <!-- Close Button -->
    <button id="close-statistics-btn" 
            style="position: absolute; top: 10px; right: 10px; background-color: white; color: #fa574e; border: none; font-size: 40px; cursor: pointer;">
        &times;
    </button>
        <h3>Quiz Statistics</h3>
        <div class="chart-container">
            <canvas id="averageScoreChart"></canvas>
        </div>
    </div>
    
    <div id="contact-section" class="section" style="display: none;"></div>

    <!-- Chart.js for Performance Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Add functionality to reload the page when the cross button is clicked
    document.getElementById('close-profile-btn').addEventListener('click', function() {
        location.reload(); // Reloads the current page
    });
    document.getElementById('close-quizzes-btn').addEventListener('click', function() {
        location.reload();
    });
    document.getElementById('close-statistics-btn').addEventListener('click', function() {
        location.reload();
    });
    </script>
    <script src="assets/js/candidate_dashboard.js"></script>
</body>
</html>