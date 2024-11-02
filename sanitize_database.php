<?php
session_start(); // Start the session at the top

include "connection.php"; // Ensure this path is correct

try {
    // Step 1: Query to find all ids in `complaints` table where `Raddress` is "undefined"
    $query = "SELECT id FROM complaints WHERE Raddress = 'undefined'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($complaints) > 0) {
        foreach ($complaints as $row) {
            $complaint_id = $row['id'];
            
            // Step 2: Delete matching rows in `case_progress` table
            $deleteProgressQuery = "DELETE FROM case_progress WHERE complaint_id = :complaint_id";
            $stmtDeleteProgress = $conn->prepare($deleteProgressQuery);
            $stmtDeleteProgress->execute(['complaint_id' => $complaint_id]);
            
            // Step 3: Delete the row in `complaints` table with the same id
            $deleteComplaintQuery = "DELETE FROM complaints WHERE id = :complaint_id";
            $stmtDeleteComplaint = $conn->prepare($deleteComplaintQuery);
            $stmtDeleteComplaint->execute(['complaint_id' => $complaint_id]);
        }
        $_SESSION['message'] = "Complaints with undefined Raddress have been deleted.";
    } else {
        $_SESSION['message'] = "No complaints found with Raddress set to 'undefined'.";
    }
} catch (PDOException $e) {
    // Handle any errors gracefully
    $_SESSION['message'] = "Error: " . $e->getMessage();
}

// Redirect to user_complaints.php after processing
header("Location: user_complaints.php");
exit(); // Always call exit after header redirection
