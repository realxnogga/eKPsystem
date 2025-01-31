<?php
session_start();
include '../connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['barangay_name'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    $barangay_name = $_POST['barangay_name'];

    // Get barangay ID
    $query = "SELECT id FROM barangays WHERE barangay_name = :barangay_name";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay_name', $barangay_name, PDO::PARAM_STR);
    $stmt->execute();
    $barangay = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$barangay) {
        echo json_encode(['error' => 'Barangay not found']);
        exit;
    }

    $barangay_id = $barangay['id'];

    // Get MOV data
    $query = "SELECT * FROM mov WHERE barangay_id = :barangay_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $stmt->execute();
    $mov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mov) {
        echo json_encode(['error' => 'No MOV files found']);
        exit;
    }

    // Prepare response
    $response = [
        'barangay_id' => $barangay_id,
        'mov_id' => $mov['id'],
        'year' => $mov['year']
    ];

    // Add file paths to response
    foreach ($mov as $key => $value) {
        if (strpos($key, '_pdf_File') !== false && !empty($value)) {
            $response[$key] = $value;
        }
    }

    if ($user_type === 'assessor') {
        // Get assessor's own rates
        $rateQuery = "SELECT * FROM movrate 
                     WHERE mov_id = :mov_id 
                     AND barangay = :barangay_id 
                     AND user_id = :user_id 
                     AND user_type = 'assessor'
                     ORDER BY created_at DESC 
                     LIMIT 1";
        $stmt = $conn->prepare($rateQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get assessor's own remarks
        $remarkQuery = "SELECT * FROM movremark 
                       WHERE mov_id = :mov_id 
                       AND barangay = :barangay_id 
                       AND user_id = :user_id 
                       AND user_type = 'assessor'
                       ORDER BY created_at DESC 
                       LIMIT 1";
        $stmt = $conn->prepare($remarkQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $remarks = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // For admin, get only admin ratings and remarks
        $rateQuery = "SELECT * FROM movrate 
                     WHERE mov_id = :mov_id 
                     AND barangay = :barangay_id
                     AND user_type = 'admin'
                     ORDER BY created_at DESC 
                     LIMIT 1";
        $stmt = $conn->prepare($rateQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);

        $remarkQuery = "SELECT * FROM movremark 
                       WHERE mov_id = :mov_id 
                       AND barangay = :barangay_id
                       AND user_type = 'admin'
                       ORDER BY created_at DESC 
                       LIMIT 1";
        $stmt = $conn->prepare($remarkQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->execute();
        $remarks = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Set default rates and remarks if none exist
    if ($rates) {
        $response['rates'] = $rates;
    } else {
        $response['rates'] = [
            'status' => 'pending',
            'IA_1a_pdf_rate' => '0',
            'IA_1b_pdf_rate' => '0',
            'IA_2a_pdf_rate' => '0',
            // Add all other rate fields with default '0'
        ];
    }

    if ($remarks) {
        $response['remarks'] = $remarks;
    } else {
        $response['remarks'] = [
            'IA_1a_pdf_remark' => '',
            'IA_1b_pdf_remark' => '',
            'IA_2a_pdf_remark' => '',
            // Add all other remark fields with empty string
        ];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
