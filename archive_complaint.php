<?php
include 'connection.php';

if (isset($_GET['id'])) {
    $complaintID = $_GET['id'];

    // Prepare the update statement to prevent SQL injection
    $query = "UPDATE complaints SET IsArchived = 1 WHERE id = :complaintID";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':complaintID', $complaintID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirect back to the complaints page after archiving
        header("Location: user_complaints.php");
        exit;
    } else {
        // Use errorInfo() method for detailed error information
        $errorInfo = $stmt->errorInfo();
        echo "Error archiving the complaint: " . $errorInfo[2];
    }
}
?>
