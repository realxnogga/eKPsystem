<?php
session_start();
include 'connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'] ?? '';
$barangay_id = $_SESSION['barangay_id'] ?? '';

// Check if the report_id is provided in the URL
if (isset($_GET['report_id'])) {
  $report_id = $_GET['report_id'];
  // Query to fetch the report data using the report_id
  $query = "SELECT * FROM reports WHERE report_id = :report_id";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':report_id', $report_id);
  $stmt->execute();
  $report = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if the report exists
  if (!$report) {
    echo "Report not found.";
    exit;
  }

  // Extract the month and year from the report_date
  $report_date = date('F Y', strtotime($report['report_date']));
} else {
  echo "Report ID is missing.";
  exit;
}

if (isset($_POST['update'])) {
  // Retrieve form data
  $report_date = $_POST['report_date'];
  $mayor = $_POST['mayor'];
  $budget = str_replace(',', '', $_POST['budget']); // remove ,
  $totalcase = $_POST['totalcase'];
  $numlupon = $_POST['numlupon'];
  $landarea = $_POST['landarea'];
  $region = $_POST['region'];
  $population = $_POST['population'];
  $male = $_POST['male'];
  $female = $_POST['female'];
  $criminal = $_POST['criminal'];
  $others = $_POST['others'];
  $civil = $_POST['civil'];
  $totalNature = $_POST['totalNature'];
  $media = $_POST['media'];
  $arbit = $_POST['arbit'];
  $concil = $_POST['concil'];
  $totalSet = $_POST['totalSet'];
  $pending = $_POST['pending'];
  $repudiated = $_POST['repudiated'];
  $dropped = $_POST['dropped'];
  $outsideBrgy = $_POST['outsideBrgy'];
  $dismissed = $_POST['dismissed'];
  $certcourt = $_POST['certcourt'];
  $totalUnset = $_POST['totalUnset'];

  // Prepare update query
  $update_query = "UPDATE reports SET report_date = :report_date, mayor = :mayor, budget = :budget, totalcase = :totalcase, numlupon = :numlupon, landarea = :landarea, region = :region, population = :population, male = :male, female = :female, criminal = :criminal, others = :others, civil = :civil, totalNature = :totalNature, media = :media, arbit = :arbit, concil = :concil, totalSet = :totalSet, pending = :pending, repudiated = :repudiated, dropped = :dropped, outsideBrgy = :outsideBrgy, dismissed = :dismissed, certcourt = :certcourt, totalUnset = :totalUnset WHERE report_id = :report_id";

  // Prepare and execute the update query
  $stmt = $conn->prepare($update_query);
  $stmt->bindParam(':report_date', $report_date);
  $stmt->bindParam(':mayor', $mayor);
  $stmt->bindParam(':budget', $budget);
  $stmt->bindParam(':totalcase', $totalcase);
  $stmt->bindParam(':numlupon', $numlupon);
  $stmt->bindParam(':landarea', $landarea);
  $stmt->bindParam(':region', $region);
  $stmt->bindParam(':population', $population);
  $stmt->bindParam(':male', $male);
  $stmt->bindParam(':female', $female);
  $stmt->bindParam(':criminal', $criminal);
  $stmt->bindParam(':others', $others);
  $stmt->bindParam(':civil', $civil);
  $stmt->bindParam(':totalNature', $totalNature);
  $stmt->bindParam(':media', $media);
  $stmt->bindParam(':arbit', $arbit);
  $stmt->bindParam(':concil', $concil);
  $stmt->bindParam(':totalSet', $totalSet);
  $stmt->bindParam(':pending', $pending);
  $stmt->bindParam(':repudiated', $repudiated);
  $stmt->bindParam(':dropped', $dropped);
  $stmt->bindParam(':outsideBrgy', $outsideBrgy);
  $stmt->bindParam(':dismissed', $dismissed);
  $stmt->bindParam(':certcourt', $certcourt);
  $stmt->bindParam(':totalUnset', $totalUnset);
  $stmt->bindParam(':report_id', $report_id);


  // Execute the update query
  if ($stmt->execute()) {

    header("Location: user_edit_report.php?report_id=" . urlencode($_GET['report_id']) . "&edit_userreport_message=reporteditsuccess");
    exit();
  } else {

    header("Location: user_edit_report.php?report_id=" . urlencode($_GET['report_id']) . "&edit_userreport_message=reportediterror");
    exit();
  }
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />
  <!-- tailwind cdn -->
<link rel="stylesheet" href="output.css">

</head>

<body class="sm:bg-gray-200 bg-white">

  <?php include "user_sidebar_header.php"; ?>

 <!-- filepath: /c:/xampp/htdocs/eKPsystem/user_edit_report.php -->
<div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">

        <!-- Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="col-span-3 lg:col-span-2 bg-white shadow-none sm:shadow-md rounded-0 sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <img src="img/cluster.png" alt="Logo" class="w-24 h-24 mr-4">
                        <div>
                            <h5 class="text-lg font-semibold">Department of the Interior and Local Government</h5>
                        </div>
                    </div>
                </div>

                <h5 class="text-lg font-semibold mb-6">Edit Report of <?php echo $report_date; ?></h5>

                <?php
                if (isset($_GET['edit_userreport_message'])) {
                    if ($_GET['edit_userreport_message'] === 'reporteditsuccess') {
                        echo "<div id='alertMessage' class='bg-green-100 text-green-800 p-4 rounded mb-4'>Report updated successfully.</div>";
                    }
                    if ($_GET['edit_userreport_message'] === 'reportediterror') {
                        echo "<div id='alertMessage' class='bg-red-100 text-red-800 p-4 rounded mb-4'>Report failed to update.</div>";
                    }
                }
                ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="report_date" class="block text-sm font-medium text-gray-700 mb-1">Report Date:</label>
                        <input type="date" id="report_date" name="report_date" value="<?php echo $report['report_date']; ?>" required class="border rounded-md p-2 w-full">
                    </div>

                    <!-- Define field sections -->
                    <?php
                    $sections = [
                        'Basic Information' => ['mayor', 'budget', 'totalcase', 'numlupon', 'landarea', 'region', 'population', 'male', 'female'],
                        'Nature of Cases' => ['criminal', 'others', 'civil', 'totalNature'],
                        'Action Taken - Settled' => ['media', 'arbit', 'concil', 'totalSet'],
                        'Action Taken - Unsettled' => ['pending', 'repudiated', 'dropped', 'outsideBrgy', 'dismissed', 'certcourt', 'totalUnset']
                    ];

                    foreach ($sections as $sectionTitle => $fields) {
                        echo '<div>';
                        echo '<h6 class="text-md font-semibold mb-4">' . $sectionTitle . '</h6>';
                        echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                        foreach ($fields as $field) {
                            $inputType = in_array($field, ['mayor', 'budget', 'landarea', 'region', 'population']) ? 'text' : 'number';
                            echo '<div>';
                            echo '<label for="' . $field . '" class="block text-sm font-medium text-gray-700 mb-1">' . ucwords(str_replace('_', ' ', $field)) . ':</label>';
                            echo '<input type="' . $inputType . '" id="' . $field . '" name="' . $field . '" value="' . $report[$field] . '" required class="border rounded-md p-2 w-full">';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>

                    <div>
                        <input type="submit" name="update" value="Update" class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded cursor-pointer">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

  <script src="hide_toast.js"></script>
</body>

</html>