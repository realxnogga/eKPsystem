<?php
session_start();
include '../connection.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Define all allowed file columns
$allowed_columns = [
    'IA_1a_pdf_File', 'IA_1b_pdf_File', 'IA_2_pdf_File', 'IA_2a_pdf_File', 'IA_2b_pdf_File', 
    'IA_2c_pdf_File', 'IA_2d_pdf_File', 'IA_2e_pdf_File', 'IB_1forcities_pdf_File', 
    'IB_1aformuni_pdf_File', 'IB_1bformuni_pdf_File', 'IB_2_pdf_File', 'IB_3_pdf_File', 
    'IB_4_pdf_File', 'IC_1_pdf_File', 'IC_2_pdf_File', 'ID_1_pdf_File', 'ID_2_pdf_File', 
    'IIA_pdf_File', 'IIB_1_pdf_File', 'IIB_2_pdf_File', 'IIC_pdf_File', 'IIIA_pdf_File', 
    'IIIB_pdf_File', 'IIIC_1forcities_pdf_File', 'IIIC_1forcities2_pdf_File', 
    'IIIC_1forcities3_pdf_File', 'IIIC_2formuni1_pdf_File', 'IIIC_2formuni2_pdf_File', 
    'IIIC_2formuni3_pdf_File', 'IIID_pdf_File', 'IV_forcities_pdf_File', 'IV_muni_pdf_File', 
    'V_1_pdf_File', 'threepeoplesorg'
];

// Fetch uploaded files from the database
$sql = "SELECT " . implode(', ', $allowed_columns) . " FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize $row as an empty array if no records found

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads
    $upload_dir = 'movfolder/';
    
    foreach ($_FILES as $key => $file) {
        if ($file['error'] === UPLOAD_ERR_OK && in_array($key, $allowed_columns)) {
            $file_name = time() . '_' . basename($file['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Update the database with the uploaded file path
                $update_sql = "UPDATE mov SET $key = :file_path WHERE user_id = :user_id AND barangay_id = :barangay_id";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bindParam(':file_path', $file_name, PDO::PARAM_STR);
                $update_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $update_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
                
                if (!$update_stmt->execute()) {
                    error_log(print_r($update_stmt->errorInfo(), true)); // Log error
                }
            }
        }
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

  <!-- <link rel="stylesheet" href="../assets/css/styles.min.css" /> -->
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  
  <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
  <!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script> -->

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
                                <img src="images/dilglogo.png" alt="DILG Logo" class="h-20" /> <!-- Adjusted height here -->
                            </div>
                            <h1 class="text-xl font-bold ml-4">Lupong Tagapamayapa Incentives Award (LTIA)</h1>
                        </div>
                        <div class="menu">
                            <ul class="flex space-x-4">
                            <li>
                            <input type="button" class="bg-gray-800 hover:bg-black-700 px-3 py-2 rounded-md text-white" value="Back" onclick="location.href='form2MOVupload.php';" style="margin-left: 0;">
                            </li>                      
                            </ul>
                        </div>
                    </div>
  <div class="container mt-5">
    <h2 class="text-left text-2xl font-semibold">FORM 1</h2>
    <form method="post" action="form2update.php" enctype="multipart/form-data">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>CRITERIA</th>
            <th>Means Of Verification</th>
            <th>File</th>
            <th>Rate</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><b>1. a) Proper Recording of every dispute/complaint</b></td>
            <td><input type="file" id="IA_1a_pdf_File" name="IA_1a_pdf_File" accept=".pdf" /></td>
            <td>
              <?php if (!empty($row['IA_1a_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
          </tr>
          <tr>
            <td>b) Sending of Notices and Summons</td>
            <td><input type="file" id="IA_1b_pdf_File" name="IA_1b_pdf_File" accept=".pdf" read/></td>
            <td>
              <?php if (!empty($row['IA_1b_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
          </tr>

          <tr>
                <td>2. Settlement and Award Period (with at least 10 settled cases within the assessment period)</td>
                <td><input type="file" id="IA_2_pdf_File" name="IA_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                  <?php if (!empty($row['IA_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
               <tr>
                <td>a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                <td><input type="file" id="IA_2a_pdf_File" name="IA_2a_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IA_2a_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td><input type="file" id="IA_2b_pdf_File" name="IA_2b_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IA_2b_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>c) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td><input type="file" id="IA_2c_pdf_File" name="IA_2c_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IA_2c_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2c_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td><input type="file" id="IA_2d_pdf_File" name="IA_2d_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IA_2d_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2d_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
                <td><input type="file" id="IA_2e_pdf_File" name="IA_2e_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IA_2e_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2e_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
              <tr>
                <td>For Cities - computer database with searchable case information</td>
                <td><input type="file" id="IB_1forcities_pdf_File" name="IB_1forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IB_1forcities_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>For Municipalities:</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>a. Manual Records</td>
                <td><input type="file" id="IB_1aformuni_pdf_File" name="IB_1aformuni_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IB_1aformuni_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1aformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>b. Digital Record Filing</td>
                <td><input type="file" id="IB_1bformuni_pdf_File" name="IB_1bformuni_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                  <?php if (!empty($row['IB_1bformuni_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1bformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td><input type="file" id="IB_2_pdf_File" name="IB_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IB_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td><input type="file" id="IB_3_pdf_File" name="IB_3_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IB_3_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td><input type="file" id="IB_4_pdf_File" name="IB_4_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IB_4_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_4_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
                <td><input type="file" id="IC_1_pdf_File" name="IC_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IC_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>2. To the DILG (Quarterly)</td>
                <td><input type="file" id="IC_2_pdf_File" name="IC_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IC_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
                <td><input type="file" id="ID_1_pdf_File" name="ID_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['ID_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>2. Minutes of the Meeting</td>
                <td><input type="file" id="ID_2_pdf_File" name="ID_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['ID_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
                <td><input type="file" id="IIA_pdf_File" name="IIA_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIA_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
                <td><input type="file" id="IIB_1_pdf_File" name="IIB_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIB_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>
                <td><input type="file" id="IIB_2_pdf_File" name="IIB_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIB_2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>C. At least 80% compliance with the terms of settlement or award after the cases have been settled</td>
                <td><input type="file" id="IIC_pdf_File" name="IIC_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIC_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIC_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
                <td><input type="file" id="IIIA_pdf_File" name="IIIA_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIA_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>B. Coordination with Concerned Agencies relating to disputes filed (PNP, DSWD, DILG, DAR, DENR, Office of the Prosecutor, Court, DOJ, CHR, etc.)</td>
                <td><input type="file" id="IIIB_pdf_File" name="IIIB_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIB_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIB_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>C. Sustained information drive to promote Katarungang Pambarangay</td>
                <td></td>
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
                <td><input type="file" id="IIIC_1forcities_pdf_File" name="IIIC_1forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIC_1forcities_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_1forcities2_pdf_File" name="IIIC_1forcities2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIC_1forcities2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_1forcities3_pdf_File" name="IIIC_1forcities3_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIC_1forcities3_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
            </tr>
              <tr>
                <td>2. For Municipalities</td>
                <td></td>
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
                <td><input type="file" id="IIIC_2formuni1_pdf_File" name="IIIC_2formuni1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIC_2formuni1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_2formuni2_pdf_File" name="IIIC_2formuni2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIC_2formuni2_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td><input type="file" id="IIIC_2formuni3_pdf_File" name="IIIC_2formuni3_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIIC_2formuni3_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
            </tr>
              <tr>
                <td>D. KP Training or seminar within the assessment period<br />
                  Organized skills training participated by the Lupong Tagapamayapa</td>
                <td><input type="file" id="IIID_pdf_File" name="IIID_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IIID_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIID_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
              <tr>
                <td>For Cities - the office or space should be exclusive for KP matters</td>
                <td><input type="file" id="IV_forcities_pdf_File" name="IV_forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IV_forcities_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
            </tr>
              <tr>
                <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td><input type="file" id="IV_muni_pdf_File" name="IV_muni_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['IV_muni_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_muni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
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
                <td><input type="file" id="V_1_pdf_File" name="V_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['V_1_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['V_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>
              <tr>
                <td>3 From People's Organizations, NGOs or Private Sector</td>
                <td><input type="file" id="3peoplesorg" name="threepeoplesorg" accept=".pdf" onchange="validateFileType(this)" /></td>
                <td>
                <?php if (!empty($row['threepeoplesorg'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['threepeoplesorg']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>rate here</td>
            <td>this is remark</td>
              </tr>

              <tr>
              <th>Total here</th>
                <td></td>
                <td>
                
            </td>
            <td>Total here</td>
            <td></td>
              </tr>
            </tbody>
          </table>
      <input type="submit" value="Update" class="btn btn-dark mt-3" />
    </form>


<!-- Main modal -->
<div id="large-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white shadow rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">

                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
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
            <div class="p-4 md:p-5 space-y-4">
              <iframe id="pdfViewer" src="" class="h-[75%] w-full "></iframe>
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
