<?php
session_start();
include '../connection.php';
// include '../functions.php';

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

// Fetch uploaded files from the database
$sql = "SELECT " . implode(', ', $allowed_columns) . " FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize $row as an empty array if no records found

// Fetch rates from the movrate table
$rate_sql = "SELECT * FROM movrate WHERE barangay = :barangay_id";
$rate_stmt = $conn->prepare($rate_sql);
$rate_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$rate_stmt->execute();
$rate_row = $rate_stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize $rate_row as an empty array if no records found

// Fetch remarks from the movremark table
$remark_sql = "SELECT * FROM movremark WHERE barangay = :barangay_id";
$remark_stmt = $conn->prepare($remark_sql);
$remark_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$remark_stmt->execute();
$remark_row = $remark_stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize $remark_row as an empty array if no records found

$file_changed = false; // Flag to track if any files have changed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = 'movfolder/';

    foreach ($allowed_columns as $column) {
        if (isset($_FILES[$column]) && $_FILES[$column]['error'] === UPLOAD_ERR_OK) {
            $file_name = time() . '_' . basename($_FILES[$column]['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES[$column]['tmp_name'], $file_path)) {
                // New file uploaded, use the new file name
                $row[$column] = $file_name;
                $file_changed = true; // Mark file as changed
            }
        } else {
            // No new file uploaded, retain the old file
            if (isset($_POST[$column . '_hidden'])) {
                $row[$column] = $_POST[$column . '_hidden'];
            }
        }
    }

    // Prepare SQL for updating the file paths
    $update_sql = "UPDATE mov SET ";
    foreach ($allowed_columns as $column) {
        $update_sql .= "$column = :$column, ";
    }
    $update_sql = rtrim($update_sql, ', ') . " WHERE user_id = :user_id AND barangay_id = :barangay_id";

    $update_stmt = $conn->prepare($update_sql);

    // Bind the updated or retained file paths
    foreach ($allowed_columns as $column) {
        $update_stmt->bindParam(":$column", $row[$column], PDO::PARAM_STR);
    }
    $update_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $update_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);

    // Execute the statement and provide feedback
    if ($update_stmt->execute()) {
        if ($file_changed) {
            echo "<script>alert('Files updated successfully!');</script>";
        } else {
            echo "<script>document.getElementById('noChangesMessage').innerHTML = 'No file changes detected.';</script>";
        }
    } else {
        echo "<script>alert('Error updating files. Please try again.');</script>";
        error_log(print_r($update_stmt->errorInfo(), true)); // Log errors for debugging
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <link rel="stylesheet" href="css/td_hover.css">
</head>

<body class="bg-[#E8E8E7]">
<?php include "../user_sidebar_header.php"; ?>

<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
        <div class="card">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                        <div class="dilglogo">
                            <img src="images/dilglogo.png" alt="DILG Logo" class="h-20" />
                        </div>
                        <h1 class="text-xl font-bold ml-4">Lupong Tagapamayapa Incentives Award (LTIA)</h1>
                    </div>
                    <div class="menu">
                        <ul class="flex space-x-4">
                            <li>
                            <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='LTIAdashboard.php';" style="margin-left: 0;">
                          <i class="ti ti-arrow-narrow-left-dashed mr-2"></i>
                          Back
                          </button>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="container mt-5">
                <div id="noChangesMessage" class="text-red-500 font-semibold"></div>
                    <h2 class="text-left text-2xl font-semibold">FORM 1</h2>
                    <form method="post" action="" enctype="multipart/form-data">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>CRITERIA</th>
                                    <th>Means Of Verification</th>
                                    <th>Rate</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example for IA_1a -->
                                <tr>
            <td><b>1. a) Proper Recording of every dispute/complaint</b></td>
            <td>
              <?php if (!empty($row['IA_1a_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_1a_pdf_rate']) ? $rate_row['IA_1a_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_1a_pdf_remark']) ? $remark_row['IA_1a_pdf_remark'] : 'No remarks'; ?></td>
          </tr>
          <tr>
            <td>b) Sending of Notices and Summons</td>
            <td>
              <?php if (!empty($row['IA_1b_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_1b_pdf_rate']) ? $rate_row['IA_1b_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_1b_pdf_remark']) ? $remark_row['IA_1b_pdf_remark'] : 'No remarks'; ?></td>
          </tr>
          <tr>
                <td>2. Settlement and Award Period (with at least 10 settled cases within the assessment period)</td>
                <td> </td>
                <td> </td>
            <td></td>
            <td></td>
              </tr>
               <tr>
                <td>a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                <td>
                <?php if (!empty($row['IA_2a_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2a_pdf_rate']) ? $rate_row['IA_2a_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2a_pdf_remark']) ? $remark_row['IA_2a_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td>
                <?php if (!empty($row['IA_2b_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2b_pdf_rate']) ? $rate_row['IA_2b_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2b_pdf_remark']) ? $remark_row['IA_2b_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>c) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td>
                <?php if (!empty($row['IA_2c_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2c_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2c_pdf_rate']) ? $rate_row['IA_2c_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2c_pdf_remark']) ? $remark_row['IA_2c_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td>
                <?php if (!empty($row['IA_2d_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2d_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2d_pdf_rate']) ? $rate_row['IA_2d_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2d_pdf_remark']) ? $remark_row['IA_2d_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
             <td>
                <?php if (!empty($row['IA_2e_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2e_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2e_pdf_rate']) ? $rate_row['IA_2e_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2e_pdf_remark']) ? $remark_row['IA_2e_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <th>B. Systematic Maintenance of Records</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>1. Record of Cases </b></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>For Cities - computer database with searchable case information</td>
                <td>
                <?php if (!empty($row['IB_1forcities_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_1forcities_pdf_rate']) ? $rate_row['IB_1forcities_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_1forcities_pdf_remark']) ? $remark_row['IB_1forcities_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>For Municipalities:</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>a. Manual Records</td>
                <td>
                <?php if (!empty($row['IB_1aformuni_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1aformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_1aformuni_pdf_rate']) ? $rate_row['IB_1aformuni_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_1aformuni_pdf_remark']) ? $remark_row['IB_1aformuni_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>b. Digital Record Filing</td>
                <td>
                  <?php if (!empty($row['IB_1bformuni_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;"class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1bformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_1bformuni_pdf_rate']) ? $rate_row['IB_1bformuni_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_1bformuni_pdf_remark']) ? $remark_row['IB_1bformuni_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td>
                <?php if (!empty($row['IB_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_2_pdf_rate']) ? $rate_row['IB_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_2_pdf_remark']) ? $remark_row['IB_2_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td>
                <?php if (!empty($row['IB_3_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_3_pdf_rate']) ? $rate_row['IB_3_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_3_pdf_remark']) ? $remark_row['IB_3_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td>
                <?php if (!empty($row['IB_4_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_4_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_4_pdf_rate']) ? $rate_row['IB_4_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_4_pdf_remark']) ? $remark_row['IB_4_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <th>C. Timely Submissions to the Court and the DILG</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. <b>To the Court:</b> Submitted/ presented copies of settlement agreement to the Court from the lapse of the ten-day period repudiating the mediation/ conciliation settlement agreement, or within five (5) calendar days from the date of the arbitration award</td>
                <td>
                <?php if (!empty($row['IC_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IC_1_pdf_rate']) ? $rate_row['IC_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IC_1_pdf_remark']) ? $remark_row['IC_1_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>2. To the DILG (Quarterly)</td>
                <td>
                <?php if (!empty($row['IC_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IC_2_pdf_rate']) ? $rate_row['IC_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IC_2_pdf_remark']) ? $remark_row['IC_2_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <th>D. Conduct of monthly meetings for administration of the Katarungang Pambarangay (KP)</th>
                  <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. Notice of Meeting</td>
                <td>
                <?php if (!empty($row['ID_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['ID_1_pdf_rate']) ? $rate_row['ID_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['ID_1_pdf_remark']) ? $remark_row['ID_1_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>2. Minutes of the Meeting</td>
                <td>
                <?php if (!empty($row['ID_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['ID_2_pdf_rate']) ? $rate_row['ID_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['ID_2_pdf_remark']) ? $remark_row['ID_2_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <th>II. EFFECTIVENESS IN SECURING THE SETTLEMENT OF INTERPERSONAL DISPUTE OBJECTIVE OF THE KATARUNGANG PAMBARANGAY</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Quantity of settled cases against filed</td>
                <td>
                <?php if (!empty($row['IIA_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIA_pdf_rate']) ? $rate_row['IIA_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIA_pdf_remark']) ? $remark_row['IIA_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>B. Quality of Settlement of Cases</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>1. Zero cases repudiated</td>
                <td>
                <?php if (!empty($row['IIB_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIB_1_pdf_rate']) ? $rate_row['IIB_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIB_1_pdf_remark']) ? $remark_row['IIB_1_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>

                <td>
                <?php if (!empty($row['IIB_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIB_2_pdf_rate']) ? $rate_row['IIB_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIB_2_pdf_remark']) ? $remark_row['IIB_2_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>C. At least 80% compliance with the terms of settlement or award after the cases have been settled</td>
                <td>
                <?php if (!empty($row['IIC_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIC_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIC_pdf_rate']) ? $rate_row['IIC_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIC_pdf_remark']) ? $remark_row['IIC_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <th>III. CREATIVITY AND RESOURCEFULNESS OF THE LUPONG TAGAPAMAYAPA</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Settlement Technique utilized by the Lupon</td>

                <td>
                <?php if (!empty($row['IIIA_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIA_pdf_rate']) ? $rate_row['IIIA_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIA_pdf_remark']) ? $remark_row['IIIA_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>B. Coordination with Concerned Agencies relating to disputes filed (PNP, DSWD, DILG, DAR, DENR, Office of the Prosecutor, Court, DOJ, CHR, etc.)</td>

                <td>
                <?php if (!empty($row['IIIB_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIB_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIB_pdf_rate']) ? $rate_row['IIIB_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIB_pdf_remark']) ? $remark_row['IIIB_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>C. Sustained information drive to promote Katarungang Pambarangay</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>1. For Cities</td>
                <td></td>
              </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_1forcities_pdf_rate']) ? $rate_row['IIIC_1forcities_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_1forcities_pdf_remark']) ? $remark_row['IIIC_1forcities_pdf_remark'] : 'No remarks'; ?></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_1forcities2_pdf_rate']) ? $rate_row['IIIC_1forcities2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_1forcities2_remark']) ? $remark_row['IIIC_1forcities2_remark'] : 'No remarks'; ?></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities3_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_1forcities3_pdf_rate']) ? $rate_row['IIIC_1forcities3_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_1forcities3_pdf_remark']) ? $remark_row['IIIC_1forcities3_pdf_remark'] : 'No remarks'; ?></td>
            </tr>
              <tr>
                <td>2. For Municipalities</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_2formuni1_pdf_rate']) ? $rate_row['IIIC_2formuni1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_2formuni1_pdf_remark']) ? $remark_row['IIIC_2formuni1_pdf_remark'] : 'No remarks'; ?></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_2formuni2_pdf_rate']) ? $rate_row['IIIC_2formuni2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_2formuni2_pdf_remark']) ? $remark_row['IIIC_2formuni2_pdf_remark'] : 'No remarks'; ?></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni3_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_2formuni3_pdf_rate']) ? $rate_row['IIIC_2formuni3_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_2formuni3_pdf_remark']) ? $remark_row['IIIC_2formuni3_pdf_remark'] : 'No remarks'; ?></td>
            </tr>
              <tr>
                <td>D. KP Training or seminar within the assessment period<br />
                  Organized skills training participated by the Lupong Tagapamayapa</td>
                <td>
                <?php if (!empty($row['IIID_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIID_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIID_pdf_rate']) ? $rate_row['IIID_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIID_pdf_remark']) ? $remark_row['IIID_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <th>IV. AREA OR FACILITY FOR KP ACTIVITIES</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>Building structure or space:</b></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>For Cities - the office or space should be exclusive for KP matters</td>
                <td>
                <?php if (!empty($row['IV_forcities_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IV_forcities_pdf_rate']) ? $rate_row['IV_forcities_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IV_forcities_pdf_remark']) ? $remark_row['IV_forcities_pdf_remark'] : 'No remarks'; ?></td>
            </tr>
              <tr>
                <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td>
                <?php if (!empty($row['IV_muni_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_muni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IV_muni_pdf_rate']) ? $rate_row['IV_muni_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IV_muni_pdf_remark']) ? $remark_row['IV_muni_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <th>V. FINANCIAL OR NON-FINANCIAL SUPPORT</th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. From City, Municipal, Provincial or NGAs</td>
                <td>
                <?php if (!empty($row['V_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['V_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['V_1_pdf_rate']) ? $rate_row['V_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['V_1_pdf_remark']) ? $remark_row['V_1_pdf_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
                <td>3 From People's Organizations, NGOs or Private Sector</td>
                <td>
                <?php if (!empty($row['threepeoplesorg_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['threepeoplesorg_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['threepeoplesorg_rate']) ? $rate_row['threepeoplesorg_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['threepeoplesorg_remark']) ? $remark_row['threepeoplesorg_remark'] : 'No remarks'; ?></td>
              </tr>
              <tr>
              <th>Total</th>
                <td></td>
                <td>            
            </td>
            <th><?php echo isset($rate_row['total']) ? $rate_row['total'] : ' '; ?></th>
            <td></td>
              </tr>
            </tbody>
          </table>
    </form>
    
<!-- Main modal -->
<div id="large-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                    PDF Viewer
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="large-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="md:p-5 space-y-4">
               <iframe id="pdfViewer" src="" class="h-[28rem] w-full"></iframe>
            </div>
        </div>
    </div>
</div>

  <script>
    $(document).ready(function() {
        $('.view-pdf').attr('data-modal-target', 'large-modal');
        $('.view-pdf').attr('data-modal-toggle', 'large-modal');

        $('.view-pdf').click(function() {
            var pdfFile = $(this).data('file'); // Get the PDF file path from data attribute
            $('#pdfViewer').attr('src', pdfFile); // Set the file path in the iframe   

        });
    });
  </script>
</body>
</html>
