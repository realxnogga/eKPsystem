<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

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
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="LTIAassets/jquery-3.6.4.min.js" defer></script>
  <script src="js/tables.js" defer></script>
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
                            <div class="ml-4">
            <h1 class="text-xl font-bold">
                Lupong Tagapamayapa Incentives Award (LTIA)  <?php echo date('Y'); ?>
            </h1>
            <hr class="my-2">
            <h2 class="text-lg font-semibold">
           <span>Barangay </span> <?php echo htmlspecialchars($barangayName, ENT_QUOTES, 'UTF-8'); ?>, 
           <span id="details-municipality-type" class="ml-2"></span> of  <?php echo htmlspecialchars($municipalityName, ENT_QUOTES, 'UTF-8'); ?>
            </h2>
        </div>
    </div>
                        <div class="menu">
                            <ul class="flex space-x-4">      
                        <li>
                        <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_dashboard.php';" style="margin-left: 0;">
                    <i class="ti ti-arrow-left-dashed mr-2"></i>
                    Back
                        </li>             
                            </ul>
                        </div>
                    </div>
      <!-- <h2 class="text-left text-2xl font-semibold">FORM 1</h2> -->
                            <?php if ($submissionExists) : ?>
    <h1><i>You have already saved a </i></h1>
    <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='form2draftmov.php';" style="margin-left: 0;">
        <!-- <i class="ti ti-file-upload mr-2"></i> Icon -->
        Draft
    </button>
<?php else : ?>
    <form method="post" action="movdraft_handler.php" enctype="multipart/form-data">
        <div class="container mt-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>CRITERIA</th>
                        <th>Means Of Verification</th>
                    </tr>
                    <tr>
                        <th colspan="2">I. EFFICIENCY IN OPERATION</th>
                    </tr>
                    <tr>
                        <th colspan="2">A. Observance of Settlement Procedure and Settlement Deadlines</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>1. a) Proper Recording of every dispute/complaint</b></td>
                        <td><input type="file" id="IA_1a_pdf_File" name="IA_1a_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>b) Sending of Notices and Summons with complete and accurate information to the parties within the prescribed period (within the next working day upon receipt of complaint)</td>
                        <td><input type="file" id="IA_1b_pdf_File" name="IA_1b_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">2. Settlement and Award Period (with at least 10 settled cases within the assessment period)</td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                        <td><input type="file" id="IA_2a_pdf_File" name="IA_2a_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                        <td><input type="file" id="IA_2b_pdf_File" name="IA_2b_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; c) Conciliation (with extended period not to exceed another 15 days)</td>
                        <td><input type="file" id="IA_2c_pdf_File" name="IA_2c_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                        <td><input type="file" id="IA_2d_pdf_File" name="IA_2d_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
                        <td><input type="file" id="IA_2e_pdf_File" name="IA_2e_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <th colspan="2">B. Systematic Maintenance of Records</th>
                    </tr>
                    <tr>
                        <td colspan="2"><b>1. Record of Cases </b></td>
                    </tr>
                    <tr id="city-row">
                        <td>&emsp;&emsp;&emsp; For Cities - computer database with searchable case information</td>
                        <td><input type="file" id="IB_1forcities_pdf_File" name="IB_1forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="municipality-row">
                        <td colspan="2">&emsp;&emsp;&emsp; For Municipalities:</td>
                    </tr>
                    <tr id="municipality-row">
                        <td>&emsp;&emsp;&emsp; a. Manual Records</td>
                        <td><input type="file" id="IB_1aformuni_pdf_File" name="IB_1aformuni_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="municipality-row">
                        <td>&emsp;&emsp;&emsp; b. Digital Record Filing</td>
                        <td><input type="file" id="IB_1bformuni_pdf_File" name="IB_1bformuni_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; 2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                        <td><input type="file" id="IB_2_pdf_File" name="IB_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; 3. Copies of reports submitted to the Court and to the DILG on file</td>
                        <td><input type="file" id="IB_3_pdf_File" name="IB_3_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; 4. All records are kept on file in a secured filing cabinet(s)</td>
                        <td><input type="file" id="IB_4_pdf_File" name="IB_4_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <th colspan="2">C. Timely Submissions to the Court and the DILG</th>
                    </tr>
                    <tr>
                        <td>1. <b>To the Court:</b> Submitted/ presented copies of settlement agreement to the Court from the lapse of the ten-day period repudiating the mediation/ conciliation settlement agreement, or within five (5) calendar days from the date of the arbitration award</td>
                        <td><input type="file" id="IC_1_pdf_File" name="IC_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>2. To the DILG (Quarterly)</td>
                        <td><input type="file" id="IC_2_pdf_File" name="IC_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <th colspan="2">D. Conduct of monthly meetings for administration of the Katarungang Pambarangay (KP)</th>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; 1. Notice of Meeting</td>
                        <td><input type="file" id="ID_1_pdf_File" name="ID_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp; 2. Minutes of the Meeting</td>
                        <td><input type="file" id="ID_2_pdf_File" name="ID_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <th colspan="2">II. EFFECTIVENESS IN SECURING THE SETTLEMENT OF INTERPERSONAL DISPUTE OBJECTIVE OF THE KATARUNGANG PAMBARANGAY</th>
                    </tr>
                    <tr>
                        <td>A. Quantity of settled cases against filed</td>
                        <td><input type="file" id="IIA_pdf_File" name="IIA_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">B. Quality of Settlement of Cases</td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp;1. Zero cases repudiated</td>
                        <td><input type="file" id="IIB_1_pdf_File" name="IIB_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>&emsp;&emsp;&emsp;2. Non-recurrence of cases settled</td>
                        <td><input type="file" id="IIB_2_pdf_File" name="IIB_2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>C. At least 80% compliance with the terms of settlement or award after the cases have been settled</td>
                        <td><input type="file" id="IIC_pdf_File" name="IIC_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <th colspan="2">III. CREATIVITY AND RESOURCEFULNESS OF THE LUPONG TAGAPAMAYAPA</th>
                    </tr>
                    <tr>
                        <td>A. Settlement Technique utilized by the Lupon</td>
                        <td><input type="file" id="IIIA_pdf_File" name="IIIA_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>B. Coordination with Concerned Agencies relating to disputes filed (PNP, DSWD, DILG, DAR, DENR, Office of the Prosecutor, Court, DOJ, CHR, etc.)</td>
                        <td><input type="file" id="IIIB_pdf_File" name="IIIB_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">C. Sustained information drive to promote Katarungang Pambarangay</td>
                    </tr>
                    <tr id="city-row">
                        <td colspan="2">1. For Cities</td>
                    </tr>
                    <tr id="city-row">
                        <td>
                            <ul>
                                <li>&emsp;&emsp;&emsp;IEC materials developed</li>
                            </ul>
                        </td>
                        <td><input type="file" id="IIIC_1forcities_pdf_File" name="IIIC_1forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="city-row">
                        <td>
                            <ul>
                                <li>&emsp;&emsp;&emsp;IEC activities conducted</li>
                            </ul>
                        </td>
                        <td><input type="file" id="IIIC_1forcities2_pdf_File" name="IIIC_1forcities2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="city-row">
                        <td>
                            <ul>
                                <li>&emsp;&emsp;&emsp;Innovative Campaign Strategy</li>
                            </ul>
                        </td>
                        <td><input type="file" id="IIIC_1forcities3_pdf_File" name="IIIC_1forcities3_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="municipality-row">
                        <td colspan="2">2. For Municipalities</td>
                    </tr>
                    <tr id="municipality-row">
                        <td>
                            <ul>
                                <li>&emsp;&emsp;&emsp; IEC materials developed</li>
                            </ul>
                        </td>
                        <td><input type="file" id="IIIC_2formuni1_pdf_File" name="IIIC_2formuni1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="municipality-row">
                        <td>
                            <ul>
                                <li>&emsp;&emsp;&emsp; IEC activities conducted</li>
                            </ul>
                        </td>
                        <td><input type="file" id="IIIC_2formuni2_pdf_File" name="IIIC_2formuni2_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="municipality-row">
                        <td>
                            <ul>
                                <li>&emsp;&emsp;&emsp; Innovative Campaign Strategy</li>
                            </ul>
                        </td>
                        <td><input type="file" id="IIIC_2formuni3_pdf_File" name="IIIC_2formuni3_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>D. KP Training or seminar within the assessment period<br />
                            Organized skills training participated by the Lupong Tagapamayapa</td>
                        <td><input type="file" id="IIID_pdf_File" name="IIID_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <th colspan="2">IV. AREA OR FACILITY FOR KP ACTIVITIES</th>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Building structure or space:</b></td>
                    </tr>
                    <tr id="city-row">
                        <td>For Cities - the office or space should be exclusive for KP matters</td>
                        <td><input type="file" id="IV_forcities_pdf_File" name="IV_forcities_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr id="municipality-row">
                        <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                        <td><input type="file" id="IV_muni_pdf_File" name="IV_muni_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <th colspan="2">V. FINANCIAL OR NON-FINANCIAL SUPPORT</th>
                    </tr>
                    <tr>
                        <td>1. From City, Municipal, Provincial or NGAs</td>
                        <td><input type="file" id="V_1_pdf_File" name="V_1_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                    <tr>
                        <td>3 From People's Organizations, NGOs or Private Sector</td>
                        <td><input type="file" id="3peoplesorg" name="threepeoplesorg_pdf_File" accept=".pdf" onchange="validateFileType(this)" /></td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" value="Save" class="btn btn-dark btn-block mt-5" style="height: 50px; width: 50%; background-color: #000000; color: #ffffff;" />
        </div>
    </form>
<?php endif; ?>

      <footer class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-full">
                <path fill="#0099ff" fill-opacity="1" d="M0,224L30,224C60,224,120,224,180,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
            </svg>
            <div class="absolute right-0 bottom-0 mb-4 mr-4">
                <img src="images/ltialogo.png" alt="LTIA Logo" class="h-20" /> <!-- Adjust height as needed -->
            </div>
        </footer>
        </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- Files Already Uploaded Modal -->
<div class="modal fade" id="alreadyUploadedModal" tabindex="-1" aria-labelledby="alreadyUploadedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- Centered the modal -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning" id="alreadyUploadedModalLabel">Notice</h5>
                <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button> <!-- Updated close button for Bootstrap 5 -->
            </div>
            <div class="modal-body text-center"> <!-- Centered the content -->
                <p>The files have already been uploaded for this barangay. Please check the draft.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this modal HTML before the closing </body> tag -->
<div class="modal fade" id="noFileUploadedModal" tabindex="-1" aria-labelledby="noFileUploadedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning" id="noFileUploadedModalLabel">Notice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Please Attach file.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Okay</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Show the "No File Uploaded" modal if the condition is met
    <?php if (isset($_SESSION['no_file_uploaded']) && $_SESSION['no_file_uploaded']): ?>
        document.addEventListener("DOMContentLoaded", function () {
            $('#noFileUploadedModal').modal('show');
            <?php unset($_SESSION['no_file_uploaded']); ?>
        });
    <?php endif; ?>
</script>

<script>
// Show the "Files Already Uploaded" modal if the condition is met
<?php if ($already_uploaded > 0): ?>
    document.addEventListener("DOMContentLoaded", function () {
        var alreadyUploadedModal = new bootstrap.Modal(document.getElementById('alreadyUploadedModal'));
        alreadyUploadedModal.show();
    });
<?php endif; ?>
</script>

<!-- Include Bootstrap 5 JavaScript library -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

    <style>
      .custom-h2 {
        font-size: 2rem;
        /* Adjust the font size */
        font-weight: bold;
        /* Make the text bold */
        color: #007bff;
        /* Bootstrap primary color (blue) */
        text-align: center;
        /* Center align the heading */
        text-transform: uppercase;
        /* Uppercase the text */
        padding: 10px 0;
        /* Add padding above and below */
        border-bottom: 2px solid #007bff;
        /* Add a blue underline */
        margin-bottom: 20px;
        /* Space below the heading */
      }

      .bg-info {
        background-color: #17a2b8 !important;
        /* Sky blue */
      }

      .table-responsive {
        margin-top: 20px;
      }

      /* Sky Blue for header and subheaders */
      .bg-skyblue {
        background-color: #87CEEB;
        /* Sky blue */
      }

      /* Yellow for Efficiency in Operation */
      .bg-yellow {
        background-color: #FFD700;
        /* Yellow */
      }

      /* Center text and add white color for sky blue headers */
      .text-white {
        color: white;
      }

      .table th,
      .table td {
        vertical-align: middle;
      }
      table tbody tr:hover {
      background-color: #f0f0f0; /* Change this color as desired */
      cursor: pointer; /* Optional: change cursor to pointer when hovering */
    }
    </style>
</body>

</html>