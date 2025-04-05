<?php
// Session initialization and login verification
ini_set('session.gc_maxlifetime', 3600); // Example: Set to 1 hour (3600 seconds)
session_set_cookie_params(3600); // This ensures the session cookie lasts for the same duration
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['quiz_id'])) {
    header('Location: login.php');
    exit();
}

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
$_SESSION['quiz_started'] = true;
$candidateName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Check if request is for answer validation
    if (isset($inputData['question_id']) && isset($inputData['selected_option'])) {
        // Variables from frontend
        $question_id = $inputData['question_id'];
        $selected_option = $inputData['selected_option'];

        // Log input for debugging
        error_log("Received question_id: $question_id");
        error_log("Received selected_option: $selected_option");

        // Fetch correct option and question marks
        $query = "SELECT correct_option, question_marks FROM questions WHERE question_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $question = $result->fetch_assoc()) {
            $correct_option_number = $question['correct_option'];
            $marks = $question['question_marks'];

            // Fetch correct option text
            $query_correct_option = "SELECT option_id, option_text FROM options WHERE question_id = ? LIMIT 4";
            $stmt_correct_option = $conn->prepare($query_correct_option);
            $stmt_correct_option->bind_param("i", $question_id);
            $stmt_correct_option->execute();
            $result_correct_option = $stmt_correct_option->get_result();

            $correct_option_text = '';
            $option_counter = 1;

            while ($row = $result_correct_option->fetch_assoc()) {
                if ($option_counter === (int)$correct_option_number) {
                    $correct_option_text = $row['option_text'];
                    break;
                }
                $option_counter++;
            }

            // Fetch selected option text
            $query_selected_option = "SELECT option_text FROM options WHERE option_id = ?";
            $stmt_selected_option = $conn->prepare($query_selected_option);
            $stmt_selected_option->bind_param("i", $selected_option);
            $stmt_selected_option->execute();
            $result_selected_option = $stmt_selected_option->get_result();

            if ($result_selected_option && $selected_option_row = $result_selected_option->fetch_assoc()) {
                $selected_option_text = $selected_option_row['option_text'];
            } else {
                error_log("No selected option found with option_id: $selected_option");
                echo json_encode(['isCorrect' => false, 'questionMarks' => 0]);
                exit();
            }

            // Compare correct and selected options
            $isCorrect = ($correct_option_text === $selected_option_text);
            $questionMarks = $isCorrect ? $marks : 0;

            // Respond with the result
            echo json_encode([
                'isCorrect' => $isCorrect,
                'questionMarks' => $questionMarks,
                'correct_option' => $correct_option_text
            ]);
        } else {
            error_log("No matching question found for question_id: $question_id");
            echo json_encode(['isCorrect' => false, 'questionMarks' => 0]);
        }
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get data from the request body
  $inputData = json_decode(file_get_contents('php://input'), true);
  
  // Log the input data for debugging
  error_log('Input Data: ' . json_encode($inputData));  // Logs the received input data
  
  $candidate_id = $_SESSION['user_id'];
  $quiz_id = $_SESSION['quiz_id'];

  // Prepare the SQL statement to fetch the image data from the database
  $stmt = $conn->prepare("SELECT image_path FROM image_capture WHERE candidate_id = ? AND quiz_id = ?");
  $stmt->bind_param("ii", $candidate_id, $quiz_id);

  // Execute the query and fetch the result
  if ($stmt->execute()) {
      $stmt->store_result();
      $stmt->bind_result($imagePath);

      if ($stmt->fetch()) {
          // Read the image data from the file
          $imageData = base64_encode(file_get_contents($imagePath)); // Assuming image is stored as a file path in `image_path`

          // Prepare the response
          $data = ["status" => "success", "imageData" => $imageData];
          
          // Log the response data before sending it
          error_log('Response Data: ' . json_encode($data));  // Logs the response data
          
          // Respond with the image data in base64 format    
          echo json_encode($data);
      } else {
          // No image found for the given candidate and quiz
          $data = ["status" => "error", "message" => "Image not found."];
          error_log('Response Data: ' . json_encode($data));  // Log the error response
          echo json_encode($data);
      }
  } else {
      $data = ["status" => "error", "message" => "Failed to fetch image."];
      error_log('Response Data: ' . json_encode($data));  // Log the error response
      echo json_encode($data);
  }

  $stmt->close();
  $conn->close();
  exit();
}

// Fetch quiz details (same as before, you can keep this section)
$quiz_id = $_SESSION['quiz_id']; // Fetch quiz ID from the session
$candidate_id = $_SESSION['user_id']; // Candidate ID stored in the session

// Prepare the SQL statement to fetch the quiz data (same as before)
$quizQuery = "SELECT quiz_duration, quiz_description FROM quizzes WHERE quiz_id = ?";
$stmtQuiz = $conn->prepare($quizQuery);
$stmtQuiz->bind_param("i", $quiz_id);
$stmtQuiz->execute();
$quizResult = $stmtQuiz->get_result();
$quizDetails = $quizResult->fetch_assoc();

// Fetch quiz questions and options (same as before)
$questionsQuery = "
    SELECT q.question_id, q.question_text, o.option_id, o.option_text
    FROM questions q
    JOIN options o ON q.question_id = o.question_id
    WHERE q.quiz_id = ?";
$stmtQuestions = $conn->prepare($questionsQuery);
$stmtQuestions->bind_param("i", $quiz_id);
$stmtQuestions->execute();
$result = $stmtQuestions->get_result();

$quiz_data = [];
while ($row = $result->fetch_assoc()) {
    $quiz_data[$row['question_id']]['question_text'] = $row['question_text'];
    $quiz_data[$row['question_id']]['options'][] = [
        'option_id' => $row['option_id'],
        'option_text' => $row['option_text']
    ];
}
$stmtQuiz->close();
$stmtQuestions->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="./assets/css/actual_quiz_page.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light px-3">
    <div class="nav-logo">
      <h1>Quiz<span>Eye</span></h1>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user user-icon"></i> 
            <span class="user-name"><?php echo htmlspecialchars($candidateName); ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <div class="quiz-header">
    <h2><?php echo htmlspecialchars($quizDetails['quiz_description']); ?></h2>
    <button class="submit-btn" style="position: absolute;top: 90px; right: 30px; background-color: #fa574e; border-radius: 8px;color: white;border: none;padding: 10px 20px;font-size: 16px;cursor: pointer;" onclick="submitQuiz()">Submit</button>
  </div>
 
  <!-- Question Navigation Blocks -->
<div class="question-navigation">
    <?php $questionNumber = 1; ?>
    <?php foreach ($quiz_data as $question_id => $question): ?>
        <div class="question-block" 
             data-question-id="<?php echo $question_id; ?>" 
             onclick="jumpToQuestion(<?php echo $questionNumber - 1; ?>)">
            <?php echo $questionNumber; ?>
        </div>
        <?php $questionNumber++; ?>
    <?php endforeach; ?>
</div>
<div class="legend">
  <div class="legend-item">
    <div class="legend-square red"></div>
    <span>Unanswered</span>
  </div>
  <div class="legend-item">
    <div class="legend-square blue"></div>
    <span>Not attempted</span>
  </div>
  <div class="legend-item">
    <div class="legend-square green"></div>
    <span>Answered</span>
  </div>
</div>
    <!-- Questions Section -->
<div class="questions-container">
  <?php $questionNumber = 1; ?>
  <?php foreach ($quiz_data as $question_id => $question): ?>
    <div class="question" id="question-<?php echo $question_id; ?>" style="display: none;">
      <p><strong>Q<?php echo $questionNumber; ?>. </strong><?php echo htmlspecialchars($question['question_text']); ?></p>
      <?php foreach ($question['options'] as $option): ?>
        <div>
          <input type="radio" name="question-<?php echo $question_id; ?>" id="option-<?php echo $option['option_id']; ?>" value="<?php echo $option['option_id']; ?>">
          <label for="option-<?php echo $option['option_id']; ?>"><?php echo htmlspecialchars($option['option_text']); ?></label>
        </div>
      <?php endforeach; ?>
    </div>
    <?php $questionNumber++; ?>
  <?php endforeach; ?>
</div>


    <!-- Navigation Buttons -->
    <div class="nav-buttons">
      <button id="prev-btn" class="btn btn-secondary">Previous</button>
      <button id="next-btn" class="btn btn-primary">Next</button>
    </div>
  </div>
  <video id="video" width="640" height="480" autoplay></video>

  <footer>
    <p class="footer-text">© 2024 QuizEye. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Modal Permission Flow
    const quizData = <?php echo json_encode($quiz_data); ?>; // Pass PHP data to JavaScript
    const quizId = <?php echo json_encode($quiz_id); ?>;
    const candidateId = <?php echo json_encode($candidate_id); ?>;
    let quizDuration = <?php echo json_encode($quizDetails['quiz_duration']); ?> * 60; // Convert minutes to seconds
    const timerElement = document.createElement('div');
timerElement.id = 'quiz-timer';
timerElement.style.cssText = "position: fixed; top: 100px; right: 170px; font-size: 20px; color: red;";
document.body.appendChild(timerElement);
    console.log(quizData);
    function updateTimer() {
    const minutes = Math.floor(quizDuration / 60);
    const seconds = quizDuration % 60;
    timerElement.innerHTML = `Time Left: ${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
    if (quizDuration <= 0) {
        autoSubmitQuiz();
    } else {
        quizDuration--;
        setTimeout(updateTimer, 1000);
    }
}
updateTimer();
    let lastFrameTime = Date.now();

    
 // Quiz navigation logic
 let isQuizSubmitted = false; // Prevent duplicate submissions//
 let currentQuestion = 0;
 let totalMarks = 0;
    const questions = $('.question');
    const questionBlocks = $('.question-block');
    const prevBtn = $('#prev-btn');
    const nextBtn = $('#next-btn');
    let answeredQuestions = new Set();

    function showQuestion(index) {
      questions.hide();
      $(questions[index]).show();
      prevBtn.prop('disabled', index === 0);
      nextBtn.prop('disabled', index === questions.length - 1);
    }
    $('input[type="radio"]').change(function () {
    const questionId = $(this).attr('name').split('-')[1];
    // Update the question block's color for answered questions
    $(`.question-block[data-question-id="${questionId}"]`).removeClass('red').addClass('green');
    answeredQuestions.add(questionId); // Add the question to the answered set
});

    $('#next-btn').click(function () {
      if (currentQuestion < questions.length - 1) {
        const currentQuestionId = $(questions[currentQuestion]).attr('id').split('-')[1];
        // Mark unanswered question blocks as red
        if (!answeredQuestions.has(currentQuestionId)) {
            $(`.question-block[data-question-id="${currentQuestionId}"]`).addClass('red');
        }
        currentQuestion++;
        showQuestion(currentQuestion);
      }
    });

    $('#prev-btn').click(function () {
      if (currentQuestion > 0) {
        currentQuestion--;
        showQuestion(currentQuestion);
      }
    });

    $('input[type="radio"]').change(function () {
    const questionId = $(this).attr('name').split('-')[1];
    const selectedOption = $(this).val();

    // Log questionId and selectedOption to console for debugging
    console.log('Selected Question ID:', questionId);
    console.log('Selected Option:', selectedOption);

    // Fetch the correct answer from the server
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question_id: questionId, selected_option: selectedOption })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server Response:', data);  // Log the response from the server for debugging

        // Log the correct option fetched from the database
        console.log('Correct Option from DB:', data.correct_option);  // Now you can access correct_option

        if (data.isCorrect) {
            totalMarks += data.questionMarks; // Add marks for the correct answer
        }

        // Log totalMarks after receiving feedback
        console.log('Total Marks:', totalMarks);

        // Update the question block's color
        $(`.question-block[data-question-id="${questionId}"]`).removeClass('red').addClass('green');
        answeredQuestions.add(questionId);

        // Display feedback in the console (this is not visible on the page)
        console.log(data.isCorrect ? 'Correct!' : 'Incorrect');
    });
});


    // Auto Submit Quiz
function autoSubmitQuiz() {
    alert('Time is up! Submitting your quiz.');
    submitQuiz();
}
// Final Submission Logic
function submitQuiz() {
  if (isQuizSubmitted) return; // Skip if already submitted
    isQuizSubmitted = true;
    fetch('submit_quiz.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            quiz_id: quizId,
            candidate_id: candidateId,
            marks: totalMarks
        })
    }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Quiz submitted successfully.');
                window.location.href = 'candidate_homepage.php';
            } else {
                alert('Quiz submission failed.');
            }
        });
}
// Confirm Submission Modal
function confirmSubmission() {
    if (quizDuration > 0) {
        const confirmation = confirm('Are you sure you want to submit? You still have time left.');
        if (confirmation) {
            submitQuiz();
        }
    } else {
        submitQuiz();
    }
}
// Attach event to the Submit button
document.querySelector('.submit-btn').addEventListener('click', confirmSubmission);
    showQuestion(currentQuestion);
    // Handle Page Reload or Navigation
window.addEventListener('beforeunload', function (e) {
    if (!isQuizSubmitted) {
        e.preventDefault();
        e.returnValue = '';
    }
});

window.addEventListener('load', function () {
    if (performance.getEntriesByType('navigation')[0].type === 'reload') {
        window.location.href = 'candidate_homepage.php';
    }
});
  </script>
</body>
</html>
