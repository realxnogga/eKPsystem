<?php
session_start();
header('Content-Type: application/json');

include_once("connection.php");

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
    $caseNum = sprintf('%03d', 1) . '-' . sprintf('%02d', $barangayID) . '-' . date('my');
} else {
    $parts = explode('-', $lastCaseNumber);
    $currentMonthYear = date('my');

    if (count($parts) === 3 && $parts[2] === $currentMonthYear) {
        $blotterNumber = intval($parts[0]) + 1;
    } else {
        $blotterNumber = 1;
    }

    $caseNum = sprintf('%03d', $blotterNumber) . '-' . sprintf('%02d', $barangayID) . '-' . $currentMonthYear;
}

$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['success' => false, 'message' => 'Invalid JSON format.']));
}

$respondents = isset($inputData['RspndtNames']) ? filter_var($inputData['RspndtNames'], FILTER_SANITIZE_STRING) : '';

if (empty($respondents)) {
    $complaintID = $inputData['complaint_id'] ?? null;

    if ($complaintID) {
        $deleteQuery = "DELETE FROM complaints WHERE id = :complaintID";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':complaintID', $complaintID, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully due to missing respondents.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete record.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Complaint ID missing for deletion.']);
    }
    exit;
}

// Proceed to process and insert the complaint data
try {
    $forTitle = filter_var($inputData['ForTitle'], FILTER_SANITIZE_STRING);
    $complainants = filter_var($inputData['CNames'], FILTER_SANITIZE_STRING);
    $complaintDesc = filter_var($inputData['CDesc'], FILTER_SANITIZE_STRING);
    $petition = filter_var($inputData['Petition'], FILTER_SANITIZE_STRING);
    $madeDate = $inputData['Mdate'];
    $receivedDate = $inputData['RDate'];
    $caseType = filter_var($inputData['CType'], FILTER_SANITIZE_STRING);
    $complainantAddress = filter_var($inputData['CAddress'], FILTER_SANITIZE_STRING);
    $respondentAddress = filter_var($inputData['RAddress'], FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("INSERT INTO complaints (UserID, BarangayID, CNum, ForTitle, CNames, RspndtNames, CDesc, Petition, Mdate, RDate, CType, CStatus, CMethod, CAddress, RAddress) 
                            VALUES (:userID, :barangayID, :caseNum, :forTitle, :complainants, :respondents, :complaintDesc, :petition, :madeDate, :receivedDate, :caseType, 'Unsettled', 'Pending', :complainantAddress, :respondentAddress)");

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

    if ($stmt->execute()) {
        $lastInsertedId = $conn->lastInsertId();
        
        $stmtCaseProgress = $conn->prepare("INSERT INTO case_progress (complaint_id, current_hearing) VALUES (:complaintId, '0')");
        $stmtCaseProgress->bindParam(':complaintId', $lastInsertedId, PDO::PARAM_INT);

        if ($stmtCaseProgress->execute()) {
            echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update case progress.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit complaint.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
