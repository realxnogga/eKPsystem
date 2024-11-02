<?php
session_start();
include '../connection.php';

$userID = $_SESSION['user_id'] ?? '';
$barangay_id = $_SESSION['barangay_id'] ?? '';

try {
    // Check if there is a draft for this user and barangay
    $checkDraftQuery = "SELECT * FROM movdraft_file WHERE user_id = :user_id AND barangay_id = :barangay_id";
    $checkStmt = $conn->prepare($checkDraftQuery);
    $checkStmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
    $checkStmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $draftData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // Check last submission date from mov table
    $lastSubmissionQuery = "SELECT submission_date FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id ORDER BY submission_date DESC LIMIT 1";
    $lastSubmissionStmt = $conn->prepare($lastSubmissionQuery);
    $lastSubmissionStmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
    $lastSubmissionStmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $lastSubmissionStmt->execute();
    $lastSubmission = $lastSubmissionStmt->fetch(PDO::FETCH_ASSOC);

    // Check if one day has passed since the last submission
    if ($lastSubmission) {
        $lastSubmissionDate = new DateTime($lastSubmission['submission_date']);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($lastSubmissionDate);

        if ($interval->d < 1 && $lastSubmissionDate->format('Y-m-d') === $currentDate->format('Y-m-d')) {
            $_SESSION['modal_message'] = 'You can only submit once per day.';
            header('Location: LTIAdashboard.php');
            exit();
        }
    }

    if ($draftData) {
        // Add the current date for submission_date
        $draftData['submission_date'] = date('Y-m-d H:i:s');

        // Build the INSERT query for `mov`
        $columns = array_keys($draftData);
        $columnList = implode(', ', $columns);
        $placeholderList = ':' . implode(', :', $columns);

        $insertQuery = "INSERT INTO mov ($columnList) VALUES ($placeholderList)";
        $insertStmt = $conn->prepare($insertQuery);

        // Bind values from draft data to the insert statement
        foreach ($draftData as $column => $value) {
            $insertStmt->bindValue(":$column", $value);
        }

        if ($insertStmt->execute()) {
            // Optional: Delete the draft after successful transfer
            $deleteDraftQuery = "DELETE FROM movdraft_file WHERE user_id = :user_id AND barangay_id = :barangay_id";
            $deleteStmt = $conn->prepare($deleteDraftQuery);
            $deleteStmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
            $deleteStmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            $_SESSION['modal_message'] = 'Files submitted successfully!';
        } else {
            $_SESSION['modal_message'] = 'Failed to submit files.';
        }
    } else {
        $_SESSION['modal_message'] = 'No draft found to submit.';
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['modal_message'] = 'An error occurred during submission.';
}

header('Location: LTIAdashboard.php');
exit();
?>
