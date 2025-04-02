<?php
session_start();
include 'connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'] ?? '';

$barangay_id = $_SESSION['barangay_id'] ?? '';

include 'count_lupon.php';
//include 'report_handler.php';

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

    header("Location: user_report.php?yearurl=$selectedYear&monthurl=$selectedMonth");
    exit();
  }


  if (isset($_POST['submitEdit'])) {

    $month = isset($_GET['monthurl']) ? $_GET['monthurl'] : $latestMonth;
    $year = isset($_GET['yearurl']) ? $_GET['yearurl'] : $latestYear;


    // Assign $_POST values to variables before binding
    $mayor = $_POST['mayor'] ?? '';
    $region = $_POST['region'] ?? '';
    $budget = $_POST['budget'] ?? 0;
    $population = $_POST['population'] ?? 0;
    $landarea = $_POST['landarea'] ?? 0;
    $numlupon = $_POST['numlup'] ?? 0;
    $male = $_POST['male'] ?? 0;
    $female = $_POST['female'] ?? 0;

    $stmt = $conn->prepare(
      "UPDATE reports 
      SET mayor = :mayor, 
          region = :region, 
          budget = :budget, 
          population = :population, 
          landarea = :landarea, 
          numlupon = :numlupon, 
          male = :male, 
          female = :female 
      WHERE user_id = :id 
        AND DATE_FORMAT(report_date, '%M %Y') = :monthtemp
         AND DATE_FORMAT(report_date, '%Y') = :yeartemp"
    );

    // Bind variables instead of expressions
    $stmt->bindParam(':mayor', $mayor);
    $stmt->bindParam(':region', $region);
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':population', $population);
    $stmt->bindParam(':landarea', $landarea);
    $stmt->bindParam(':numlupon', $numlupon);
    $stmt->bindParam(':male', $male);
    $stmt->bindParam(':female', $female);
    $stmt->bindParam(':id', $userID);
    $stmt->bindParam(':monthtemp', $month);
    $stmt->bindParam(':yeartemp', $year);


    if ($stmt->execute()) {

      $temp = isset($_GET['yearurl']) ? $_GET['yearurl'] : $latestyear;
      $temp1 = isset($_GET['monthurl']) ? $_GET['monthurl'] : $latestMonth;

      header("Location: user_report.php?yearurl=$temp&monthurl=$temp1&update_info_message=success");
      exit;
    } else {

      $temp = isset($_GET['yearurl']) ? $_GET['yearurl'] : $latestyear;
      $temp1 = isset($_GET['monthurl']) ? $_GET['monthurl'] : $latestMonth;

      header("Location: user_report.php?yearurl=$temp&monthurl=$temp1&update_info_message=error");
      exit;
    }
  }
}

if (isset($_GET['yearurl']) || isset($_GET['monthurl'])) {
  $reportData = fetchMonthlyReportDataFunc($conn, $userID, $_GET['monthurl']);
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
<link rel="stylesheet" href="output.css">

</head>


<body class="bg-gray-200">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">

      <!-- Row 1 -->
      <div class="grid lg:grid-cols-3 gap-4">

        <div class="lg:col-span-2 col-span-3 bg-white sm:shadow-md shadow-none sm:rounded-lg rounded-0">
          <div class="p-6">

            <div class="mb-6">
              <div class="flex items-center">
                <img src="img/cluster.png" alt="Logo" class="w-24 h-24 sm:w-30 sm:h-30 mr-4">
                <div>
                  <h5 class="text-lg font-semibold mb-2">Department of the Interior and Local Government</h5>
                </div>
              </div>

              <br>

              <h5 class="text-lg font-semibold mb-4">Report Overview</h5>
              <a href="user_add_report.php" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-400">
                (+) Add existing Report
              </a>



              <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <h2 class="text-xl font-semibold">Annual Report (<?php echo isset($_GET['yearurl']) ? $_GET['yearurl'] : $latestYear; ?>)</h2>
                  <label class="block mt-2">Select Year:</label>
                  <select name="selected_year" class="border border-gray-300 rounded p-2 w-full">
                    <option value="" disabled <?php echo empty($selectedYear) ? 'selected' : ''; ?>>Select a year</option>
                    <?php foreach ($yearArray as $year) : ?>
                      <option value="<?php echo $year['year']; ?>" <?php echo ($selectedYear == $year['year']) ? 'selected' : ''; ?>>
                        <?php echo $year['year']; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <input type="submit" name="submit_annual" value="Select Annual Report" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-400 mt-2">
                </div>

                <div>
                  <h2 class="text-xl font-semibold">Monthly Report (<?php echo isset($_GET['monthurl']) ? $_GET['monthurl'] : $latestMonth; ?>)</h2>
                  <label class="block mt-2">Select Month:</label>
                  <select name="selected_month" class="border border-gray-300 rounded p-2 w-full">
                    <option disabled value="" <?php echo empty($monthArray) ? 'selected' : ''; ?>>Select a Month</option>
                    <?php foreach ($monthArray as $month) : ?>
                      <option value="<?php echo $month['month_year']; ?>" <?php echo ($selectedMonth == $month['month_year']) ? 'selected' : ''; ?>>
                        <?php echo $month['month_year']; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <input type="submit" name="submit_month" value="Select Monthly Report" class="mt-2 <?php echo empty($monthArray) ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-blue-500 text-white hover:bg-blue-400'; ?> py-2 px-4 rounded" <?php echo empty($monthArray) ? 'disabled' : ''; ?>>
                </div>
              </form>



              <hr class="my-6">

              <div>
                <h5 class="text-lg font-semibold">NATURE OF CASES</h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                  <div>
                    <label for="criminal" class="block">Criminal:</label>
                    <input type="number" id="criminal" name="criminal" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['criminal'] : (!empty($lmrd) ? $lmrd['criminal'] : ''); ?>">
                  </div>
                  <div>
                    <label for="civil" class="block">Civil:</label>
                    <input type="number" id="civil" name="civil" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['civil'] : (!empty($lmrd) ? $lmrd['civil'] : ''); ?>">
                  </div>
                  <div>
                    <label for="others" class="block">Others:</label>
                    <input type="number" id="others" name="others" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['others'] : (!empty($lmrd) ? $lmrd['others'] : ''); ?>">
                  </div>
                  <div>
                    <label for="totalNature" class="block">Total:</label>
                    <input type="number" id="totalNature" name="totalNature" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['totalNature'] : (!empty($lmrd) ? $lmrd['totalNature'] : ''); ?>">
                  </div>
                </div>

                <hr class="my-6">

                <h5 class="text-lg font-semibold">ACTION TAKEN - SETTLED</h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                  <div>
                    <label for="mediation" class="block">Mediation:</label>
                    <input type="number" id="mediation" name="mediation" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['media'] : (!empty($lmrd) ? $lmrd['media'] : ''); ?>">
                  </div>
                  <div>
                    <label for="conciliation" class="block">Conciliation:</label>
                    <input type="number" id="conciliation" name="conciliation" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['concil'] : (!empty($lmrd) ? $lmrd['concil'] : ''); ?>">
                  </div>
                  <div>
                    <label for="arbit" class="block">Arbitration:</label>
                    <input type="number" id="arbit" name="arbit" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['arbit'] : (!empty($lmrd) ? $lmrd['arbit'] : ''); ?>">
                  </div>
                  <div>
                    <label for="totalSet" class="block">Total:</label>
                    <input type="number" id="totalSet" name="totalSet" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['totalSet'] : (!empty($lmrd) ? $lmrd['totalSet'] : ''); ?>">
                  </div>
                </div>

                <hr class="my-6">

                <h5 class="text-lg font-semibold">ACTION TAKEN - UNSETTLED</h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                  <div>
                    <label for="pending" class="block">Pending:</label>
                    <input type="number" id="pending" name="pending" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['pending'] : (!empty($lmrd) ? $lmrd['pending'] : ''); ?>">
                  </div>
                  <div>
                    <label for="dismissed" class="block">Dismissed:</label>
                    <input type="number" id="dismissed" name="dismissed" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['dismissed'] : (!empty($lmrd) ? $lmrd['dismissed'] : ''); ?>">
                  </div>
                  <div>
                    <label for="repudiated" class="block">Repudiated:</label>
                    <input type="number" id="repudiated" name="repudiated" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['repudiated'] : (!empty($lmrd) ? $lmrd['repudiated'] : ''); ?>">
                  </div>
                  <div>
                    <label for="certified" class="block">Certified to Court:</label>
                    <input type="number" id="certified" name="certified" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['certcourt'] : (!empty($lmrd) ? $lmrd['certcourt'] : ''); ?>">
                  </div>
                </div>



              </div>

            </div>
            <a href="user_view_report.php?yearurl=<?= urlencode($_GET['yearurl'] ?? $latestYear) ?>&monthurl=<?= urlencode($_GET['monthurl'] ?? $latestMonth) ?>">
              <button class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-400">
                View Report
              </button>
            </a>
          </div>
        </div>

   
          <div class="lg:col-span-1 col-span-3 bg-white sm:shadow-md shadow-none sm:rounded-lg rounded-0 w-full p-6">
           
              <h5 class="text-lg font-semibold mb-4">Information</h5>
              <hr class="mb-6">

              <?php
              if (isset($_GET['update_info_message'])) {
                if ($_GET['update_info_message'] === 'success') {
                  echo "<div id='alertMessage' class='bg-green-100 text-green-700 p-4 rounded-md'>Updated successfully.</div>";
                }
                if ($_GET['update_info_message'] === 'error') {
                  echo "<div id='alertMessage' class='bg-red-100 text-red-700 p-4 rounded-md'>Update failed.</div>";
                }
              }
              ?>

              <form method="POST" action="">
                <div class="grid grid-cols-1 gap-4">
                  <div>
                    <label for="mayor" class="block">Mayor:</label>
                    <input type="text" id="mayor" name="mayor" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['mayor'] : (!empty($lmrd) ? $lmrd['mayor'] : ''); ?>">
                  </div>
                  <div>
                    <label for="region" class="block">Region:</label>
                    <input type="text" id="region" name="region" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['region'] : (!empty($lmrd) ? $lmrd['region'] : ''); ?>">
                  </div>
                  <div>
                    <label for="budget" class="block">Budget Allocated:</label>
                    <input type="text" id="budget" name="budget" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['budget'] : (!empty($lmrd) ? $lmrd['budget'] : ''); ?>">
                  </div>
                  <div>
                    <label for="popul" class="block">Population:</label>
                    <input type="text" id="popul" name="population" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['population'] : (!empty($lmrd) ? $lmrd['population'] : ''); ?>">
                  </div>
                  <div>
                    <label for="landarea" class="block">Land Area:</label>
                    <input type="text" id="landarea" name="landarea" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['landarea'] : (!empty($lmrd) ? $lmrd['landarea'] : ''); ?>">
                  </div>
                  <div>
                    <label for="totalc" class="block">Total No. of Cases:</label>
                    <input type="number" id="totalc" name="totalc" readonly class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['totalcase'] : (!empty($lmrd) ? $lmrd['totalcase'] : ''); ?>">
                  </div>
                  <div>
                    <label for="numlup" class="block">Number of Lupons:</label>
                    <input type="number" id="numlup" name="numlup" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['numlupon'] : (!empty($lmrd) ? $lmrd['numlupon'] : ''); ?>">
                  </div>
                  <div>
                    <label for="male" class="block">Male:</label>
                    <input type="number" id="male" name="male" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['male'] : (!empty($lmrd) ? $lmrd['male'] : ''); ?>">
                  </div>
                  <div>
                    <label for="female" class="block">Female:</label>
                    <input type="number" id="female" name="female" class="border border-gray-300 rounded p-2 w-full" value="<?php echo !empty($reportData) ? $reportData['female'] : (!empty($lmrd) ? $lmrd['female'] : ''); ?>">
                  </div>
                </div>
                <button type="submit" name="submitEdit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-400 mt-4">
                  Update
                </button>
              </form>

           
          </div>
        
      </div>

    </div>
  </div>

  <script src="hide_toast.js"></script>
    <!-- tailwind cdn -->
    <script src="https://cdn.tailwindcss.com"></script>
</body>

</html>