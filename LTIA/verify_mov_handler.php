<?php
session_start();
include '../connection.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'assessor'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    if (!isset($_POST['field']) || !isset($_POST['mov_id']) || !isset($_POST['barangay_id'])) {
        throw new Exception('Missing required parameters');
    }

    $field = $_POST['field'];
    $mov_id = $_POST['mov_id'];
    $barangay_id = $_POST['barangay_id'];
    $current_date = date('Y-m-d H:i:s');

    // First check if a record exists and get current verification status
    $checkSql = "SELECT id, $field as current_status FROM movverify WHERE mov_id = :mov_id AND barangay_id = :barangay_id";
    $stmt = $conn->prepare($checkSql);
    $stmt->bindParam(':mov_id', $mov_id);
    $stmt->bindParam(':barangay_id', $barangay_id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Toggle the status (if 1 make it 0, if 0 make it 1)
        $newStatus = $existing['current_status'] ? 0 : 1;
        
        // Update existing record
        $sql = "UPDATE movverify SET 
                $field = :new_status,
                last_modified_at = :last_modified_at 
                WHERE mov_id = :mov_id 
                AND barangay_id = :barangay_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':new_status', $newStatus);
    } else {
        // Create new record with all verify fields set to 0 by default
        $sql = "INSERT INTO movverify (
                mov_id, 
                barangay_id, 
                IA_1a_pdf_verify, IA_1b_pdf_verify, IA_2a_pdf_verify,
                IA_2b_pdf_verify, IA_2c_pdf_verify, IA_2d_pdf_verify, IA_2e_pdf_verify,
                IB_1forcities_pdf_verify, IB_1aformuni_pdf_verify, IB_1bformuni_pdf_verify,
                IB_2_pdf_verify, IB_3_pdf_verify, IB_4_pdf_verify,
                IC_1_pdf_verify, IC_2_pdf_verify,
                ID_1_pdf_verify, ID_2_pdf_verify,
                IIA_pdf_verify, IIB_1_pdf_verify, IIB_2_pdf_verify,
                IIC_pdf_verify, IIIA_pdf_verify, IIIB_pdf_verify,
                IIIC_1forcities_pdf_verify, IIIC_1forcities2_pdf_verify, IIIC_1forcities3_pdf_verify,
                IIIC_2formuni1_pdf_verify, IIIC_2formuni2_pdf_verify, IIIC_2formuni3_pdf_verify,
                IIID_pdf_verify, IV_forcities_pdf_verify, IV_muni_pdf_verify,
                V_1_pdf_verify, threepeoplesorg_verify,
                created_at, last_modified_at
            ) VALUES (
                :mov_id, :barangay_id,
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                0, 0, 0, 0,
                :created_at, :last_modified_at
            )";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':created_at', $current_date);
        $newStatus = 1; // For new records, set to 1 since we're verifying
    }

    $stmt->bindParam(':last_modified_at', $current_date);
    $stmt->bindParam(':mov_id', $mov_id);
    $stmt->bindParam(':barangay_id', $barangay_id);
    
    if ($stmt->execute()) {
        // If it was an insert, we need to update the specific field to 1
        if (!$existing) {
            $updateSql = "UPDATE movverify SET 
                         $field = :new_status 
                         WHERE mov_id = :mov_id 
                         AND barangay_id = :barangay_id";
            $stmt = $conn->prepare($updateSql);
            $stmt->bindParam(':new_status', $newStatus);
            $stmt->bindParam(':mov_id', $mov_id);
            $stmt->bindParam(':barangay_id', $barangay_id);
            $stmt->execute();
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => $newStatus ? 'Item verified successfully' : 'Item unverified successfully',
            'verified' => $newStatus
        ]);
    } else {
        throw new Exception('Failed to update verification status');
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}