<?php
session_start();
include '../connection.php';

$userID = $_SESSION['user_id'] ?? '';
$barangay_id = $_SESSION['barangay_id'] ?? '';
$currentYear = date('Y'); // Get the current year

try {
    // Check if a submission already exists in 'mov' for this user, barangay, and year
    $checkSubmissionQuery = "SELECT * FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id AND year = :year";
    $checkStmt = $conn->prepare($checkSubmissionQuery);
    $checkStmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
    $checkStmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
    $checkStmt->execute();
    $existingSubmission = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingSubmission) {
        $_SESSION['modal_message'] = 'You have already submitted files for this year.';
    } else {
        // Check if there is a draft for this user and barangay
        $checkDraftQuery = "SELECT * FROM movdraft_file WHERE user_id = :user_id AND barangay_id = :barangay_id";
        $checkStmt = $conn->prepare($checkDraftQuery);
        $checkStmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
        $checkStmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $draftData = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($draftData) {
            // Add 'year' column value
            $draftData['year'] = $currentYear;

            // Remove the 'date' key if not present in 'mov'
            unset($draftData['date']);

            // Build the INSERT query for mov
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
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['modal_message'] = 'An error occurred during submission.';
}

header('Location: ltia_dashboard.php');
exit();
?>
