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
	$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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

		// Step 3: Fetch barangays and their total ratings from movrate, sorted by total in descending order
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
// Fetch existing certification data if it exists
$query = "SELECT * FROM movassessmentmembers WHERE municipality_id = :municipality_id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
$stmt->execute();
$certification_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Process form submission
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$chairperson = $_POST['chairperson'];
	$member1 = $_POST['member1'];
	$member2 = $_POST['member2'];
	$member3 = $_POST['member3'];
	$date = date("Y-m-d");

	if ($certification_data) {
		// Update existing record
		$query = "UPDATE movassessmentmembers SET chairperson = :chairperson, member1 = :member1, member2 = :member2, member3 = :member3, date = :date WHERE municipality_id = :municipality_id";
	} else {
		// Insert new record
		$query = "INSERT INTO movassessmentmembers (municipality_id, chairperson, member1, member2, member3, date) VALUES (:municipality_id, :chairperson, :member1, :member2, :member3, :date)";
	}

	$stmt = $conn->prepare($query);
	$stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
	$stmt->bindParam(':chairperson', $chairperson, PDO::PARAM_STR);
	$stmt->bindParam(':member1', $member1, PDO::PARAM_STR);
	$stmt->bindParam(':member2', $member2, PDO::PARAM_STR);
	$stmt->bindParam(':member3', $member3, PDO::PARAM_STR);
	$stmt->bindParam(':date', $date, PDO::PARAM_STR);

	if ($stmt->execute()) {
		$message = "Members Saved";
		$certification_data = [
			'chairperson' => $chairperson,
			'member1' => $member1,
			'member2' => $member2,
			'member3' => $member3,
			'date' => $date
		];
	} else {
		$message = "Error saving certification details.";
	}
} // Fetch available years from `movrate` table
$query = "SELECT DISTINCT YEAR(daterate) AS year FROM movrate ORDER BY year DESC"; // use 'daterate' instead of 'date'
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
				font-size: 10pt;
			}


			.btn,
			.btn-save,
			.text-right {
				display: none;
			}


			.print-content h1 {
				font-size: 18pt;
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

			<div class="text-right">
				<button onclick="printSecondCard()" class="btn btn-primary">Print</button>
				<button onclick="downloadSecondCard()" class="btn btn-secondary">Download</button>
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
							<p style="padding-left: 5em;">City/Municipality <span style="display: inline-block; width: 3em; text-align: center;">:</span> CITY OF <?php echo htmlspecialchars($municipality_name); ?></p>
							<p style="padding-left: 5em;">Region <span style="display: inline-block; width: 3em; text-align: center;">:</span> IVA</p>
							<p style="padding-left: 5em;">Province <span style="display: inline-block; width: 3em; text-align: center;">:</span> LAGUNA</p>
							<p style="padding-left: 5em;">Category <span style="display: inline-block; width: 3em; text-align: center;">:</span> CITY</p>
						</div>

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
											<td class="px-4 py-2"><?php echo htmlspecialchars($row['total']); ?></td>
											<td class="px-4 py-2"><?php echo getAdjectivalRating($row['total']); ?></td>
											<td class="px-4 py-2"><?php echo $rank++; ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<br>
						<b>C. WE CERTIFY TO THE CORRECTNESS OF THE ABOVE INFORMATION</b><br><br>
						<div class="certification-section text-center">
							<form method="post" action="" enctype="multipart/form-data">
								<input type="text" name="chairperson" class="underline-input" placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['chairperson'] ?? ''); ?>"><br>
								Chairperson - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>

								<input type="text" name="member1" class="underline-input" placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['member1'] ?? ''); ?>"><br>
								Member - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>

								<input type="text" name="member2" class="underline-input" placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['member2'] ?? ''); ?>"><br>
								Member - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>

								<input type="text" name="member3" class="underline-input" placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['member3'] ?? ''); ?>"><br>
								Member - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>
							</form>
						</div>

						<br><br>
						<b>D. DATE ACCOMPLISHED</b><br>
						<span class="spacingtabs"> <?php echo date("F j, Y"); ?></span>
						<br><br>

						<!-- Do not print this -->
						<div class="text-right mt-4">
							<input type="submit" value="Save" style="background-color: #000035;" class="btn-save">
						</div>
					</div>
				</div>
			</div>

			<script>
				function printSecondCard() {
					window.print();
				}
			</script>

			<!-- Bootstrap Modal -->
			<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="confirmationModalLabel">Lupong Tagapamayapa Incentives Award</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<?php echo isset($message) ? htmlspecialchars($message) : ''; ?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					<?php if (isset($message)): ?>
						var confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
						confirmationModal.show();
					<?php endif; ?>
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
</style>

</html>