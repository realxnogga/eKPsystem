<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$municipality_id = $_SESSION['municipality_id']; 
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : $currentYear;

function getPerformanceRating($total) {
    if ($total >= 100) {
        return "Outstanding";
    } elseif ($total >= 90) {
        return "Very Satisfactory";
    } elseif ($total >= 80) {
        return "Fair";
    } elseif ($total >= 70) {
        return "Poor";
    } else {
        return "Very Poor";
    }
}

// Fetch municipality name
$municipalityQuery = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
$municipalityStmt = $conn->prepare($municipalityQuery);
$municipalityStmt->bindValue(':municipality_id', $municipality_id, PDO::PARAM_INT);
$municipalityStmt->execute();
$municipalityName = $municipalityStmt->fetchColumn();

// Fetch available years for the dropdown
$yearQuery = "SELECT DISTINCT year FROM movrate ORDER BY year DESC";
$yearStmt = $conn->prepare($yearQuery);
$yearStmt->execute();
$years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

// Add current year if it's missing
if (!in_array($currentYear, $years)) {
    array_unshift($years, $currentYear);
}

// Fetch barangays and average scores for the selected year
$query = "
SELECT b.barangay_name, AVG(COALESCE(m.total, 0)) AS average_total 
FROM barangays b 
LEFT JOIN movrate m ON b.id = m.barangay AND m.year = :year
WHERE b.municipality_id = :municipality_id
GROUP BY b.barangay_name
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':municipality_id', $municipality_id, PDO::PARAM_INT);
$stmt->bindValue(':year', $selectedYear, PDO::PARAM_INT);
$stmt->execute();

$barangays = [];
$totals = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $barangays[] = $row['barangay_name'];
    $totals[] = $row['average_total'];
}

// Fetch assessor_type data for the same municipality
$assessorQuery = "
SELECT u.first_name, u.last_name, u.assessor_type
FROM users u
WHERE u.municipality_id = :municipality_id AND u.user_type = 'assessor'
";
$assessorStmt = $conn->prepare($assessorQuery);
$assessorStmt->bindValue(':municipality_id', $municipality_id, PDO::PARAM_INT);
$assessorStmt->execute();
$assessors = $assessorStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin data for the same municipality
$adminQuery = "
SELECT u.first_name, u.last_name
FROM users u
WHERE u.municipality_id = :municipality_id AND u.user_type = 'admin'
";
$adminStmt = $conn->prepare($adminQuery);
$adminStmt->bindValue(':municipality_id', $municipality_id, PDO::PARAM_INT);
$adminStmt->execute();
$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
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
              <h1 class="text-xl font-bold flex items-center ml-4">
                <span>Lupong Tagapamayapa Incentives Award (LTIA)</span>
              <form method="get" action="">
                <select name="year" onchange="this.form.submit()">
                  <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php if ($year == $selectedYear) echo 'selected'; ?>>
                      <?php echo htmlspecialchars($year); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
              </h1>
            </div>
            <div class="menu">
              <ul class="flex space-x-4">
                <li>
                  <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_admin_dashboard.php';" style="margin-left: 0;">
                  <i class="ti ti-building-community mr-2"> </i> 
                      Back
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <h1 class="text-2xl font-bold mb-4">Overall Summary Info</h1>

          <div class="flex mt-6">
  <div id="chart-container" class="w-1/2">
    <canvas id="barangayChart"></canvas>
  </div>
  <div id="additional-info" class="w-1/2 p-4 bg-white rounded-lg shadow-md ml-4" style="font-size: 16px;">
    <!-- Add your additional content here -->
    <h2 class="text-lg font-bold mb-4">Members Committee of <span id="details-municipality-type"></span> of <?php echo (htmlspecialchars($municipalityName)); ?></h2>
    <?php if (!empty($admin)): ?>
      <div class="flex items-center mt-4">
        <h3 class="text-lg font-bold" id="admin-title">Admin:</h3>
        <p class="ml-2"><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></p>
      </div>
    <?php else: ?>
      <p>No admin found for this municipality.</p>
      <hr>
    <?php endif; ?>
    <?php if (!empty($assessors)): ?>
      <ul>
        <?php foreach ($assessors as $assessor): ?>
          <li><strong h3 class="text-lg font-bold"><?php echo htmlspecialchars($assessor['assessor_type']); ?></strong>: <?php echo htmlspecialchars($assessor['first_name'] . ' ' . $assessor['last_name']); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No assessors found for this municipality.</p>
    <?php endif; ?>
  </div>
</div>

<div class="w-full p-4 bg-white rounded-lg shadow-md mt-4">
  <?php
  try {
    // Step 1: Get the municipality ID for the logged-in user
    $query = "SELECT municipality_id FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_row && isset($user_row['municipality_id'])) {
      $municipality_id = $user_row['municipality_id'];

      // Step 2: Fetch municipality name
      $query = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
      $stmt->execute();

      $municipality_row = $stmt->fetch(PDO::FETCH_ASSOC);
      $municipality_name = $municipality_row ? strtoupper($municipality_row['municipality_name']) : 'No municipality found';

      // Step 3: Fetch barangays and their total ratings from movrate, grouped by barangay and calculate the average
      $query = "
          SELECT b.barangay_name AS barangay, AVG(m.total) AS average_total
          FROM barangays b
          JOIN movrate m ON b.id = m.barangay
          WHERE b.municipality_id = :municipality_id
          GROUP BY b.barangay_name
          ORDER BY average_total DESC";

      $stmt = $conn->prepare($query);
      $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
      $stmt->execute();
      $barangay_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $municipality_name = 'No municipality ID found for this user';
      $barangay_ratings = []; // Empty if no data found
    }
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
  }

  function getAdjectivalRating($total)
  {
    if ($total >= 100) {
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

  // Fetch available years from movrate table
  $query = "SELECT DISTINCT YEAR(year) AS year FROM movrate ORDER BY year DESC"; // use 'daterate' instead of 'date'
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

  // Add the current year if not present in the years array
  $currentYear = (int)date('Y');
  if (!in_array($currentYear, $years)) {
    $years[] = $currentYear;
    rsort($years); // Sort years in descending order
  }

  // Get selected year from request or default to the latest year
  $selectedYear = $_GET['year'] ?? $currentYear;

  // Filter dataset by selected year
  $query = "
      SELECT b.barangay_name AS barangay, AVG(m.total) AS average_total
      FROM barangays b
      JOIN movrate m ON b.id = m.barangay
      WHERE b.municipality_id = :municipality_id
        AND YEAR(m.year) = :selectedYear  -- use 'year' instead of 'date'
      GROUP BY b.barangay_name
      ORDER BY average_total DESC";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
  $stmt->bindParam(':selectedYear', $selectedYear, PDO::PARAM_INT);
  $stmt->execute();
  $barangay_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <h2 class="text-lg font-bold mb-4">Comparative Evaluation Results</h2>
  <div class="overflow-x-auto mt-4">
    <table class="table table-bordered w-full border border-gray-800">
      <thead>
        <tr>
          <th class="px-4 py-2 text-left">LUPONG TAGAPAMAYAPA (LT)</th>
          <th class="px-4 py-2 text-left">OVERALL PERFORMANCE RATING</th>
          <th class="px-4 py-2 text-left">ADJECTIVAL RATING</th>
          <th class="px-4 py-2 text-left">RANK</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $num = 1;
        $rank = 1;
        foreach ($barangay_ratings as $row): ?>
          <tr>
            <td class="px-4 py-2"><?php echo $num++; ?>. <span class="spacingtabs"><?php echo htmlspecialchars($row['barangay']); ?></span></td>
            <td class="px-4 py-2"><?php echo htmlspecialchars($row['average_total']); ?></td>
            <td class="px-4 py-2"><?php echo getAdjectivalRating($row['average_total']); ?></td>
            <td class="px-4 py-2"><?php echo $rank++; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const barangays = <?php echo json_encode($barangays); ?>;
const totals = <?php echo json_encode($totals); ?>;

const performanceLabels = totals.map(total => {
  if (total >= 100) return 'Outstanding';
  else if (total >= 90) return 'Very Satisfactory';
  else if (total >= 80) return 'Fair';
  else if (total >= 70) return 'Poor';
  else return 'Very Poor';
});

const ctx = document.getElementById('barangayChart').getContext('2d');
const barangayChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: barangays,
    datasets: [{
      label: 'Average Total Score',
      data: totals,
      backgroundColor: totals.map(total => 
        total >= 100 ? 'rgba(0, 51, 102, 0.6)' : 'rgba(0, 51, 102, 0.6)'), 
      borderColor: totals.map(total => 
        total >= 100 ? 'rgba(0, 153, 51, 1)' : 'rgba(54, 162, 235, 1)'),
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        max: 100,
        title: {
          display: true,
          text: 'Average Total Score'
        }
      }
    },
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: {
          color: '#333',
          font: {
            size: 14
          }
        }
      },
      tooltip: {
        callbacks: {
          afterLabel: function(context) {
            return 'Performance: ' + performanceLabels[context.dataIndex];
          }
        }
      }
    },
    animation: {
      duration: 1000, // Animation duration in milliseconds
      easing: 'easeInOutBounce', // Animation easing function
    }
  }
});

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Lists of cities and municipalities
    const cities = ["Calamba", "Biñan", "San Pedro", "Sta Rosa", "Cabuyao", "San Pablo"];
    const municipalities = ["Bay", "Alaminos", "Calauan", "Los Baños"];

    /**
     * Normalize names for consistent comparison
     * @param {string} name - Name to normalize
     * @returns {string} Normalized name
     */
    function normalizeName(name) {
        return name.toLowerCase().replace(/\s+/g, "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    /**
     * Classify a municipality name as "City" or "Municipality"
     * @param {string} municipalityName - Name to classify
     * @returns {string} "City", "Municipality", or "Unknown"
     */
    function classifyMunicipality(municipalityName) {
        const normalized = normalizeName(municipalityName);
        const normalizedCities = cities.map(normalizeName);
        const normalizedMunicipalities = municipalities.map(normalizeName);

        if (normalizedCities.includes(normalized)) {
            return "City";
        } else if (normalizedMunicipalities.includes(normalized)) {
            return "Municipality";
        } else {
            return "Unknown";
        }
    }

    // Get municipality name from PHP and classify
    const municipalityName = <?php echo json_encode($municipalityName); ?>;
    const classification = classifyMunicipality(municipalityName);

    // Update header and details with the classification
    document.getElementById("details-municipality-type").textContent = classification;

    // Update admin title based on classification
    const adminTitle = document.getElementById("admin-title");
    if (classification === "Municipality") {
        adminTitle.textContent = "MLGOO:";
    } else if (classification === "City") {
        adminTitle.textContent = "CLGOO:";
    }
});
</script>

        </div>
      </div>
    </div>
  </div>
</body>
<style>
    /* Custom CSS to control the canvas size */
    #chart-container {
      max-width: 3000px; /* Adjust width as needed */
      max-height: 350px; /* Adjust height as needed */
      margin: auto;
    }
  </style>

    </div>
  </div>

</body>
</html>
