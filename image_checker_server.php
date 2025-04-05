<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'proctored_quiz'); // Replace with your actual database credentials

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve the file path of the captured image
$query = "SELECT image_path FROM image_capture WHERE candidate_id = ? AND quiz_id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $candidate_id = 6; // Replace with the actual candidate ID
    $quiz_id = 13;     // Replace with the actual quiz ID

    $stmt->bind_param("ii", $candidate_id, $quiz_id);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    
    if ($stmt->fetch()) {
        // Check if the file exists
        if (file_exists($imagePath)) {
            echo json_encode(["status" => "success", "message" => "Image file exists on the server.", "image_path" => $imagePath]);
        } else {
            echo json_encode(["status" => "error", "message" => "Image file does not exist on the server.", "image_path" => $imagePath]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No record found in the database for the given candidate and quiz."]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Failed to prepare the database query."]);
}

// Close the connection
$conn->close();
?>
