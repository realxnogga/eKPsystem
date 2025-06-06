<?php
session_start();
include_once("connection.php");

include 'user_set_timezone.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}


// Step 1: Query the user's "lupons" table
$userID = $_SESSION['user_id'];
// Corrected SQL query based on the actual column name
$luponsQuery = "SELECT name1, name2, name3, name4, name5, name6, name7, name8, name9, name10, name11, name12, name13, name14, name15, name16, name17, name18, name19, name20 FROM lupons WHERE user_id = :userID";

$stmt = $conn->prepare($luponsQuery);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();

$luponsData = $stmt->fetch(PDO::FETCH_ASSOC);
$luponsArray = [];

// Create an array of lupons' names
for ($i = 1; $i <= 20; $i++) {
  $fieldName = 'name' . $i;
  if (!empty($luponsData[$fieldName])) {
    $luponsArray[] = $luponsData[$fieldName];
  }
}

$complaint = array(); // Initialize an empty array to store complaint data
$complaintId = isset($_GET['id']) ? $_GET['id'] : null;

if ($complaintId) {
  // Check if the complaint ID is provided in the URL
  // Add code to query the database and retrieve the complaint data based on the $complaintId
  $query = "SELECT * FROM complaints WHERE id = :complaintId";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':complaintId', $complaintId, PDO::PARAM_INT);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $userID = $_SESSION['user_id'];
  $barangayID = $_SESSION['barangay_id'];


  $inputData = json_decode(file_get_contents('php://input'), true);


  $caseNum = $inputData['CNum'];
  $forTitle = $inputData['ForTitle'];
  $complainants = $inputData['CNames'];
  $respondents = $inputData['RspndtNames'];
  $complaintDesc = $inputData['CDesc'];
  $petition = $inputData['Petition'];
  $madeDate = $inputData['Mdate'];
  $receivedDate = $inputData['RDate'];
  $complainantAddress = $inputData['CAddress'];
  $respondentAddress = $inputData['RAddress'];

  if (!empty($receivedDate)) {
    $formattedReceivedDate = date('Y-m-d', strtotime($receivedDate));
  } else {
    // Handle empty date value (You might assign a default value or show an error message)
    // For example:
    $formattedReceivedDate = null; // or set to a default date: 'YYYY-MM-DD'
  }
  $pangkat = $inputData['Pangkat'];
  $caseType = $inputData['CType'];
  $cStatus = $inputData['CStatus'];
  $cMethod = $inputData['CMethod'];

  if ($cStatus === 'Others') {
    // If 'Outside the Jurisdiction' is selected, set the Case Method value to null or an empty string
    $cMethod = null; // Or $cMethod = ''; depending on how you handle null values in the database
  }


  // only update seen column condition inside if is met
  $seen = "";
  $removenotif = "";


  if ($cStatus == 'Settled' && $cMethod == 'Mediation') {

    $seen = " seen = 0,";
    $removenotif = " removenotif = 0,";

    // $currentDateTime = date('Y-m-d H:i:s');

    // $madeDatePlus14 = date('Y-m-d H:i:s', strtotime($madeDate . ' + 14 days'));

    // if ($currentDateTime > $madeDatePlus14) {
    //     $seen = " seen = 0,";
    //     $removenotif = " removenotif = 0,";
    // }
  }

  // Update the complaint in the 'complaints' table using an UPDATE query
  $query = "UPDATE complaints SET CNum = :caseNum, ForTitle = :forTitle, CNames = :complainants, RspndtNames = :respondents, CDesc = :complaintDesc, Petition = :petition, Mdate = :madeDate, RDate = :receivedDate, Pangkat = :pangkat, CType = :caseType, CStatus = :cStatus, CMethod = :cMethod, CAddress = :complainantAddress, RAddress = :respondentAddress,";

  // Only add the `seen` column update if needed
  $query .= !empty($seen) ? $seen : "";
  $query .= !empty($removenotif) ? $removenotif : "";

  // Remove trailing comma if there is no `seen` update
  $query = rtrim($query, ",");

  $query .= " WHERE id = :complaintId";

  $stmt = $conn->prepare($query);

  $inputData = json_decode(file_get_contents('php://input'), true);


  $stmt->bindParam(':caseNum', $caseNum, PDO::PARAM_STR);
  $stmt->bindParam(':forTitle', $forTitle, PDO::PARAM_STR);
  $stmt->bindParam(':complainants', $complainants, PDO::PARAM_STR);
  $stmt->bindParam(':respondents', $respondents, PDO::PARAM_STR);
  $stmt->bindParam(':complaintDesc', $complaintDesc, PDO::PARAM_STR);
  $stmt->bindParam(':petition', $petition, PDO::PARAM_STR);
  $stmt->bindParam(':madeDate', $madeDate, PDO::PARAM_STR);
  $stmt->bindParam(':receivedDate', $formattedReceivedDate, PDO::PARAM_STR);
  $stmt->bindParam(':pangkat', $pangkat, PDO::PARAM_STR);
  $stmt->bindParam(':caseType', $caseType, PDO::PARAM_STR);
  $stmt->bindParam(':complaintId', $complaintId, PDO::PARAM_INT);
  $stmt->bindParam(':cStatus', $cStatus, PDO::PARAM_STR);
  $stmt->bindParam(':cMethod', $cMethod, PDO::PARAM_STR);
  $stmt->bindParam(':complainantAddress', $complainantAddress, PDO::PARAM_STR);
  $stmt->bindParam(':respondentAddress', $respondentAddress, PDO::PARAM_STR);

  $updateSuccessful = $stmt->execute();

  if ($updateSuccessful) {
      
 // if update successfull update the "complaint_updated_date" table
  $curdate = date("Y-m-d H:i:s");
  $updateComplaintquery = "UPDATE complaints SET complaint_updated_date = '$curdate' WHERE id = $complaintId";
  $updateComplaintstmt = $conn->prepare($updateComplaintquery);
  $updateComplaintstmt->execute();
// ------------------------------

    // if update successfull update the "complaint_updated_date" table
    $curdate = date("Y-m-d H:i:s");
    $updateComplaintquery = "UPDATE complaints SET complaint_updated_date = '$curdate' WHERE id = $complaintId";
    $updateComplaintstmt = $conn->prepare($updateComplaintquery);
    $updateComplaintstmt->execute();
    // ------------------------------

    echo json_encode(['status' => 'success', 'message' => 'Complaint Updated Successfully!']);

    // Fetch the updated complaint data from the database
    $query = "SELECT * FROM complaints WHERE id = :complaintId";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':complaintId', $complaintId);
    $stmt->execute();
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
    exit;
  } else {
    echo json_encode(['status' => 'failed', 'message' => 'Failed to Update Complaint. Check Code.']);
    exit;
  }
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Information</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />
  <!-- tailwind cdn -->
  <link rel="stylesheet" href="output.css">

  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

  <!-- select2 for dropdown -->
  <link href="node_modules/select2/dist/css/select2.min.css" rel="stylesheet" />
  <script src="node_modules/select2/dist/js/select2.min.js"></script>


  <style>
    .card {
      box-shadow: 0 0 0.3cm rgba(0, 0, 0, 0.2);
      border-radius: 15px;
    }
  </style>

  <!-- ############################################################################### -->

  <script>
    async function sendData(CNum, ForTitle, CNames, RspndtNames, CDesc, Petition, Mdate, RDate, CAddress, RAddress, Pangkat, CType, CStatus, CMethod) {
      try {
        const response = await fetch("", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            CNum: CNum,
            ForTitle: ForTitle,
            CNames: CNames,
            RspndtNames: RspndtNames,
            CDesc: CDesc,
            Petition: Petition,
            Mdate: Mdate,
            RDate: RDate,
            CAddress: CAddress,
            RAddress: RAddress,
            Pangkat: Pangkat,
            CType: CType,
            CStatus: CStatus,
            CMethod: CMethod
          })
        });

        const result = await response.json();

        if (result.message && result.message != '') {

          document.getElementById('message').textContent = result.message;
          document.getElementById('message').classList.remove('hidden');
          document.getElementById('message').classList.add('flex');

          if (result.status === 'success') {
            document.getElementById('message').classList.add('bg-green-300');

            // scroll to top to see shit
            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });

            setTimeout(function() {
              window.location.href = "user_complaints.php";
            }, 1000);

          }
          if (result.status === 'failed') {
            document.getElementById('message').classList.add('bg-red-300');

            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });

            setTimeout(function() {
              window.location.href = "user_complaints.php";
            }, 1000);

          }

        }

      } catch (error) {
        console.error("Error:", error);
      }
    }

    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('formEditComplaint');
      const submitButton = form.querySelector('[type="submit"]');

      form.onsubmit = function(event) {
        event.preventDefault();

        submitButton.disabled = true;
        submitButton.style.backgroundColor = "gray";
        submitButton.style.cursor = "not-allowed";

        const CNum = document.querySelector('input[name="CNum"]').value;
        const ForTitle = document.querySelector('select[name="ForTitle"]').value;
        const CNames = document.querySelector('input[name="CNames"]').value;
        const RspndtNames = document.querySelector('input[name="RspndtNames"]').value;
        const CDesc = document.querySelector('input[name="CDesc"]').value;
        const Petition = document.querySelector('input[name="Petition"]').value;
        const Mdate = document.querySelector('input[name="Mdate"]').value;
        const RDate = document.querySelector('input[name="RDate"]').value;
        const CAddress = document.querySelector('input[name="CAddress"]').value;
        const RAddress = document.querySelector('input[name="RAddress"]').value;
        const Pangkat = document.querySelector('input[name="Pangkat"]').value;
        const CType = document.querySelector('select[name="CType"]').value;
        const CStatus = document.querySelector('select[name="CStatus"]').value;
        const CMethod = document.querySelector('select[name="CMethod"]').value;

        if (navigator.onLine) {

          sendData(CNum, ForTitle, CNames, RspndtNames, CDesc, Petition, Mdate, RDate, CAddress, RAddress, Pangkat, CType, CStatus, CMethod);

        } else {
          localStorage.setItem('addComplaint', JSON.stringify({
            CNum,
            ForTitle,
            CNames,
            RspndtNames,
            CDesc,
            Petition,
            Mdate,
            RDate,
            CAddress,
            RAddress,
            Pangkat,
            CType,
            CStatus,
            CMethod
          }));
          alert('No internet. Your request will be executed once the internet is restored.');
        }
      };

      function syncWhenOnline() {
        const addComplaint = JSON.parse(localStorage.getItem('addComplaint'));
        if (addComplaint) {

          sendData(addComplaint.CNum, addComplaint.ForTitle, addComplaint.CNames, addComplaint.RspndtNames,
            addComplaint.CDesc, addComplaint.Petition, addComplaint.Mdate, addComplaint.RDate, addComplaint.CAddress, addComplaint.RAddress, addComplaint.Pangkat, addComplaint.CType, addComplaint.CStatus, addComplaint.CMethod);

          localStorage.removeItem('addComplaint');
        }
      }

      if (navigator.onLine) {
        syncWhenOnline();
      }

      window.addEventListener('online', syncWhenOnline);

    });
  </script>

  <!-- ############################################################################### -->

</head>

<body class="bg-gray-200">

  <?php include "user_sidebar_header.php"; ?>

  <!-- filepath: /c:/xampp/htdocs/eKPsystem/user_edit_complaint.php -->
  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">
      <div class="bg-white shadow-lg rounded-lg p-6">
        <div>
          <div class="flex items-center">
            <img src="img/cluster.png" alt="Logo" class="w-24 h-24 sm:w-30 sm:h-30 mr-4">
            <div>
              <h5 class="text-lg font-semibold mb-2">Department of the Interior and Local Government</h5>
            </div>
          </div>
          <br>
          <h5 class="text-lg font-semibold mb-1">Edit Complaint</h5>
          <p id="message" class="hidden bg-green-100 text-green-700 p-4 rounded-md"></p>

          <form id="formEditComplaint" class="space-y-1">
            <!-- Case No -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Case No.<span class="text-red-500"> *</span></label>
                <input type="text" class="border rounded-md p-2" id="CNum" name="CNum" placeholder="Case No. - Blotter No. - MMYY" onblur="validate(1)" value="<?php echo $complaint['CNum']; ?>" required>
              </div>

              <!-- For -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">For:<span class="text-red-500"> *</span></label>
                <select class="border rounded-md p-2" id="ForTitle" name="ForTitle" required>
                  <option value="<?php echo $complaint['ForTitle']; ?>" selected><?php echo $complaint['ForTitle']; ?></option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
              <!-- Complainants -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Complainants:<span class="text-red-500"> *</span></label>
                <input type="text" class="border rounded-md p-2" id="CNames" name="CNames" placeholder="Enter name of complainants" onblur="validate(3)" required value="<?php echo $complaint['CNames']; ?>">
              </div>

              <!-- Respondents -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Respondents:<span class="text-red-500"> *</span></label>
                <input type="text" class="border rounded-md p-2" id="RspndtNames" name="RspndtNames" placeholder="Enter name of respondents" onblur="validate(4)" required value="<?php echo $complaint['RspndtNames']; ?>">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
              <!-- Complaint -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Complaint:<span class="text-red-500"> *</span></label>
                <input type="text" class="border rounded-md p-2" id="CDesc" name="CDesc" placeholder="" onblur="validate(5)" required value="<?php echo $complaint['CDesc']; ?>">
              </div>

              <!-- Petition -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Petition:<span class="text-red-500"> *</span></label>
                <input type="text" class="border rounded-md p-2" id="Petition" name="Petition" placeholder="" onblur="validate(6)" required value="<?php echo $complaint['Petition']; ?>">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
              <!-- Complainant Address -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Complainant Address:<span class="text-red-500"> *</span></label>
                <input type="text" class="border rounded-md p-2" id="CAddress" name="CAddress" placeholder="Enter complainant address" required value="<?php echo $complaint['CAddress']; ?>">
              </div>

              <!-- Respondent Address -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Respondent Address:<span class="text-red-500"> *</span></label>
                <input type="text" class="border rounded-md p-2" id="RAddress" name="RAddress" placeholder="Enter respondent address" required value="<?php echo $complaint['RAddress']; ?>">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
              <!-- Made and Received Dates -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Made:<span class="text-red-500"> *</span></label>
                <input type="datetime-local" class="border rounded-md p-2" id="Mdate" name="Mdate" onblur="validate(7)" value="<?php echo $complaint['Mdate']; ?>">
              </div>
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Received:</label>
                <input type="date" class="border rounded-md p-2" id="RDate" name="RDate" onblur="validate(8)" value="<?php echo $complaint['RDate']; ?>">
              </div>
            </div>

            <!-- Pangkat -->
            <div class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 mb-1">Pangkat:</label>
              <input type="text" class="border rounded-md p-2" id="Pangkat" name="Pangkat" placeholder="Enter name of Punong Barangay" oninput="showDropdown()" value="<?php echo $complaint['Pangkat']; ?>">
              <div id="pangkatDropdown"></div>
            </div>

            <!-- Case Status and Method -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4">
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Case Status:<span class="text-red-500"> *</span></label>
                <select name="CStatus" id="cStatusSelect" class="border rounded-md p-2">
                  <option value="Settled" <?php if ($complaint['CStatus'] === 'Settled') echo 'selected'; ?>>Settled</option>
                  <option value="Unsettled" <?php if ($complaint['CStatus'] === 'Unsettled') echo 'selected'; ?>>Unsettled</option>
                  <option value="Others" <?php if ($complaint['CStatus'] === 'Others') echo 'selected'; ?>>Outside the Jurisdiction</option>
                </select>
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Case Method:<span class="text-red-500"> *</span></label>
                <select name="CMethod" id="CMethodSelect" class="border rounded-md p-2">
                  <?php
                  $methodOptions = [];

                  if ($complaint['CStatus'] === 'Settled') {
                    $methodOptions = ['Mediation', 'Conciliation', 'Arbitration'];
                  } else if ($complaint['CStatus'] === 'Unsettled') {
                    $methodOptions = ['Pending', 'Dismissed', 'Repudiated', 'Certified to File Action in Court', 'Dropped/Withdrawn'];
                  }

                  foreach ($methodOptions as $option) {
                    echo '<option value="' . $option . '"';
                    if ($complaint['CMethod'] === $option) {
                      echo ' selected';
                    }
                    echo '>' . $option . '</option>';
                  }

                  if ($complaint['CStatus'] === 'Others') {
                    echo '<option value="" selected disabled hidden>Select Method</option>';
                  }
                  ?>
                </select>
              </div>

              <!-- Case Type -->
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1">Case Type:<span class="text-red-500"> *</span></label>
                <select name="CType" class="border rounded-md p-2">
                  <option value="Civil" <?php if ($complaint['CType'] === 'Civil') echo 'selected'; ?>>Civil</option>
                  <option value="Criminal" <?php if ($complaint['CType'] === 'Criminal') echo 'selected'; ?>>Criminal</option>
                  <option value="Others" <?php if ($complaint['CType'] === 'Others') echo 'selected'; ?>>Others</option>
                </select>
              </div>
            </div>


            <br>
            <!-- Submit Button -->
            <div>
              <input type="submit" class="bg-blue-500 hover:bg-blue-400 px-4 py-2 rounded-md text-white w-full sm:w-auto" name="submit" value="Update">

            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      var luponsArray = <?php echo json_encode($luponsArray); ?>;

      document.addEventListener('DOMContentLoaded', function() {
        var hiddenCMethod = document.getElementById('hiddenCMethod');
        var cMethodSelect = document.getElementById('CMethodSelect');
        var cStatusSelect = document.getElementById('cStatusSelect');

        // Function to update Case Method options based on Case Status
        function updateMethodOptions(selectedStatus) {
          var methodOptions = [];

          if (selectedStatus === 'Settled') {
            methodOptions = ['Mediation', 'Conciliation', 'Arbitration'];
          } else if (selectedStatus === 'Unsettled') {
            methodOptions = ['Pending', 'Dismissed', 'Repudiated', 'Certified to File Action in Court', 'Dropped/Withdrawn'];
          }

          cMethodSelect.innerHTML = ''; // Clear previous options

          methodOptions.forEach(function(option) {
            var optionElement = document.createElement('option');
            optionElement.value = option;
            optionElement.textContent = option;
            cMethodSelect.appendChild(optionElement);
          });

          // Set the selected option based on the hidden input value
          cMethodSelect.value = hiddenCMethod.value;
        }

        // Event listener to handle changes in Case Status
        cStatusSelect.addEventListener('change', function() {
          var selectedStatus = cStatusSelect.value;

          if (selectedStatus === 'Others') {
            cMethodSelect.style.display = 'none';
            cMethodSelect.value = null;
            hiddenCMethod.value = null; // Reset hidden value
          } else {
            cMethodSelect.style.display = 'block';
            updateMethodOptions(selectedStatus);
          }
        });

        // Initial setup
        var initialStatus = cStatusSelect.value;
        if (initialStatus !== 'Others') {
          updateMethodOptions(initialStatus);
        } else {
          cMethodSelect.style.display = 'none';
          hiddenCMethod.value = null; // Reset hidden value
        }
      });


      $(document).ready(function() {
        var suggestions = [
          "Tumults and other disturbances of public order; Tumltuous disturbances or interruption liable to cause disturbance (Art. 153)",
          "Unlawful use of means of publication and unlawful utterances (Art. 154)",
          "Alarms and Scandals (Art.155)",
          "Using false certificates (Art. 175)",
          "Using fictitious names and concealing true names (Art. 178)",
          "Illegal use of uniform and insignias (Art. 179)",
          "Physical injuries inflicted in a tumultuous affray (Art. 252)",
          "Giving assistance to suicide (Art. 253)",
          "Responsibility of participants in a duel (Art. 260)",
          "Less serious physical injuries [which shall incapacitate the offended party for labor for ten (10) days or more, or shall require medical assistance for the same period] (Art. 265]",
          "Slight physical injuries and maltreatment (Art. 266)",
          "Unlawful arrest (Art. 269)",
          "Inducing a minor to abandon his home (Art. 271)",
          "Abandonment of persons in danger and abandonment of one's own victim (Art. 275)",
          "Abandoning a minor (Art. 276)",
          "Abandonment of minor by a person entrusted with his custody; indifference of parents (Art. 277)",
          "Qualified trespass to dwelling (Art. 280)",
          "Other forms of trespass (Art. 281)",
          "Light threats (Art. 283)",
          "Other light threats (Art. 285)",
          "Grave coercion (Art. 286)",
          "Light coercions and unjust taxation (Art. 287)",
          "Other similar coercions (Compulsory purchase of merchandise and payment of wages by means of tokens) (Art. 288)",
          "Discovering secrets through the seizure of correspondence (Art. 290)",
          "Revealing secrets with abuse of office (if secrets are not revealed) (Art.291)",
          "Theft (Art. 309)",
          "Altering boundaries or landmarks (Art. 313)",
          "Swindling or Estafa (Art. 315)",
          "Other forms of swindling (Art. 316)",
          "Swindling a minor (Art. 317)",
          "Other deceits (Art. 318)",
          "Removal, sale or pledge of mortgaged property (Art. 319)",
          "Special cases of malicious mischief (Art. 328)",
          "Other mischief (Art. 327, in relation to Art. 329)",
          "Simple seduction (Art. 338)",
          "Acts of lasciviousness with the consent of the offended party (Art. 339)",
          "Threatening to publish and offer to prevent such publication for compensation (Art. 356)",
          "Prohibited publication of acts referred to in the course of official proceedings (Art. 357)",
          "Slander (Oral Defamation) (Art. 356)",
          "Slander by Deed (Art. 359)",
          "Incriminating Innocent Person (Art. 363)",
          "Intriguing against honor (Art. 364)",
          "Reckless imprudence and Simple negligence (Art. 365)",
          "Violation of B.P. NO. 22 or the Bouncing Checks Law",
          "Nuisance (Art. 694 of the Civil Code in the relation to Art. 695, for local ordinance with penal sanctions)",
          "Violation of P.D. No. 1612 or the Anti-Fencing Law",
          "Violation of Republic Act No. 11313 or 'The Safe Spaces Act' Gender-based sexual harassment in streets and public spaces.",
        ];

        // Initialize Select2
        $('#ForTitle').select2({
          theme: 'bootstrap-5',
          placeholder: 'Select or start typing...',
          data: suggestions.map(function(item) {
            return {
              id: item,
              text: item
            };
          }),
          tags: true,
          createTag: function(params) {
            var term = $.trim(params.term);
            if (term === '') {
              return null;
            }
            return {
              id: term,
              text: term,
              newTag: true
            };
          },
          tokenSeparators: [','],
          closeOnSelect: false
        });

        // // Add a click event listener to the "Other" option
        // $('#ForTitle').on('select2:select', function(e) {
        //   var selectedValue = e.params.data.id;
        //   if (selectedValue === 'Other') {
        //     // Clear the selected value
        //     $(this).val(null).trigger('change');
        //     // Enable typing for the "Other" case
        //     $(this).select2('open');
        //   }
        // });

        // Handle keyup event to update the input value with the typed text
        $('#ForTitle').on('keyup', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            var value = $(this).val();
            // Add the typed text as a tag
            if (value.trim() !== '') {
              $(this).append(new Option(value, value, true, true)).trigger('change');
            }
            // Clear the input
            $(this).val(null);
          }
        });
      });
    </script>
    <script src="edit_script.js"></script>

</body>

</html>