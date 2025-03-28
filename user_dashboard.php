  <?php
  session_start();

  include 'connection.php';

  if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
  }

  $userID = $_SESSION['user_id'] ?? '';
  $barangay_id = $_SESSION['barangay_id'] ?? '';

  $stmt_casetype_query = "SELECT 
                            SUM(CASE WHEN CType = 'Criminal' AND isArchived = 0 THEN 1 ELSE 0 END) AS criminal_count,
                            SUM(CASE WHEN CType = 'Civil' AND isArchived = 0 THEN 1 ELSE 0 END) AS civil_count,
                            SUM(CASE WHEN CType = 'Others' AND isArchived = 0 THEN 1 ELSE 0 END) AS others_count
                        FROM complaints 
                        WHERE UserID = :user_id AND BarangayID = :barangay_id AND MONTH(Mdate) = MONTH(NOW())";

  $stmt_casetype_count = $conn->prepare($stmt_casetype_query);
  $stmt_casetype_count->bindParam(':user_id', $userID);
  $stmt_casetype_count->bindParam(':barangay_id', $barangay_id);
  $stmt_casetype_count->execute();
  $stmt_casetype_count_temp = $stmt_casetype_count->fetch(PDO::FETCH_ASSOC);

  $natureOfCasesData = [
    'Criminal' => $stmt_casetype_count_temp['criminal_count'] ?? 0,
    'Civil' => $stmt_casetype_count_temp['civil_count'] ?? 0,
    'Others' => $stmt_casetype_count_temp['others_count'] ?? 0
  ];

  // for cases card
  $stmt_casestatus_query = "SELECT 
                            SUM(CASE WHEN CMethod = 'Pending' AND isArchived = 0 THEN 1 END) AS total_pending_count,
                            SUM(CASE WHEN CStatus = 'Unsettled' AND isArchived = 0 THEN 1 END) AS total_unsettled_count,
                            SUM(CASE WHEN CStatus = 'Settled' AND isArchived = 0 THEN 1 END) AS total_settled_count
                        FROM complaints 
                        WHERE UserID = :user_id AND BarangayID = :barangay_id AND MONTH(Mdate) = MONTH(NOW())";

  $stmt_casestatus_count = $conn->prepare($stmt_casestatus_query);
  $stmt_casestatus_count->bindParam(':user_id', $userID);
  $stmt_casestatus_count->bindParam(':barangay_id', $barangay_id);
  $stmt_casestatus_count->execute();
  $stmt_casestatus_count_temp = $stmt_casestatus_count->fetch(PDO::FETCH_ASSOC);

  $totalSettledCount = $stmt_casestatus_count_temp['total_settled_count'] ?? 0;
  $totalUnsettledCount = $stmt_casestatus_count_temp['total_unsettled_count'] ?? 0;
  $totalPendingCount = $stmt_casestatus_count_temp['total_pending_count'] ?? 0;

  // for summary card
  $stmt_complaint_query = "SELECT 
                            SUM(CASE WHEN MONTH(Mdate) = 1 AND isArchived = 0 THEN 1 END) AS jan,
                            SUM(CASE WHEN MONTH(Mdate) = 2 AND isArchived = 0 THEN 1 END) AS feb,
                            SUM(CASE WHEN MONTH(Mdate) = 3 AND isArchived = 0 THEN 1 END) AS mar,
                            SUM(CASE WHEN MONTH(Mdate) = 4 AND isArchived = 0 THEN 1 END) AS apr,
                            SUM(CASE WHEN MONTH(Mdate) = 5 AND isArchived = 0 THEN 1 END) AS may,
                            SUM(CASE WHEN MONTH(Mdate) = 6 AND isArchived = 0 THEN 1 END) AS jun,
                            SUM(CASE WHEN MONTH(Mdate) = 7 AND isArchived = 0 THEN 1 END) AS july,
                            SUM(CASE WHEN MONTH(Mdate) = 8 AND isArchived = 0 THEN 1 END) AS aug,
                            SUM(CASE WHEN MONTH(Mdate) = 9 AND isArchived = 0 THEN 1 END) AS sep,
                            SUM(CASE WHEN MONTH(Mdate) = 10 AND isArchived = 0 THEN 1 END) AS oct,
                            SUM(CASE WHEN MONTH(Mdate) = 11 AND isArchived = 0 THEN 1 END) AS nov,
                            SUM(CASE WHEN MONTH(Mdate) = 12 AND isArchived = 0 THEN 1 END) AS decs
                        FROM complaints 
                        WHERE UserID = :user_id AND BarangayID = :barangay_id AND YEAR(Mdate) = YEAR(NOW())";

  $stmt_complaint_count = $conn->prepare($stmt_complaint_query);
  $stmt_complaint_count->bindParam(':user_id', $userID);
  $stmt_complaint_count->bindParam(':barangay_id', $barangay_id);
  $stmt_complaint_count->execute();
  $stmt_complaint_count_temp = $stmt_complaint_count->fetch(PDO::FETCH_ASSOC);

  $summaryData = [
    'January' => $stmt_complaint_count_temp['jan'] ?? 0,
    'February' => $stmt_complaint_count_temp['feb'] ?? 0,
    'March' => $stmt_complaint_count_temp['mar'] ?? 0,
    'April' => $stmt_complaint_count_temp['apr'] ?? 0,
    'May' => $stmt_complaint_count_temp['may'] ?? 0,
    'June' => $stmt_complaint_count_temp['jun'] ?? 0,
    'July' => $stmt_complaint_count_temp['july'] ?? 0,
    'August' => $stmt_complaint_count_temp['aug'] ?? 0,
    'September' => $stmt_complaint_count_temp['sep'] ?? 0,
    'October' => $stmt_complaint_count_temp['oct'] ?? 0,
    'November' => $stmt_complaint_count_temp['nov'] ?? 0,
    'December' => $stmt_complaint_count_temp['decs'] ?? 0,
  ];


  ?>

 <!-- filepath: /c:/xampp/htdocs/eKPsystem/user_dashboard.php -->
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
<script src="node_modules/chart.js/dist/chart.umd.js"></script>
<script src="node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
<!-- jquery -->
<script src="node_modules/jquery/dist/jquery.min.js"></script>
<!-- flowbite component -->
<script src="node_modules/flowbite/dist/flowbite.min.js"></script>
<link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
<!-- tabler icon -->
<link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
<!-- tabler support -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

<!-- tailwind cdn -->
<link rel="stylesheet" href="output.css">

  <title>Dashboard</title>
</head>

<body class="bg-gray-200">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">

      <!-- Section Header -->
      <div class="flex items-center my-4">
        <div class="flex-grow border-t border-gray-400"></div>
        <span class="px-4 text-lg text-gray-500">Cases</span>
        <div class="flex-grow border-t border-gray-400"></div>
      </div>

      <!-- Cards Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Settled Cases Card -->
        <div class="bg-cover bg-center bg-custom-settled rounded-lg shadow-lg text-center text-white p-6">
          <h5 class="text-xl font-semibold mb-4">Settled Cases</h5>
          <p class="text-4xl font-bold"><?php echo $totalSettledCount; ?></p>
        </div>

        <!-- Unsettled Cases Card -->
        <div class="bg-cover bg-center bg-custom-unsettled rounded-lg shadow-lg text-center text-white p-6">
          <h5 class="text-xl font-semibold mb-4">Unsettled Cases</h5>
          <p class="text-4xl font-bold"><?php echo $totalUnsettledCount; ?></p>
        </div>

        <!-- Pending Cases Card -->
        <div class="bg-cover bg-center bg-custom-pending rounded-lg shadow-lg text-center text-white p-6">
          <h5 class="text-xl font-semibold mb-4">Pending Cases</h5>
          <p class="text-4xl font-bold"><?php echo $totalPendingCount; ?></p>
        </div>
      </div>

      <!-- Summary and Nature of Cases Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
        <!-- Summary Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h5 class="text-xl font-semibold mb-4 text-gray-800">Summary (<?php echo date('Y'); ?>)</h5>
          <canvas id="complaintsChart" width="800" height="400"></canvas>
          <script>
            var ctx = document.getElementById('complaintsChart').getContext('2d');
            var myChart = new Chart(ctx, {
              type: 'line',
              data: {
                labels: <?php echo json_encode(array_keys($summaryData)) ?>,
                datasets: [{
                  label: 'Number of Complaints',
                  data: <?php echo json_encode($summaryData) ?>,
                  backgroundColor: 'rgba(255, 99, 132, 0.2)',
                  borderColor: 'rgba(255, 99, 132, 1)',
                  borderWidth: 1
                }]
              },
              options: {
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            });
          </script>
        </div>

        <!-- Nature of Cases Card -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h5 class="text-xl font-semibold mb-4 text-gray-800">Nature of Cases (<?php echo date('F Y'); ?>)</h5>
          <canvas id="natureOfCasesChart" width="800" height="400"></canvas>
          <script>
            var ctx = document.getElementById('natureOfCasesChart').getContext('2d');
            var myChart = new Chart(ctx, {
              type: 'bar',
              data: {
                labels: <?php echo json_encode(array_keys($natureOfCasesData)) ?>,
                datasets: [{
                  label: 'Count',
                  data: <?php echo json_encode($natureOfCasesData) ?>,
                  backgroundColor: 'rgba(54, 162, 235, 0.5)',
                  borderColor: 'rgba(54, 162, 235, 1)',
                  borderWidth: 1
                }]
              },
              options: {
                scales: {
                  y: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'Count'
                    }
                  },
                  x: {
                    title: {
                      display: true
                    }
                  }
                }
              }
            });
          </script>
        </div>
      </div>

      <!-- References Section -->
      <div class="flex items-center my-4">
        <div class="flex-grow border-t border-gray-400"></div>
        <span class="px-4 text-lg text-gray-500">References & Links</span>
        <div class="flex-grow border-t border-gray-400"></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- References Card -->
        <div class="bg-white cols-span-3 md:col-span-2 rounded-lg shadow-lg p-6">
          <h5 class="text-xl font-semibold mb-4 text-gray-800">References</h5>
          <ul class="list-disc list-inside text-gray-600">
          <a href="links/Related_Laws_Katarungang_Pambarangay_Handbook (1).pdf" download>Related Laws KP Handbook</a><br>
                <a href="links/OFFENSES WITHIN KP_Jurisdiction_for Admin.pptx.pdf" download>Offenses within KP Jurisdiction for Admin</a><br>
                <a href="links/LTIA-FORMS-6-7-1.docx.pdf" download>LTIA-FORMS-6-7-1</a><br>
                <a href="links/KP-for-Atty-Ver.ppt.pdf" download>Revised KP Law</a><br>
                <a href="links/template-conso-report-KP (1).xlsx - Sheet1.pdf" download>Consolidated KP Compliance Report</a><br>
                <a href="links/KATARUNGANG PAMBARANGAY.pptx.pdf" download>DILG Laguna Cluster-A SUB LGRC</a><br>
                <a href="links/KP-Flowchart-with-link-to-KP-Forms_atty-ver.pptx.pdf" download>KP Flowchart with Links</a><br>
                <a href="links/KP actual process_Jurusdictional aspect.pptx.pdf" download>KP Actual Process Jurisdictional Aspect</a><br>
                <a href="links/Katarungang-Pambarangay-2018-v2.pptx.pdf" download>KP 2018 V2</a><br>
                <a href="links/criminal-cases-under-the-jurisdiction-of-KP_atty-ver.pptx.pdf" download>Criminal Cases Under the Jurisdiction of KP</a><br>
                <a href="links/KP-IRR.pdf" download>KP Forms English</a><br>
                <a href="links/543442409-KP-Forms-Tagalog-1.pdf" download>KP Forms Tagalog</a>
            <!-- Add more links as needed -->
          </ul>
        </div>

        <!-- External Links -->
        <div class="grid cols-span-3 md:cols-span-1 gap-4">
          <a href="https://www.dilg.gov.ph/" class="block bg-contain bg-center bg-custom-official rounded-lg shadow-lg h-40" target="_blank"></a>
          <a href="https://www.facebook.com/dilglaguna.clustera.7" class="block bg-contain bg-custom-fb rounded-lg shadow-lg h-40" target="_blank"></a>
          <a href="https://www.instagram.com/dilgr4a/" class="block bg-cover bg-contain bg-custom-ig rounded-lg shadow-lg h-40" target="_blank"></a>
        </div>
      </div>
    </div>
  </div>

</body>

</html>