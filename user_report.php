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

  <style>
    .card {
      box-shadow: 0 0 0.3cm rgba(0, 0, 0, 0.2);
      border-radius: 15px;
    }
  </style>

</head>


<body class="bg-[#E8E8E7]">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <!--  Row 1 -->
      <div class="row">
        <div class="col-lg-7 d-flex align-items-strech">
          <div class="card w-100">
            <div class="card-body">

              <div class="mb-3 mb-sm-0">

                <div class="d-flex align-items-center">
                  <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
                  <div>
                    <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
                  </div>
                </div>

                <br>

                <h5 class="card-title mb-9 fw-semibold">Report Overview</h5>
                <a href="user_add_report.php" class="btn btn-primary">(+) Add existing Report</a>

                <div class="flex items-center gap-x-2 my-2">

                  <form method="POST">

                    <h2>Annual Report (<?php echo isset($_GET['yearurl']) ? $_GET['yearurl'] : $latestYear; ?>)</h2>
                    <label>Select Year:</label>
                    <select name="selected_year">

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
                    <input class="bg-blue-500 py-2 px-3 hover:bg-blue-400 rounded text-white" type="submit" name="submit_annual" value="Select Annual Report">
                    <!-- ------------------------------------- -->
                    <h2>Monthly Report (<?php echo isset($_GET['monthurl']) ? $_GET['monthurl'] : $latestMonth;  ?>)</h2>
                    <label>Select Month:</label>
                    <select name="selected_month">

                      <option disabled value="" <?php echo empty($monthArray) ? 'selected' : ''; ?>>Select a Month</option>

                      <?php foreach ($monthArray as $month) : ?>
                        <option
                          value="<?php echo $month['month_year']; ?>"
                          <?php echo ($selectedMonth == $month['month_year']) ? 'selected' : ''; ?>>
                          <?php echo $month['month_year']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <input <?php echo empty($monthArray) ? 'disabled' : ''; ?> class="<?php echo empty($monthArray) ? 'bg-gray-300 hover:bg-gray-400 cursor-not-allowed' : ''; ?> bg-blue-500 py-2 px-3 hover:bg-blue-400 rounded text-white" type="submit" name="submit_month" value="Select Monthly Report">
                    <!-- ------------------------------------- -->
                  </form>

                </div>

                <hr>
                <br>

                <div>

                  <b>
                    <b>NATURE OF CASES</b>
                    <div class="row">
                      <div class="col-md-3 mb-3">
                        <label for="criminal">Criminal:</label>
                        <input type="number" class="form-control" id="criminal" name="criminal" readonly
                          value="<?php echo !empty($reportData) ? $reportData['criminal'] : (!empty($lmrd) ? $lmrd['criminal'] : ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="civil">Civil:</label>
                        <input type="number" class="form-control" id="civil" name="civil" readonly
                          value="<?php echo !empty($reportData) ? $reportData['civil'] : (!empty($lmrd) ? $lmrd['civil'] : ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="others">Others:</label>
                        <input type="number" class="form-control" id="others" name="others" readonly
                          value="<?php echo !empty($reportData) ? $reportData['others'] : (!empty($lmrd) ? $lmrd['others'] : ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="totalNature">Total:</label>
                        <input type="number" class="form-control" id="totalNature" name="totalNature" readonly
                          value="<?php echo !empty($reportData) ? $reportData['totalNature'] : (!empty($lmrd) ? $lmrd['totalNature'] : ''); ?>">
                      </div>
                    </div>

                    <hr>

                    <b>ACTION TAKEN - SETTLED</b>
                    <div class="row">
                      <div class="col-md-3 mb-3">
                        <label for="mediation">Mediation:</label>
                        <input type="number" class="form-control" id="mediation" name="mediation" readonly
                          value="<?php echo !empty($reportData) ? $reportData['media'] : (!empty($lmrd) ? $lmrd['media'] : ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="conciliation">Conciliation:</label>
                        <input type="number" class="form-control" id="conciliation" name="conciliation" readonly
                          value="<?php echo !empty($reportData) ? $reportData['concil'] : (!empty($lmrd) ? $lmrd['concil'] : ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="arbit">Arbitration:</label>
                        <input type="number" class="form-control" id="arbit" name="arbit" readonly
                          value="<?php echo !empty($reportData) ? $reportData['arbit'] : (!empty($lmrd) ? $lmrd['arbit'] : ''); ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label for="totalSet">Total:</label>
                        <input type="number" class="form-control" id="totalSet" name="totalSet" readonly
                          value="<?php echo !empty($reportData) ? $reportData['totalSet'] : (!empty($lmrd) ? $lmrd['totalSet'] : ''); ?>">
                      </div>
                    </div>
                    <hr>

                    <div>

                      <b>ACTION TAKEN - UNSETTLED</b>
                      <div class="row">
                        <div class="col-md-3 mb-3">
                          <label for="pending">Pending:</label>
                          <input type="number" class="form-control" id="pending" name="pending" readonly
                            value="<?php echo !empty($reportData) ? $reportData['pending'] : (!empty($lmrd) ? $lmrd['pending'] : ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                          <label for="dismissed">Dismissed:</label>
                          <input type="number" class="form-control" id="dismissed" name="dismissed" readonly
                            value="<?php echo !empty($reportData) ? $reportData['dismissed'] : (!empty($lmrd) ? $lmrd['dismissed'] : ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                          <label for="repudiated">Repudiated:</label>
                          <input type="number" class="form-control" id="repudiated" name="repudiated" readonly
                            value="<?php echo !empty($reportData) ? $reportData['repudiated'] : (!empty($lmrd) ? $lmrd['repudiated'] : ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                          <label for="certified">Certified to Court:</label>
                          <input type="number" class="form-control" id="certified" name="certified" readonly
                            value="<?php echo !empty($reportData) ? $reportData['certcourt'] : (!empty($lmrd) ? $lmrd['certcourt'] : ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                          <label for="dropped">Dropped/Withdrawn:</label>
                          <input type="number" class="form-control" id="dropped" name="dropped" readonly
                            value="<?php echo !empty($reportData) ? $reportData['dropped'] : (!empty($lmrd) ? $lmrd['dropped'] : ''); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                          <label for="totalUnset">Total:</label>
                          <input type="number" class="form-control" id="totalUnset" name="totalUnset" readonly
                            value="<?php echo !empty($reportData) ? $reportData['totalSet'] : (!empty($lmrd) ? $lmrd['totalSet'] : ''); ?>">
                        </div>
                      </div>

                      
                      <a href="user_view_report.php?yearurl=<?= urlencode($_GET['yearurl'] ?? $latestYear) ?>&monthurl=<?= urlencode($_GET['monthurl'] ?? $latestMonth) ?>">

                        <button class="bg-blue-500 py-2 px-3 hover:bg-blue-400 rounded text-white">
                          View Report
                        </button>

                      </a>


                    </div>
                </div>
              </div>


            </div>
          </div></b>

        </div>
        <div class="col-lg-4">
          <div class="row">
            <div class="col-lg-12">
              <div class="card overflow-hidden">
                <div class="card-body p-4">
                  <h5 class="card-title mb-9 fw-semibold">Information</h5>
                  <hr>
                  <br>
                  <b>

                    <?php

                    if (isset($_GET['update_info_message'])) {
                      if ($_GET['update_info_message'] === 'success') {
                        echo "<div id='alertMessage' class='alert alert-success' role='alert'>Updated successfully.</div>";
                      }
                      if ($_GET['update_info_message'] === 'error') {
                        echo "<div id='alertMessage' class='alert alert-danger' role='alert'>Update failed.</div>";
                      }
                    }

                    ?>

                    <form method="POST" action="">
                      
                      <div class="form-group">
                        <label for="mayor">Mayor:</label>
                        <input type="text" class="form-control" id="mayor" name="mayor"

                          value="<?php echo !empty($reportData) ? $reportData['mayor'] : (!empty($lmrd) ? $lmrd['mayor'] : ''); ?>">

                      </div>
                      <div class="form-group">
                        <label for="region">Region:</label>
                        <input type="text" class="form-control" id="region" name="region"
                          value="<?php echo !empty($reportData) ? $reportData['region'] : (!empty($lmrd) ? $lmrd['region'] : ''); ?>">
                      </div>
                      <div class="form-group">
                        <label for="budget">Budget Allocated:</label>
                        <input type="text" class="form-control" id="budget" name="budget"
                          value="<?php echo !empty($reportData) ? $reportData['budget'] : (!empty($lmrd) ? $lmrd['budget'] : ''); ?>">
                      </div>

                      <div class="form-group">
                        <label for="popul">Population:</label>
                        <input type="text" class="form-control" id="popul" name="population"
                          value="<?php echo !empty($reportData) ? $reportData['population'] : (!empty($lmrd) ? $lmrd['population'] : ''); ?>">
                      </div>
                      <div class="form-group">
                        <label for="landarea">Land Area:</label>
                        <input type="text" class="form-control" id="landarea" name="landarea"
                          value="<?php echo !empty($reportData) ? $reportData['landarea'] : (!empty($lmrd) ? $lmrd['landarea'] : ''); ?>">
                      </div>

                      <div class="form-group">
                        <label for="totalc">Total No. of Cases:</label>
                        <input type="number" class="form-control" id="totalc" name="totalc" readonly
                          value="<?php echo !empty($reportData) ? $reportData['totalcase'] : (!empty($lmrd) ? $lmrd['totalcase'] : ''); ?>">
                      </div>

                      <div class="form-group">
                        <label for="numlup">Number of Lupons:</label>
                        <input type="number" class="form-control" id="numlup" name="numlup"
                          value="<?php echo !empty($reportData) ? $reportData['numlupon'] : (!empty($lmrd) ? $lmrd['numlupon'] : ''); ?>">
                      </div>

                      <div class="form-group">
                        <label for="male">Male:</label>
                        <input type="number" class="form-control" id="male" name="male"
                          value="<?php echo !empty($reportData) ? $reportData['male'] : (!empty($lmrd) ? $lmrd['numlupon'] : ''); ?>">
                      </div>

                      <div class="form-group">
                        <label for="female">Female:</label>
                        <input type="number" class="form-control" id="female" name="female"
                          value="<?php echo !empty($reportData) ? $reportData['female'] : (!empty($lmrd) ? $lmrd['female'] : ''); ?>">
                      </div>
                      <br>
                      <button name="submitEdit" type="submit" class="bg-blue-500  py-2 px-3 hover:bg-blue-400 rounded text-white" name="submit">
                        Update
                     </button>
                    </form>

                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="hide_toast.js"></script>
</body>

</html>