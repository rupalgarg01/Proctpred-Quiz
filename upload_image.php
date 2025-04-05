<?php
// Set headers to allow JSON data exchange
header("Content-Type: application/json");

// Capture the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if all required fields are present in the received data
if (isset($data['candidateId'], $data['image'], $data['quizId'])) {
    // Sanitize the received data
    $candidateId = htmlspecialchars($data['candidateId']);
    $quizId = htmlspecialchars($data['quizId']);
    $imageData = $data['image'];

    // Decode the Base64 image
    $image_parts = explode(";base64,", $imageData);
    if (count($image_parts) > 1) {
        $image_base64 = base64_decode($image_parts[1]);

        // Database connection details
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "proctored_quiz";

        // Create the database connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die(json_encode(["status" => "error", "message" => "Database connection failed."]));
        }

        // Prepare the SQL statement to insert data into the image_captures table
        $stmt = $conn->prepare("INSERT INTO image_captures (candidate_id, quiz_id, image_data) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $candidateId, $quizId, $image_base64);

        // Execute the query and respond based on the success
        if ($stmt->execute()) {
            // Respond with success message
            echo json_encode(["status" => "success", "message" => "Image uploaded and details saved successfully."]);
        } else {
            // Respond with failure message if unable to save the details
            echo json_encode(["status" => "error", "message" => "Failed to save quiz details."]);
        }

        // Close the statement and the database connection
        $stmt->close();
        $conn->close();
    } else {
        // Respond with error if the image data is invalid
        echo json_encode(["status" => "error", "message" => "Invalid image data."]);
    }
} else {
    // Respond with error if required data is missing
    echo json_encode(["status" => "error", "message" => "Invalid data provided."]);
}
?>
