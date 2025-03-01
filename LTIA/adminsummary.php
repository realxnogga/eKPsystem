<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
	header("Location: login.php");
	exit;
}

$user_id = $_SESSION['user_id'];

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
	<title>LTIA Form 3</title>
	<link rel="icon" type="image/x-icon" href="../img/favicon.ico">
	<link rel="stylesheet" href="../assets/css/styles.min.css" />
	<!-- * Bootstrap v5.3.0-alpha1 (https://getbootstrap.com/) -->

	<!-- remove later -->
	<script src="https://cdn.tailwindcss.com"></script>

	<style>
		/* General print styling */
		@media print {

			body * {
				visibility: hidden;
			}

			/* --------- */
			.print-content {
				inset: 0;
        position: absolute;
			}

			.headerwiwit {
        height: fit-content;
				display: flex;
				flex-direction: row;
				background-color: #000035;

			}
			.headerwiwit div{
        padding: none;
			}
			.headerwiwit div h1{
        font-size: medium;
			}
			.headerwiwit div img{
        height: 4rem;
				width: 4rem;
			}
		

			/* ---------- */

			.print-content,
			.print-content * {
				visibility: visible;
			}


			.print-content {
				width: 100%;
				height: 100%;
				font-size: 11pt;
				/* Adjust font size to fit content */
				margin: 0;
				/* No margin to use full page space */
		
				/* Set padding to give a little breathing room */
				box-sizing: border-box;
			}


			.print-content .card {
				width: 100%;
				max-width: 100%;
				padding: 0;
				margin: 0;
				box-sizing: border-box;
				box-shadow: none;
			}


			.print-content table {
				width: 100%;
				border-collapse: collapse;
			}


			.print-content th,
			.print-content td {
				padding: 8px;
				font-size: 12pt;
			}


			.btn,
			.btn-save,
			.text-right {
				display: none;
			}


			.print-content h1 {
				font-size: 14pt;
			}

			.print-content p,
			.print-content b {
				font-size: 12pt;
			}


			.print-content .spacingtabs {
				display: inline-block;
				width: 6em;
				text-align: center;
			}


			.print-content p {
				word-wrap: break-word;
			}
		}
	</style>

</head>

<body class="bg-[#E8E8E7]">
	<?php include "../admin_sidebar_header.php"; ?>
	<div class="p-4 sm:ml-44 ">
		<div class="rounded-lg mt-16">
			<!-- First Card -->
			<div class="card">
				<div class="card-body">
					<div class="menu flex items-center justify-between">
						<button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='adminform2evaluate.php';">
							<i class="ti ti-building-community mr-2"></i> Back
						</button>
					</div>
				</div>
			</div>
			<div class="text-right">
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
				
			

			<div class="text-right">
				<button onclick="printSecondCard()" class="btn btn-primary">Print</button>
			</div>

			<!-- Content to be printed -->
			<div class="print-content">
				<!-- header -->
				<div class="card mt-4">
					<div class="card-body">
						<!-- Logo, Title, and Subtitle Section -->
						<div class="headerwiwit flex items-center justify-center gap-x-5">
							<!-- DILG Logo -->
							<div class="dilglogo flex justify-center">
								<img src="../img/dilg.png" alt="DILG Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
							</div>

							<!-- Title in Bordered Box -->
							<div class="text-center">
								<h1 class="text-xl font-bold">
									CY Lupong Tagapamayapa Incentives Award (LTIA) <br>
									LTIA FORM 3 (C/M) - COMPARATIVE EVALUATION FORM
								</h1>
							</div>

							<!-- LTIA Logo -->
							<div class="ltialogo flex justify-center">
								<img src="images/ltialogo.png" alt="LTIA Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
							</div>
						</div>

						<!-- Identifying Information Section -->
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
	</div>
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
</script>


						<br>
						<b>B. COMPARATIVE EVALUATION RESULTS</b><br>

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

						<br>
						<b>C. WE CERTIFY TO THE CORRECTNESS OF THE ABOVE INFORMATION</b><br><br>
                        <div class="flex flex-col items-center mt-4">
                        <?php if (!empty($admin)): ?>
                        <div class="flex items-center mt-4">
                        <strong class="text-lg font-bold">
                            <strong id="admin-title">Admin:</strong>
                            <p class="ml-2"> <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?> Awards Committee</strong></p>
                        </div>
                        <hr class="my-2">
                        <?php else: ?>
                        <p class="text-red-500">No admin found for this municipality.</p>
                        <hr class="my-2">
                        <?php endif; ?>
                        
                        <?php if (!empty($assessors)): ?>
                        <ul class="list-disc pl-5">
                            <?php foreach ($assessors as $assessor): ?>
                            <li class="mb-2">
                                <strong class="text-lg font-bold">Members- <?php echo htmlspecialchars($assessor['first_name'] . ' ' . $assessor['last_name']); ?> Awards Committee</strong>
                            </li>
                            <hr class="my-2">
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="text-red-500">No assessors found for this municipality.</p>
                        <?php endif; ?>
						</div>

						<br><br>
						<b>D. DATE ACCOMPLISHED</b><br>
						<span class="spacingtabs"> <?php echo date("F j, Y"); ?></span>
						<br><br>

						<!-- Do not print this -->
						<div class="text-right mt-4">
							<input type="submit" value="Save" style="background-color: #000035;" class="btn-save">
							</form>
						</div>
					</div>
				</div>
			</div>
			
			<script>
				function printSecondCard() {
					window.print();
				}

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
        adminTitle.textContent = "MLGOO-";
    } else if (classification === "City") {
        adminTitle.textContent = "CLGOO-";
    }
});
			</script>
</body>
<style>
	.spacingtabs {
		padding-left: 2em;
		/* Adjust as needed for spacing */
	}

	.spacingtabs2 {
		padding-left: 2em;
		/* Adjust as needed for spacing */
	}

	.underline-input {
		text-align: center;
		border: none;
		border-bottom: 1px solid #5A5A5A;
		/* Black underline */
		outline: none;
		background-color: transparent;
		width: 25%;
		font-size: 16px;
		padding: 5px 0;
	}

	.underline-input:focus {
		border-bottom-color: #007bff;
		/* Highlight underline on focus */
	}
    ul li{
        list-style-type: none;
    }
</style>

</html>