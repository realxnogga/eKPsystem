<?php
session_start();
include '../connection.php';
//  include '../functions.php';

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
    'V_1_pdf_File', 'threepeoplesorg_pdf_File'
];

// Fetch uploaded files from the database
$sql = "SELECT " . implode(', ', $allowed_columns) . " FROM movdraft_file WHERE user_id = :user_id AND barangay_id = :barangay_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize $row as an empty array if no records found

$file_changed = false; // Flag to track if any files have changed

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $upload_dir = 'movfolder/';

//     foreach ($allowed_columns as $column) {
//         if (isset($_FILES[$column]) && $_FILES[$column]['error'] === UPLOAD_ERR_OK) {
//             $file_name = time() . '_' . basename($_FILES[$column]['name']);
//             $file_path = $upload_dir . $file_name;

//             if (move_uploaded_file($_FILES[$column]['tmp_name'], $file_path)) {
//                 // New file uploaded, use the new file name
//                 $row[$column] = $file_name;
//                 $file_changed = true; // Mark file as changed
//             }
//         } else {
//             // No new file uploaded, retain the old file
//             if (isset($_POST[$column . '_hidden'])) {
//                 $row[$column] = $_POST[$column . '_hidden'];
//             }
//         }
//     }
//  separated ang file nato "movdraftUpadate.php sya
//     // Prepare SQL for updating the file paths
//     $update_sql = "UPDATE movdraft_file SET ";
//     foreach ($allowed_columns as $column) {
//         $update_sql .= "$column = :$column, ";
//     }
//     $update_sql = rtrim($update_sql, ', ') . " WHERE user_id = :user_id AND barangay_id = :barangay_id";

//     $update_stmt = $conn->prepare($update_sql);

//     // Bind the updated or retained file paths
//     foreach ($allowed_columns as $column) {
//         $update_stmt->bindParam(":$column", $row[$column], PDO::PARAM_STR);
//     }
//     $update_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
//     $update_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);

//     // Execute the statement and provide feedback
//     if ($update_stmt->execute()) {
//       if ($file_changed) {
//           echo "<script>
//               document.getElementById('feedbackMessage').innerHTML = 'Files updated successfully!';
//               var feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
//               feedbackModal.show();
//           </script>";
//       } else {
//           echo "<script>
//               document.getElementById('feedbackMessage').innerHTML = 'No file changes detected.';
//               var feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
//               feedbackModal.show();
//           </script>";
//       }
//   } else {
//       echo "<script>
//           document.getElementById('feedbackMessage').innerHTML = 'Error updating files. Please try again.';
//           var feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
//           feedbackModal.show();
//       </script>";
//       error_log(print_r($update_stmt->errorInfo(), true));
//       }
//       // Redirect to prevent form resubmission
// header("Location: " . $_SERVER['REQUEST_URI']);
// exit;
// }
// Define user and barangay ID from session
$userID = $_SESSION['user_id'];
$barangayID = $_SESSION['barangay_id'] ?? '';

// Initialize variables
$submissionExists = false;
$barangayName = '';
$municipalityName = '';
$municipalityID = '';

// Query to check if the user's barangay has a submission
$checkQuery = "SELECT COUNT(*) FROM movdraft_file WHERE barangay_id = :barangay_id";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bindParam(':barangay_id', $barangayID, PDO::PARAM_INT);
$checkStmt->execute();
if ($checkStmt->fetchColumn() > 0) {
    $submissionExists = true;
}

// Query to fetch the barangay name and municipality ID
if (!empty($barangayID)) {
    $barangayQuery = "SELECT barangay_name, municipality_id FROM barangays WHERE id = :barangay_id";
    $barangayStmt = $conn->prepare($barangayQuery);
    $barangayStmt->bindParam(':barangay_id', $barangayID, PDO::PARAM_INT);
    $barangayStmt->execute();
    $barangayResult = $barangayStmt->fetch(PDO::FETCH_ASSOC);

    if ($barangayResult) {
        $barangayName = $barangayResult['barangay_name'];
        $municipalityID = $barangayResult['municipality_id'];
    }
}

// Query to fetch the municipality name
if (!empty($municipalityID)) {
    $municipalityQuery = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
    $municipalityStmt = $conn->prepare($municipalityQuery);
    $municipalityStmt->bindParam(':municipality_id', $municipalityID, PDO::PARAM_INT);
    $municipalityStmt->execute();
    $municipalityName = $municipalityStmt->fetchColumn() ?: 'Unknown';
}

?>  

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="css/td_hover.css">
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const cities = ["Calamba", "Biñan", "San Pedro", "Sta Rosa", "Cabuyao", "San Pablo"];
      const municipalities = ["Bay", "Alaminos", "Calauan", "Los Baños"];

      function normalizeName(name) {
        return name.toLowerCase().replace(/\s+/g, "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
      }

      function classifyMunicipality(municipalityName) {
        const normalized = normalizeName(municipalityName);
        const normalizedCities = cities.map(normalizeName);
        const normalizedMunicipalities = municipalities.map(normalizeName);

        if (normalizedCities.includes(normalized)) {
          return "City";
        } else if (normalizedMunicipalities.includes(normalized)) {
          return "Municipality";
        } else {
          return "Unknown";
        }
      }

      const municipalityName = <?php echo json_encode($municipalityName); ?>;
      const classification = classifyMunicipality(municipalityName);

      document.getElementById("details-municipality-type").textContent = classification;

      if (classification === "City") {
        document.querySelectorAll('#city-row').forEach(row => row.style.display = '');
        document.querySelectorAll('#municipality-row').forEach(row => row.style.display = 'none');
      } else if (classification === "Municipality") {
        document.querySelectorAll('#city-row').forEach(row => row.style.display = 'none');
        document.querySelectorAll('#municipality-row').forEach(row => row.style.display = '');
      }
    });
  </script>
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
                        <h1 class="text-xl font-bold ml-4">Lupong Tagapamayapa Incentives Award (LTIA) <?php echo date('Y'); ?>
                        <hr class="my-2">
            <span>Barangay </span> 
                  <span ><?= htmlspecialchars($barangayName, ENT_QUOTES, 'UTF-8') ?></span>, 
                  <span id="details-municipality-type" class="ml-2"></span>
                  <span>of <?= htmlspecialchars($municipalityName, ENT_QUOTES, 'UTF-8') ?></span>
               </h2></h1>
                     </div>
                    <div class="menu">
                        <ul class="flex space-x-4">
                        <li>
                        <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='form2MOVupload.php';" style="margin-left: 0;">
                    <i class="ti ti-arrow-left-dashed mr-2"></i>
                    Back
                        </li>             
                            </ul>
                    </div>
                </div>
          <div class="container mt-5">
                <div id="noChangesMessage" class="text-red-500 font-semibold"></div>
                    <h2 class="text-left text-2xl font-semibold">FORM 1 Draft</h2>

           <form id="uploadForm" method="post" enctype="multipart/form-data">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>CRITERIA</th>
                                    <th>Means Of Verification</th>
                                    <th>File</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example for IA_1a -->
                                <tr>
            <td><b>1. a) Proper Recording of every dispute/complaint</b></td>
            <td><input type="file" id="IA_1a_pdf_File" name="IA_1a_pdf_File" accept=".pdf" onchange="validateFileType(this)"/>
            <input type="hidden" name="IA_1a_pdf_File" id="IA_1a_pdf_File" value="<?php echo $row['IA_1a_pdf_File']; ?>">
            </td>
            <td>
            <?php if (!empty($row['IA_1a_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IA_1a_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IA_1a_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td>b) Sending of Notices and Summons</td>
            <td><input type="file" id="IA_1b_pdf_File" name="IA_1b_pdf_File" accept=".pdf" onchange="validateFileType(this)"/>
            <input type="hidden" name="IA_1b_pdf_File" id="IA_1b_pdf_File" value="<?php echo $row['IA_1b_pdf_File']; ?>">
            </td>
            <td>
            <?php if (!empty($row['IA_1b_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IA_1b_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IA_1b_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
          </tr>
          <tr>
                <td>2. Settlement and Award Period (with at least 10 settled cases within the assessment period)</td>
                <td> </td>
                <td> </td>
              </tr>
               <tr>
                <td>a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                <td><input type="file" id="IA_2a_pdf_File" name="IA_2a_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IA_2a_pdf_File" id="IA_2a_pdf_File" value="<?php echo $row['IA_2a_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IA_2a_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IA_2a_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IA_2a_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td><input type="file" id="IA_2b_pdf_File" name="IA_2b_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IA_2b_pdf_File" id="IA_2b_pdf_File" value="<?php echo $row['IA_2b_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IA_2b_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IA_2b_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IA_2b_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>c) Conciliation (with extended period not to exceed another 15 days)</td>
                <td><input type="file" id="IA_2c_pdf_File" name="IA_2c_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IA_2c_pdf_File" id="IA_2c_pdf_File" value="<?php echo $row['IA_2c_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IA_2c_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IA_2c_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IA_2c_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td><input type="file" id="IA_2d_pdf_File" name="IA_2d_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IA_2d_pdf_File" id="IA_2d_pdf_File" value="<?php echo $row['IA_2d_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IA_2d_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IA_2d_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IA_2d_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
                <td><input type="file" id="IA_2e_pdf_File" name="IA_2e_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IA_2e_pdf_File" id="IA_2e_pdf_File" value="<?php echo $row['IA_2e_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IA_2e_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IA_2e_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IA_2e_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <th>B. Systematic Maintenance of Records</th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>1. Record of Cases </b></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row">
                <td>For Cities - computer database with searchable case information</td>
                <td><input type="file" id="IB_1forcities_pdf_File" name="IB_1forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IB_1forcities_pdf_File" id="IB_1forcities_pdf_File" value="<?php echo $row['IB_1forcities_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IB_1forcities_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IB_1forcities_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IB_1forcities_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr id="municipality-row">
                <td>For Municipalities:</td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row">
                <td>a. Manual Records</td>
                <td><input type="file" id="IB_1aformuni_pdf_File" name="IB_1aformuni_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IB_1aformuni_pdf_File" id="IB_1aformuni_pdf_File" value="<?php echo $row['IB_1aformuni_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IB_1aformuni_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IB_1aformuni_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IB_1aformuni_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr id="municipality-row">
                <td>b. Digital Record Filing</td>
                <td><input type="file" id="IB_1bformuni_pdf_File" name="IB_1bformuni_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IB_1bformuni_pdf_File" id="IB_1bformuni_pdf_File" value="<?php echo $row['IB_1bformuni_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IB_1bformuni_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IB_1bformuni_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IB_1bformuni_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td><input type="file" id="IB_2_pdf_File" name="IB_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IB_2_pdf_File" id="IB_2_pdf_File" value="<?php echo $row['IB_2_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IB_2_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IB_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IB_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td><input type="file" id="IB_3_pdf_File" name="IB_3_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IB_3_pdf_File" id="IB_3_pdf_File" value="<?php echo $row['IB_3_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IB_3_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IB_3_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IB_3_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td><input type="file" id="IB_4_pdf_File" name="IB_4_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IB_4_pdf_File" id="IB_4_pdf_File" value="<?php echo $row['IB_4_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IB_4_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IB_4_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IB_4_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <th>C. Timely Submissions to the Court and the DILG</th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. <b>To the Court:</b> Submitted/ presented copies of settlement agreement to the Court from the lapse of the ten-day period repudiating the mediation/ conciliation settlement agreement, or within five (5) calendar days from the date of the arbitration award</td>
                <td><input type="file" id="IC_1_pdf_File" name="IC_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IC_1_pdf_File" id="IC_1_pdf_File" value="<?php echo $row['IC_1_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IC_1_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IC_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IC_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>2. To the DILG (Quarterly)</td>
                <td><input type="file" id="IC_2_pdf_File" name="IC_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IC_2_pdf_File" id="IC_2_pdf_File" value="<?php echo $row['IC_2_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IC_2_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IC_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IC_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <th>D. Conduct of monthly meetings for administration of the Katarungang Pambarangay (KP)</th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. Notice of Meeting</td>
                <td><input type="file" id="ID_1_pdf_File" name="ID_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="ID_1_pdf_File" id="ID_1_pdf_File" value="<?php echo $row['ID_1_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['ID_1_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['ID_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['ID_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>2. Minutes of the Meeting</td>
                <td><input type="file" id="ID_2_pdf_File" name="ID_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="ID_2_pdf_File" id="ID_2_pdf_File" value="<?php echo $row['ID_2_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['ID_2_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['ID_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['ID_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <th>II. EFFECTIVENESS IN SECURING THE SETTLEMENT OF INTERPERSONAL DISPUTE OBJECTIVE OF THE KATARUNGANG PAMBARANGAY</th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Quantity of settled cases against filed</td>
                <td><input type="file" id="IIA_pdf_File" name="IIA_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIA_pdf_File" id="IIA_pdf_File" value="<?php echo $row['IIA_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIA_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIA_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIA_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>B. Quality of Settlement of Cases</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>1. Zero cases repudiated</td>
                <td><input type="file" id="IIB_1_pdf_File" name="IIB_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIB_1_pdf_File" id="IIB_1_pdf_File" value="<?php echo $row['IIB_1_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIB_1_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIB_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIB_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>
                <td><input type="file" id="IIB_2_pdf_File" name="IIB_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIB_2_pdf_File" id="IIB_2_pdf_File" value="<?php echo $row['IIB_2_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIB_2_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIB_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIB_2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>C. At least 80% compliance with the terms of settlement or award after the cases have been settled</td>
                <td><input type="file" id="IIC_pdf_File" name="IIC_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIC_pdf_File" id="IIC_pdf_File" value="<?php echo $row['IIC_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIC_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIC_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIC_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <th>III. CREATIVITY AND RESOURCEFULNESS OF THE LUPONG TAGAPAMAYAPA</th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Settlement Technique utilized by the Lupon</td>
                <td><input type="file" id="IIIA_pdf_File" name="IIIA_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIA_pdf_File" id="IIIA_pdf_File" value="<?php echo $row['IIIA_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIA_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIA_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIA_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>B. Coordination with Concerned Agencies relating to disputes filed (PNP, DSWD, DILG, DAR, DENR, Office of the Prosecutor, Court, DOJ, CHR, etc.)</td>
                <td><input type="file" id="IIIB_pdf_File" name="IIIB_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIB_pdf_File" id="IIIB_pdf_File" value="<?php echo $row['IIIB_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIB_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIB_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIB_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>C. Sustained information drive to promote Katarungang Pambarangay</td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row">
                <td>1. For Cities</td>
                <td></td>
              </tr>
              <tr id="city-row">
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_1forcities_pdf_File" name="IIIC_1forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIC_1forcities_pdf_File" id="IIIC_1forcities_pdf_File" value="<?php echo $row['IIIC_1forcities_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIC_1forcities_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIC_1forcities_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
            </tr>
              <tr id="city-row">
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_1forcities2_pdf_File" name="IIIC_1forcities2_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIC_1forcities2_pdf_File" id="IIIC_1forcities2_pdf_File" value="<?php echo $row['IIIC_1forcities2_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities2_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIC_1forcities2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIC_1forcities2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
            </tr>
              <tr id="city-row">
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_1forcities3_pdf_File" name="IIIC_1forcities3_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIC_1forcities3_pdf_File" id="IIIC_1forcities3_pdf_File" value="<?php echo $row['IIIC_1forcities3_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities3_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIC_1forcities3_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIC_1forcities3_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
            </tr>
              <tr id="municipality-row">
                <td>2. For Municipalities</td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row">
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_2formuni1_pdf_File" name="IIIC_2formuni1_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIC_2formuni1_pdf_File" id="IIIC_2formuni1_pdf_File" value="<?php echo $row['IIIC_2formuni1_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni1_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIC_2formuni1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIC_2formuni1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
            </tr>
              <tr id="municipality-row">
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_2formuni2_pdf_File" name="IIIC_2formuni2_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIC_2formuni2_pdf_File" id="IIIC_2formuni2_pdf_File" value="<?php echo $row['IIIC_2formuni2_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni2_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIC_2formuni2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIC_2formuni2_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
            </tr>
              <tr id="municipality-row">
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_2formuni3_pdf_File" name="IIIC_2formuni3_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIIC_2formuni3_pdf_File" id="IIIC_2formuni3_pdf_File" value="<?php echo $row['IIIC_2formuni3_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni3_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIIC_2formuni3_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIIC_2formuni3_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
            </tr>
              <tr>
                <td>D. KP Training or seminar within the assessment period<br />
                  Organized skills training participated by the Lupong Tagapamayapa</td>
                <td><input type="file" id="IIID_pdf_File" name="IIID_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IIID_pdf_File" id="IIID_pdf_File" value="<?php echo $row['IIID_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IIID_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IIID_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IIID_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <th>IV. AREA OR FACILITY FOR KP ACTIVITIES</th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>Building structure or space:</b></td>
                <td></td>
                <td></td>
              </tr>
              <tr id = "city-row">
                <td>For Cities - the office or space should be exclusive for KP matters</td>
                <td><input type="file" id="IV_forcities_pdf_File" name="IV_forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IV_forcities_pdf_File" id="IV_forcities_pdf_File" value="<?php echo $row['IV_forcities_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IV_forcities_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IV_forcities_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IV_forcities_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
            </tr>
            <tr id="municipality-row">
            <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td><input type="file" id="IV_muni_pdf_File" name="IV_muni_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="IV_muni_pdf_File" id="IV_muni_pdf_File" value="<?php echo $row['IV_muni_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['IV_muni_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['IV_muni_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['IV_muni_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <th>V. FINANCIAL OR NON-FINANCIAL SUPPORT</th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. From City, Municipal, Provincial or NGAs</td>
                <td><input type="file" id="V_1_pdf_File" name="V_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="V_1_pdf_File" id="V_1_pdf_File" value="<?php echo $row['V_1_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['V_1_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['V_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['V_1_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
              <tr>
                <td>3 From People's Organizations, NGOs or Private Sector</td>
                <td><input type="file" id="3peoplesorg" name="threepeoplesorg_pdf_File" accept=".pdf" onchange="validateFileType(this)" />
                <input type="hidden" name="threepeoplesorg_pdf_File" id="threepeoplesorg_pdf_File" value="<?php echo $row['threepeoplesorg_pdf_File']; ?>">
            </td>
                <td>
                <?php if (!empty($row['threepeoplesorg_pdf_File'])) : ?>
            <button type="button" class="btn btn-primary view-pdf" style="background-color: #00008B;" data-file="movfolder/<?php echo htmlspecialchars($row['threepeoplesorg_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>">(<?php echo htmlspecialchars($row['threepeoplesorg_pdf_File'], ENT_QUOTES, 'UTF-8'); ?>)</button>
            <?php else : ?>
              <span>No file uploaded</span>
            <?php endif; ?>
            </td>
              </tr>
            </tbody>
          </table>
          <div class="d-flex justify-content-between">
          <button type="button" style="background-color: #000033;"class="btn btn-secondary" data-action="movdraftUpdate.php" onclick="submitForm(this)">Save as Draft</button>
          <button type="button" style="background-color: #000033;"class="btn btn-primary" data-action="form2MOVupload_handler.php" onclick="submitForm(this)">Submit</button>       
          <!-- <input type="Submit" name="update" value="Update" style="background-color: #000033;" class="btn btn-dark mt-3" />
          <input type="Submit" name="Submit" value="Submit" style="background-color: #000033;" class="btn btn-dark mt-3" /> -->
        </div>

    </form>
    <script>
    function checkInputs() {
        const fileInputs = document.querySelectorAll("#uploadForm input[type='file']");
        let hasValidInput = false; // Flag to check if at least one input is valid

        for (let input of fileInputs) {
            const hiddenInput = input.nextElementSibling; // assuming the next element is the hidden input
            if (input.value !== "" || (hiddenInput && hiddenInput.value !== "")) {
                hasValidInput = true; // If any file or hidden input has a value, set the flag to true
                break; // No need to check further
            }
        }

        return hasValidInput; // Return true if at least one input is valid, otherwise false
    }

    function submitForm(button) {
        const action = button.getAttribute('data-action');

        if (action === "form2MOVupload_handler.php") {
            if (!checkInputs()) {
                $('#alertModal').modal('show');
                return; // Stop form submission if no valid inputs are provided
            }

            $('#confirmModal').modal('show');
            document.getElementById('confirmSubmit').onclick = function () {
                const form = document.getElementById('uploadForm');
                form.action = action;
                form.submit();
            };
        } else {
            const form = document.getElementById('uploadForm');
            form.action = action;
            form.submit();
        }
    }
</script>

<!-- Missing Files Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="alertModalLabel">Missing Files!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">Please verify that all required files are uploaded before submitting.</p>
                <p>Ensure each criteria has the necessary files attached.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-dark" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary" id="confirmModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">I confirm that all the criteria are correct.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" style="background-color: #00ace6;" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" style="background-color: #3366ff;" id="confirmSubmit">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="successModalLabel">Submission Successful</h5>
                <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Your submission has been successful.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Show success modal on confirm
document.getElementById('confirmSubmit').addEventListener('click', function () {
    $('#confirmModal').modal('hide');
    setTimeout(function () {
        $('#successModal').modal('show');
    }, 500);
});
</script>

<!-- Modal for Update (modalmov_message) -->
<div class="modal fade" id="modalmov_message" tabindex="-1" aria-labelledby="modalmovMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalmovMessageLabel">
                    <?php echo $_SESSION['modalmov_message'] === 'Files submitted successfully!' ? 'Success' : 'Notice'; ?>
                </h5>
                <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo htmlspecialchars($_SESSION['modalmov_message'] ?? ''); ?></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        <?php if (isset($_SESSION['modalmov_message'])): ?>
            var modalmovMessage = new bootstrap.Modal(document.getElementById('modalmov_message'));
            modalmovMessage.show();
            <?php unset($_SESSION['modalmov_message']); ?>
        <?php endif; ?>
    });
</script>


<!-- Main modal for pdf viewer   -->
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
