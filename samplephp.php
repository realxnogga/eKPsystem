<?php
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['image'])) {
    $imageData = $data['image'];
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);
    
    $filename = 'crop/' . time() . '.png';
    
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (file_put_contents($filename, $imageData)) {
        echo "Image saved successfully: $filename";
    } else {
        echo "Failed to save image.";
    }
} else {
    echo "No image data received.";
}
?>
