<?php
$successMessage = "";
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
        if ($parts[2] === $currentMonthYear) {
            $blotterNumber = intval($parts[0]) + 1;
        } else {
            // Reset blotter number if month/year has changed
            $blotterNumber = 1;
        }

        // Format the case number
$caseNum = sprintf('%02d', $blotterNumber) . '-' . $parts[1] . '-' . $currentMonthYear;
    } else {
        // Handle unexpected format of $lastCaseNumber
        $caseNum = '01-000-' . date('my');
    }
}

if (isset($_POST['submit'])) {
    // Sanitize and validate user input
    $forTitle = $_POST['ForTitle'];
    $complainants = $_POST['CNames'];
    $respondents = $_POST['RspndtNames'];
    $complaintDesc = $_POST['CDesc'];
    $petition = $_POST['Petition'];
    $madeDate = $_POST['Mdate'];
    $receivedDate = $_POST['RDate'];
    $caseType = $_POST['CType'];
    $caseNum = $_POST['CNum'];
    $complainantAddress = $_POST['CAddress'];
    $respondentAddress = $_POST['RAddress'];    

    // Insert the complaint into the 'complaints' table with default values
    $stmt = $conn->prepare("INSERT INTO complaints (UserID, BarangayID, CNum, ForTitle, CNames, RspndtNames, CDesc, Petition, Mdate, RDate, CType, CStatus, CMethod, CAddress, RAddress) VALUES (:userID, :barangayID, :caseNum, :forTitle, :complainants, :respondents, :complaintDesc, :petition, :madeDate, :receivedDate, :caseType, 'Unsettled', 'Pending', :complainantAddress, :respondentAddress)"); // Updated query to include address fields
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
    $stmt->bindParam(':complainantAddress', $complainantAddress, PDO::PARAM_STR); // New line to bind complainant address
    $stmt->bindParam(':respondentAddress', $respondentAddress, PDO::PARAM_STR); // New line to bind respondent address

    if ($stmt->execute()) {
        // Get the ID of the last inserted complaint
        $lastInsertedId = $conn->lastInsertId();

        // Insert into case_progress table for the new complaint with default values
        $stmtCaseProgress = $conn->prepare("INSERT INTO case_progress (complaint_id, current_hearing) VALUES (:complaintId, '0')");
        $stmtCaseProgress->bindParam(':complaintId', $lastInsertedId, PDO::PARAM_INT);

        if ($stmtCaseProgress->execute()) {
            // Case progress updated successfully
            $successMessage = '<div class="alert alert-success" role="alert">
                Complaint Submitted Successfully!
            </div>';
        } else {
            // Failed to update case progress
            $successMessage = '<div class="alert alert-danger" role="alert">
                Failed to Update Case Progress. Contact Devs.
            </div>';
        }
    } else {
        // Failed to submit complaint
        $successMessage = '<div class="alert alert-danger" role="alert">
                Failed to Submit Complaint. Contact Devs.
              </div>';
    }
}


?>
