<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// Define allowed file columns
$allowed_columns = [
    'IA_1a_pdf_File', 'IA_1b_pdf_File', 'IA_2a_pdf_File', 'IA_2b_pdf_File',
    'IA_2c_pdf_File', 'IA_2d_pdf_File', 'IA_2e_pdf_File', 'IB_1forcities_pdf_File',
    'IB_1aformuni_pdf_File', 'IB_1bformuni_pdf_File', 'IB_2_pdf_File', 'IB_3_pdf_File',
    'IB_4_pdf_File', 'IC_1_pdf_File', 'IC_2_pdf_File', 'ID_1_pdf_File', 'ID_2_pdf_File',
    'IIA_pdf_File', 'IIB_1_pdf_File', 'IIB_2_pdf_File', 'IIC_pdf_File', 'IIIA_pdf_File',
    'IIIB_pdf_File', 'IIIC_1forcities_pdf_File', 'IIIC_1forcities2_pdf_File',
    'IIIC_1forcities3_pdf_File', 'IIIC_2formuni1_pdf_File', 'IIIC_2formuni2_pdf_File',
    'IIIC_2formuni3_pdf_File', 'IIID_pdf_File', 'IV_forcities_pdf_File', 'IV_muni_pdf_File',
    'V_1_pdf_File', 'threepeoplesorg_File'
];

// Fetch existing file data
$sql = "SELECT " . implode(', ', $allowed_columns) . " FROM movdraft_file WHERE user_id = :user_id AND barangay_id = :barangay_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$file_changed = false;
$upload_dir = 'movfolder/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process each file
    foreach ($allowed_columns as $column) {
        if (isset($_FILES[$column]) && $_FILES[$column]['error'] === UPLOAD_ERR_OK) {
            $file_name = time() . '_' . basename($_FILES[$column]['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES[$column]['tmp_name'], $file_path)) {
                $row[$column] = $file_name;
                $file_changed = true;
            }
        } else {
            if (isset($_POST[$column . '_hidden'])) {
                $row[$column] = $_POST[$column . '_hidden'];
            }
        }
    }

    $update_sql = "UPDATE movdraft_file SET " . implode(", ", array_map(fn($col) => "$col = :$col", $allowed_columns)) . " WHERE user_id = :user_id AND barangay_id = :barangay_id";
    $update_stmt = $conn->prepare($update_sql);
    foreach ($allowed_columns as $column) {
        $update_stmt->bindParam(":$column", $row[$column], PDO::PARAM_STR);
    }
    $update_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $update_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        $_SESSION['message'] = $file_changed ? 'Saved!' : 'No file changes detected.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Error updating files. Please try again.';
        $_SESSION['message_type'] = 'error';
    }

    header("Location: form2draftmov.php");
    exit;
}
?>         