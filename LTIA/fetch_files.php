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
    $current_year = date('Y'); // Get current year

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

    // Get MOV data for current year only
    $query = "SELECT * FROM mov WHERE barangay_id = :barangay_id AND year = :current_year";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $stmt->bindParam(':current_year', $current_year, PDO::PARAM_STR);
    $stmt->execute();
    $mov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mov) {
        echo json_encode(['error' => 'No MOV files found for current year']);
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
        // Get assessor's own rates for current year and specific MOV
        $rateQuery = "SELECT * FROM movrate 
                     WHERE mov_id = :mov_id 
                     AND barangay = :barangay_id 
                     AND user_id = :user_id 
                     AND user_type = 'assessor'
                     AND year = :current_year
                     ORDER BY created_at DESC 
                     LIMIT 1";
        $stmt = $conn->prepare($rateQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':current_year', $current_year, PDO::PARAM_STR);
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get assessor's own remarks for current year and specific MOV
        $remarkQuery = "SELECT * FROM movremark 
                       WHERE mov_id = :mov_id 
                       AND barangay = :barangay_id 
                       AND user_id = :user_id 
                       AND user_type = 'assessor'
                       AND year = :current_year
                       ORDER BY created_at DESC 
                       LIMIT 1";
        $stmt = $conn->prepare($remarkQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':current_year', $current_year, PDO::PARAM_STR);
        $stmt->execute();
        $remarks = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // For admin, get only admin ratings and remarks for current year and specific MOV
        $rateQuery = "SELECT * FROM movrate 
                     WHERE mov_id = :mov_id 
                     AND barangay = :barangay_id
                     AND user_type = 'admin'
                     AND year = :current_year
                     ORDER BY created_at DESC 
                     LIMIT 1";
        $stmt = $conn->prepare($rateQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':current_year', $current_year, PDO::PARAM_STR);
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);

        $remarkQuery = "SELECT * FROM movremark 
                       WHERE mov_id = :mov_id 
                       AND barangay = :barangay_id
                       AND user_type = 'admin'
                       AND year = :current_year
                       ORDER BY created_at DESC 
                       LIMIT 1";
        $stmt = $conn->prepare($remarkQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':current_year', $current_year, PDO::PARAM_STR);
        $stmt->execute();
        $remarks = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Set default rates and remarks if none exist
    if ($rates) {
        // Convert any NULL values to empty strings for rates
        $response['rates'] = array_map(function($value) {
            return $value === null ? '' : $value;
        }, $rates);
    } else {
        $response['rates'] = [
            'IA_1a_pdf_rate' => '',
            'IA_1b_pdf_rate' => '',
            'IA_2a_pdf_rate' => '',
            'IA_2b_pdf_rate' => '',
            'IA_2c_pdf_rate' => '',
            'IA_2d_pdf_rate' => '',
            'IA_2e_pdf_rate' => '',
            'IB_1forcities_pdf_rate' => '',
            'IB_1aformuni_pdf_rate' => '',
            'IB_1bformuni_pdf_rate' => '',
            'IB_2_pdf_rate' => '',
            'IB_3_pdf_rate' => '',
            'IB_4_pdf_rate' => '',
            'IC_1_pdf_rate' => '',
            'IC_2_pdf_rate' => '',
            'ID_1_pdf_rate' => '',
            'ID_2_pdf_rate' => '',
            'IIA_pdf_rate' => '',
            'IIB_1_pdf_rate' => '',
            'IIB_2_pdf_rate' => '',
            'IIC_pdf_rate' => '',
            'IIIA_pdf_rate' => '',
            'IIIB_pdf_rate' => '',
            'IIIC_1forcities_pdf_rate' => '',
            'IIIC_1forcities2_pdf_rate' => '',
            'IIIC_1forcities3_pdf_rate' => '',
            'IIIC_2formuni1_pdf_rate' => '',
            'IIIC_2formuni2_pdf_rate' => '',
            'IIIC_2formuni3_pdf_rate' => '',
            'IIID_pdf_rate' => '',
            'IV_forcities_pdf_rate' => '',
            'IV_muni_pdf_rate' => '',
            'V_1_pdf_rate' => '',
            'threepeoplesorg_rate' => ''
        ];
    }

    if ($remarks) {
        $response['remarks'] = $remarks;
    } else {
        $response['remarks'] = [
            'IA_1a_pdf_remark' => '',
            'IA_1b_pdf_remark' => '',
            'IA_2a_pdf_remark' => '',
            'IA_2b_pdf_remark' => '',
            'IA_2c_pdf_remark' => '',
            'IA_2d_pdf_remark' => '',
            'IA_2e_pdf_remark' => '',
            'IB_1forcities_pdf_remark' => '',
            'IB_1aformuni_pdf_remark' => '',
            'IB_1bformuni_pdf_remark' => '',
            'IB_2_pdf_remark' => '',
            'IB_3_pdf_remark' => '',
            'IB_4_pdf_remark' => '',
            'IC_1_pdf_remark' => '',
            'IC_2_pdf_remark' => '',
            'ID_1_pdf_remark' => '',
            'ID_2_pdf_remark' => '',
            'IIA_pdf_remark' => '',
            'IIB_1_pdf_remark' => '',
            'IIB_2_pdf_remark' => '',
            'IIC_pdf_remark' => '',
            'IIIA_pdf_remark' => '',
            'IIIB_pdf_remark' => '',
            'IIIC_1forcities_pdf_remark' => '',
            'IIIC_1forcities2_pdf_remark' => '',
            'IIIC_1forcities3_pdf_remark' => '',
            'IIIC_2formuni1_pdf_remark' => '',
            'IIIC_2formuni2_pdf_remark' => '',
            'IIIC_2formuni3_pdf_remark' => '',
            'IIID_pdf_remark' => '',
            'IV_forcities_pdf_remark' => '',
            'IV_muni_pdf_remark' => '',
            'V_1_pdf_remark' => '',
            'threepeoplesorg_remark' => ''
        ];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
