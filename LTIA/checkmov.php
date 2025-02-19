<?php
session_start();
include '../connection.php';

$user_id = $_POST['user_id'];
$barangay_id = $_POST['barangay_id'];
$current_year = date('Y');

$sql = "SELECT COUNT(*) FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id AND year = :year";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
$stmt->bindParam(':year', $current_year, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->fetchColumn() > 0) {
    echo 'exists';
} else {
    echo 'not_exists';
}
?>