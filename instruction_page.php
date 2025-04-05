<?php
// Start the session
session_start();

// Fetch quiz information from the query parameters
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0; // Default to 0 if not available
$quiz_duration = isset($_GET['quiz_duration']) ? intval($_GET['quiz_duration']) : 0; // Default to 0 if not available
$total_questions = isset($_GET['total_questions']) ? intval($_GET['total_questions']) : 0; // Default to 0 if not available

// Store the quiz information into session variables
$_SESSION['quiz_id'] = $quiz_id;
$_SESSION['quiz_duration'] = $quiz_duration;
$_SESSION['total_questions'] = $total_questions;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Instructions</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/instruction_page.css">
</head>
<body>
    <div class="container">
        <header class="quiz-header">
            <h1>Welcome to the Quiz!</h1>
            <p>Please read the instructions carefully before you start.</p>
        </header>

        <section class="quiz-instructions">
            <h2>Quiz Guidelines</h2>

            <p>This quiz is multiple-choice (MCQ) based. Make sure you're ready to focus on the quiz.</p>
            <ul>
                <li><strong>Time Limit:</strong> You have <span class="highlight"><?php echo htmlspecialchars($_SESSION['quiz_duration']); ?> minutes</span> to complete the quiz.</li>
                <li><strong>Total Questions:</strong> <span class="highlight"><?php echo htmlspecialchars($_SESSION['total_questions']); ?> questions</span>.</li>
                <li><strong>Answers:</strong> Each question has four options. Select the correct answer.</li>
                <li><strong>Submission:</strong> Your quiz will be submitted automatically when the time runs out.</li>
                <li><strong>Final Submit:</strong> You cannot change your answers after final submission or when time runs out.</li>
            </ul>
        </section>

        <section class="network-connectivity">
            <h2>Network & Technical Requirements</h2>
            <p>Ensure the following before starting the quiz:</p>
            <ul>
                <li><strong>Stable Internet Connection:</strong> A reliable connection is required throughout the quiz. Disconnections may result in auto-submission.</li>
                <li><strong>Device Compatibility:</strong> Use a laptop or desktop with an updated browser for the best experience.</li>
                <li><strong>Browser Requirement:</strong> This quiz works best on the latest versions of Chrome, Firefox, or Safari. Do not use incognito or private mode.</li>
                <li><strong>Disable Pop-Ups:</strong> Make sure pop-up blockers are disabled, as they may interrupt the quiz experience.</li>
                <li><strong>No Multiple Logins:</strong> Multiple logins or attempts to login from different devices may invalidate your quiz attempt.</li>
            </ul>
        </section>

        <section class="anti-cheat">
            <h2>Anti-Cheating Measures</h2>
            <p>To maintain fairness, the following measures are in place:</p>
            <ul>
                <li>The quiz runs in <span class="highlight">fullscreen mode</span>. Exiting fullscreen or switching tabs will be logged.</li>
                <li><strong>Webcam Monitoring:</strong> Your webcam will be used to monitor suspicious behavior.</li>
                <li><strong>Screen Activity:</strong> Screen activity is monitored. Trying to copy, paste, or screenshot may lead to disqualification.</li>
            </ul>
        </section>

        <footer>
            <p>By clicking "Next", you agree to the guidelines and rules listed above. Good luck!</p>
            <form action="image_capture.php" method="POST">
                <button type="submit">Next</button>
            </form>
        </footer>
    </div>
</body>
</html>
