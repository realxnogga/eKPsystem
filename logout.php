<?php
session_start();
include 'connection.php';

$sql = 'UPDATE users SET is_loggedin = 0 WHERE id = :id';
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);

if ($stmt->execute()) {
    session_destroy();
    session_unset();

    header("Location: login.php");
    exit;
}
