<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to create a quiz.";
    exit;
}

// Retrieve instructor ID from session
$inst_id = $_SESSION['user_id'];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "proctored_quiz");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve quiz data
$quiz_description = $_POST['quiz_description'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$quiz_duration = (int)($_POST['quiz_duration'] ?? 0); // Ensure it's an integer
$question_count = (int)($_POST['question_count'] ?? 0); // Ensure it's an integer

// Insert quiz information into 'quizzes' table, including inst_id
$sql = "INSERT INTO quizzes (quiz_description, start_time, end_time, inst_id, quiz_duration) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssii", $quiz_description, $start_time, $end_time, $inst_id, $quiz_duration);

if ($stmt->execute()) {
    $quiz_id = $conn->insert_id; // Get the last inserted quiz ID

    // Loop through the questions
    for ($i = 1; $i <= $question_count; $i++) {
        $question_title = $_POST["question_{$i}_title"] ?? '';
        $question_marks = (int)($_POST["question_{$i}_marks"] ?? 0); // Ensure it's an integer
        $correct_option = $_POST["question_{$i}_correct"] ?? '';

        // Insert question into 'questions' table
        $sql_question = "INSERT INTO questions (quiz_id, question_text, question_marks, correct_option) VALUES (?, ?, ?, ?)";
        $stmt_question = $conn->prepare($sql_question);
        $stmt_question->bind_param("isis", $quiz_id, $question_title, $question_marks, $correct_option);

        if ($stmt_question->execute()) {
            $question_id = $conn->insert_id;

            // Insert options into 'options' table
            for ($j = 1; $j <= 4; $j++) {
                $option_text = $_POST["question_{$i}_option_$j"] ?? '';
                
                if (!empty($option_text)) {
                    $sql_option = "INSERT INTO options (question_id, option_text) VALUES (?, ?)";
                    $stmt_option = $conn->prepare($sql_option);
                    $stmt_option->bind_param("is", $question_id, $option_text);
            
                    if (!$stmt_option->execute()) {
                        echo "Error inserting option for question $question_id: " . $stmt_option->error . "<br>";
                    }
                }
            }
            
        } else {
            echo "Error inserting question: " . $stmt_question->error . "<br>";
        }
    }

    // Redirect to instructor homepage with a success message
    $message = "Quiz created successfully!";
    header("Location: instructor_homepage.php?status=" . urlencode($message));
    exit;

} else {
    // Redirect to instructor homepage with an error message
    $message = "Error creating quiz: " . $stmt->error;
    header("Location: instructor_homepage.php?status=" . urlencode($message));
    exit;
}

// Close statements and connection
$stmt->close();
$conn->close();
?>
