<?php
session_start();
include '../connection.php'; // Ensure you have your PDO connection

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barangay_name'])) {
    $barangay_name = $_POST['barangay_name'];

    try {
        // Query to fetch all the file columns based on the selected barangay name
        $query = "
            SELECT m.IA_1a_pdf_File, m.IA_1b_pdf_File, m.IA_2a_pdf_File, m.IA_2b_pdf_File, m.IA_2c_pdf_File, 
                   m.IA_2d_pdf_File, m.IA_2e_pdf_File, m.IB_1forcities_pdf_File, m.IB_1aformuni_pdf_File, 
                   m.IB_1bformuni_pdf_File, m.IB_2_pdf_File, m.IB_3_pdf_File, m.IB_4_pdf_File, m.IC_1_pdf_File, 
                   m.IC_2_pdf_File, m.ID_1_pdf_File, m.ID_2_pdf_File, m.IIA_pdf_File, m.IIB_1_pdf_File, 
                   m.IIB_2_pdf_File, m.IIC_pdf_File, m.IIIA_pdf_File, m.IIIB_pdf_File, m.IIIC_1forcities_pdf_File, 
                   m.IIIC_1forcities2_pdf_File, m.IIIC_1forcities3_pdf_File, m.IIIC_2formuni1_pdf_File, 
                   m.IIIC_2formuni2_pdf_File, m.IIIC_2formuni3_pdf_File, m.IIID_pdf_File, m.IV_forcities_pdf_File, 
                   m.IV_muni_pdf_File, m.V_1_pdf_File, m.threepeoplesorg 
            FROM mov m
            INNER JOIN barangays b ON m.barangay_id = b.id
            WHERE b.barangay_name = :barangay_name
        ";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':barangay_name', $barangay_name, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the files from the database
        $files = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($files) {
            // Return the files as a JSON response
            echo json_encode($files);
        } else {
            echo json_encode(['error' => 'No files found for the selected barangay']);
        }
    } catch (PDOException $e) {
        // Handle the error and return a JSON error response
        echo json_encode(['error' => 'Error fetching files: ' . $e->getMessage()]);
    }
} else {
    // Handle invalid request
    echo json_encode(['error' => 'Invalid request']);
}
?>
