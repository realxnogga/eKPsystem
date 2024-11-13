<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}

if (isset($_GET['municipality_id'])) {
    $municipality_id = intval($_GET['municipality_id']); // Sanitize the input

    try {
        // Fetch municipality name
        $query = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $municipality_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $municipality_name = $municipality_row ? strtoupper($municipality_row['municipality_name']) : 'No municipality found';

        // Fetch barangays and their total ratings from movrate, sorted by total in descending order
        $query = "
            SELECT b.barangay_name AS barangay, m.total 
            FROM barangays b
            JOIN movrate m ON b.id = m.barangay
            WHERE b.municipality_id = :municipality_id
            ORDER BY m.total DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        $barangay_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    
    function getAdjectivalRating($total) {
      if ($total === 100) {
          return "Outstanding";
      } elseif ($total >= 90 && $total <= 99) {
          return "Very Satisfactory";
      } elseif ($total >= 80 && $total <= 89) {
          return "Fair";
      } elseif ($total >= 70 && $total <= 79) {
          return "Poor";
      } else {
          return "Very Poor";
      }
    }
} else {
    echo "No municipality selected.";
    exit;
}
// Fetch movassessmentmembers data for the given municipality
$query = "SELECT chairperson, member1, member2, member3, date FROM movassessmentmembers WHERE municipality_id = :municipality_id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
$stmt->execute();
$assessment_members = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch available years from `movrate` table for dropdown
$query = "SELECT DISTINCT YEAR(daterate) AS year FROM movrate ORDER BY year DESC"; // use 'daterate' instead of 'date'
$stmt = $conn->prepare($query);
$stmt->execute();
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get selected year from request or default to the latest year
$selectedYear = $_GET['year'] ?? $years[0];

// Filter dataset by selected year
$query = "
    SELECT b.barangay_name AS barangay, m.total 
    FROM barangays b
    JOIN movrate m ON b.id = m.barangay
    WHERE b.municipality_id = :municipality_id
      AND YEAR(m.daterate) = :selectedYear  -- use 'daterate' instead of 'date'
    ORDER BY m.total DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
$stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
$stmt->execute();
$barangay_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA Form 3</title>
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <link rel="stylesheet" href="../assets/css/styles.min.css" />

</head>
<body class="bg-[#E8E8E7]">
<?php include "../sa_sidebar_header.php"; ?>
<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
      <div class="card">
        <div class="card-body">
          <div class="menu d-flex justify-content-between align-items-center">
            <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='sa_dashboard.php';">
              <i class="ti ti-building-community mr-2"> </i> Back
            </button>
                    <form method="get" action="">
          <select name="year" onchange="this.form.submit()">
            <?php foreach ($years as $year): ?>
              <option value="<?php echo $year; ?>" <?php if ($year == $selectedYear) echo 'selected'; ?>>
                <?php echo htmlspecialchars($year); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
          </div>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-body">
        <div class="flex justify-center items-center mb-4 space-x-4">
            <!-- DILG Logo -->
            <div class="dilglogo">
              <img src="../img/dilg.png" alt="DILG Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
            </div>

            <!-- Title in Bordered Box -->
            <div class="border border-gray-800 rounded-md p-4 text-center">
              <h1 class="text-xl font-bold">
                CY Lupong Tagapamayapa Incentives Award (LTIA) <br>
                LTIA FORM 3 (C/M) - COMPARATIVE EVALUATION FORM
              </h1>
            </div>

            <!-- LTIA Logo -->
            <div class="dilglogo">
              <img src="images/ltialogo.png" alt="LTIA Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
            </div>
          </div>

          <div class="border border-gray-800 rounded-md p-4 mt-4">
          <b>A. IDENTIFYING INFORMATION</b>
          <p style="padding-left: 5em;">City/Municipality <span style="display: inline-block; width: 3em; text-align: center;">:</span> CITY OF <?php echo htmlspecialchars($municipality_name); ?></p>
          <p style="padding-left: 5em;">Region <span style="display: inline-block; width: 3em; text-align: center;">:</span> IVA</p>
          <p style="padding-left: 5em;">Province <span style="display: inline-block; width: 3em; text-align: center;">:</span> LAGUNA</p>
          <p style="padding-left: 5em;">Category <span style="display: inline-block; width: 3em; text-align: center;">:</span> CITY</p>
      </div><br>

          <b>B. COMPARATIVE EVALUATION RESULTS</b>
          <table class="table table-bordered w-full border border-gray-800 mt-4">
              <thead>
                  <tr>
                      <th>LUPONG TAGAPAMAYAPA (LT)</th>
                      <th>OVERALL PERFORMANCE RATING</th>
                      <th>ADJECTIVAL RATING</th>
                      <th>RANK</th>
                  </tr>
              </thead>
              <tbody>
              <?php 
              $num = 1;
              $rank = 1;
              foreach ($barangay_ratings as $row): ?>
                  <tr>
                      <td><?php echo $num++; ?>. <?php echo htmlspecialchars($row['barangay']); ?></td>
                      <td><?php echo htmlspecialchars($row['total']); ?></td>
                      <td><?php echo getAdjectivalRating($row['total']); ?></td>
                      <td><?php echo $rank++; ?></td>
                  </tr>
              <?php endforeach; ?>
          </tbody>
          </table>
          <br>
                    <b> C. WE CERTIFY TO THE CORRECTNESS OF THE ABOVE INFORMATION </b><br><br>
                    <div class="certification-section text-center">
                    <input type="text" name="chairperson" placeholder="Enter Name" value="<?php echo htmlspecialchars($assessment_members['chairperson'] ?? ''); ?>"><br>
                    Chairperson - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>

                    <input type="text" name="member1" placeholder="Enter Name" value="<?php echo htmlspecialchars($assessment_members['member1'] ?? ''); ?>"><br>
                    Member - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>

                    <input type="text" name="member2" placeholder="Enter Name" value="<?php echo htmlspecialchars($assessment_members['member2'] ?? ''); ?>"><br>
                    Member - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>

                    <input type="text" name="member3" placeholder="Enter Name" value="<?php echo htmlspecialchars($assessment_members['member3'] ?? ''); ?>"><br>
                    Member - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>
                
                </div>
                <br><br>
                <b>D. DATE ACCOMPLISHED<b><br>
                <span class="spacingtabs"> <?php echo date("F j, Y"); ?>

                <br>
                <br>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
<style>
.spacingtabs {
    padding-left: 2em; /* Adjust as needed for spacing */
}.spacingtabs2 {
    padding-left: 2em; /* Adjust as needed for spacing */
}
</style>
</html>
