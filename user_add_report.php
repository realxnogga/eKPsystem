<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'] ?? '';
$barangay_id = $_SESSION['barangay_id'] ?? '';


// $delete_message = isset($_SESSION['delete_message']) ? $_SESSION['delete_message'] : "";
// unset($_SESSION['delete_message']); 

if (!empty($_SESSION['delete_message'])) {
  $message = urlencode($_SESSION['delete_message']);
  unset($_SESSION['delete_message']);
  header("Location: user_add_report.php?delete_userreport_message=$message");
  exit();
}


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
  // Retrieve form data
  $report_date = $_POST['report_date'];
  $mayor = $_POST['mayor'];
  $region = $_POST['region'];
  $budget = str_replace(',', '', $_POST['budget']); // remove ,
  $population = $_POST['population'];
  $totalcase = $_POST['totalcase'];
  $numlupon = $_POST['numlupon'];
  $male = $_POST['male'];
  $female = $_POST['female'];
  $landarea = $_POST['landarea'];
  $criminal = $_POST['criminal'];
  $civil = $_POST['civil'];
  $others = $_POST['others'];
  $totalNature = $_POST['totalNature'];
  $media = $_POST['media'];
  $concil = $_POST['concil'];
  $arbit = $_POST['arbit'];
  $totalSet = $_POST['totalSet'];
  $pending = $_POST['pending'];
  $dismissed = $_POST['dismissed'];
  $repudiated = $_POST['repudiated'];
  $certcourt = $_POST['certcourt'];
  $dropped = $_POST['dropped'];
  $totalUnset = $_POST['totalUnset'];
  $outsideBrgy = $_POST['outsideBrgy'];

  // Check if a report already exists for the specified month and year
  $existing_report_query = "SELECT * FROM reports WHERE user_id = :user_id AND barangay_id = :barangay_id AND MONTH(report_date) = MONTH(:report_date) AND YEAR(report_date) = YEAR(:report_date)";
  $stmt = $conn->prepare($existing_report_query);
  $stmt->bindParam(':user_id', $userID);
  $stmt->bindParam(':barangay_id', $barangay_id);
  $stmt->bindParam(':report_date', $report_date);
  $stmt->execute();
  $existing_report = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing_report) {
    // Report already exists for the specified month and year
    // $message = "A report already exists for " . date('F Y', strtotime($report_date));

    header("Location: user_add_report.php?&add_userreport_message=reportalreadyexist");
    exit();
  } else {

    // Insert new row into the reports table
    $insert_query = "INSERT INTO reports (user_id, barangay_id, report_date, mayor, region, municipality, budget, population, totalcase, numlupon, male, female, landarea, criminal, civil, others, totalNature, media, concil, arbit, totalSet, pending, dismissed, repudiated, certcourt, dropped, totalUnset, outsideBrgy)
                         VALUES (:user_id, :barangay_id, :report_date, :mayor, :region, :municipality, :budget, :population, :totalcase, :numlupon, :male, :female, :landarea, :criminal, :civil, :others, :totalNature, :media, :concil, :arbit, :totalSet, :pending, :dismissed, :repudiated, :certcourt, :dropped, :totalUnset, :outsideBrgy)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bindParam(':user_id', $userID);
    $stmt->bindParam(':barangay_id', $barangay_id);
    $stmt->bindParam(':report_date', $report_date);
    $stmt->bindParam(':mayor', $mayor);
    $stmt->bindParam(':region', $region);
    $stmt->bindParam(':municipality', $_SESSION['municipality_name']); // add this for municipality
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':population', $population);
    $stmt->bindParam(':totalcase', $totalcase);
    $stmt->bindParam(':numlupon', $numlupon);
    $stmt->bindParam(':male', $male);
    $stmt->bindParam(':female', $female);
    $stmt->bindParam(':landarea', $landarea);
    $stmt->bindParam(':criminal', $criminal);
    $stmt->bindParam(':civil', $civil);
    $stmt->bindParam(':others', $others);
    $stmt->bindParam(':totalNature', $totalNature);
    $stmt->bindParam(':media', $media);
    $stmt->bindParam(':concil', $concil);
    $stmt->bindParam(':arbit', $arbit);
    $stmt->bindParam(':totalSet', $totalSet);
    $stmt->bindParam(':pending', $pending);
    $stmt->bindParam(':dismissed', $dismissed);
    $stmt->bindParam(':repudiated', $repudiated);
    $stmt->bindParam(':certcourt', $certcourt);
    $stmt->bindParam(':dropped', $dropped);
    $stmt->bindParam(':totalUnset', $totalUnset);
    $stmt->bindParam(':outsideBrgy', $outsideBrgy);

    if ($stmt->execute()) {

      header("Location: user_add_report.php?&add_userreport_message=reportsuccessadd");
      exit();
    } else {

      header("Location: user_add_report.php?&add_userreport_message=reportfailedadd");
      exit();
    }
  }
}


?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports</title>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />
  <!-- tailwind cdn -->
  <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="sm:bg-gray-200 bg-white">

  <?php include "user_sidebar_header.php"; ?>

  <!-- filepath: /c:/xampp/htdocs/eKPsystem/user_add_report.php -->
  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">

      <!-- Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 col-span-3 bg-white shadow-none sm:shadow-md rounded-0 sm:rounded-lg p-6">
         <div class="flex items-center">
                <img src="img/cluster.png" alt="Logo" class="w-24 h-24 sm:w-30 sm:h-30 mr-4">
                <div>
                  <h5 class="text-lg font-semibold mb-2">Department of the Interior and Local Government</h5>
                </div>
           </div>
          <br>
          <h5 class="text-lg font-semibold mb-6">Add Existing Report</h5>

          <?php
          if (isset($_GET['add_userreport_message'])) {
            if ($_GET['add_userreport_message'] === 'reportalreadyexist') {
              echo "<div id='alertMessage' class='bg-red-100 text-red-700 p-3 rounded mb-4'>Report already exists for the specified month and year.</div>";
            }
            if ($_GET['add_userreport_message'] === 'reportsuccessadd') {
              echo "<div id='alertMessage' class='bg-green-100 text-green-700 p-3 rounded mb-4'>Report added successfully.</div>";
            }
            if ($_GET['add_userreport_message'] === 'reportfailedadd') {
              echo "<div id='alertMessage' class='bg-red-100 text-red-700 p-3 rounded mb-4'>Report Failed to add.</div>";
            }
          }
          ?>

          <form method="POST" class="space-y-4">
            <div>
              <label for="report_date" class="block text-sm font-medium">Report Date:</label>
              <input type="date" id="report_date" name="report_date" class="border rounded-md p-2 w-full" required onchange="fetchReportData()">
            </div>

            <script>
              function fetchReportData() {
                var selectedDate = document.getElementById('report_date').value;
                var selectedYear = (new Date(selectedDate)).getFullYear();

                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                  if (this.readyState == 4 && this.status == 200) {
                    var data = JSON.parse(this.responseText);
                    document.getElementById('mayor').value = data.mayor;
                    document.getElementById('budget').value = data.budget;
                    document.getElementById('landarea').value = data.landarea;
                    document.getElementById('region').value = data.region;
                    document.getElementById('population').value = data.population;
                    document.getElementById('numlupon').value = data.numlupon;
                  }
                };
                xhr.open('GET', 'fetch_data.php?year=' + selectedYear, true);
                xhr.send();
              }
            </script>

            <?php
            $sections = [
              'Basic Information' => ['mayor', 'budget', 'totalcase', 'numlupon', 'landarea', 'region', 'population', 'male', 'female'],
              'Nature of Cases' => ['criminal', 'others', 'civil', 'totalNature'],
              'Action Taken - Settled' => ['media', 'arbit', 'concil', 'totalSet'],
              'Action Taken - Unsettled' => ['pending', 'repudiated', 'dropped', 'outsideBrgy', 'dismissed', 'certcourt', 'totalUnset']
            ];

            foreach ($sections as $sectionTitle => $fields) {
              echo '<div>';
              echo '<h6 class="font-semibold mb-2">' . $sectionTitle . '</h6>';
              echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
              foreach ($fields as $field) {
                $inputType = in_array($field, ['mayor', 'budget', 'landarea', 'region', 'population', 'numlupon']) ? 'text' : 'number';
                echo '<div>';
                echo '<label for="' . $field . '" class="block text-sm font-medium text-gray-700 mb-1">' . ucwords(str_replace('_', ' ', $field)) . ':</label>';
                echo '<input type="' . $inputType . '" id="' . $field . '" name="' . $field . '" class="border rounded-md p-2 w-full" required>';
                echo '</div>';
              }
              echo '</div>';
              echo '</div>';
            }
            ?>

            <button type="submit" name="submit" class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-md">Submit</button>
          </form>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-1 col-span-3 bg-white shadow-none sm:shadow-md rounded-0 sm:rounded-lg p-6 h-fit">
          <h5 class="text-lg font-semibold mb-6">Existing Reports</h5>

          <?php
          if (isset($_GET['delete_userreport_message'])) {
            if ($_GET['delete_userreport_message'] === 'Report Deleted Successfully') {
              echo "<div id='alertMessage' class='bg-green-100 text-green-700 p-3 rounded mb-4'>Report deleted successfully.</div>";
            } else {
              echo "<div id='alertMessage' class='bg-red-100 text-red-700 p-3 rounded mb-4'>Report failed to delete.</div>";
            }
          }
          ?>

          <table class="min-w-full border border-gray-300 rounded-md">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Report Date</th>
                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $existing_reports_query = "SELECT * FROM reports WHERE user_id = :user_id AND barangay_id = :barangay_id";
              $stmt = $conn->prepare($existing_reports_query);
              $stmt->bindParam(':user_id', $userID);
              $stmt->bindParam(':barangay_id', $barangay_id);
              $stmt->execute();
              $existing_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

              foreach ($existing_reports as $report) {
                $formatted_date = date('F Y', strtotime($report['report_date']));
                echo '<tr class="border-t">';
                echo '<td class="px-4 py-2">' . $formatted_date . '</td>';
                echo '<td class="px-4 py-2 space-x-2">';
                echo '<a href="user_edit_report.php?report_id=' . $report['report_id'] . '" class="text-blue-600 hover:underline">Edit</a>';
                echo '<button onclick="confirmDelete(' . $report['report_id'] . ');" class="text-red-600 hover:underline">Delete</button>';
                echo '</td>';
                echo '</tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="hide_toast.js"></script>
</body>
<!-- Add a JavaScript function to display a confirmation dialog -->
<script>
  function confirmDelete(reportId) {
    if (confirm('Are you sure you want to delete this report?')) {
      window.location.href = 'delete_report.php?report_id=' + reportId;
    }
  }
</script>

</html>