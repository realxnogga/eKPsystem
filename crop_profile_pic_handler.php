<?php
session_start();
include 'connection.php';

$userId = $_SESSION['user_id'];
$uploadDir = 'profile_pictures/';

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['image'])) {
    $imageData = $data['image'];
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);
    
    $filename = $uploadDir . $userId . '.png';

    $imagename = $userId . '.png';
    
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (file_put_contents($filename, $imageData)) {
        echo "Image saved successfully: $imagename";

        $updateStmt = $conn->prepare("UPDATE users SET profile_picture = :profile_pic WHERE id = :user_id");
        $updateStmt->bindParam(':profile_pic', $imagename, PDO::PARAM_STR);
        $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $updateStmt->execute();

    } else {
        echo "Failed to save image.";
    }
} else {
    echo "No image data received.";
}
?>
