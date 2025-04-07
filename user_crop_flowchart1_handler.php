<?php
session_start();
include 'connection.php';

$userId = $_SESSION['user_id'];
$uploadDir = 'flowchart_image1/';

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
        
        //  Check if the user already has a flowchart image
        $countrowquery = "SELECT COUNT(*) FROM user_flowchart1 WHERE user_id = :user_id";
        $checkRowStmt = $conn->prepare($countrowquery);
        $checkRowStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $checkRowStmt->execute();
        $rowCount = (int) $checkRowStmt->fetchColumn();

        if ($rowCount === 0) {
            $insertStmt = $conn->prepare("INSERT INTO user_flowchart1 (user_id, flowchart_image1) VALUES (:user_id, :flowchart_image)");
            $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $insertStmt->bindParam(':flowchart_image', $imagename, PDO::PARAM_STR);
            $insertStmt->execute();
        } else {
            $updateStmt = $conn->prepare("UPDATE user_flowchart1 SET flowchart_image1 = :flowchart_image WHERE user_id = :user_id");
            $updateStmt->bindParam(':flowchart_image', $imagename, PDO::PARAM_STR);
            $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $updateStmt->execute();
        }

        echo "Image saved successfully: $imagename";

    } else {
        echo "Failed to save image.";
    }
} else {
    echo "No image data received.";
}
?>
