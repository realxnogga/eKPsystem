<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id'], $_SESSION['user_type'], $_SESSION['barangay_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../login.php?error=session_expired");
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

// Get selected year
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) && ctype_digit($_GET['year']) ? $_GET['year'] : $currentYear;

// Fetch uploaded files
$sql = "SELECT * FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id AND year = :year";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Fetch rates and remarks
$rate_sql = "SELECT * FROM movrate WHERE barangay = :barangay_id AND year = :year";
$remark_sql = "SELECT * FROM movremark WHERE barangay = :barangay_id AND year = :year";

$rate_stmt = $conn->prepare($rate_sql);
$remark_stmt = $conn->prepare($remark_sql);

$rate_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$rate_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$remark_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$remark_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);

$rate_stmt->execute();
$remark_stmt->execute();

$rate_row = $rate_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$remark_row = $remark_stmt->fetch(PDO::FETCH_ASSOC) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = 'movfolder/';
    foreach ($allowed_columns as $column) {
        if (isset($_FILES[$column]) && $_FILES[$column]['error'] === UPLOAD_ERR_OK) {
            $file_name = time() . '_' . basename($_FILES[$column]['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES[$column]['tmp_name'], $file_path)) {
                $row[$column] = $file_name;
            }
        } else {
            $row[$column] = $_POST[$column . '_hidden'] ?? null;
        }
    }

    $update_sql = "UPDATE mov SET ";
    foreach ($allowed_columns as $column) {
        $update_sql .= "$column = :$column, ";
    }
    $update_sql = rtrim($update_sql, ', ') . " WHERE user_id = :user_id AND barangay_id = :barangay_id AND year = :year";

    $update_stmt = $conn->prepare($update_sql);
    foreach ($allowed_columns as $column) {
        $update_stmt->bindValue(":$column", $row[$column] ?? null, PDO::PARAM_STR);
    }
    $update_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $update_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
    $update_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?year=" . $selectedYear);
        exit;
    } else {
        error_log("Update failed: " . print_r($update_stmt->errorInfo(), true));
    }
}

// Fetch years
$yearQuery = "SELECT DISTINCT year FROM mov";
$yearResult = $conn->query($yearQuery);
$years = $yearResult->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($currentYear, $years)) {
    $years[] = $currentYear;
}
rsort($years);
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
                <div class="flex items-center space-x-4">
                        <div class="dilglogo">
                            <img src="images/dilglogo.png" alt="DILG Logo" class="h-20" />
                        </div>
                        <h1 class="text-xl font-bold">
                Lupong Tagapamayapa Incentives Award (LTIA)
                        <form method="get" action=""  class="inline-block">
                        <select name="year" id="year" onchange="this.form.submit()">
                            <?php foreach ($years as $year) : ?>
                                <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form> 
                    <hr class="my-2">
            <span>Barangay </span> 
                  <span ><?= htmlspecialchars($barangayName, ENT_QUOTES, 'UTF-8') ?></span>, 
                  <span id="details-municipality-type" class="ml-2"></span>
                  <span>of <?= htmlspecialchars($municipalityName, ENT_QUOTES, 'UTF-8') ?></span>
               </h2>
                        <?php
                        // Update your SQL queries to include the selected year as a filter
                        $sql = "SELECT * FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id AND year = :year";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
                        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                        // Fetch rates from the movrate table for the selected year
                      $rate_sql = "SELECT * FROM movrate WHERE barangay = :barangay_id AND year = :year";
                      $rate_stmt = $conn->prepare($rate_sql);
                      $rate_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
                      $rate_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
                      $rate_stmt->execute();
                      $rate_row = $rate_stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize as an empty array if no records found

                      // Fetch remarks from the movremark table for the selected year
                      $remark_sql = "SELECT * FROM movremark WHERE barangay = :barangay_id AND year = :year";
                      $remark_stmt = $conn->prepare($remark_sql);
                      $remark_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
                      $remark_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
                      $remark_stmt->execute();
                      $remark_row = $remark_stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize as an empty array if no records found

                        ?>
                        </h1>
                    </div>
                    <div class="menu">
                        <ul class="flex space-x-4">
                            <li>
                            <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_dashboard.php';" style="margin-left: 0;">
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
                                    <th>Choose Files to Replace MOV</th>
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
            <td class="text-center align-middle"><input type="file" id="IA_1a_pdf_File" name="IA_1a_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit1', 'cancel1')" />
              <input type="hidden" name="IA_1a_pdf_File_hidden" id="IA_1a_pdf_File_hidden" value="<?php echo !empty($row['IA_1a_pdf_File']) ? htmlspecialchars($row['IA_1a_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_1a_pdf_File" id="submit1" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel1" onclick="clearInput('IA_1a_pdf_File', 'submit1', 'cancel1')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IA_1b_pdf_File" name="IA_1b_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit2', 'cancel2')" />
              <input type="hidden" name="IA_1b_pdf_File_hidden" id="IA_1b_pdf_File_hidden" value="<?php echo !empty($row['IA_1b_pdf_File']) ? htmlspecialchars($row['IA_1b_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_1b_pdf_File" id="submit2" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel2" onclick="clearInput('IA_1b_pdf_File', 'submit2', 'cancel2')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IA_2a_pdf_File" name="IA_2a_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit3', 'cancel3')" />
              <input type="hidden" name="IA_2a_pdf_File_hidden" id="IA_2a_pdf_File_hidden" value="<?php echo !empty($row['IA_2a_pdf_File']) ? htmlspecialchars($row['IA_2a_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2a_pdf_File" id="submit3" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel3" onclick="clearInput('IA_2a_pdf_File', 'submit3', 'cancel3')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IA_2b_pdf_File" name="IA_2b_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit4', 'cancel4')" />
              <input type="hidden" name="IA_2b_pdf_File_hidden" id="IA_2b_pdf_File_hidden" value="<?php echo !empty($row['IA_2b_pdf_File']) ? htmlspecialchars($row['IA_2b_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2b_pdf_File" id="submit4" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel4" onclick="clearInput('IA_2b_pdf_File', 'submit4', 'cancel4')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IA_2c_pdf_File" name="IA_2c_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit5', 'cancel5')" />
              <input type="hidden" name="IA_2c_pdf_File_hidden" id="IA_2c_pdf_File_hidden" value="<?php echo !empty($row['IA_2c_pdf_File']) ? htmlspecialchars($row['IA_2c_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2c_pdf_File" id="submit5" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel5" onclick="clearInput('IA_2c_pdf_File', 'submit5', 'cancel5')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IA_2d_pdf_File" name="IA_2d_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit6', 'cancel6')" />
              <input type="hidden" name="IA_2d_pdf_File_hidden" id="IA_2d_pdf_File_hidden" value="<?php echo !empty($row['IA_2d_pdf_File']) ? htmlspecialchars($row['IA_2d_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2d_pdf_File" id="submit6" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel6" onclick="clearInput('IA_2d_pdf_File', 'submit6', 'cancel6')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IA_2e_pdf_File" name="IA_2e_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit7', 'cancel7')" />
              <input type="hidden" name="IA_2e_pdf_File_hidden" id="IA_2e_pdf_File_hidden" value="<?php echo !empty($row['IA_2e_pdf_File']) ? htmlspecialchars($row['IA_2e_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2e_pdf_File" id="submit7" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel7" onclick="clearInput('IA_2e_pdf_File', 'submit7', 'cancel7')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
              </tr>
              <tr>
                <th>B. Systematic Maintenance of Records</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>1. Record of Cases </b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row">
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
            <td class="text-center align-middle"><input type="file" id="IB_1forcities_pdf_File" name="IB_1forcities_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit8', 'cancel8')" />
              <input type="hidden" name="IB_1forcities_pdf_File_hidden" id="IB_1forcities_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_1forcities_pdf_File']) ? htmlspecialchars($row['IB_1forcities_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_1forcities_pdf_File" id="submit2" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel8" onclick="clearInput('IB_1forcities_pdf_File', 'submit8', 'cancel8')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>For Municipalities:</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row">
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
            <td class="text-center align-middle"><input type="file" id="IB_1aformuni_pdf_File" name="IB_1aformuni_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit9', 'cancel9')" />
              <input type="hidden" name="IB_1aformuni_pdf_File_hidden" id="IB_1aformuni_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_1aformuni_pdf_File']) ? htmlspecialchars($row['IB_1aformuni_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_1aformuni_pdf_File" id="submit9" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel9" onclick="clearInput('IB_1aformuni_pdf_File', 'submit9', 'cancel9')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
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
            <td class="text-center align-middle"><input type="file" id="IB_1bformuni_pdf_File" name="IB_1bformuni_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit10', 'cancel10')" />
              <input type="hidden" name="IB_1bformuni_pdf_File_hidden" id="IB_1bformuni_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_1bformuni_pdf_File']) ? htmlspecialchars($row['IB_1bformuni_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_1bformuni_pdf_File" id="submit10" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel10" onclick="clearInput('IB_1bformuni_pdf_File', 'submit10', 'cancel10')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IB_2_pdf_File" name="IB_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit11', 'cancel11')" />
              <input type="hidden" name="IB_2_pdf_File_hidden" id="IB_2_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_2_pdf_File']) ? htmlspecialchars($row['IB_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_2_pdf_File" id="submit11" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel11" onclick="clearInput('IB_2_pdf_File', 'submit11', 'cancel11')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IB_3_pdf_File" name="IB_3_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit12', 'cancel12')" />
              <input type="hidden" name="IB_3_pdf_File_hidden" id="IB_3_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_3_pdf_File']) ? htmlspecialchars($row['IB_3_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_3_pdf_File" id="submit12" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel12" onclick="clearInput('IB_3_pdf_File', 'submit12', 'cancel12')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IB_4_pdf_File" name="IB_4_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit13', 'cancel13')" />
              <input type="hidden" name="IB_4_pdf_File_hidden" id="IB_4_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_4_pdf_File']) ? htmlspecialchars($row['IB_4_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_4_pdf_File" id="submit13" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel13" onclick="clearInput('IB_4_pdf_File', 'submit13', 'cancel13')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>C. Timely Submissions to the Court and the DILG</th>
                <th></th>
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
            <td class="text-center align-middle"><input type="file" id="IC_1_pdf_File" name="IC_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit14', 'cancel14')" />
              <input type="hidden" name="IC_1_pdf_File_hidden" id="IC_1_pdf_File_hidden" 
               value="<?php echo !empty($row['IC_1_pdf_File']) ? htmlspecialchars($row['IC_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IC_1_pdf_File" id="submit14" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel14" onclick="clearInput('IC_1_pdf_File', 'submit14', 'cancel14')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IC_2_pdf_File" name="IC_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit15', 'cancel15')" />
              <input type="hidden" name="IC_2_pdf_File_hidden" id="IC_2_pdf_File_hidden" 
               value="<?php echo !empty($row['IC_2_pdf_File']) ? htmlspecialchars($row['IC_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IC_2_pdf_File" id="submit15" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel15" onclick="clearInput('IC_2_pdf_File', 'submit15', 'cancel15')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>D. Conduct of monthly meetings for administration of the Katarungang Pambarangay (KP)</th>
                <th></th>
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
            <td class="text-center align-middle"><input type="file" id="ID_1_pdf_File" name="ID_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit16', 'cancel16')" />
              <input type="hidden" name="ID_1_pdf_File_hidden" id="ID_1_pdf_File_hidden" 
               value="<?php echo !empty($row['ID_1_pdf_File']) ? htmlspecialchars($row['ID_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="ID_1_pdf_File" id="submit16" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel16" onclick="clearInput('ID_1_pdf_File', 'submit16', 'cancel16')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="ID_2_pdf_File" name="ID_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit17', 'cancel17')" />
              <input type="hidden" name="ID_2_pdf_File_hidden" id="ID_2_pdf_File_hidden" 
               value="<?php echo !empty($row['ID_2_pdf_File']) ? htmlspecialchars($row['ID_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="ID_2_pdf_File" id="submit17" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel17" onclick="clearInput('ID_2_pdf_File', 'submit17', 'cancel17')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>II. EFFECTIVENESS IN SECURING THE SETTLEMENT OF INTERPERSONAL DISPUTE OBJECTIVE OF THE KATARUNGANG PAMBARANGAY</th>
                <th></th>
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
            <td class="text-center align-middle"><input type="file" id="IIA_pdf_File" name="IIA_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit18', 'cancel18')" />
              <input type="hidden" name="IIA_pdf_File_hidden" id="IIA_pdf_File_hidden" 
               value="<?php echo !empty($row['IIA_pdf_File']) ? htmlspecialchars($row['IIA_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIA_pdf_File" id="submit18" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel18" onclick="clearInput('IIA_pdf_File', 'submit18', 'cancel18')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>B. Quality of Settlement of Cases</td>
                <td></td>
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
            <td class="text-center align-middle"><input type="file" id="IIB_1_pdf_File" name="IIB_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit19', 'cancel19')" />
              <input type="hidden" name="IIB_1_pdf_File_hidden" id="IIB_1_pdf_File_hidden" 
               value="<?php echo !empty($row['IIB_1_pdf_File']) ? htmlspecialchars($row['IIB_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIB_1_pdf_File" id="submit19" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel19" onclick="clearInput('IIB_1_pdf_File', 'submit19', 'cancel19')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IIB_2_pdf_File" name="IIB_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit20', 'cancel20')" />
              <input type="hidden" name="IIB_2_pdf_File_hidden" id="IIB_2_pdf_File_hidden" 
               value="<?php echo !empty($row['IIB_2_pdf_File']) ? htmlspecialchars($row['IIB_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIB_2_pdf_File" id="submit20" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel20" onclick="clearInput('IIB_2_pdf_File', 'submit20', 'cancel20')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IIC_pdf_File" name="IIC_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit21', 'cancel21')" />
              <input type="hidden" name="IIC_pdf_File_hidden" id="IIC_pdf_File_hidden" 
               value="<?php echo !empty($row['IIC_pdf_File']) ? htmlspecialchars($row['IIC_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIC_pdf_File" id="submit21" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel21" onclick="clearInput('IIC_pdf_File', 'submit21', 'cancel21')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>III. CREATIVITY AND RESOURCEFULNESS OF THE LUPONG TAGAPAMAYAPA</th>
                <th></th>
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
            <td class="text-center align-middle"><input type="file" id="IIIA_pdf_File" name="IIIA_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit22', 'cancel22')" />
              <input type="hidden" name="IIIA_pdf_File_hidden" id="IIIA_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIA_pdf_File']) ? htmlspecialchars($row['IIIA_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIA_pdf_File" id="submit22" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel22" onclick="clearInput('IIIA_pdf_File', 'submit22', 'cancel22')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IIIB_pdf_File" name="IIIB_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit23', 'cancel23')" />
              <input type="hidden" name="IIIB_pdf_File_hidden" id="IIIB_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIB_pdf_File']) ? htmlspecialchars($row['IIIB_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIB_pdf_File" id="submit23" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel23" onclick="clearInput('IIIB_pdf_File', 'submit23', 'cancel23')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>C. Sustained information drive to promote Katarungang Pambarangay</td>
                <td></td>
                <td></td>
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
                <td>
                <?php if (!empty($row['IIIC_1forcities_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_1forcities_pdf_rate']) ? $rate_row['IIIC_1forcities_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_1forcities_pdf_remark']) ? $remark_row['IIIC_1forcities_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIC_1forcities_pdf_File" name="IIIC_1forcities_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit24', 'cancel24')" />
              <input type="hidden" name="IIIC_1forcities_pdf_File_hidden" id="IIIC_1forcities_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_1forcities_pdf_File']) ? htmlspecialchars($row['IIIC_1forcities_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_1forcities_pdf_File" id="submit24" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel24" onclick="clearInput('IIIC_1forcities_pdf_File', 'submit24', 'cancel24')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="city-row">
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
            <td class="text-center align-middle"><input type="file" id="IIIC_1forcities2_pdf_File" name="IIIC_1forcities2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit25', 'cancel25')" />
              <input type="hidden" name="IIIC_1forcities2_pdf_File_hidden" id="IIIC_1forcities2_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_1forcities2_pdf_File']) ? htmlspecialchars($row['IIIC_1forcities2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_1forcities2_pdf_File" id="submit25" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel25" onclick="clearInput('IIIC_1forcities2_pdf_File', 'submit25', 'cancel25')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="city-row">
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
            <td class="text-center align-middle"><input type="file" id="IIIC_1forcities3_pdf_File" name="IIIC_1forcities3_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit26', 'cancel26')" />
              <input type="hidden" name="IIIC_1forcities3_pdf_File_hidden" id="IIIC_1forcities3_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_1forcities3_pdf_File']) ? htmlspecialchars($row['IIIC_1forcities3_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_1forcities3_pdf_File" id="submit26" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel26" onclick="clearInput('IIIC_1forcities3_pdf_File', 'submit26', 'cancel26')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>2. For Municipalities</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row">
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
            <td class="text-center align-middle"><input type="file" id="IIIC_2formuni1_pdf_File" name="IIIC_2formuni1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit27', 'cancel27')" />
              <input type="hidden" name="IIIC_2formuni1_pdf_File_hidden" id="IIIC_2formuni1_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_2formuni1_pdf_File']) ? htmlspecialchars($row['IIIC_2formuni1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_2formuni1_pdf_File" id="submit27" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel27" onclick="clearInput('IIIC_2formuni1_pdf_File', 'submit27', 'cancel27')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
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
            <td class="text-center align-middle"><input type="file" id="IIIC_2formuni2_pdf_File" name="IIIC_2formuni2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit28', 'cancel28')" />
              <input type="hidden" name="IIIC_2formuni2_pdf_File_hidden" id="IIIC_2formuni2_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_2formuni2_pdf_File']) ? htmlspecialchars($row['IIIC_2formuni2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_2formuni2_pdf_File" id="submit28" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel28" onclick="clearInput('IIIC_2formuni2_pdf_File', 'submit28', 'cancel28')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
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
            <td class="text-center align-middle"><input type="file" id="IIIC_2formuni3_pdf_File" name="IIIC_2formuni3_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit29', 'cancel29')" />
              <input type="hidden" name="IIIC_2formuni3_pdf_File_hidden" id="IIIC_2formuni3_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_2formuni3_pdf_File']) ? htmlspecialchars($row['IIIC_2formuni3_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_2formuni3_pdf_File" id="submit29" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel29" onclick="clearInput('IIIC_2formuni3_pdf_File', 'submit29', 'cancel29')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
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
            <td class="text-center align-middle"><input type="file" id="IIID_pdf_File" name="IIID_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit30', 'cancel30')" />
              <input type="hidden" name="IIID_pdf_File_hidden" id="IIID_pdf_File_hidden" 
               value="<?php echo !empty($row['IIID_pdf_File']) ? htmlspecialchars($row['IIID_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIID_pdf_File" id="submit30" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel30" onclick="clearInput('IIID_pdf_File', 'submit30', 'cancel30')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>IV. AREA OR FACILITY FOR KP ACTIVITIES</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>Building structure or space:</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row">
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
            <td class="text-center align-middle"><input type="file" id="IV_forcities_pdf_File" name="IV_forcities_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit31', 'cancel31')" />
              <input type="hidden" name="IV_forcities_pdf_File_hidden" id="IV_forcities_pdf_File_hidden" 
               value="<?php echo !empty($row['IV_forcities_pdf_File']) ? htmlspecialchars($row['IV_forcities_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IV_forcities_pdf_File" id="submit31" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel31" onclick="clearInput('IV_forcities_pdf_File', 'submit31', 'cancel31')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
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
            <td class="text-center align-middle"><input type="file" id="IV_muni_pdf_File" name="IV_muni_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit32', 'cancel32')" />
              <input type="hidden" name="IV_muni_pdf_File_hidden" id="IV_muni_pdf_File_hidden" 
               value="<?php echo !empty($row['IV_muni_pdf_File']) ? htmlspecialchars($row['IV_muni_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IV_muni_pdf_File" id="submit32" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel32" onclick="clearInput('IV_muni_pdf_File', 'submit32', 'cancel32')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>  
          </tr>
              <tr>
                <th>V. FINANCIAL OR NON-FINANCIAL SUPPORT</th>
                <th></th>
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
            <td class="text-center align-middle"><input type="file" id="V_1_pdf_File" name="V_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit33', 'cancel33')" />
              <input type="hidden" name="V_1_pdf_File_hidden" id="V_1_pdf_File_hidden" 
               value="<?php echo !empty($row['V_1_pdf_File']) ? htmlspecialchars($row['V_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="V_1_pdf_File" id="submit33" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel33" onclick="clearInput('V_1_pdf_File', 'submit33', 'cancel33')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>  
          </tr>
              <tr>
                <td>3 From People's Organizations, NGOs or Private Sector</td>
                <td>
                <?php if (!empty($row['threepeoplesorg_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['threepeoplesorg_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>   
            <td><?php echo isset($rate_row['threepeoplesorg_rate']) ? $rate_row['threepeoplesorg_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['threepeoplesorg_remark']) ? $remark_row['threepeoplesorg_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="threepeoplesorg_pdf_File" name="threepeoplesorg_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit34', 'cancel34')" />
              <input type="hidden" name="threepeoplesorg_pdf_File_hidden" id="threepeoplesorg_pdf_File_hidden" 
               value="<?php echo !empty($row['threepeoplesorg_pdf_File']) ? htmlspecialchars($row['threepeoplesorg_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="threepeoplesorg_pdf_File" id="submit34" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel34" onclick="clearInput('threepeoplesorg_pdf_File', 'submit34', 'cancel34')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>   
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
    <!-- Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="responseModalLabel">Notification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($message)) echo htmlspecialchars($message); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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
    document.addEventListener("DOMContentLoaded", function() {
    <?php if (!empty($message)) : ?>
        var responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
        responseModal.show();
    <?php endif; ?>
});
    $(document).ready(function() {
        $('.view-pdf').attr('data-modal-target', 'large-modal');
        $('.view-pdf').attr('data-modal-toggle', 'large-modal');

        $('.view-pdf').click(function() {
            var pdfFile = $(this).data('file'); // Get the PDF file path from data attribute
            $('#pdfViewer').attr('src', pdfFile); // Set the file path in the iframe   

        });
    });
    function toggleSubmitButton(input, submitId, cancelId) {
    const submitButton = document.getElementById(submitId);
    const cancelButton = document.getElementById(cancelId);

    if (input.files.length > 0) {
        if (submitButton) submitButton.style.display = 'inline-block'; // Show Update button
        if (cancelButton) cancelButton.style.display = 'inline-block'; // Show Cancel button
    } else {
        if (submitButton) submitButton.style.display = 'none'; // Hide Update button
        if (cancelButton) cancelButton.style.display = 'none'; // Hide Cancel button
    }
}

function clearInput(inputId, submitId, cancelId) {
    const input = document.getElementById(inputId);
    const submitButton = document.getElementById(submitId);
    const cancelButton = document.getElementById(cancelId);

    if (input) input.value = ''; // Clear file input
    if (submitButton) submitButton.style.display = 'none'; // Hide Update button
    if (cancelButton) cancelButton.style.display = 'none'; // Hide Cancel button
}


function validateFileType(input) {
    const file = input.files[0];
    if (file && !file.name.endsWith('.pdf')) {
        alert('Please upload a valid PDF file.');
        input.value = ''; // Clear the invalid file
    }
}
  
  </script>
</body>
</html>
