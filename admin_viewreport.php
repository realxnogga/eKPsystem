<?php
session_start();
include 'connection.php';

include 'admin_func.php';


$userID = $_GET['user_id'];
// $barangay_id = $_GET['barangay_id'];


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// include 'viewreporthandler.php'; 

$yearArray = [];
$selectedYear = '';
$monthArray = [];
$selectedMonth = '';
$reportData = [];

$latestYear = '';
$latestMonth = '';
$latestReportData = [];


function getLatestYearFunc($conn, $userID)
{
  $query = $conn->prepare("SELECT DATE_FORMAT(report_date, '%Y') FROM reports WHERE user_id = :user_id ORDER BY report_date DESC LIMIT 1");
  $query->execute(['user_id' => $userID]);
  return $query->fetchColumn();
}
$latestYear = getLatestYearFunc($conn, $userID);


function getLatestMonthFunc($conn, $userID)
{
  $query = $conn->prepare("SELECT DATE_FORMAT(report_date, '%M %Y') FROM reports WHERE user_id = :user_id 
  ORDER BY report_date DESC LIMIT 1");
  $query->execute(['user_id' => $userID]);
  return $query->fetchColumn();
}
$latestMonth = getLatestMonthFunc($conn, $userID);


function fetchLatestMonthlyReportDataFunc($conn, $user_id, $whatMonth)
{
  $report_query = $conn->prepare("SELECT * FROM reports WHERE user_id = :user_id AND DATE_FORMAT(report_date, '%M %Y') = :selected_month");
  $report_query->execute(['user_id' => $user_id, 'selected_month' => $whatMonth]);
  return $report_query->fetch(PDO::FETCH_ASSOC);
}
$lmrd = fetchLatestMonthlyReportDataFunc($conn, $userID, $latestMonth);


function getVariedYearsFunc($conn, $userID)
{
  $query = $conn->prepare("SELECT DATE_FORMAT(report_date, '%Y') AS year FROM reports WHERE user_id = :user_id GROUP BY year");
  $query->execute(['user_id' => $userID]);
  return $query->fetchAll(PDO::FETCH_ASSOC);
}
$yearArray = getVariedYearsFunc($conn, $userID);


function getVariedMonthsFunc($conn, $userID, $whatYear)
{
  $query = $conn->prepare("SELECT DISTINCT DATE_FORMAT(report_date, '%M %Y') AS month_year FROM reports WHERE user_id = :user_id AND DATE_FORMAT(report_date, '%Y') = :selected_year");
  $query->execute([
    'user_id' => $userID,
    'selected_year' => $whatYear
  ]);
  return $query->fetchAll(PDO::FETCH_ASSOC);
}


function fetchMonthlyReportDataFunc($conn, $user_id, $whatMonth)
{
  $report_query = $conn->prepare("SELECT * FROM reports WHERE user_id = :user_id AND DATE_FORMAT(report_date, '%M %Y') = :selected_month");
  $report_query->execute(['user_id' => $user_id, 'selected_month' => $whatMonth]);
  return $report_query->fetch(PDO::FETCH_ASSOC);
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['submit_annual'])) {

    $selectedYear = $_POST['selected_year'] ?? '';
    // populate the montharray when annual is click
    $monthArray = getVariedMonthsFunc($conn, $userID, $selectedYear);
  }

  if (isset($_POST['submit_month'])) {

    $selectedMonth = $_POST['selected_month'] ?? '';

    $selectedYear = $_POST['selected_year'] ?? '';
    // populate the montharray when annual is click
    $monthArray = getVariedMonthsFunc($conn, $userID, $selectedYear);

    $reportData = fetchMonthlyReportDataFunc($conn, $userID, $selectedMonth);
  }
}



?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secretaries Corner</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script src="node_modules/jquery/dist/jquery.min.js"></script>

  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />

  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">

  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

  <link href="output.css" rel="stylesheet">

  <link rel="stylesheet" href="hide_show_icon.css">

  <!-- <style>
    input[type="text"] {
      flex: 1;
    }

    input[type="submit"] {
      all: unset;
      padding: 6px 12px;
      border: 1px solid #ccc;
      background-color: #f8f9fa;
      color: black;
      font-family: inherit;
      text-align: center;
      display: inline-block;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #e2e6ea;
    }
  </style> -->
</head>

<body class="bg-white sm:bg-gray-200">

  <?php include "admin_sidebar_header.php"; ?>

  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">

      <!--  Row 1 -->
      <div class="rounded-lg mt-16 bg-white shadow-md p-6">


      <div class="flex items-center mb-6">
            <img src="img/cluster.png" alt="Logo" class="w-24 h-24 mr-4">
            <div>
              <h5 class="text-lg font-semibold">Department of the Interior and Local Government</h5>
            </div>
</div>

        <h5 class="text-lg font-semibold mb-4">Account Request</h5>
        <hr class="mb-6">

        <br>

        <form method="POST" class="grid grid-cols-2 gap-0 sm:gap-4">

          <div class="col-span-2 sm:col-span-1">
            <h2>Annual Report (<?php echo empty($selectedYear) ? (empty($latestYear) ? '<span style="font-style: italic; color: gray;">No Report Yet</span>' : $latestYear) : $selectedYear; ?>)</h2>
            <div class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Year:</label>
              <div class="flex gap-x-4">
                <select class="p-2 border border-gray-300 w-full" name="selected_year">

                  <option value="" disabled <?php echo empty($selectedYear) ? 'selected' : ''; ?>>
                    Select a year
                  </option>

                  <?php foreach ($yearArray as $year) : ?>
                    <option
                      value="<?php echo $year['year']; ?>"
                      <?php echo ($selectedYear == $year['year']) ? 'selected' : ''; ?>>
                      <?php echo $year['year']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <input class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-md" type="submit" name="submit_annual" value="Go">
              </div>
            </div>

            <!-- ------------------------------------- -->

            <h2>Monthly Report (<?php echo empty($selectedMonth) ? (empty($latestMonth) ? '<span style="font-style: italic; color: gray;">No Report Yet</span>' : $latestMonth) : $selectedMonth; ?>)</h2>
            <div class="flex flex-col">


              <label class="block text-sm font-medium text-gray-700 mb-1">Select Month:</label>
              <div class="flex gap-x-4">
                <select class="p-2 border border-gray-300 w-full" name="selected_month">

                  <option value="" <?php echo empty($monthArray) ? 'selected' : ''; ?>>Select a Month</option>

                  <?php foreach ($monthArray as $month) : ?>
                    <option
                      value="<?php echo $month['month_year']; ?>"
                      <?php echo ($selectedMonth == $month['month_year']) ? 'selected' : ''; ?>>
                      <?php echo $month['month_year']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <input class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-md" type="submit" name="submit_month" value="Go">
              </div>
            </div>
          </div>

          <!-- ------------------------------------- -->
        </form>



        <div>
          <div class="grid grid-cols-1 sm:grid-cols-2  gap-0 sm:gap-4">


            <div class="col-span-2 sm:col-span-1">
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="mayor">MAYOR:</label>
                <input type="text" class="border rounded-md p-2" id="mayor" name="mayor" readonly
                  value="<?php echo !empty($reportData) ? $reportData['mayor'] : (!empty($lmrd) ? $lmrd['mayor'] : ''); ?>">
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="region">REGION:</label>
                <input type="text" class="border rounded-md p-2" id="region" name="region" readonly
                  value="<?php echo !empty($reportData) ? $reportData['region'] : (!empty($lmrd) ? $lmrd['region'] : ''); ?>">
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="budget">BUDGET ALLOCATED:</label>
                <input type="text" class="border rounded-md p-2" id="budget" name="budget" readonly
                  value="<?php echo !empty($reportData) ? $reportData['budget'] : (!empty($lmrd) ? $lmrd['budget'] : ''); ?>">
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="popul">POPULATION:</label>
                <input type="text" class="border rounded-md p-2" id="popul" name="population" readonly
                  value="<?php echo !empty($reportData) ? $reportData['population'] : (!empty($lmrd) ? $lmrd['population'] : ''); ?>">
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="landarea">LAND AREA:</label>
                <input type="text" class="border rounded-md p-2" id="landarea" name="landarea" readonly
                  value="<?php echo !empty($reportData) ? $reportData['landarea'] : (!empty($lmrd) ? $lmrd['landarea'] : ''); ?>">
              </div>

            </div>

            <div class="col-span-2 sm:col-span-1">
              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="totalc">TOTAL NO. OF CASES:</label>
                <input type="number" class="border rounded-md p-2" id="totalc" name="totalc" readonly
                  value="<?php echo !empty($reportData) ? $reportData['totalcase'] : (!empty($lmrd) ? $lmrd['totalcase'] : ''); ?>">
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="numlup">NUMBER OF LUPONS:</label>
                <input type="number" class="border rounded-md p-2" id="numlup" name="numlup" readonly
                  value="<?php echo !empty($reportData) ? $reportData['numlupon'] : (!empty($lmrd) ? $lmrd['numlupon'] : ''); ?>">
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="male">MALE:</label>
                <input type="number" class="border rounded-md p-2" id="male" name="male" readonly
                  value="<?php echo !empty($reportData) ? $reportData['male'] : (!empty($lmrd) ? $lmrd['male'] : ''); ?>">
              </div>

              <div class="flex flex-col">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="female">FEMALE:</label>
                <input type="number" class="border rounded-md p-2" id="female" name="female" readonly
                  value="<?php echo !empty($reportData) ? $reportData['female'] : (!empty($lmrd) ? $lmrd['female'] : ''); ?>">
              </div>
            </div>

            <div class="col-span-2 sm:col-span-1">
              <b>Nature of Cases</b>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-0 sm:gap-4">
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="criminal">Criminal:</label>
                  <input type="number" class="border rounded-md p-2" id="criminal" name="criminal" readonly
                    value="<?php echo !empty($reportData) ? $reportData['criminal'] : (!empty($lmrd) ? $lmrd['criminal'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="civil">Civil:</label>
                  <input type="number" class="border rounded-md p-2" id="civil" name="civil" readonly
                    value="<?php echo !empty($reportData) ? $reportData['civil'] : (!empty($lmrd) ? $lmrd['civil'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="others">Others:</label>
                  <input type="number" class="border rounded-md p-2" id="others" name="others" readonly
                    value="<?php echo !empty($reportData) ? $reportData['others'] : (!empty($lmrd) ? $lmrd['others'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="totalNature">Total:</label>
                  <input type="number" class="border rounded-md p-2" id="totalNature" name="totalNature" readonly
                    value="<?php echo !empty($reportData) ? $reportData['totalNature'] : (!empty($lmrd) ? $lmrd['totalNature'] : ''); ?>">
                </div>
              </div>
            </div>

            <div>
              <b>Action Taken - Settled</b>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-0 sm:gap-4">
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="mediation">Mediation:</label>
                  <input type="number" class="border rounded-md p-2" id="mediation" name="mediation" readonly
                    value="<?php echo !empty($reportData) ? $reportData['media'] : (!empty($lmrd) ? $lmrd['media'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="conciliation">Conciliation:</label>
                  <input type="number" class="border rounded-md p-2" id="conciliation" name="conciliation" readonly
                    value="<?php echo !empty($reportData) ? $reportData['concil'] : (!empty($lmrd) ? $lmrd['concil'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="arbit">Arbitration:</label>
                  <input type="number" class="border rounded-md p-2" id="arbit" name="arbit" readonly
                    value="<?php echo !empty($reportData) ? $reportData['arbit'] : (!empty($lmrd) ? $lmrd['arbit'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label class="block text-sm font-medium text-gray-700 mb-1" for="totalSet">Total:</label>
                  <input type="number" class="border rounded-md p-2" id="totalSet" name="totalSet" readonly
                    value="<?php echo !empty($reportData) ? $reportData['totalSet'] : (!empty($lmrd) ? $lmrd['totalSet'] : ''); ?>">
                </div>
              </div>
            </div>

            <div class="col-span-2">
              <b>Action Taken - Unsettled</b>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-0 sm:gap-4">
                <div class="flex flex-col">
                  <label for="pending">Pending:</label>
                  <input type="number" class="border rounded-md p-2" id="pending" name="pending" readonly
                    value="<?php echo !empty($reportData) ? $reportData['pending'] : (!empty($lmrd) ? $lmrd['pending'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label for="dismissed">Dismissed:</label>
                  <input type="number" class="border rounded-md p-2" id="dismissed" name="dismissed" readonly
                    value="<?php echo !empty($reportData) ? $reportData['dismissed'] : (!empty($lmrd) ? $lmrd['dismissed'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label for="repudiated">Repudiated:</label>
                  <input type="number" class="border rounded-md p-2" id="repudiated" name="repudiated" readonly
                    value="<?php echo !empty($reportData) ? $reportData['repudiated'] : (!empty($lmrd) ? $lmrd['repudiated'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label for="certified">Certified to Court:</label>
                  <input type="number" class="border rounded-md p-2" id="certified" name="certified" readonly
                    value="<?php echo !empty($reportData) ? $reportData['certcourt'] : (!empty($lmrd) ? $lmrd['certcourt'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label for="dropped">Dropped/Withdrawn:</label>
                  <input type="number" class="border rounded-md p-2" id="dropped" name="dropped" readonly
                    value="<?php echo !empty($reportData) ? $reportData['dropped'] : (!empty($lmrd) ? $lmrd['dropped'] : ''); ?>">
                </div>
                <div class="flex flex-col">
                  <label for="totalUnset">Total:</label>
                  <input type="number" class="border rounded-md p-2" id="totalUnset" name="totalUnset" readonly
                    value="<?php echo !empty($reportData) ? $reportData['totalUnset'] : (!empty($lmrd) ? $lmrd['totalUnset'] : ''); ?>">
                </div>
              </div>
            </div>

            <div>
              <b>Outside the Jurisdiction of Barangay</b>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="flex flex-col">
                  <label for="outside"></label>
                  <input type="number" class="border rounded-md p-2" id="outside" name="outside" readonly
                    value="<?php echo !empty($reportData) ? $reportData['outsideBrgy'] : (!empty($lmrd) ? $lmrd['outsideBrgy'] : ''); ?>">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>