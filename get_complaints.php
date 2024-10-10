<?php
include 'connection.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$userID = $_SESSION['user_id'];

$query = "SELECT * FROM complaints WHERE UserID = '$userID' AND IsArchived = 0 ORDER BY MDate DESC";
$result = $conn->query($query);

$complaints = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $complaints[] = $row;
}

echo json_encode($complaints);
?>
