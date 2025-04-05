<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proctored_quiz";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Extract data from the request
    $quizId = $input['quiz_id'];
    $candidateId = $input['candidate_id'];
    $marks = $input['marks'];

    // Clear the quiz_started session flag
    unset($_SESSION['quiz_started']);

    // Save the quiz results to the database (implement your logic here)
    // For example:
    // $db->saveQuizResults($quizId, $candidateId, $marks);

    echo json_encode(['success' => true]);
    exit();}

$data = json_decode(file_get_contents('php://input'), true);
$quiz_id = $data['quiz_id'];
$candidate_id = $data['candidate_id'];
$marks = $data['marks'];

// Check if the entry already exists to prevent duplicates
$query = "SELECT COUNT(*) AS count FROM results WHERE candidate_id = ? AND quiz_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $candidate_id, $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) { // Insert only if no previous entry exists
    $insertQuery = "INSERT INTO results (candidate_id, quiz_id, marks) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iii", $candidate_id, $quiz_id, $marks);
    $success = $insertStmt->execute();
    echo json_encode(['success' => $success]);
    $insertStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Quiz already submitted.']);
}

$stmt->close();
$conn->close();
?>
