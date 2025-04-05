<?php
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

// Get data from the AJAX request
$input = json_decode(file_get_contents('php://input'), true);
$quiz_id = $input['quiz_id'];
$candidate_id = $input['candidate_id'];
$reason = $input['reason'];

// Check if flag already exists or update the flag status
$checkQuery = "SELECT * FROM cheating_flags WHERE quiz_id = ? AND candidate_id = ?";
$stmtCheck = $conn->prepare($checkQuery);
$stmtCheck->bind_param("ii", $quiz_id, $candidate_id);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result->num_rows > 0) {
    // Update the cheating flag
    $updateQuery = "UPDATE cheating_flags SET flag_count = flag_count + 1 WHERE quiz_id = ? AND candidate_id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("ii", $quiz_id, $candidate_id);
    $stmtUpdate->execute();
} else {
    // Insert a new flag record
    $insertQuery = "INSERT INTO cheating_flags (quiz_id, candidate_id, reason, flag_count) VALUES (?, ?, ?, 1)";
    $stmtInsert = $conn->prepare($insertQuery);
    $stmtInsert->bind_param("iis", $quiz_id, $candidate_id, $reason);
    $stmtInsert->execute();
}

$stmtCheck->close();
$stmtUpdate->close();
$stmtInsert->close();
$conn->close();

// Send response back to front-end
echo json_encode(['success' => true]);
?>
