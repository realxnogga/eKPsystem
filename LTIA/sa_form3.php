<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}
$municipality_id = $_SESSION['municipality_id']; 
// Fetch municipality name
$municipalityQuery = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
$municipalityStmt = $conn->prepare($municipalityQuery);
$municipalityStmt->bindValue(':municipality_id', $municipality_id, PDO::PARAM_INT);
$municipalityStmt->execute();
$municipalityName = $municipalityStmt->fetchColumn();

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
} 
else {
    echo "No municipality selected.";
    exit;
}

// Fetch available years from `movrate` table for dropdown
$query = "SELECT DISTINCT year FROM movrate ORDER BY year DESC"; // Changed from YEAR(daterate)
$stmt = $conn->prepare($query);
$stmt->execute();
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get selected year from request or default to the latest year
$selectedYear = $_GET['year'] ?? $years[0];

// Modify the query to calculate average for duplicate entries
$query = "
    SELECT b.barangay_name AS barangay, 
           COALESCE(AVG(m.total), 0) as average_total,
           COUNT(m.id) as entry_count
    FROM barangays b
    LEFT JOIN movrate m ON b.id = m.barangay
    WHERE b.municipality_id = :municipality_id
    GROUP BY b.id
    HAVING average_total > 0
    ORDER BY average_total DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
$stmt->execute();
$barangay_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add this after fetching barangay_ratings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_averages'])) {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE
        $upsert_query = "INSERT INTO average (mov_id, barangay, avg, year) 
                        SELECT 
                            mv.id as mov_id,
                            b.id as barangay,
                            AVG(m.total) as avg,
                            :year
                        FROM movrate m 
                        JOIN barangays b ON m.barangay = b.id
                        JOIN mov mv ON mv.barangay_id = b.id AND mv.year = :year
                        WHERE b.municipality_id = :municipality_id
                        AND m.year = :year
                        GROUP BY b.id, mv.id
                        HAVING mov_id IS NOT NULL
                        ON DUPLICATE KEY UPDATE
                            avg = VALUES(avg),
                            year = VALUES(year)";
                        
        $stmt = $conn->prepare($upsert_query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
        $stmt->execute();

        $conn->commit();
        echo "<script>alert('Averages saved/updated successfully!');</script>";
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "<script>alert('Error saving averages: " . addslashes($e->getMessage()) . "');</script>";
    }
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
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LTIA Form 3</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico"> 
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>
  <script>
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
    const municipalityName = <?php echo json_encode($municipality_name); ?>;
    const classification = classifyMunicipality(municipalityName);

    // Update header and details with the classification
    document.getElementById("details-municipality-type").textContent = classification;
    document.getElementById("municipality-category").textContent = classification.toUpperCase();
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
</script>
</head>
<body class="bg-[#E8E8E7]">
<?php include "../sa_sidebar_header.php"; ?>
<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
      <div class="card">
        <div class="card-body">
          <div class="menu d-flex justify-content-between align-items-center">
            <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_sa_dashboard.php';">
              <i class="ti ti-building-community mr-2"> </i> Back
            </button>
                    <form method="get" action="">
                      <input type="hidden" name="municipality_id" value="<?php echo $municipality_id; ?>">
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
<p style="padding-left: 5em;">
    City/Municipality: 
    <span id="details-municipality-type" style="display: inline-block; white-space: nowrap; width: auto; text-transform: uppercase;"></span> 
    OF <?php echo strtoupper(htmlspecialchars($municipality_name)); ?>
</p>
<p style="padding-left: 5em;">
    Region <span style="display: inline-block; width: 3em; text-align: center;">:</span> IVA
</p>
<p style="padding-left: 5em;">
    Province <span style="display: inline-block; width: 3em; text-align: center;">:</span> LAGUNA
</p>
<p style="padding-left: 5em;">
    Category <span style="display: inline-block; white-space: nowrap; width: auto; text-transform: uppercase;"></span>: 
    <span id="municipality-category" style="text-transform: uppercase;"></span>
</p>
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
              foreach ($barangay_ratings as $row): 
                  $average_total = round($row['average_total'], 2); // Round to 2 decimal places
                  ?>
                  <tr>
                      <td><?php echo $num++; ?>. <?php echo htmlspecialchars($row['barangay']); ?></td>
                      <td><?php echo htmlspecialchars($average_total); ?></td>
                      <td><?php echo getAdjectivalRating($average_total); ?></td>
                      <td><?php echo $rank++; ?></td>
                  </tr>
              <?php endforeach; ?>
          </tbody>
          </table>
          <form method="POST" class="inline">
                      <button type="submit" name="save_averages" 
                              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                          Save Averages
                      </button>
                  </form>
          <br>

          <div class="certification-section text-center">
          <h3 class="text-xl font-bold mb-4">C. WE CERTIFY TO THE CORRECTNESS OF THE ABOVE INFORMATION</h3>

<?php if (!empty($admin)): ?>
    <div class="pb-2 mb-4">
        <h4 class="text-lg font-semibold">Admin</h4>
        <p class="text-gray-700"><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></p>
        <hr class="thin-hr">
    </div>
<?php else: ?>
    <p class="text-red-500 font-semibold">No admin found for this municipality.</p>
<?php endif; ?>

<?php if (!empty($assessors)): ?>
    <div class="mt-4">
        <h4 class="text-lg font-semibold mb-2">Assessors</h4>
        <ul class="space-y-2">
            <?php foreach ($assessors as $assessor): ?>
                <li class="pb-2">
                    <span class="font-semibold"><?php echo htmlspecialchars($assessor['assessor_type']); ?></span>:
                    <?php echo htmlspecialchars($assessor['first_name'] . ' ' . $assessor['last_name']); ?> - 
                    <span class="italic text-gray-600">Members Committee</span>
                    <hr class="thin-hr">
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <p class="text-red-500 font-semibold mt-2">No assessors found for this municipality.</p>
<?php endif; ?>
</div>

<br><br>

<b>D. DATE ACCOMPLISHED</b><br>
<span class="spacingtabs"><?php echo date("F j, Y"); ?></span>

<br><br>

<div class="text-center mt-4"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
<style>
     .thin-hr {
        border: none;
        height: 1px;
        background-color: black;
        width: 100%;
        margin: 4px 0;
    }
.spacingtabs {
    padding-left: 2em; /* Adjust as needed for spacing */
}.spacingtabs2 {
    padding-left: 2em; /* Adjust as needed for spacing */
}

.barangay-select {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: white;
    font-size: 14px;
    width: 100%;
}

.barangay-select option {
    padding: 8px;
    font-size: 14px;
}

.barangay-select:focus {
    outline: none;
    border-color: #666;
}

.mt-4 {
    margin-top: 1rem;
}

.text-center {
    text-align: center;
}

.inline {
    display: inline-block;
}
</style>
<script>
document.querySelectorAll('.barangay-select').forEach(select => {
    select.addEventListener('change', function() {
        // You can add any additional functionality here if needed
    });
});
</script>
</html>
