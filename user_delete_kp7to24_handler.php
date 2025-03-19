

<?php
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['formID'])) {
      $formID = $data['formID'];

    
      // Perform deletion logic, e.g., database query
      $query = "DELETE FROM hearings WHERE id = :id";
      $stmt = $conn->prepare($query);
    
      // Bind parameters
      $stmt->bindParam(':id', $formID, PDO::PARAM_INT);
    
      if ($stmt->execute()) {
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false]);
      }
    } else {
      echo json_encode(['success' => false]);
    }
    exit;
    }
?>
