<?php
session_start(); // Start the session

// Check if the instructor is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Include PHPMailer and database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
//to show error
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proctored_quiz";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}

// Fetch quizzes created by the instructor
$instructor_id = $_SESSION['user_id'];
$sql = "SELECT * FROM quizzes WHERE inst_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];
while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}

// Fetch candidates for each quiz
$candidate_data = [];
foreach ($quizzes as $quiz) {
    $quiz_id = $quiz['quiz_id'];
    $sql = "SELECT c.id, c.name, c.email, c.gender FROM candidates c
            JOIN quiz_candidates qc ON c.id = qc.candidate_id
            WHERE qc.quiz_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $candidates = [];
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
    $candidate_data[$quiz_id] = $candidates;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => 'Invalid action.'];

    if ($_POST['action'] == 'add_candidate') {
        $candidate_name = trim($_POST['candidate_name']);
        $candidate_email = trim($_POST['candidate_email']);
        $candidate_gender = trim($_POST['candidate_gender']);
        $quiz_id = intval($_POST['quiz_id']);
    
        // Validate input
        if (empty($candidate_name) || empty($candidate_email) || empty($candidate_gender)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit();
        }
    
        // Generate temporary password
        $temp_password = bin2hex(random_bytes(4));
    
        // Check if candidate already exists
        $stmt = $conn->prepare("SELECT id FROM candidates WHERE name = ? AND email = ?");
        $stmt->bind_param("ss", $candidate_name, $candidate_email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $candidate_exists = $result->num_rows > 0;
    
        if ($candidate_exists) {
            $row = $result->fetch_assoc();
            $candidate_id = $row['id'];
        } else {
            // Add candidate to the database
            $stmt = $conn->prepare("INSERT INTO candidates (name, email, gender, pwd, reg_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $candidate_name, $candidate_email, $candidate_gender, $temp_password);
            if ($stmt->execute()) {
                $candidate_id = $stmt->insert_id;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add candidate to the database. ' . $stmt->error]);
                exit();
            }
        }
    
        // Check if the candidate is already in the quiz
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM quiz_candidates WHERE quiz_id = ? AND candidate_id = ?");
        $stmt->bind_param("ii", $quiz_id, $candidate_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'This candidate is already added to the quiz.']);
            exit();
        }
    
        // Add candidate to the quiz
        $stmt = $conn->prepare("INSERT INTO quiz_candidates (quiz_id, candidate_id, instructor_id, added_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $quiz_id, $candidate_id, $instructor_id);
        if ($stmt->execute()) {
            if (!$candidate_exists) {
                // Send email only for new candidates
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'anshishubhipr@gmail.com';
                    $mail->Password = 'hpwczembjzsarnjv';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
    
                    $mail->setFrom('anshishubhipr@gmail.com', 'Quiz Management');
                    $mail->addAddress($candidate_email, $candidate_name);
    
                    $mail->isHTML(true);
                    $mail->Subject = 'Quiz Login Details';
                    $mail->Body = "
                        <p>Dear $candidate_name,</p>
                        <p>You have been registered for a quiz.</p>
                        <p>Your login details are as follows:</p>
                        <ul>
                            <li><strong>Email:</strong> $candidate_email</li>
                            <li><strong>Temporary Password:</strong> $temp_password</li>
                        </ul>
                        <p>Kindly log in and change your password immediately.</p>
                        <p>Best regards,<br>Quiz Management Team</p>
                    ";
    
                    $mail->send();
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"]);
                    exit();
                }
            }
    
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add candidate to the quiz. ' . $stmt->error]);
        }
    
        exit();
    }
    
    
    elseif ($_POST['action'] == 'delete_candidate') {
        // Deleting a candidate
        $candidate_id = intval($_POST['candidate_id']);
        $quiz_id = intval($_POST['quiz_id']);

        // Validate input
        if (empty($candidate_id) || empty($quiz_id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid candidate or quiz ID.']);
            exit();
        }

        // Delete the candidate from the quiz_candidates table
        $stmt = $conn->prepare("DELETE FROM quiz_candidates WHERE candidate_id = ? AND quiz_id = ?");
        $stmt->bind_param("ii", $candidate_id, $quiz_id);

        if ($stmt->execute()) {
            // Optionally, delete from the candidates table (if no longer associated with other quizzes)
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM quiz_candidates WHERE candidate_id = ?");
            $stmt->bind_param("i", $candidate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] == 0) {
                $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
                $stmt->bind_param("i", $candidate_id);
                $stmt->execute();
            }

            $response = ['success' => true, 'message' => 'Candidate removed successfully.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove candidate. ' . $stmt->error];
        }

        echo json_encode($response);
        exit();
    }
    
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management</title>
    <link rel="stylesheet" href="./assets/css/quiz_magmt.css">
</head>

<body>
    <!-- Navbar -->
    <header class="navbar">
        <button class="previous-button" onclick="goBack()">
            <span class="previous-icon">⬅</span>
        </button>
        <h1>Quiz<span>Eye</span> - Quiz Management</h1>
    </header>

    <div class="content">
        <h2>Manage Your Quizzes</h2>

        <!-- Search Bar -->
        

        <!-- Display Quizzes dynamically -->
        <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-card">
                <div class="quiz-header">
                    <div class="quiz-title"><?php echo htmlspecialchars($quiz['quiz_description']); ?></div>
                    <button class="add-candidate" title="Add Candidate" onclick="addCandidate(<?php echo $quiz['quiz_id']; ?>)">+</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Candidate Name</th>
                            <th>Candidate Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="quiz-<?php echo $quiz['quiz_id']; ?>">
                        <?php
                        if (isset($candidate_data[$quiz['quiz_id']])) {
                            $counter = 1;
                            foreach ($candidate_data[$quiz['quiz_id']] as $candidate):
                        ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                    <td>
                                        <button class="delete-btn" onclick="deleteCandidate(<?php echo $candidate['id']; ?>, <?php echo $quiz['quiz_id']; ?>, this)">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                        <?php endforeach; } ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = "instructor_homepage.php";
            }
        }

        function addCandidate(quizId) {
    const candidateName = prompt("Enter the candidate's name:");
    const candidateEmail = prompt("Enter the candidate's email:");
    const candidateGender = prompt("Enter the candidate's gender (Male/Female/Others):");

    if (candidateName && candidateEmail && candidateGender) {
        const validGenders = ["Male", "Female", "Others"];
        if (!validGenders.includes(candidateGender)) {
            alert("Invalid gender! Please enter Male, Female, or Others.");
            return;
        }

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add_candidate&quiz_id=${quizId}&candidate_name=${candidateName}&candidate_email=${candidateEmail}&candidate_gender=${candidateGender}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tableBody = document.getElementById(`quiz-${quizId}`);
                const newRow = tableBody.insertRow();
                const rowCount = tableBody.rows.length;

                newRow.insertCell(0).textContent = rowCount; // Row number
                newRow.insertCell(1).textContent = candidateName; // Candidate Name
                newRow.insertCell(2).textContent = candidateEmail; // Candidate Email
                newRow.insertCell(3).textContent = candidateGender; // Candidate Gender

                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'delete-btn';
                deleteBtn.textContent = '🗑️';
                deleteBtn.onclick = function () {
                    deleteCandidate(data.candidate_id, quizId, newRow);
                };
                newRow.insertCell(4).appendChild(deleteBtn);

                alert("Candidate added successfully!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    } else {
        alert("Name, email, and gender are required!");
    }
}


function deleteCandidate(candidateId, quizId, row) {
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_candidate&quiz_id=${quizId}&candidate_id=${candidateId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Candidate removed successfully!");
            location.reload(); // Reload the page to reflect changes
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
    });
}

    </script>
</body>

</html>
