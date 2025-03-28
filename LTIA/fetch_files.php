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
    
    // Use selected year if provided, otherwise use current year
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');

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

    // Get MOV data for selected year
    $query = "SELECT * FROM mov WHERE barangay_id = :barangay_id AND year = :year";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_STR);
    $stmt->execute();
    $mov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mov) {
        echo json_encode(['error' => 'No MOV files found for selected year']);
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
        if (strpos($key, '_File') !== false && !empty($value)) {
            $response[$key] = $value;
        }
    }

    // Get verification statuses
    $verifyQuery = "SELECT * FROM movverify 
                    WHERE mov_id = :mov_id 
                    AND barangay_id = :barangay_id
                    AND year = :year";
    $stmt = $conn->prepare($verifyQuery);
    $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
    $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_STR);
    $stmt->execute();
    $verifications = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($verifications) {
        // Ensure threepeoplesorg verification is properly mapped
        if (isset($verifications['threepeoplesorg_verify'])) {
            $verifications['threepeoplesorg_verify'] = $verifications['threepeoplesorg_verify'];
        }
        $response['verifications'] = $verifications;
    } else {
        // Initialize verification status as 0 (unverified)
        $response['verifications'] = array_fill_keys([
            'IA_1a_pdf_verify', 'IA_1b_pdf_verify', 'IA_2a_pdf_verify',
            'IA_2b_pdf_verify', 'IA_2c_pdf_verify', 'IA_2d_pdf_verify',
            'IA_2e_pdf_verify', 'IB_1forcities_pdf_verify', 'IB_1aformuni_pdf_verify',
            'IB_1bformuni_pdf_verify', 'IB_2_pdf_verify', 'IB_3_pdf_verify',
            'IB_4_pdf_verify', 'IC_1_pdf_verify', 'IC_2_pdf_verify',
            'ID_1_pdf_verify', 'ID_2_pdf_verify', 'IIA_pdf_verify',
            'IIB_1_pdf_verify', 'IIB_2_pdf_verify', 'IIC_pdf_verify',
            'IIIA_pdf_verify', 'IIIB_pdf_verify', 'IIIC_1forcities_pdf_verify',
            'IIIC_1forcities2_pdf_verify', 'IIIC_1forcities3_pdf_verify',
            'IIIC_2formuni1_pdf_verify', 'IIIC_2formuni2_pdf_verify',
            'IIIC_2formuni3_pdf_verify', 'IIID_pdf_verify',
            'IV_forcities_pdf_verify', 'IV_muni_pdf_verify', 'V_1_pdf_verify',
            'threepeoplesorg_verify'  // Added this field
        ], 0);
    }

    if ($user_type === 'assessor') {
        // Get assessor's own rates for selected year and specific MOV
        $rateQuery = "SELECT * FROM movrate 
                     WHERE mov_id = :mov_id 
                     AND barangay = :barangay_id 
                     AND user_id = :user_id 
                     AND user_type = 'assessor'
                     AND year = :year
                     ORDER BY created_at DESC 
                     LIMIT 1";
        $stmt = $conn->prepare($rateQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_STR);
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get assessor's own remarks for selected year and specific MOV
        $remarkQuery = "SELECT * FROM movremark 
                       WHERE mov_id = :mov_id 
                       AND barangay = :barangay_id 
                       AND user_id = :user_id 
                       AND user_type = 'assessor'
                       AND year = :year
                       ORDER BY created_at DESC 
                       LIMIT 1";
        $stmt = $conn->prepare($remarkQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_STR);
        $stmt->execute();
        $remarks = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // For admin, get only admin ratings and remarks for selected year and specific MOV
        $rateQuery = "SELECT * FROM movrate 
                     WHERE mov_id = :mov_id 
                     AND barangay = :barangay_id
                     AND user_type = 'admin'
                     AND year = :year
                     ORDER BY created_at DESC 
                     LIMIT 1";
        $stmt = $conn->prepare($rateQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_STR);
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);

        $remarkQuery = "SELECT * FROM movremark 
                       WHERE mov_id = :mov_id 
                       AND barangay = :barangay_id
                       AND user_type = 'admin'
                       AND year = :year
                       ORDER BY created_at DESC 
                       LIMIT 1";
        $stmt = $conn->prepare($remarkQuery);
        $stmt->bindParam(':mov_id', $mov['id'], PDO::PARAM_INT);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_STR);
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
        // Convert any NULL values to empty strings for remarks
        $response['remarks'] = array_map(function($value) {
            return $value === null ? '' : $value;
        }, $remarks);
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
