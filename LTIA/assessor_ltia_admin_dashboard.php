<?php
session_start();

include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'assessor') {
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

// Fetch available years for the dropdown
$yearQuery = "SELECT DISTINCT year FROM movrate 
              WHERE barangay IN (
                  SELECT id FROM barangays 
                  WHERE municipality_id = :municipality_id
              ) 
              AND user_id = :user_id
              ORDER BY year DESC";
$yearStmt = $conn->prepare($yearQuery);
$yearStmt->bindValue(':municipality_id', $municipality_id, PDO::PARAM_INT);
$yearStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$yearStmt->execute();
$years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

// Add current year if it's missing
if (!in_array($currentYear, $years)) {
    array_unshift($years, $currentYear);
}

// Fetch barangays and scores for the selected year
$query = "
SELECT b.barangay_name, COALESCE(m.total, 0) AS total 
FROM barangays b 
LEFT JOIN movrate m ON b.id = m.barangay AND m.year = :year AND m.user_id = :user_id
WHERE b.municipality_id = :municipality_id
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':municipality_id', $municipality_id, PDO::PARAM_INT);
$stmt->bindValue(':year', $selectedYear, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();

$barangays = [];
$totals = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $barangays[] = $row['barangay_name'];
    $totals[] = $row['total'];
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <script src="../node_modules/chart.js/dist/chart.umd.js"></script>
  
</head>

<body class="bg-[#E8E8E7]">

 <?php include "../assessor_sidebar_header.php"; ?>

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
                  <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='assessor_ltia_adminform2evaluate.php';" style="margin-left: 0;">
                  <i class="ti ti-building-community mr-2"> </i> 
                      Barangays
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <div id="chart-container" class="mt-6">
            <canvas id="barangayChart"></canvas>
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
        label: 'Total Score',
        data: totals,
        backgroundColor: totals.map(total => 
          total >= 100 ? 'rgba(0, 153, 51, 0.6)' : 'rgba(54, 162, 235, 0.6)'),
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
          max: 120,
          title: {
            display: true,
            text: 'Total Score'
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
</script>

         <footer class="position-relative">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-100">
              <path fill="#0099ff" fill-opacity="1" d="M0,224L30,224C60,224,120,224,180,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
            </svg>
            <div class="position-absolute bottom-0 end-0 mb-3 me-3 d-flex justify-content-center">
              <img src="images/ltialogo.png" alt="LTIA Logo" class="img-fluid" style="max-height: 80px; width: auto;" />
            </div>
          </footer>

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
