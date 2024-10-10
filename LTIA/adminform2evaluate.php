<?php
session_start();

include '../connection.php'; // Ensure this file is using a PDO connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID from the session

try {
    // Step 1: Retrieve the municipality_id from the logged-in user's record
    $query = "SELECT municipality_id FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_row && isset($user_row['municipality_id'])) {
        $municipality_id = $user_row['municipality_id'];
        
        // Step 2: Use the municipality_id to fetch the corresponding municipality_name
        $query = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $municipality_row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Step 3: Set the municipality name for use in the form
        if ($municipality_row && isset($municipality_row['municipality_name'])) {
            $municipality_name = $municipality_row['municipality_name'];
        } else {
            $municipality_name = 'No municipality found for this user';
        }

        // Step 4: Fetch barangays associated with this municipality
        $query = "SELECT id, barangay_name FROM barangays WHERE municipality_id = :municipality_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        $barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $municipality_name = 'No municipality ID found for this user';
        $barangays = []; // Empty array if no barangays found
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secretaries Corner</title>
<style>
/* CSS to ensure alerts fit well within the table cells */
.alert {
    display: flex;           /* Use flex to center content */
    align-items: center;    /* Vertically center the content */
    justify-content: center; /* Horizontally center the content */
    padding: 0.5rem 1rem;   /* Adjust padding for better spacing */
    margin: 0;              /* Remove margin to prevent overflow */
    border-radius: 0.25rem; /* Optional: make the corners rounded */
    font-size: 0.875rem;    /* Optional: adjust font size for better readability */
}

.file-column {
    overflow: hidden;       /* Prevent overflow of alert */
    text-overflow: ellipsis; /* Add ellipsis if text is too long */
    white-space: nowrap;    /* Prevent wrapping */
}

</style>
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
 <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
$(document).ready(function () {
    // Handle barangay selection
    $('#barangay_select').on('change', function () {
        var selectedBarangayName = $(this).val();
        $('#selected_barangay').val(selectedBarangayName);

        if (selectedBarangayName) {
            $.ajax({
                url: 'fetch_files.php',
                method: 'POST',
                data: { barangay_name: selectedBarangayName },
                dataType: 'json',
                success: function (data) {
                    console.log('Returned data:', data);

                    // Handle each PDF file from the returned data
                    var fileTypes = [
                        'IA_1a', 'IA_1b', 'IA_2a', 'IA_2b', 'IA_2c', 'IA_2d', 'IA_2e',
                        'IB_1forcities', 'IB_1aformuni', 'IB_1bformuni', 'IB_2', 'IB_3',
                        'IB_4', 'IC_1', 'IC_2', 'ID_1', 'ID_2', 'IIA', 'IIB_1', 'IIB_2',
                        'IIC', 'IIIA', 'IIIB', 'IIIC_1forcities', 'IIIC_1forcities2',
                        'IIIC_1forcities3', 'IIIC_2formuni1', 'IIIC_2formuni2', 'IIIC_2formuni3',
                        'IIID', 'IV_forcities', 'IV_muni', 'V_1', 'threepeoplesorg'
                    ];

                    // Loop through each file type and handle visibility
                    fileTypes.forEach(function (type) {
                        var fileColumn = $('.file-column[data-type="' + type + '"]'); // Get the specific file column

                        if (data[type + '_pdf_File'] || data['threepeoplesorg']) {
                            var filePath = 'movfolder/' + (data[type + '_pdf_File'] || data['threepeoplesorg']);
                            $('.view-pdf[data-type="' + type + '"]').attr('data-file', filePath).show(); // Show view button
                            fileColumn.html('<button type="button" class="btn btn-primary view-pdf" data-type="' + type + '" data-file="' + filePath + '">View</button>'); // Add view button to the file column
                        } else {
                            // No file uploaded, show "No uploaded file" message
                            fileColumn.html('<div class="alert alert-warning mb-0">No uploaded file</div>');
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.log('Error fetching files:', xhr.responseText);
                }
            });
        } else {
            // If no barangay is selected, clear all file columns
            $('.file-column').html('<div class="alert alert-info mb-0">Select barangay</div>');
        }
    });

    // Handle PDF viewing inside the modal
    $(document).on('click', '.view-pdf', function () {
        var file = $(this).data('file'); // Get the file URL
        console.log('PDF URL:', file); // Debug the file path

        if (file) {
            // Set the source of the iframe in the modal to the PDF URL
            $('#pdfViewer').attr('src', file);

            // Show the modal by removing the hidden class
            $('#large-modal').removeClass('hidden');
        } else {
            alert('No file available to view.');
        }
    });

    // Close the modal when the close button is clicked
    $('[data-modal-hide="large-modal"]').on('click', function () {
        $('#large-modal').addClass('hidden'); // Hide the modal
        $('#pdfViewer').attr('src', ''); // Clear the iframe src when modal is closed
    });
});

</script>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "../admin_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
    <div class="card">
    <div class="card-body">
    <div class="flex justify-between items-center mb-4">
    <div class="flex items-center">
        <div class="dilglogo">
            <img src="../img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
        </div>
        <div class="ml-4">
            <h1 class="text-xl font-bold">
                Lupong Tagapamayapa Incentives Award (LTIA)
            </h1>
            <hr class="my-2">
            <h2 class="text-lg font-semibold">
                Municipality of <?php echo htmlspecialchars($municipality_name); ?>
            </h2>
        </div>
    </div>

            <div class="menu">
              <ul class="flex space-x-4">
              <li>
                  <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='#';" style="margin-left: 0;">
                  <i class="ti ti-file-analytics mr-2">  </i>
                      Summary
                  </button>
                </li>
                <li>
                  <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='admin_dashboard.php';" style="margin-left: 0;">
                  <i class="ti ti-building-community mr-2"> </i> 
                      Back
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <h2 class="text-left text-2xl font-semibold">FORM 1</h2>
          <div class="form-group mt-4">
                    <label for="barangay_select" class="block text-lg font-medium text-gray-700">Select Barangay</label>
                        <select id="barangay_select" name="barangay" class="form-control">
                            <option value="">Select Barangay</option>
                            <?php foreach ($barangays as $barangay): ?>
                                <option value="<?php echo htmlspecialchars($barangay['barangay_name']); ?>">
                                    <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
<form method="post" action="form2update.php" enctype="multipart/form-data">
<input type="hidden" id="selected_barangay" name="selected_barangay" value="" />
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>CRITERIA</th>
            <th>Assignee Points</th>
            <th>File</th>
            <th>Rate</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
            <details>
                <summary><b>1. a) Proper Recording of Every Dispute/Complaint - Evaluation Criteria</b></summary>
                <p><br>
                    <b>Scoring Details:</b> <br><br>
                    <b>5 points</b> - Submitted/presented the record book or logbook reflecting all the required details.<br>
                    <b>2.5 points</b> - Submitted/presented the record book or logbook reflecting some of the necessary details.<br>
                    <b>0 points</b> - No presented record book or logbook.<br><br>

                    <b>Note:</b> Check if the record contains the following:
                    <ul>
                    <li>Docket number</li>
                    <li>Names of the parties</li>
                    <li>Date and time filed</li>
                    <li>Nature of the case</li>
                    <li>Disposition</li>
                    </ul>
                </p>
                </details>
        </td>
            <td>20</td>
            <td class="file-column" data-type="IA_1a">
              <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
               </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
          </tr>
          <tr>
            <td><details>
          <summary><b> b. ) Sending of Notices/Summons to Parties - Evaluation Criteria</b></summary>
          <p>
            <b>Criteria:</b> Sending of Notices/Summons to parties within the prescribed period (within the next working day upon receipt of complaint).
          </p>

          <p><b>Scoring Breakdown:</b></p>
          <ul>
            <li><b>5 points</b> - Submitted/presented 80-100% of summons with complete and accurate information issued within the prescribed period.</li>
            <li><b>3 points</b> - Submitted/presented 50-79% of summons with complete and accurate information issued within the prescribed period.</li>
            <li><b>2 points</b> - Submitted/presented 1-49% of summons with complete and accurate information issued within the prescribed period.</li>
            <li><b>0 points</b> - No summons/notices submitted/presented.</li>
          </ul>

          <p><b>Note:</b> Scores will be given only when a file copy of the summons issued within the next working day is stamped with the date and time of receipt.</p>
        </details>
        </td>
            <td>10</td>
            <td class="file-column" data-type="IA_1b">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
          </tr>
          <tr>
          <tr>
  <td>
    <details>
      <summary>
        2. Settlement and Award Period (with at least 10 settled cases within the assessment period)
      </summary>
      <p>10 points – 80-100% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>8 points – 60-79% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>6 points – 40-59% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>4 points – 20-39% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>2 points – 1-19% cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
      <p>0 points – 0 cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period</p>
    </details>
  </td>
  <td>10</td>
  <td></td>
  <td></td>
  <td></td>
</tr>

               <tr>
                <td>a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                <td></td>
                <td class="file-column" data-type="IA_2a">
              <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
          </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td></td>
                <td class="file-column" data-type="IA_2b">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>c) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td></td>
                


                <td class="file-column" data-type="IA_2c">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td></td>
                <td class="file-column" data-type="IA_2d">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
                <td></td>
                <td class="file-column" data-type="IA_2e">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IB_1forcities">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IB_1aformuni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>b. Digital Record Filing</td>
                <td></td>
                <td class="file-column" data-type="IB_1bformuni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td></td>
                <td class="file-column" data-type="IB_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td></td>
                <td class="file-column" data-type="IB_3">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td></td>
                <td class="file-column" data-type="IB_4">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IC_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. To the DILG (Quarterly)</td>
                <td></td>
                <td class="file-column" data-type="IC_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
            </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="ID_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Minutes of the Meeting</td>
                <td></td>
                <td class="file-column" data-type="ID_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IIA">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IIB_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>
                <td></td>
                <td class="file-column" data-type="IIB_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>C. At least 80% compliance with the terms of settlement or award after the cases have been settled</td>
                <td></td>
                <td class="file-column" data-type="IIC">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IIIA">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>B. Coordination with Concerned Agencies relating to disputes filed (PNP, DSWD, DILG, DAR, DENR, Office of the Prosecutor, Court, DOJ, CHR, etc.)</td>
                <td></td>
                <td class="file-column" data-type="IIIB">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IIIC_1forcities">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td></td>
                <td class="file-column" data-type="IIIC_1forcities2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td></td>
                <td class="file-column" data-type="IIIC_1forcities3">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IIIC_2formuni1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td></td>
                <td class="file-column" data-type="IIIC_2formuni2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td></td>
                <td class="file-column" data-type="IIIC_2formuni3">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>D. KP Training or seminar within the assessment period<br />
                  Organized skills training participated by the Lupong Tagapamayapa</td>
                <td></td>
                <td class="file-column" data-type="IIID">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="IV_forcities">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td></td>
                <td class="file-column" data-type="IV_muni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
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
                <td></td>
                <td class="file-column" data-type="V_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>3 From People's Organizations, NGOs or Private Sector</td>
                <td></td>
                <td class="file-column" data-type="threepeoplesorg">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
            <td><input type="number" value="" name=""></td>
            <td><textarea name="" placeholder="Remarks"></textarea></td>
              </tr>
            </tbody>
          </table>
      <input type="submit" value="Okay" class="btn btn-dark mt-3" />
    </form>
        </div>
      </div>
    </div>
  </div>

<!-- Main modal for PDF viewing -->
<div id="large-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto fixed inset-0 z-50 justify-center items-center w-full h-full">
    <div class="relative p-4 w-full max-w-6xl h-[85%]">
        <!-- Modal content -->
        <div class="relative bg-white shadow rounded-lg h-full dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">PDF Viewer</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="large-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-4 h-full">
                <iframe id="pdfViewer" src="" class="w-full h-full rounded-md border"></iframe>
            </div>
        </div>
    </div>
</div>
    </div>
  </div>

</body>
</html>
