<?php
session_start(); // Always start the session at the top
header('Content-Type: application/json');

include_once("connection.php"); // Include database connection

if (!isset($conn)) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    die(json_encode(["success" => false, "message" => "You must be logged in to submit a complaint."]));
}

$userID = $_SESSION['user_id'];
$barangayID = $_SESSION['barangay_id'];

// Get the last used Case Number
$query = "SELECT CNum AS lastCaseNumber FROM complaints WHERE UserID = :userID ORDER BY Mdate DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);
$lastCaseNumber = $row ? $row['lastCaseNumber'] : null;

if (!$lastCaseNumber) {
    $caseNum = '01-000-' . date('my');
} else {
    // Extract the parts of the last case number
    $parts = explode('-', $lastCaseNumber);
    
    if (count($parts) === 3) {
        $currentMonthYear = date('my');
        
        // If current month/year is the same as last case, increment blotter number
        $blotterNumber = ($parts[2] === $currentMonthYear) ? (intval($parts[0]) + 1) : 1;

        // Format the case number
        $caseNum = sprintf('%02d', $blotterNumber) . '-' . $parts[1] . '-' . $currentMonthYear;
    } else {
        // Handle unexpected format of $lastCaseNumber
        $caseNum = '01-000-' . date('my');
    }
}

// Assuming all form data is sent in POST request
$inputData = json_decode(file_get_contents('php://input'), true);
$rawInput = file_get_contents('php://input');
error_log("Raw input: " . $rawInput);  // This logs the raw input to your error log for debugging

$inputData = json_decode($rawInput, true);
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

// Check if the JSON decode was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON format']);
    exit;
}

// Proceed with handling the data

// Check for JSON decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());  // Log JSON error message
    die(json_encode(['success' => false, 'message' => 'Invalid JSON format.']));
}


if ($inputData) {
    // Sanitize and validate user input
    $forTitle = filter_var($inputData['ForTitle'], FILTER_SANITIZE_STRING);
    $complainants = filter_var($inputData['CNames'], FILTER_SANITIZE_STRING);
    $respondents = filter_var($inputData['RspndtNames'], FILTER_SANITIZE_STRING);
    $complaintDesc = filter_var($inputData['CDesc'], FILTER_SANITIZE_STRING);
    $petition = filter_var($inputData['Petition'], FILTER_SANITIZE_STRING);
    $madeDate = $inputData['Mdate']; // Validate this input
    $receivedDate = $inputData['RDate']; // Validate this input
    $caseType = filter_var($inputData['CType'], FILTER_SANITIZE_STRING);
    $complainantAddress = filter_var($inputData['CAddress'], FILTER_SANITIZE_STRING);
    $respondentAddress = filter_var($inputData['RAddress'], FILTER_SANITIZE_STRING);

    // Insert the complaint into the 'complaints' table
    $stmt = $conn->prepare("INSERT INTO complaints (UserID, BarangayID, CNum, ForTitle, CNames, RspndtNames, CDesc, Petition, Mdate, RDate, CType, CStatus, CMethod, CAddress, RAddress) VALUES (:userID, :barangayID, :caseNum, :forTitle, :complainants, :respondents, :complaintDesc, :petition, :madeDate, :receivedDate, :caseType, 'Unsettled', 'Pending', :complainantAddress, :respondentAddress)");

    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':barangayID', $barangayID, PDO::PARAM_INT);
    $stmt->bindParam(':caseNum', $caseNum, PDO::PARAM_STR);
    $stmt->bindParam(':forTitle', $forTitle, PDO::PARAM_STR);
    $stmt->bindParam(':complainants', $complainants, PDO::PARAM_STR);
    $stmt->bindParam(':respondents', $respondents, PDO::PARAM_STR);
    $stmt->bindParam(':complaintDesc', $complaintDesc, PDO::PARAM_STR);
    $stmt->bindParam(':petition', $petition, PDO::PARAM_STR);
    $stmt->bindParam(':madeDate', $madeDate, PDO::PARAM_STR);
    $stmt->bindParam(':receivedDate', $receivedDate, PDO::PARAM_STR);
    $stmt->bindParam(':caseType', $caseType, PDO::PARAM_STR);
    $stmt->bindParam(':complainantAddress', $complainantAddress, PDO::PARAM_STR);
    $stmt->bindParam(':respondentAddress', $respondentAddress, PDO::PARAM_STR);

    // Execute the statement and return a JSON response
    try {
        if ($stmt->execute()) {
            // Get the ID of the last inserted complaint
            $lastInsertedId = $conn->lastInsertId();

            // Insert into case_progress table
            $stmtCaseProgress = $conn->prepare("INSERT INTO case_progress (complaint_id, current_hearing) VALUES (:complaintId, '0')");
            $stmtCaseProgress->bindParam(':complaintId', $lastInsertedId, PDO::PARAM_INT);

            if ($stmtCaseProgress->execute()) {
                echo json_encode(['success' => true, 'message' => 'Complaint Submitted Successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to Update Case Progress. Contact Devs.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to Submit Complaint. Contact Devs.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>