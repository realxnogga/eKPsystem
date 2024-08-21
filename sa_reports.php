<?php
session_start();
include 'connection.php';
//include 'superadmin-navigation.php';
include 'functions.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}
$selectedMonth = ''; // Initialize the variable

$searchedMunicipality = '';

// Handling search functionality
if (isset($_POST['search'])) {
  $searchedMunicipality = $_POST['municipality']; // Get the searched municipality

  // Get the selected month from the dropdown
  $selectedMonth = $_POST['selected_month'];
  $selectedMonth = date('F Y', strtotime($selectedMonth)); // Convert selected month to Month Year format

  $stmt = $conn->prepare("
        SELECT u.id, u.municipality_id, u.first_name, u.last_name, m.municipality_name,
        COALESCE(SUM(r.totalSet), 0) AS Settled,
        COALESCE(SUM(r.totalUnset), 0) AS Unsettled
        FROM users u
        INNER JOIN municipalities m ON u.municipality_id = m.id
        LEFT JOIN barangays b ON m.id = b.municipality_id
        LEFT JOIN reports r ON b.id = r.barangay_id AND DATE_FORMAT(r.report_date, '%Y-%m') = :selectedMonth
        WHERE u.user_type = 'admin' 
        AND m.municipality_name LIKE :municipality
        GROUP BY u.id
    ");

  $stmt->bindValue(':municipality', '%' . $searchedMunicipality . '%', PDO::PARAM_STR);
  $stmt->bindValue(':selectedMonth', $selectedMonth, PDO::PARAM_STR);
  $stmt->execute();
  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $currentMonth = date('Y-m'); // Get current year and month in YYYY-MM format

  $stmt = $conn->prepare("
    SELECT u.id, u.municipality_id, u.first_name, u.last_name, m.municipality_name,
    COALESCE(SUM(r.totalSet), 0) AS Settled,
    COALESCE(SUM(r.totalUnset), 0) AS Unsettled
    FROM users u
    INNER JOIN municipalities m ON u.municipality_id = m.id
    LEFT JOIN barangays b ON m.id = b.municipality_id
    LEFT JOIN reports r ON b.id = r.barangay_id AND DATE_FORMAT(r.report_date, '%Y-%m') = :currentMonth
    WHERE u.user_type = 'admin' 
    AND m.municipality_name LIKE :municipality
    GROUP BY u.id
");
  $stmt->bindValue(':municipality', '%' . $searchedMunicipality . '%', PDO::PARAM_STR);
  $stmt->bindValue(':currentMonth', $currentMonth, PDO::PARAM_STR);
  $stmt->execute();
  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="assets/css/styles.min.css" />

</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

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

          <h5 class="card-title mb-9 fw-semibold">Report Overview</h5>
          <hr>
          <b>
            <br>
            <form method="POST">
              <div class="search-wrapper">
                <input type="text" name="municipality" placeholder="Search Municipality" value="<?php echo $searchedMunicipality; ?>">
                <button type="submit" name="search" class="btn-light search-btn">
                  <i class="fas fa-search"></i></button>
              </div><br><br>
              <!-- Month and year dropdown -->
              <div class="select-and-clear-container">
                <select name="selected_month">
                  <?php
                  // Loop through months and years to generate options for the last 12 months
                  for ($i = 0; $i < 12; $i++) {
                    $timestamp = strtotime("-$i months");
                    $monthYear = date('F Y', $timestamp);
                    $value = date('Y-m', $timestamp);
                  ?>
                    <option value="<?php echo $value; ?>"><?php echo $monthYear; ?></option>
                  <?php } ?>
                </select><br><br>

                <input type="submit" name="clear" value="Clear" formnovalidate>
              </div>
            </form>
            <h1><?php echo $selectedMonth; ?></h1>
            <div class="columns-container">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th style="padding: 8px; background-color: #d3d3d3;">Municipality</th>
                    <th style="padding: 8px; background-color: #d3d3d3;">Admin</th>
                    <th style="padding: 8px; background-color: #d3d3d3;">
                      Settled
                      <a href="?sort=settled_asc">&#8593;</a>
                      <a href="?sort=settled_desc">&#8595;</a>
                    </th>
                    <th style="padding: 8px; background-color: #d3d3d3;">
                      Unsettled
                      <a href="?sort=unsettled_asc">&#8593;</a>
                      <a href="?sort=unsettled_desc">&#8595;</a>
                    </th>
                    <th style="padding: 8px; background-color: #d3d3d3;">Actions</th>

                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Function to compare values for sorting
                  function compareValues($a, $b)
                  {
                    return $a <=> $b;
                  }

                  // Check if sorting parameter exists
                  if (isset($_GET['sort'])) {
                    $sort = $_GET['sort'];

                    // Define the sorting order based on the parameter value
                    switch ($sort) {
                      case 'settled_asc':
                        usort($user, function ($a, $b) {
                          return compareValues($a['Settled'], $b['Settled']);
                        });
                        break;
                      case 'settled_desc':
                        usort($user, function ($a, $b) {
                          return compareValues($b['Settled'], $a['Settled']);
                        });
                        break;
                      case 'unsettled_asc':
                        usort($user, function ($a, $b) {
                          return compareValues($a['Unsettled'], $b['Unsettled']);
                        });
                        break;
                      case 'unsettled_desc':
                        usort($user, function ($a, $b) {
                          return compareValues($b['Unsettled'], $a['Unsettled']);
                        });
                        break;
                      default:
                        // Handle default case or error
                        break;
                    }
                  }

                  // Output the sorted or default user data
                  foreach ($user as $row) { ?>
                    <tr>
                      <td><?php echo $row['municipality_name']; ?></td>
                      <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                      <td><?php echo $row['Settled']; ?></td>
                      <td><?php echo $row['Unsettled']; ?></td>
                      <td><a href="sa_viewreport.php" class="btn btn-primary m-1">View Report</a>
                      </td>

                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </b>
        </div>
      </div>
    </div>
  </div>

</body>

</html>