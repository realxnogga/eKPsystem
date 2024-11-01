<?php
session_start();
include '../connection.php';

// Fetch user ID and barangay ID from session
$userID = $_SESSION['user_id'] ?? '';
$barangay_id = $_SESSION['barangay_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        // Handle Update Logic for 'Update' button

        // Example: Update some fields in the `mov` table or any other table
        $update_query = "UPDATE mov SET some_column = :some_value WHERE barangay_id = :barangay_id AND user_id = :user_id";
        $stmt = $conn->prepare($update_query);
        $stmt->bindParam(':some_value', $some_value);  // Replace with actual form data
        $stmt->bindParam(':barangay_id', $barangay_id);
        $stmt->bindParam(':user_id', $userID);

        if ($stmt->execute()) {
            echo "<script>alert('Update successful!'); window.location.href='your_redirect_page.php';</script>";
        } else {
            echo "<script>alert('Error updating record.');</script>";
        }

    } elseif (isset($_POST['submit'])) {
        // Handle Insert Logic for 'Submit' button

        // Example: Insert into the `mov` table or another table
        $insert_query = "INSERT INTO another_table (user_id, barangay_id, other_columns) VALUES (:user_id, :barangay_id, :other_values)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bindParam(':user_id', $userID);
        $stmt->bindParam(':barangay_id', $barangay_id);
        $stmt->bindParam(':other_values', $other_values);  // Replace with actual form data

        if ($stmt->execute()) {
            echo "<script>alert('Form submitted successfully!'); window.location.href='your_redirect_page.php';</script>";
        } else {
            echo "<script>alert('Error submitting form.');</script>";
        }
    }
}
?>
