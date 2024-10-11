//schedule submissions insert in php:
<?php
session_start();
include '../connection.php';

$userID = $_SESSION['user_id'] ?? '';
$barangay_id = $_SESSION['barangay_id'] ?? '';

// Check if the barangay has already uploaded files and get the last upload date
$check_query = "SELECT date_uploaded FROM mov WHERE barangay_id = :barangay_id";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
$check_stmt->execute();
$last_upload = $check_stmt->fetchColumn();

// Check if the current date is at least 5 months later than the last upload date
$allow_submission = true;
if ($last_upload) {
    $last_upload_date = new DateTime($last_upload);
    $current_date = new DateTime();
    $interval = $last_upload_date->diff($current_date);

    if ($interval->m < 5 && $interval->y == 0) {
        $allow_submission = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $allow_submission) {

    function uniqueNameConverter($arg)
    {
        $pathInfo = pathinfo($arg);
        $filename = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
        $timestamp = date("YmdHis");
        $randomString = substr(md5(mt_rand()), 0, 8); // Adding a random string
        return $filename . "_" . $timestamp . "_" . $randomString . "." . $extension;
    }

    $files = [
        'IA_1a_pdf_File',
        'IA_1b_pdf_File',
        'IA_2a_pdf_File',
        'IA_2b_pdf_File',
        'IA_2c_pdf_File',
        'IA_2d_pdf_File',
        'IA_2e_pdf_File',
        'IB_1forcities_pdf_File',
        'IB_1aformuni_pdf_File',
        'IB_1bformuni_pdf_File',
        'IB_2_pdf_File',
        'IB_3_pdf_File',
        'IB_4_pdf_File',
        'IC_1_pdf_File',
        'IC_2_pdf_File',
        'ID_1_pdf_File',
        'ID_2_pdf_File',
        'IIA_pdf_File',
        'IIB_1_pdf_File',
        'IIB_2_pdf_File',
        'IIC_pdf_File',
        'IIIA_pdf_File',
        'IIIB_pdf_File',
        'IIIC_1forcities_pdf_File',
        'IIIC_1forcities2_pdf_File',
        'IIIC_1forcities3_pdf_File',
        'IIIC_2formuni1_pdf_File',
        'IIIC_2formuni2_pdf_File',
        'IIIC_2formuni3_pdf_File',
        'IIID_pdf_File',
        'IV_forcities_pdf_File',
        'IV_muni_pdf_File',
        'V_1_pdf_File',
        'threepeoplesorg'
    ];

    $fileNames = [];
    foreach ($files as $file) {
        if (isset($_FILES[$file]) && $_FILES[$file]['error'] == 0) {
            $fileNames[$file] = uniqueNameConverter($_FILES[$file]['name']);
        } else {
            $fileNames[$file] = null; // Assign NULL if the file is not uploaded
        }
    }

    // Prepare the SQL query
    $insert_query = "INSERT INTO mov (
        user_id, 
        barangay_id,
        IA_1a_pdf_File,
        IA_1b_pdf_File,
        IA_2a_pdf_File,
        IA_2b_pdf_File,
        IA_2c_pdf_File,
        IA_2d_pdf_File,
        IA_2e_pdf_File,
        IB_1forcities_pdf_File,
        IB_1aformuni_pdf_File,
        IB_1bformuni_pdf_File,
        IB_2_pdf_File,
        IB_3_pdf_File,
        IB_4_pdf_File,
        IC_1_pdf_File,
        IC_2_pdf_File,
        ID_1_pdf_File,
        ID_2_pdf_File,
        IIA_pdf_File,
        IIB_1_pdf_File,
        IIB_2_pdf_File,
        IIC_pdf_File, 
        IIIA_pdf_File,
        IIIB_pdf_File,
        IIIC_1forcities_pdf_File,
        IIIC_1forcities2_pdf_File,
        IIIC_1forcities3_pdf_File,
        IIIC_2formuni1_pdf_File,
        IIIC_2formuni2_pdf_File,
        IIIC_2formuni3_pdf_File, 
        IIID_pdf_File, 
        IV_forcities_pdf_File, 
        IV_muni_pdf_File,
        V_1_pdf_File, 
        threepeoplesorg,
        date_uploaded
    ) VALUES (
        :user_id, 
        :barangay_id, 
        :IA_1a_pdf_File,
        :IA_1b_pdf_File,
        :IA_2a_pdf_File,
        :IA_2b_pdf_File,
        :IA_2c_pdf_File,
        :IA_2d_pdf_File,
        :IA_2e_pdf_File,
        :IB_1forcities_pdf_File,
        :IB_1aformuni_pdf_File,
        :IB_1bformuni_pdf_File,
        :IB_2_pdf_File,
        :IB_3_pdf_File,
        :IB_4_pdf_File,
        :IC_1_pdf_File,
        :IC_2_pdf_File,
        :ID_1_pdf_File,
        :ID_2_pdf_File,
        :IIA_pdf_File,
        :IIB_1_pdf_File,
        :IIB_2_pdf_File,
        :IIC_pdf_File, 
        :IIIA_pdf_File,
        :IIIB_pdf_File,
        :IIIC_1forcities_pdf_File,
        :IIIC_1forcities2_pdf_File,
        :IIIC_1forcities3_pdf_File,
        :IIIC_2formuni1_pdf_File,
        :IIIC_2formuni2_pdf_File,
        :IIIC_2formuni3_pdf_File, 
        :IIID_pdf_File, 
        :IV_forcities_pdf_File, 
        :IV_muni_pdf_File,
        :V_1_pdf_File, 
        :threepeoplesorg,
        NOW()
    )";

    $stmt = $conn->prepare($insert_query);
    
    $stmt->bindParam(':user_id', $userID);
    $stmt->bindParam(':barangay_id', $barangay_id);

    foreach ($files as $file) {
        $stmt->bindParam(":$file", $fileNames[$file], PDO::PARAM_STR);
    }

    // Execute and move files
    if ($stmt->execute()) {
        foreach ($files as $file) {
            if (isset($fileNames[$file]) && $fileNames[$file] !== null) {
                $fileTMP = $_FILES[$file]['tmp_name'];
                $fileDestination = 'movfolder/' . $fileNames[$file];
                move_uploaded_file($fileTMP, $fileDestination);
            }
        }
        echo "<script>alert('Files uploaded successfully!'); 
        window.location.href='form2movview.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error inserting into database.');</script>";
    }
} else {
    $message = $last_upload ? 'You can submit again after 5 months.' : 'Files already uploaded for this barangay.';
    echo "<script>alert('$message'); window.location.href='form2movview.php';</script>";
}

?>








<?php
session_start();
include '../connection.php'; // Ensure this file uses a PDO connection

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedBarangay = $_POST['selected_barangay'] ?? '';

    // Retrieve all rate inputs
    $rates = [
        'IA_1a_pdf_rate' => $_POST['IA_1a_pdf_rate'] ?? 0,
        'IA_1b_pdf_rate' => $_POST['IA_1b_pdf_rate'] ?? 0,
        'IB_1forcities_pdf_rate' => $_POST['IB_1forcities_pdf_rate'] ?? 0,
        'IB_1aformuni_pdf_rate' => $_POST['IB_1aformuni_pdf_rate'] ?? 0,
        'IB_1bformuni_pdf_rate' => $_POST['IB_1bformuni_pdf_rate'] ?? 0,
        'IB_2_pdf_rate' => $_POST['IB_2_pdf_rate'] ?? 0,
        'IB_3_pdf_rate' => $_POST['IB_3_pdf_rate'] ?? 0,
        'IB_4_pdf_rate' => $_POST['IB_4_pdf_rate'] ?? 0,
        'IC_1_pdf_rate' => $_POST['IC_1_pdf_rate'] ?? 0,
        'IC_2_pdf_rate' => $_POST['IC_2_pdf_rate'] ?? 0,
        'ID_1_pdf_rate' => $_POST['ID_1_pdf_rate'] ?? 0,
        'ID_2_pdf_rate' => $_POST['ID_2_pdf_rate'] ?? 0,
        'IIA_pdf_rate' => $_POST['IIA_pdf_rate'] ?? 0,
        'IIB_1_pdf_rate' => $_POST['IIB_1_pdf_rate'] ?? 0,
        'IIB_2_pdf_rate' => $_POST['IIB_2_pdf_rate'] ?? 0,
        'IIC_pdf_rate' => $_POST['IIC_pdf_rate'] ?? 0,
        'IIIA_pdf_rate' => $_POST['IIIA_pdf_rate'] ?? 0,
        'IIIB_pdf_rate' => $_POST['IIIB_pdf_rate'] ?? 0,
        'IIIC_1forcities_pdf_rate' => $_POST['IIIC_1forcities_pdf_rate'] ?? 0,
        'IIIC_1forcities2_pdf_rate' => $_POST['IIIC_1forcities2_pdf_rate'] ?? 0,
        'IIIC_1forcities3_pdf_rate' => $_POST['IIIC_1forcities3_pdf_rate'] ?? 0,
        'IIIC_2formuni1_pdf_rate' => $_POST['IIIC_2formuni1_pdf_rate'] ?? 0,
        'IIIC_2formuni2_pdf_rate' => $_POST['IIIC_2formuni2_pdf_rate'] ?? 0,
        'IIIC_2formuni3_pdf_rate' => $_POST['IIIC_2formuni3_pdf_rate'] ?? 0,
        'IIID_pdf_rate' => $_POST['IIID_pdf_rate'] ?? 0,
        'IV_forcities_pdf_rate' => $_POST['IV_forcities_pdf_rate'] ?? 0,
        'IV_muni_pdf_rate' => $_POST['IV_muni_pdf_rate'] ?? 0,
        'V_1_pdf_rate' => $_POST['V_1_pdf_rate'] ?? 0,
        'threepeoplesorg_rate' => $_POST['threepeoplesorg_rate'] ?? 0,

        // Add any additional rate fields here as necessary
    ];

    // Retrieve all remark inputs
    $remarks = [
        'IA_1a_pdf_remark' => $_POST['IA_1a_pdf_remark'] ?? '',
        'IA_1b_pdf_remark' => $_POST['IA_1b_pdf_remark'] ?? '',
        'IB_1forcities_pdf_remark' => $_POST['IB_1forcities_pdf_remark'] ?? '',
        'IB_1aformuni_pdf_remark' => $_POST['IB_1aformuni_pdf_remark'] ?? '',
        'IB_1bformuni_pdf_remark' => $_POST['IB_1bformuni_pdf_remark'] ?? '',
        'IB_2_pdf_remark' => $_POST['IB_2_pdf_remark'] ?? '',
        'IB_3_pdf_remark' => $_POST['IB_3_pdf_remark'] ?? '',
        'IB_4_pdf_remark' => $_POST['IB_4_pdf_remark'] ?? '',
        'IC_1_pdf_remark' => $_POST['IC_1_pdf_remark'] ?? '',
        'IC_2_pdf_remark' => $_POST['IC_2_pdf_remark'] ?? '',
        'ID_1_pdf_remark' => $_POST['ID_1_pdf_remark'] ?? '',
        'ID_2_pdf_remark' => $_POST['ID_2_pdf_remark'] ?? '',
        'IIA_pdf_remark' => $_POST['IIA_pdf_remark'] ?? '',
        'IIB_1_pdf_remark' => $_POST['IIB_1_pdf_remark'] ?? '',
        'IIB_2_pdf_remark' => $_POST['IIB_2_pdf_remark'] ?? '',
        'IIC_pdf_remark' => $_POST['IIC_pdf_remark'] ?? '',
        'IIIA_pdf_remark' => $_POST['IIIA_pdf_remark'] ?? '',
        'IIIB_pdf_remark' => $_POST['IIIB_pdf_remark'] ?? '',
        'IIIC_1forcities_pdf_remark' => $_POST['IIIC_1forcities_pdf_remark'] ?? '',
        'IIIC_1forcities2_pdf_remark' => $_POST['IIIC_1forcities2_pdf_remark'] ?? '',
        'IIIC_1forcities3_pdf_remark' => $_POST['IIIC_1forcities3_pdf_remark'] ?? '',
        'IIIC_2formuni1_pdf_remark' => $_POST['IIIC_2formuni1_pdf_remark'] ?? '',
        'IIIC_2formuni2_pdf_remark' => $_POST['IIIC_2formuni2_pdf_remark'] ?? '',
        'IIIC_2formuni3_pdf_remark' => $_POST['IIIC_2formuni3_pdf_remark'] ?? '',
        'IIID_pdf_remark' => $_POST['IIID_pdf_remark'] ?? '',
        'IV_forcities_pdf_remark' => $_POST['IV_forcities_pdf_remark'] ?? '',
        'IV_muni_pdf_remark' => $_POST['IV_muni_pdf_remark'] ?? '',
        'V_1_pdf_remark' => $_POST['V_1_pdf_remark'] ?? '',
        'threepeoplesorg_remark' => $_POST['threepeoplesorg_remark'] ?? '',
        // Add any additional remark fields here as necessary
    ];

    try {
        // Insert data into the `movrate` table
        $query_rate = "INSERT INTO movrate (rate_id, barangay, IA_1a_pdf_rate, IA_1b_pdf_rate, IB_1forcities_pdf_rate, IB_1aformuni_pdf_rate, IB_1bformuni_pdf_rate, IB_2_pdf_rate, IB_3_pdf_rate, IB_4_pdf_rate, IC_1_pdf_rate, IC_2_pdf_rate, ID_1_pdf_rate, ID_2_pdf_rate, IIA_pdf_rate, IIB_1_pdf_rate, IIB_2_pdf_rate, IIC_pdf_rate, IIIA_pdf_rate, IIIB_pdf_rate, IIIC_1forcities_pdf_rate, IIIC_1forcities2_pdf_rate, IIIC_1forcities3_pdf_rate, IIIC_2formuni1_pdf_rate, IIIC_2formuni2_pdf_rate, IIIC_2formuni3_pdf_rate, IIID_pdf_rate, IV_forcities_pdf_rate, IV_muni_pdf_rate, V_1_pdf_rate, threepeoplesorg_rate) 
                       VALUES (NULL, :barangay, :IA_1a_pdf_rate, :IA_1b_pdf_rate, :IB_1forcities_pdf_rate, :IB_1aformuni_pdf_rate, :IB_1bformuni_pdf_rate, :IB_2_pdf_rate, :IB_3_pdf_rate, :IB_4_pdf_rate, :IC_1_pdf_rate, :IC_2_pdf_rate, :ID_1_pdf_rate, :ID_2_pdf_rate, :IIA_pdf_rate, :IIB_1_pdf_rate, :IIB_2_pdf_rate, :IIC_pdf_rate, :IIIA_pdf_rate, :IIIB_pdf_rate, :IIIC_1forcities_pdf_rate, :IIIC_1forcities2_pdf_rate, :IIIC_1forcities3_pdf_rate, :IIIC_2formuni1_pdf_rate, :IIIC_2formuni2_pdf_rate, :IIIC_2formuni3_pdf_rate, :IIID_pdf_rate, :IV_forcities_pdf_rate, :IV_muni_pdf_rate, :V_1_pdf_rate, :threepeoplesorg_rate)";
        
        $stmt_rate = $conn->prepare($query_rate);
        $stmt_rate->bindParam(':barangay', $selectedBarangay, PDO::PARAM_STR);
        $stmt_rate->bindParam(':IA_1a_pdf_rate', $rates['IA_1a_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IA_1b_pdf_rate', $rates['IA_1b_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_1forcities_pdf_rate', $rates['IB_1forcities_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_1aformuni_pdf_rate', $rates['IB_1aformuni_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_1bformuni_pdf_rate', $rates['IB_1bformuni_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_2_pdf_rate', $rates['IB_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_3_pdf_rate', $rates['IB_3_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_4_pdf_rate', $rates['IB_4_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IC_1_pdf_rate', $rates['IC_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IC_2_pdf_rate', $rates['IC_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':ID_1_pdf_rate', $rates['ID_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':ID_2_pdf_rate', $rates['ID_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIA_pdf_rate', $rates['IIA_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIB_1_pdf_rate', $rates['IIB_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIB_2_pdf_rate', $rates['IIB_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIC_pdf_rate', $rates['IIC_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIA_pdf_rate', $rates['IIIA_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIB_pdf_rate', $rates['IIIB_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_1forcities_pdf_rate', $rates['IIIC_1forcities_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_1forcities2_pdf_rate', $rates['IIIC_1forcities2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_1forcities3_pdf_rate', $rates['IIIC_1forcities3_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_2formuni1_pdf_rate', $rates['IIIC_2formuni1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_2formuni2_pdf_rate', $rates['IIIC_2formuni2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_2formuni3_pdf_rate', $rates['IIIC_2formuni3_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIID_pdf_rate', $rates['IIID_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IV_forcities_pdf_rate', $rates['IV_forcities_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IV_muni_pdf_rate', $rates['IV_muni_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':V_1_pdf_rate', $rates['V_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':threepeoplesorg_rate', $rates['threepeoplesorg_rate'], PDO::PARAM_INT);
        $stmt_rate->execute();

        // Insert data into the `movremark` table
        $query_remark = "INSERT INTO movremark (remark_id, barangay, IA_1a_pdf_remark, IA_1b_pdf_remark, IB_1forcities_pdf_remark, IB_1aformuni_pdf_remark, IB_1bformuni_pdf_remark, IB_2_pdf_remark, IB_3_pdf_remark, IB_4_pdf_remark, IC_1_pdf_remark, IC_2_pdf_remark, ID_1_pdf_remark, ID_2_pdf_remark, IIA_pdf_remark, IIB_1_pdf_remark, IIB_2_pdf_remark, IIC_pdf_remark, IIIA_pdf_remark, IIIB_pdf_remark, IIIC_1forcities_pdf_remark, IIIC_1forcities2_pdf_remark, IIIC_1forcities3_pdf_remark, IIIC_2formuni1_pdf_remark, IIIC_2formuni2_pdf_remark, IIIC_2formuni3_pdf_remark, IIID_pdf_remark, IV_forcities_pdf_remark, IV_muni_pdf_remark, threepeoplesorg_remark) 
                         VALUES (NULL, :barangay, :IA_1a_pdf_remark, :IA_1b_pdf_remark, :IB_1forcities_pdf_remark, :IB_1aformuni_pdf_remark, :IB_1bformuni_pdf_remark, :IB_2_pdf_remark, :IB_3_pdf_remark, :IB_4_pdf_remark, :IC_1_pdf_remark, :IC_2_pdf_remark, :ID_1_pdf_remark, :ID_2_pdf_remark, :IIA_pdf_remark, :IIB_1_pdf_remark, :IIB_2_pdf_remark, :IIC_pdf_remark, :IIIA_pdf_remark, :IIIB_pdf_remark, :IIIC_1forcities_pdf_remark, :IIIC_1forcities2_pdf_remark, :IIIC_1forcities3_pdf_remark, :IIIC_2formuni1_pdf_remark, :IIIC_2formuni2_pdf_remark, :IIIC_2formuni3_pdf_remark, :IIID_pdf_remark, :IV_forcities_pdf_remark, :IV_muni_pdf_remark, :V_1_pdf_remark, :threepeoplesorg_remark)";
        
        $stmt_remark = $conn->prepare($query_remark);
        $stmt_remark->bindParam(':barangay', $selectedBarangay, PDO::PARAM_STR);
        $stmt_remark->bindParam(':IA_1a_pdf_remark', $remarks['IA_1a_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IA_1b_pdf_remark', $remarks['IA_1b_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_1forcities_pdf_remark', $remarks['IB_1forcities_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_1aformuni_pdf_remark', $remarks['IB_1aformuni_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_1bformuni_pdf_remark', $remarks['IB_1bformuni_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_2_pdf_remark', $remarks['IB_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_3_pdf_remark', $remarks['IB_3_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_4_pdf_remark', $remarks['IB_4_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IC_1_pdf_remark', $remarks['IC_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IC_2_pdf_remark', $remarks['IC_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':ID_1_pdf_remark', $remarks['ID_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':ID_2_pdf_remark', $remarks['ID_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIA_pdf_remark', $remarks['IIA_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIB_1_pdf_remark', $remarks['IIB_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIB_2_pdf_remark', $remarks['IIB_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIC_pdf_remark', $remarks['IIC_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIA_pdf_remark', $remarks['IIIA_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIB_pdf_remark', $remarks['IIIB_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_1forcities_pdf_remark', $remarks['IIIC_1forcities_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_1forcities2_pdf_remark', $remarks['IIIC_1forcities2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_1forcities3_pdf_remark', $remarks['IIIC_1forcities3_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_2formuni1_pdf_remark', $remarks['IIIC_2formuni1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_2formuni2_pdf_remark', $remarks['IIIC_2formuni2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_2formuni3_pdf_remark', $remarks['IIIC_2formuni3_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIID_pdf_remark', $remarks['IIID_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IV_forcities_pdf_remark', $remarks['IV_forcities_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IV_muni_pdf_remark', $remarks['IV_muni_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':V_1_pdf_remark', $remarks['V_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':threepeoplesorg_remark', $remarks['threepeoplesorg_remark'], PDO::PARAM_STR);
        $stmt_remark->execute();

        // Commit the transaction
        $conn->commit();

        echo "<script>alert('Data inserted successfully!'); window.location.href='form_view.php';</script>";
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        echo "<script>alert('Error inserting data: " . $e->getMessage() . "'); window.location.href='form_view.php';</script>";
    }
}

































<?php
session_start();
include '../connection.php'; // Ensure this file uses a PDO connection

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedBarangay = $_POST['selected_barangay'] ?? '';

    // Retrieve all rate inputs
    $rates = [
        'IA_1a_pdf_rate' => $_POST['IA_1a_pdf_rate'] ?? 0,
        'IA_1b_pdf_rate' => $_POST['IA_1b_pdf_rate'] ?? 0,
        'IB_1forcities_pdf_rate' => $_POST['IB_1forcities_pdf_rate'] ?? 0,
        'IB_1aformuni_pdf_rate' => $_POST['IB_1aformuni_pdf_rate'] ?? 0,
        'IB_1bformuni_pdf_rate' => $_POST['IB_1bformuni_pdf_rate'] ?? 0,
        'IB_2_pdf_rate' => $_POST['IB_2_pdf_rate'] ?? 0,
        'IB_3_pdf_rate' => $_POST['IB_3_pdf_rate'] ?? 0,
        'IB_4_pdf_rate' => $_POST['IB_4_pdf_rate'] ?? 0,
        'IC_1_pdf_rate' => $_POST['IC_1_pdf_rate'] ?? 0,
        'IC_2_pdf_rate' => $_POST['IC_2_pdf_rate'] ?? 0,
        'ID_1_pdf_rate' => $_POST['ID_1_pdf_rate'] ?? 0,
        'ID_2_pdf_rate' => $_POST['ID_2_pdf_rate'] ?? 0,
        'IIA_pdf_rate' => $_POST['IIA_pdf_rate'] ?? 0,
        'IIB_1_pdf_rate' => $_POST['IIB_1_pdf_rate'] ?? 0,
        'IIB_2_pdf_rate' => $_POST['IIB_2_pdf_rate'] ?? 0,
        'IIC_pdf_rate' => $_POST['IIC_pdf_rate'] ?? 0,
        'IIIA_pdf_rate' => $_POST['IIIA_pdf_rate'] ?? 0,
        'IIIB_pdf_rate' => $_POST['IIIB_pdf_rate'] ?? 0,
        'IIIC_1forcities_pdf_rate' => $_POST['IIIC_1forcities_pdf_rate'] ?? 0,
        'IIIC_1forcities2_pdf_rate' => $_POST['IIIC_1forcities2_pdf_rate'] ?? 0,
        'IIIC_1forcities3_pdf_rate' => $_POST['IIIC_1forcities3_pdf_rate'] ?? 0,
        'IIIC_2formuni1_pdf_rate' => $_POST['IIIC_2formuni1_pdf_rate'] ?? 0,
        'IIIC_2formuni2_pdf_rate' => $_POST['IIIC_2formuni2_pdf_rate'] ?? 0,
        'IIIC_2formuni3_pdf_rate' => $_POST['IIIC_2formuni3_pdf_rate'] ?? 0,
        'IIID_pdf_rate' => $_POST['IIID_pdf_rate'] ?? 0,
        'IV_forcities_pdf_rate' => $_POST['IV_forcities_pdf_rate'] ?? 0,
        'IV_muni_pdf_rate' => $_POST['IV_muni_pdf_rate'] ?? 0,
        'V_1_pdf_rate' => $_POST['V_1_pdf_rate'] ?? 0,
        'threepeoplesorg_rate' => $_POST['threepeoplesorg_rate'] ?? 0,

        // Add any additional rate fields here as necessary
    ];

    // Retrieve all remark inputs
    $remarks = [
        'IA_1a_pdf_remark' => $_POST['IA_1a_pdf_remark'] ?? '',
        'IA_1b_pdf_remark' => $_POST['IA_1b_pdf_remark'] ?? '',
        'IB_1forcities_pdf_remark' => $_POST['IB_1forcities_pdf_remark'] ?? '',
        'IB_1aformuni_pdf_remark' => $_POST['IB_1aformuni_pdf_remark'] ?? '',
        'IB_1bformuni_pdf_remark' => $_POST['IB_1bformuni_pdf_remark'] ?? '',
        'IB_2_pdf_remark' => $_POST['IB_2_pdf_remark'] ?? '',
        'IB_3_pdf_remark' => $_POST['IB_3_pdf_remark'] ?? '',
        'IB_4_pdf_remark' => $_POST['IB_4_pdf_remark'] ?? '',
        'IC_1_pdf_remark' => $_POST['IC_1_pdf_remark'] ?? '',
        'IC_2_pdf_remark' => $_POST['IC_2_pdf_remark'] ?? '',
        'ID_1_pdf_remark' => $_POST['ID_1_pdf_remark'] ?? '',
        'ID_2_pdf_remark' => $_POST['ID_2_pdf_remark'] ?? '',
        'IIA_pdf_remark' => $_POST['IIA_pdf_remark'] ?? '',
        'IIB_1_pdf_remark' => $_POST['IIB_1_pdf_remark'] ?? '',
        'IIB_2_pdf_remark' => $_POST['IIB_2_pdf_remark'] ?? '',
        'IIC_pdf_remark' => $_POST['IIC_pdf_remark'] ?? '',
        'IIIA_pdf_remark' => $_POST['IIIA_pdf_remark'] ?? '',
        'IIIB_pdf_remark' => $_POST['IIIB_pdf_remark'] ?? '',
        'IIIC_1forcities_pdf_remark' => $_POST['IIIC_1forcities_pdf_remark'] ?? '',
        'IIIC_1forcities2_pdf_remark' => $_POST['IIIC_1forcities2_pdf_remark'] ?? '',
        'IIIC_1forcities3_pdf_remark' => $_POST['IIIC_1forcities3_pdf_remark'] ?? '',
        'IIIC_2formuni1_pdf_remark' => $_POST['IIIC_2formuni1_pdf_remark'] ?? '',
        'IIIC_2formuni2_pdf_remark' => $_POST['IIIC_2formuni2_pdf_remark'] ?? '',
        'IIIC_2formuni3_pdf_remark' => $_POST['IIIC_2formuni3_pdf_remark'] ?? '',
        'IIID_pdf_remark' => $_POST['IIID_pdf_remark'] ?? '',
        'IV_forcities_pdf_remark' => $_POST['IV_forcities_pdf_remark'] ?? '',
        'IV_muni_pdf_remark' => $_POST['IV_muni_pdf_remark'] ?? '',
        'V_1_pdf_remark' => $_POST['V_1_pdf_remark'] ?? '',
        'threepeoplesorg_remark' => $_POST['threepeoplesorg_remark'] ?? '',
        // Add any additional remark fields here as necessary
    ];

    try {
        $conn->beginTransaction();
        // Insert data into the `movrate` table
        $query_rate = "INSERT INTO movrate (rate_id, barangay, IA_1a_pdf_rate, IA_1b_pdf_rate, IB_1forcities_pdf_rate, IB_1aformuni_pdf_rate, IB_1bformuni_pdf_rate, IB_2_pdf_rate, IB_3_pdf_rate, IB_4_pdf_rate, IC_1_pdf_rate, IC_2_pdf_rate, ID_1_pdf_rate, ID_2_pdf_rate, IIA_pdf_rate, IIB_1_pdf_rate, IIB_2_pdf_rate, IIC_pdf_rate, IIIA_pdf_rate, IIIB_pdf_rate, IIIC_1forcities_pdf_rate, IIIC_1forcities2_pdf_rate, IIIC_1forcities3_pdf_rate, IIIC_2formuni1_pdf_rate, IIIC_2formuni2_pdf_rate, IIIC_2formuni3_pdf_rate, IIID_pdf_rate, IV_forcities_pdf_rate, IV_muni_pdf_rate, V_1_pdf_rate, threepeoplesorg_rate) 
                       VALUES (NULL, :barangay, :IA_1a_pdf_rate, :IA_1b_pdf_rate, :IB_1forcities_pdf_rate, :IB_1aformuni_pdf_rate, :IB_1bformuni_pdf_rate, :IB_2_pdf_rate, :IB_3_pdf_rate, :IB_4_pdf_rate, :IC_1_pdf_rate, :IC_2_pdf_rate, :ID_1_pdf_rate, :ID_2_pdf_rate, :IIA_pdf_rate, :IIB_1_pdf_rate, :IIB_2_pdf_rate, :IIC_pdf_rate, :IIIA_pdf_rate, :IIIB_pdf_rate, :IIIC_1forcities_pdf_rate, :IIIC_1forcities2_pdf_rate, :IIIC_1forcities3_pdf_rate, :IIIC_2formuni1_pdf_rate, :IIIC_2formuni2_pdf_rate, :IIIC_2formuni3_pdf_rate, :IIID_pdf_rate, :IV_forcities_pdf_rate, :IV_muni_pdf_rate, :V_1_pdf_rate, :threepeoplesorg_rate)";
        
        $stmt_rate = $conn->prepare($query_rate);
        $stmt_rate->bindParam(':barangay', $selectedBarangay, PDO::PARAM_STR);
        $stmt_rate->bindParam(':IA_1a_pdf_rate', $rates['IA_1a_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IA_1b_pdf_rate', $rates['IA_1b_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_1forcities_pdf_rate', $rates['IB_1forcities_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_1aformuni_pdf_rate', $rates['IB_1aformuni_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_1bformuni_pdf_rate', $rates['IB_1bformuni_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_2_pdf_rate', $rates['IB_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_3_pdf_rate', $rates['IB_3_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IB_4_pdf_rate', $rates['IB_4_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IC_1_pdf_rate', $rates['IC_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IC_2_pdf_rate', $rates['IC_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':ID_1_pdf_rate', $rates['ID_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':ID_2_pdf_rate', $rates['ID_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIA_pdf_rate', $rates['IIA_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIB_1_pdf_rate', $rates['IIB_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIB_2_pdf_rate', $rates['IIB_2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIC_pdf_rate', $rates['IIC_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIA_pdf_rate', $rates['IIIA_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIB_pdf_rate', $rates['IIIB_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_1forcities_pdf_rate', $rates['IIIC_1forcities_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_1forcities2_pdf_rate', $rates['IIIC_1forcities2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_1forcities3_pdf_rate', $rates['IIIC_1forcities3_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_2formuni1_pdf_rate', $rates['IIIC_2formuni1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_2formuni2_pdf_rate', $rates['IIIC_2formuni2_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIIC_2formuni3_pdf_rate', $rates['IIIC_2formuni3_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IIID_pdf_rate', $rates['IIID_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IV_forcities_pdf_rate', $rates['IV_forcities_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':IV_muni_pdf_rate', $rates['IV_muni_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':V_1_pdf_rate', $rates['V_1_pdf_rate'], PDO::PARAM_INT);
        $stmt_rate->bindParam(':threepeoplesorg_rate', $rates['threepeoplesorg_rate'], PDO::PARAM_INT);
        $stmt_rate->execute();

        // Insert data into the `movremark` table
        $query_remark = "INSERT INTO movremark (remark_id, barangay, IA_1a_pdf_remark, IA_1b_pdf_remark, IB_1forcities_pdf_remark, IB_1aformuni_pdf_remark, IB_1bformuni_pdf_remark, IB_2_pdf_remark, IB_3_pdf_remark, IB_4_pdf_remark, IC_1_pdf_remark, IC_2_pdf_remark, ID_1_pdf_remark, ID_2_pdf_remark, IIA_pdf_remark, IIB_1_pdf_remark, IIB_2_pdf_remark, IIC_pdf_remark, IIIA_pdf_remark, IIIB_pdf_remark, IIIC_1forcities_pdf_remark, IIIC_1forcities2_pdf_remark, IIIC_1forcities3_pdf_remark, IIIC_2formuni1_pdf_remark, IIIC_2formuni2_pdf_remark, IIIC_2formuni3_pdf_remark, IIID_pdf_remark, IV_forcities_pdf_remark, IV_muni_pdf_remark, threepeoplesorg_remark) 
                         VALUES (NULL, :barangay, :IA_1a_pdf_remark, :IA_1b_pdf_remark, :IB_1forcities_pdf_remark, :IB_1aformuni_pdf_remark, :IB_1bformuni_pdf_remark, :IB_2_pdf_remark, :IB_3_pdf_remark, :IB_4_pdf_remark, :IC_1_pdf_remark, :IC_2_pdf_remark, :ID_1_pdf_remark, :ID_2_pdf_remark, :IIA_pdf_remark, :IIB_1_pdf_remark, :IIB_2_pdf_remark, :IIC_pdf_remark, :IIIA_pdf_remark, :IIIB_pdf_remark, :IIIC_1forcities_pdf_remark, :IIIC_1forcities2_pdf_remark, :IIIC_1forcities3_pdf_remark, :IIIC_2formuni1_pdf_remark, :IIIC_2formuni2_pdf_remark, :IIIC_2formuni3_pdf_remark, :IIID_pdf_remark, :IV_forcities_pdf_remark, :IV_muni_pdf_remark, :V_1_pdf_remark, :threepeoplesorg_remark)";
        
        $stmt_remark = $conn->prepare($query_remark);
        $stmt_remark->bindParam(':barangay', $selectedBarangay, PDO::PARAM_STR);
        $stmt_remark->bindParam(':IA_1a_pdf_remark', $remarks['IA_1a_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IA_1b_pdf_remark', $remarks['IA_1b_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_1forcities_pdf_remark', $remarks['IB_1forcities_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_1aformuni_pdf_remark', $remarks['IB_1aformuni_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_1bformuni_pdf_remark', $remarks['IB_1bformuni_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_2_pdf_remark', $remarks['IB_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_3_pdf_remark', $remarks['IB_3_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IB_4_pdf_remark', $remarks['IB_4_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IC_1_pdf_remark', $remarks['IC_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IC_2_pdf_remark', $remarks['IC_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':ID_1_pdf_remark', $remarks['ID_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':ID_2_pdf_remark', $remarks['ID_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIA_pdf_remark', $remarks['IIA_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIB_1_pdf_remark', $remarks['IIB_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIB_2_pdf_remark', $remarks['IIB_2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIC_pdf_remark', $remarks['IIC_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIA_pdf_remark', $remarks['IIIA_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIB_pdf_remark', $remarks['IIIB_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_1forcities_pdf_remark', $remarks['IIIC_1forcities_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_1forcities2_pdf_remark', $remarks['IIIC_1forcities2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_1forcities3_pdf_remark', $remarks['IIIC_1forcities3_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_2formuni1_pdf_remark', $remarks['IIIC_2formuni1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_2formuni2_pdf_remark', $remarks['IIIC_2formuni2_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIIC_2formuni3_pdf_remark', $remarks['IIIC_2formuni3_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IIID_pdf_remark', $remarks['IIID_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IV_forcities_pdf_remark', $remarks['IV_forcities_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':IV_muni_pdf_remark', $remarks['IV_muni_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':V_1_pdf_remark', $remarks['V_1_pdf_remark'], PDO::PARAM_STR);
        $stmt_remark->bindParam(':threepeoplesorg_remark', $remarks['threepeoplesorg_remark'], PDO::PARAM_STR);
        $stmt_remark->execute();

        // Commit the transaction
        $conn->commit();
  // Redirect or display success message
  echo "Records inserted successfully.";
} catch (PDOException $e) {
    // Check if a transaction is active before rolling back
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage();
}}


























//modal

<?php
session_start();
include '../connection.php';

// Fetch uploaded files from the database
$sql = "SELECT `IA_1a_pdf_File`, `IA_1b_pdf_File`, `IA_2_pdf_File`, `IA_2a_pdf_File`, `IA_2b_pdf_File`, `IA_2c_pdf_File`, 
`IA_2d_pdf_File`, `IA_2e_pdf_File`, `IB_1forcities_pdf_File`, `IB_1aformuni_pdf_File`, `IB_1bformuni_pdf_File`, 
`IB_2_pdf_File`, `IB_3_pdf_File`, `IB_4_pdf_File`, `IC_1_pdf_File`, `IC_2_pdf_File`, `ID_1_pdf_File`, `ID_2_pdf_File`, 
`IIA_pdf_File`, `IIB_1_pdf_File`, `IIB_2_pdf_File`, `IIC_pdf_File`, `IIIA_pdf_File`, `IIIB_pdf_File`, 
`IIIC_1forcities_pdf_File`, `IIIC_1forcities2_pdf_File`, `IIIC_1forcities3_pdf_File`, `IIIC_2formuni1_pdf_File`, 
`IIIC_2formuni2_pdf_File`, `IIIC_2formuni3_pdf_File`, `IIID_pdf_File`, `IV_forcities_pdf_File`, `IV_muni_pdf_File`, 
`V_1_pdf_File`, `threepeoplesorg` FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded PDFs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Uploaded PDF Files</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($row as $fileField => $filePath) {
                    if ($filePath) {
                        echo "
                                <td></td>
                                <td>
                                    <button class='btn btn-primary view-pdf' data-file='movfolder/{$filePath}'>View</button>
                                </td>
                              ";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">PDF Viewer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfViewer" src="" width="100%" height="500px"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Event listener for "View" button
            $('.view-pdf').on('click', function() {
                var filePath = $(this).data('file');
                $('#pdfViewer').attr('src', filePath);
                $('#pdfModal').modal('show');
            });
        });
    </script>
</body>
</html>
