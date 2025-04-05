<?php
// Start the session and check if the user is logged in
session_start();

// Assuming the user ID is stored in the session after login
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to start the quiz.";
    exit;
}

$candidate_id = $_SESSION['user_id']; // Get the candidate's ID from the session

$quiz_id = isset($_SESSION['quiz_id']) ? $_SESSION['quiz_id'] : '';
$quiz_duration = isset($_SESSION['quiz_duration']) ? $_SESSION['quiz_duration'] : '';
$total_questions = isset($_SESSION['total_questions']) ? $_SESSION['total_questions'] : '';

if (empty($quiz_id)) {
    die(json_encode(["status" => "error", "message" => "Quiz ID is missing."]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle image upload from the form submission
    if (isset($_POST['image']) && !empty($_POST['image'])) {
        // Extract the image data and other details
        $imageData = $_POST['image'];
        $candidateId = $candidate_id;
        $quizId = $quiz_id;

        // Clean the image data (strip the base64 prefix)
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = base64_decode($imageData);

        // Create a unique filename for the image
        $imageFilename = 'uploads/' . 'candidate_' . $candidateId . '_quiz_' . $quizId . '.png';

        // Ensure the directory exists, if not, create it
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Save the image to the server
        if (file_put_contents($imageFilename, $imageData)) {
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'proctored_quiz'); // Replace with actual database credentials

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Prepare the INSERT query
            $query = "INSERT INTO image_capture (candidate_id, quiz_id, image_path, captured_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                // Bind the parameters
                $stmt->bind_param("iis", $candidateId, $quizId, $imageFilename);

                // Execute the query
                if ($stmt->execute()) {
                    // Redirect to proctoring_setup.php upon success
                    header("Location: actual_quiz_page.php");
                    exit;
                } else {
                    // Error saving image to the database
                    $errorMessage = "Failed to save image to database: " . $stmt->error;
                    echo json_encode(["status" => "error", "message" => $errorMessage]);
                }

                // Close the statement
                $stmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to prepare the database query."]);
            }

            // Close the connection
            $conn->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save image to server."]);
        }

        exit; // Terminate after sending the response
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Start - Image Capture</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/image_capture.css">
</head>
<body>
    <div class="container">
        <header class="quiz-header">
            <p>Your participation is essential. We need your help in ensuring a fair quiz experience by following these simple steps:</p>
        </header>

        <section class="capture-section">
            <video id="video" autoplay></video>
            <canvas id="canvas" style="display: none;"></canvas>
            <img id="captured-image" style="display: none; max-width: 500px; border: 2px solid #ccc; border-radius: 10px; margin: 20px auto;" alt="Captured Image">
            <div class="button-group">
                <button id="capture-btn" class="btn">Capture Image</button>
                <button id="reset-btn" class="btn" style="display: none;">Retake Image</button>
            </div>
            <p id="image-status">No image captured yet.</p>
        </section>

        <footer>
            <form id="quiz-form" method="POST" action="">
                <input type="hidden" name="image" id="image-data">
                <button id="start-quiz-btn" class="btn" disabled>Next</button>
            </form>
        </footer>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js"></script>
    <script>
        // JavaScript to capture quiz details passed from the URL
        const quizId = "<?php echo $quiz_id; ?>";
        const quizDuration = "<?php echo $quiz_duration; ?>";
        const totalQuestions = "<?php echo $total_questions; ?>";
        const candidateId = "<?php echo $candidate_id; ?>"; // Get the candidate ID from PHP session

        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let captureBtn = document.getElementById('capture-btn');
        let resetBtn = document.getElementById('reset-btn');
        let startQuizBtn = document.getElementById('start-quiz-btn');
        let imageStatus = document.getElementById('image-status');
        let capturedImageData = null;

        // Set up camera stream
        function initializeCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    video.srcObject = stream;
                    video.style.display = 'block'; // Show video when camera is accessed
                    video.play();
                })
                .catch(function (err) {
                    console.error("Error accessing camera: " + err);
                    alert("Camera access is required for this quiz. Please enable camera permissions in your browser settings and reload the page.");
                });
        }

        // Call initializeCamera on page load for the default camera prompt
        initializeCamera();

        // Handle image capture
        captureBtn.addEventListener('click', function () {
            captureImage();
        });

        // Function to capture the image
        function captureImage() {
            let context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
            
            // Store the captured image data and display it
            capturedImageData = canvas.toDataURL('image/png');
            displayCapturedImage();
        }

        // Display the captured image and adjust button visibility
        function displayCapturedImage() {
            const capturedImageDisplay = document.getElementById('captured-image') || createImageElement();
            capturedImageDisplay.src = capturedImageData;
            capturedImageDisplay.style.display = 'block';
            
            video.style.display = 'none'; // Hide the video
            captureBtn.style.display = 'none'; // Hide the capture button
            resetBtn.style.display = 'inline-block'; // Show the reset button
            imageStatus.innerText = "Image captured successfully!";
            checkReadyToStartQuiz();
        }

        // Create image element to show captured image
        function createImageElement() {
            const img = document.createElement('img');
            img.id = 'captured-image';
            document.querySelector('.capture-section').appendChild(img);
            return img;
        }

        // Handle resetting the image
        resetBtn.addEventListener('click', function () {
            resetImageCapture();
        });

        // Function to reset the image capture setup
        function resetImageCapture() {
            document.getElementById('captured-image').style.display = 'none'; // Hide captured image
            video.style.display = 'block'; // Show the video again
            captureBtn.style.display = 'inline-block'; // Show the capture button
            resetBtn.style.display = 'none'; // Hide the reset button
            imageStatus.innerText = "No image captured yet.";
            capturedImageData = null; // Clear captured image data
            checkReadyToStartQuiz();
        }

        // Check if the image is ready to start the quiz
        function checkReadyToStartQuiz() {
            startQuizBtn.disabled = !capturedImageData; // Enable start quiz button only if image is captured
            if (capturedImageData) {
                // Store the image data in the hidden input field
                document.getElementById('image-data').value = capturedImageData;
            }
        }

        // Quiz start logic (form submission happens here)
        startQuizBtn.addEventListener('click', function () {
            if (capturedImageData) {
                // Form will automatically submit with the captured image data
                document.getElementById('quiz-form').submit();
            } else {
                alert("No image captured. Please capture your image before starting the quiz.");
            }
        });
    </script>
</body>
</html>
