<?php
session_start();

include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mov_id = $_POST['mov_id'] ?? null;
    $barangay_id = $_POST['barangay_id'] ?? null;

    $rates = [
        'IA_1a_pdf_rate', 'IA_1b_pdf_rate', 'IA_2a_pdf_rate', 'IA_2b_pdf_rate', 'IA_2c_pdf_rate', 'IA_2d_pdf_rate', 'IA_2e_pdf_rate',
        'IB_1forcities_pdf_rate', 'IB_1aformuni_pdf_rate', 'IB_1bformuni_pdf_rate', 'IB_2_pdf_rate', 'IB_3_pdf_rate', 'IB_4_pdf_rate',
        'IC_1_pdf_rate', 'IC_2_pdf_rate', 'ID_1_pdf_rate', 'ID_2_pdf_rate', 'IIA_pdf_rate', 'IIB_1_pdf_rate', 'IIB_2_pdf_rate',
        'IIC_pdf_rate', 'IIIA_pdf_rate', 'IIIB_pdf_rate', 'IIIC_1forcities_pdf_rate', 'IIIC_1forcities2_pdf_rate',
        'IIIC_1forcities3_pdf_rate', 'IIIC_2formuni1_pdf_rate', 'IIIC_2formuni2_pdf_rate', 'IIIC_2formuni3_pdf_rate',
        'IIID_pdf_rate', 'IV_forcities_pdf_rate', 'IV_muni_pdf_rate', 'V_1_pdf_rate', 'threepeoplesorg_rate', 'total'
    ];

    $remarks = [
        'IA_1a_pdf_remark', 'IA_1b_pdf_remark', 'IA_2a_pdf_remark', 'IA_2b_pdf_remark', 'IA_2c_pdf_remark', 'IA_2d_pdf_remark',
        'IA_2e_pdf_remark', 'IB_1forcities_pdf_remark', 'IB_1aformuni_pdf_remark', 'IB_1bformuni_pdf_remark', 'IB_2_pdf_remark',
        'IB_3_pdf_remark', 'IB_4_pdf_remark', 'IC_1_pdf_remark', 'IC_2_pdf_remark', 'ID_1_pdf_remark', 'ID_2_pdf_remark',
        'IIA_pdf_remark', 'IIB_1_pdf_remark', 'IIB_2_pdf_remark', 'IIC_pdf_remark', 'IIIA_pdf_remark', 'IIIB_pdf_remark',
        'IIIC_1forcities_pdf_remark', 'IIIC_1forcities2_pdf_remark', 'IIIC_1forcities3_pdf_remark', 'IIIC_2formuni1_pdf_remark',
        'IIIC_2formuni2_pdf_remark', 'IIIC_2formuni3_pdf_remark', 'IIID_pdf_remark', 'IV_forcities_pdf_remark',
        'IV_muni_pdf_remark', 'V_1_pdf_remark', 'threepeoplesorg_remark'
    ];

    if (!$mov_id || !$barangay_id) {
        header("Location: adminform2evaluate.php?status=error&message=Missing required values.");
        exit;
    }

    try {
        $conn->beginTransaction();

        // Check if the record exists in `movrate`
        $check_query = "SELECT COUNT(*) FROM `movrate` WHERE `mov_id` = :mov_id AND `barangay` = :barangay";
        $stmt = $conn->prepare($check_query);
        $stmt->bindParam(':mov_id', $mov_id, PDO::PARAM_INT);
        $stmt->bindParam(':barangay', $barangay_id, PDO::PARAM_INT);
        $stmt->execute();
        $record_exists = $stmt->fetchColumn() > 0;

        if ($record_exists) {
            $movrate_query = "UPDATE `movrate` SET " . implode(", ", array_map(fn($r) => "$r = :$r", $rates)) . "
                              WHERE `mov_id` = :mov_id AND `barangay` = :barangay";
        } else {
            $movrate_query = "INSERT INTO `movrate` (`barangay`, `mov_id`, " . implode(", ", $rates) . ")
                              VALUES (:barangay, :mov_id, " . implode(", ", array_map(fn($r) => ':' . $r, $rates)) . ")";
        }

        $stmt = $conn->prepare($movrate_query);
        $stmt->bindParam(':barangay', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':mov_id', $mov_id, PDO::PARAM_INT);
        foreach ($rates as $rate) {
            $value = $_POST[$rate] ?? null;
            $stmt->bindValue(':' . $rate, $value === '' ? null : $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        // Check if the record exists in `movremark`
        $check_query_remark = "SELECT COUNT(*) FROM `movremark` WHERE `mov_id` = :mov_id AND `barangay` = :barangay";
        $stmt = $conn->prepare($check_query_remark);
        $stmt->bindParam(':mov_id', $mov_id, PDO::PARAM_INT);
        $stmt->bindParam(':barangay', $barangay_id, PDO::PARAM_INT);
        $stmt->execute();
        $remark_exists = $stmt->fetchColumn() > 0;

        if ($remark_exists) {
            $movremark_query = "UPDATE `movremark` SET " . implode(", ", array_map(fn($r) => "$r = :$r", $remarks)) . "
                                WHERE `mov_id` = :mov_id AND `barangay` = :barangay";
        } else {
            $movremark_query = "INSERT INTO `movremark` (`barangay`, `mov_id`, " . implode(", ", $remarks) . ")
                                VALUES (:barangay, :mov_id, " . implode(", ", array_map(fn($r) => ':' . $r, $remarks)) . ")";
        }

        $stmt = $conn->prepare($movremark_query);
        $stmt->bindParam(':barangay', $barangay_id, PDO::PARAM_INT);
        $stmt->bindParam(':mov_id', $mov_id, PDO::PARAM_INT);
        foreach ($remarks as $remark) {
            $value = $_POST[$remark] ?? null;
            $stmt->bindValue(':' . $remark, $value === '' ? null : $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        $conn->commit();
        header("Location: adminform2evaluate.php?status=success&message=Saved");
    } catch (Exception $e) {
        $conn->rollBack();
        $error_message = urlencode("Failed to insert or update data: " . $e->getMessage());
        header("Location: adminform2evaluate.php?status=error&message=$error_message");
    }
    exit;
}
?>
