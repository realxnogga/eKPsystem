<?php
session_start();

include '../connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'assessor'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['mov_id', 'barangay_id'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required values: ' . $field]);
            exit;
        }
    }

    $mov_id = $_POST['mov_id'];
    $barangay_id = $_POST['barangay_id'];
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    $current_date = date('Y-m-d H:i:s');

    $conn->beginTransaction();

    // Handle Rates
    $rateQuery = "SELECT id FROM movrate 
                  WHERE mov_id = :mov_id 
                  AND barangay = :barangay_id 
                  AND user_id = :user_id 
                  AND user_type = :user_type";
    
    $stmt = $conn->prepare($rateQuery);
    $stmt->bindParam(':mov_id', $mov_id, PDO::PARAM_INT);
    $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
    $stmt->execute();
    $existing_rate = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare rate data
    $rate_fields = [
        'IA_1a_pdf_rate', 'IA_1b_pdf_rate', 'IA_2a_pdf_rate',
        'IA_2b_pdf_rate', 'IA_2c_pdf_rate', 'IA_2d_pdf_rate', 'IA_2e_pdf_rate',
        'IB_1forcities_pdf_rate', 'IB_1aformuni_pdf_rate', 'IB_1bformuni_pdf_rate', 'IB_2_pdf_rate', 'IB_3_pdf_rate', 'IB_4_pdf_rate',
        'IC_1_pdf_rate', 'IC_2_pdf_rate', 'ID_1_pdf_rate', 'ID_2_pdf_rate', 'IIA_pdf_rate', 'IIB_1_pdf_rate', 'IIB_2_pdf_rate',
        'IIC_pdf_rate', 'IIIA_pdf_rate', 'IIIB_pdf_rate', 'IIIC_1forcities_pdf_rate', 'IIIC_1forcities2_pdf_rate',
        'IIIC_1forcities3_pdf_rate', 'IIIC_2formuni1_pdf_rate', 'IIIC_2formuni2_pdf_rate', 'IIIC_2formuni3_pdf_rate',
        'IIID_pdf_rate', 'IV_forcities_pdf_rate', 'IV_muni_pdf_rate', 'V_1_pdf_rate', 'threepeoplesorg_rate', 'total'
    ];

    $rate_data = [];
    foreach ($rate_fields as $field) {
        // Check if the field exists and is not empty
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $rate_data[$field] = $_POST[$field];
        } else {
            $rate_data[$field] = null;
        }
    }

    if ($existing_rate) {
        // Update existing rate
        $updateRate = "UPDATE movrate SET ";
        foreach ($rate_data as $field => $value) {
            $updateRate .= "$field = :$field, ";
        }
        $updateRate .= "last_modified_at = :last_modified_at WHERE id = :id";
        
        $stmt = $conn->prepare($updateRate);
        foreach ($rate_data as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        $stmt->bindParam(':last_modified_at', $current_date);
        $stmt->bindParam(':id', $existing_rate['id']);
    } else {
        // Insert new rate
        $fields = array_merge(array_keys($rate_data), ['mov_id', 'barangay', 'user_id', 'user_type', 'created_at', 'last_modified_at']);
        $values = array_fill(0, count($fields), '?');
        
        $insertRate = "INSERT INTO movrate (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        
        $stmt = $conn->prepare($insertRate);
        $i = 1;
        foreach ($rate_data as $value) {
            $stmt->bindValue($i++, $value);
        }
        $stmt->bindValue($i++, $mov_id);
        $stmt->bindValue($i++, $barangay_id);
        $stmt->bindValue($i++, $user_id);
        $stmt->bindValue($i++, $user_type);
        $stmt->bindValue($i++, $current_date);
        $stmt->bindValue($i++, $current_date);
    }
    $stmt->execute();

    // Handle Remarks (similar structure)
    $remarkQuery = "SELECT id FROM movremark 
                   WHERE mov_id = :mov_id 
                   AND barangay = :barangay_id 
                   AND user_id = :user_id 
                   AND user_type = :user_type";
    
    $stmt = $conn->prepare($remarkQuery);
    $stmt->bindParam(':mov_id', $mov_id, PDO::PARAM_INT);
    $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
    $stmt->execute();
    $existing_remark = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare remark data
    $remark_fields = [
        'IA_1a_pdf_remark', 'IA_1b_pdf_remark', 'IA_2a_pdf_remark',
        'IA_2b_pdf_remark', 'IA_2c_pdf_remark', 'IA_2d_pdf_remark', 'IA_2e_pdf_remark',
        'IB_1forcities_pdf_remark', 'IB_1aformuni_pdf_remark', 'IB_1bformuni_pdf_remark', 'IB_2_pdf_remark', 'IB_3_pdf_remark', 'IB_4_pdf_remark',
        'IC_1_pdf_remark', 'IC_2_pdf_remark', 'ID_1_pdf_remark', 'ID_2_pdf_remark', 'IIA_pdf_remark', 'IIB_1_pdf_remark', 'IIB_2_pdf_remark',
        'IIC_pdf_remark', 'IIIA_pdf_remark', 'IIIB_pdf_remark', 'IIIC_1forcities_pdf_remark', 'IIIC_1forcities2_pdf_remark',
        'IIIC_1forcities3_pdf_remark', 'IIIC_2formuni1_pdf_remark', 'IIIC_2formuni2_pdf_remark', 'IIIC_2formuni3_pdf_remark',
        'IIID_pdf_remark', 'IV_forcities_pdf_remark', 'IV_muni_pdf_remark', 'V_1_pdf_remark', 'threepeoplesorg_remark'
    ];

    $remark_data = [];
    foreach ($remark_fields as $field) {
        $remark_data[$field] = $_POST[$field] ?? '';
    }

    if ($existing_remark) {
        // Update existing remark
        $updateRemark = "UPDATE movremark SET ";
        foreach ($remark_data as $field => $value) {
            $updateRemark .= "$field = :$field, ";
        }
        $updateRemark .= "last_modified_at = :last_modified_at WHERE id = :id";
        
        $stmt = $conn->prepare($updateRemark);
        foreach ($remark_data as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        $stmt->bindParam(':last_modified_at', $current_date);
        $stmt->bindParam(':id', $existing_remark['id']);
    } else {
        // Insert new remark
        $fields = array_merge(array_keys($remark_data), ['mov_id', 'barangay', 'user_id', 'user_type', 'created_at', 'last_modified_at']);
        $values = array_fill(0, count($fields), '?');
        
        $insertRemark = "INSERT INTO movremark (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        
        $stmt = $conn->prepare($insertRemark);
        $i = 1;
        foreach ($remark_data as $value) {
            $stmt->bindValue($i++, $value);
        }
        $stmt->bindValue($i++, $mov_id);
        $stmt->bindValue($i++, $barangay_id);
        $stmt->bindValue($i++, $user_id);
        $stmt->bindValue($i++, $user_type);
        $stmt->bindValue($i++, $current_date);
        $stmt->bindValue($i++, $current_date);
    }
    $stmt->execute();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Changes saved successfully']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Failed to save changes: ' . $e->getMessage()]);
}
?>
