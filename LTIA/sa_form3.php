<?php
session_start();
include '../connection.php';

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
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barangay Ratings</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>
<body class="bg-[#E8E8E7]">
  <?php include "../admin_sidebar_header.php"; ?>
  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
      <div class="card">
        <div class="card-body">
          <div class="menu">
            <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='sa_dashboard.php';">
              <i class="ti ti-building-community mr-2"> </i> Back
            </button>
          </div>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-body">
          <div class="border border-gray-800 rounded-md p-4 mt-4">
            <b>A. IDENTIFYING INFORMATION</b>
            <p>City/Municipality: CITY OF <?php echo htmlspecialchars($municipality_name); ?></p>
            <p>Region: IVA</p>
            <p>Province: LAGUNA</p>
            <p>Category: CITY</p>
          </div>

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
        </div>
      </div>
    </div>
  </div>
</body>
</html>
