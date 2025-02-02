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

$_SESSION['test'] = $lmrd;




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

  <style>
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
  </style>
</head>

<body class="bg-[#E8E8E7]">

  <?php include "admin_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <!--  Row 1 -->
      <div class="card">
        <div class="card-body">

          <div class="d-flex align-items-center">
            <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
            <div>
              <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>
          <br>

          <h5 class="card-title mb-9 fw-semibold">Secretaries Corner</h5>
          <hr>
          <b>
            <br>

            <form method="POST">

              <h2>Annual Report (<?php echo empty($selectedYear) ? (empty($latestYear) ? '<span style="font-style: italic; color: gray;">No Report Yet</span>' : $latestYear) : $selectedYear; ?>)</h2>
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
              <input type="submit" name="submit_annual" value="Select Annual Report">
              <!-- ------------------------------------- -->
              <h2>Monthly Report (<?php echo empty($selectedMonth) ? (empty($latestMonth) ? '<span style="font-style: italic; color: gray;">No Report Yet</span>' : $latestMonth) : $selectedMonth; ?>)</h2>
              <label>Select Month:</label>
              <select name="selected_month">

                <option value="" <?php echo empty($monthArray) ? 'selected' : ''; ?>>Select a Month</option>

                <?php foreach ($monthArray as $month) : ?>
                  <option
                    value="<?php echo $month['month_year']; ?>"
                    <?php echo ($selectedMonth == $month['month_year']) ? 'selected' : ''; ?>>
                    <?php echo $month['month_year']; ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="submit" name="submit_month" value="Select Monthly Report">
              <!-- ------------------------------------- -->
            </form>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="mayor">MAYOR:</label>
                  <input type="text" class="form-control" id="mayor" name="mayor" readonly
                    value="<?php echo !empty($reportData) ? $reportData['mayor'] : (!empty($lmrd) ? $lmrd['mayor'] : ''); ?>">
                </div>

                <div class="form-group">
                  <label for="region">REGION:</label>
                  <input type="text" class="form-control" id="region" name="region" readonly
                    value="<?php  echo !empty($reportData) ? $reportData['region'] : (!empty($lmrd) ? $lmrd['region'] : ''); ?>">
                </div>

                <div class="form-group">
                  <label for="budget">BUDGET ALLOCATED:</label>
                  <input type="text" class="form-control" id="budget" name="budget" readonly
                  value="<?php  echo !empty($reportData) ? $reportData['budget'] : (!empty($lmrd) ? $lmrd['budget'] : ''); ?>">
                </div>

                <div class="form-group">
                  <label for="popul">POPULATION:</label>
                  <input type="text" class="form-control" id="popul" name="population" readonly
                  value="<?php  echo !empty($reportData) ? $reportData['population'] : (!empty($lmrd) ? $lmrd['population'] : ''); ?>">
                </div>

                <div class="form-group">
                  <label for="landarea">LAND AREA:</label>
                  <input type="text" class="form-control" id="landarea" name="landarea" readonly
                  value="<?php  echo !empty($reportData) ? $reportData['landarea'] : (!empty($lmrd) ? $lmrd['landarea'] : ''); ?>">
                </div>

              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="totalc">TOTAL NO. OF CASES:</label>
                  <input type="number" class="form-control" id="totalc" name="totalc" readonly
                  value="<?php  echo !empty($reportData) ? $reportData['totalcase'] : (!empty($lmrd) ? $lmrd['totalcase'] : ''); ?>">
                </div>

                <div class="form-group">
                  <label for="numlup">NUMBER OF LUPONS:</label>
                  <input type="number" class="form-control" id="numlup" name="numlup" readonly
                  value="<?php  echo !empty($reportData) ? $reportData['numlupon'] : (!empty($lmrd) ? $lmrd['numlupon'] : ''); ?>">
                </div>

                <div class="form-group">
                  <label for="male">MALE:</label>
                  <input type="number" class="form-control" id="male" name="male" readonly
                  value="<?php  echo !empty($reportData) ? $reportData['male'] : (!empty($lmrd) ? $lmrd['male'] : ''); ?>">
                </div>

                <div class="form-group">
                  <label for="female">FEMALE:</label>
                  <input type="number" class="form-control" id="female" name="female" readonly
                  value="<?php  echo !empty($reportData) ? $reportData['female'] : (!empty($lmrd) ? $lmrd['female'] : ''); ?>">
                </div>



                <div class="row">
                  <div class="col-md-6">
                    <b>Nature of Cases</b>
                    <div class="row">
                      <div class="col-md-4">
                        <label for="criminal">Criminal:</label>
                        <input type="number" class="form-control" id="criminal" name="criminal" readonly
                        value="<?php  echo !empty($reportData) ? $reportData['criminal'] : (!empty($lmrd) ? $lmrd['criminal'] : ''); ?>">
                      </div>
                      <div class="col-md-4">
                        <label for="civil">Civil:</label>
                        <input type="number" class="form-control" id="civil" name="civil" readonly
                        value="<?php  echo !empty($reportData) ? $reportData['civil'] : (!empty($lmrd) ? $lmrd['civil'] : ''); ?>">
                      </div>
                      <div class="col-md-4">
                        <label for="others">Others:</label>
                        <input type="number" class="form-control" id="others" name="others" readonly
                        value="<?php  echo !empty($reportData) ? $reportData['others'] : (!empty($lmrd) ? $lmrd['others'] : ''); ?>">
                      </div>
                      <div class="col-md-4">
                        <label for="totalNature">Total:</label>
                        <input type="number" class="form-control" id="totalNature" name="totalNature" readonly
                        value="<?php  echo !empty($reportData) ? $reportData['totalNature'] : (!empty($lmrd) ? $lmrd['totalNature'] : ''); ?>">
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <b>Action Taken - Settled</b>
                  <div class="row">
                    <div class="col-md-4">
                      <label for="mediation">Mediation:</label>
                      <input type="number" class="form-control" id="mediation" name="mediation" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['media'] : (!empty($lmrd) ? $lmrd['media'] : ''); ?>">
                    </div>
                    <div class="col-md-4">
                      <label for="conciliation">Conciliation:</label>
                      <input type="number" class="form-control" id="conciliation" name="conciliation" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['concil'] : (!empty($lmrd) ? $lmrd['concil'] : ''); ?>">
                    </div>
                    <div class="col-md-4">
                      <label for="arbit">Arbitration:</label>
                      <input type="number" class="form-control" id="arbit" name="arbit" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['arbit'] : (!empty($lmrd) ? $lmrd['arbit'] : ''); ?>">
                    </div>
                    <div class="col-md-4">
                      <label for="totalSet">Total:</label>
                      <input type="number" class="form-control" id="totalSet" name="totalSet" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['totalSet'] : (!empty($lmrd) ? $lmrd['totalSet'] : ''); ?>">
                    </div>

                    <b>Outside the Jurisdiction of Barangay</b>

                    <div class="col-md-2">
                      <label for="outside"></label>
                      <input type="number" class="form-control" id="outside" name="outside" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['outsideBrgy'] : (!empty($lmrd) ? $lmrd['outsideBrgy'] : ''); ?>">
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <b>Action Taken - Unsettled</b>
                  <div class="row">

                    <div class="col-md-4">
                      <label for="pending">Pending:</label>
                      <input type="number" class="form-control" id="pending" name="pending" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['pending'] : (!empty($lmrd) ? $lmrd['pending'] : ''); ?>">
                    </div>

                    <div class="col-md-4">
                      <label for="dismissed">Dismissed:</label>
                      <input type="number" class="form-control" id="dismissed" name="dismissed" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['dismissed'] : (!empty($lmrd) ? $lmrd['dismissed'] : ''); ?>">
                    </div>

                    <div class="col-md-4">
                      <label for="repudiated">Repudiated:</label>
                      <input type="number" class="form-control" id="repudiated" name="repudiated" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['repudiated'] : (!empty($lmrd) ? $lmrd['repudiated'] : ''); ?>">
                    </div>

                    <div class="col-md-4">
                      <label for="certified">Certified to Court:</label>
                      <input type="number" class="form-control" id="certified" name="certified" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['certcourt'] : (!empty($lmrd) ? $lmrd['certcourt'] : ''); ?>">
                    </div>

                    <div class="col-md-4">
                      <label for="dropped">Dropped/Withdrawn:</label>
                      <input type="number" class="form-control" id="dropped" name="dropped" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['dropped'] : (!empty($lmrd) ? $lmrd['dropped'] : ''); ?>">
                    </div>

                    <div class="col-md-4">
                      <label for="totalUnset">Total:</label>
                      <input type="number" class="form-control" id="totalUnset" name="totalUnset" readonly
                      value="<?php  echo !empty($reportData) ? $reportData['totalUnset'] : (!empty($lmrd) ? $lmrd['totalUnset'] : ''); ?>">
                    </div>
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
