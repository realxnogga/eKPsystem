  <?php
  session_start();
  include 'connection.php';
  //include 'index-navigation.php';
  include 'functions.php';

  if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
  }

  include 'report_handler.php';

  $searchInput = isset($_GET['search']) ? $_GET['search'] : '';

  $userID = $_SESSION['user_id'];

  $query = "SELECT * FROM complaints WHERE UserID = '$userID' AND IsArchived = 0";

  if (!empty($searchInput)) {

    $query .= " AND (CNum LIKE '%$searchInput%' OR ForTitle LIKE '%$searchInput%' OR CNames LIKE '%$searchInput%' OR RspndtNames LIKE '%$searchInput%')";
  }

  $query .= " ORDER BY MDate DESC";

  $result = $conn->query($query);

  include 'add_handler.php';

  // Retrieve the search input from the form
  $searchInput = isset($_GET['search']) ? $_GET['search'] : '';

  // Retrieve user-specific complaints from the database
  $userID = $_SESSION['user_id'];

  // Initial query for all complaints sorted by Mdate in descending order
  $query = "SELECT * FROM complaints WHERE UserID = '$userID' AND IsArchived = 0";

  // Modify your SQL query to filter out archived complaints if a search is performed
  if (!empty($searchInput)) {
    // Add conditions to filter based on the search input
    $query .= " AND (CNum LIKE '%$searchInput%' OR ForTitle LIKE '%$searchInput%' OR CNames LIKE '%$searchInput%' OR RspndtNames LIKE '%$searchInput%')";
  }

  include 'count_lupon.php';

  ?>

  <!doctype html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <style>
      .card {
        box-shadow: 0 0 0.3cm rgba(0, 0, 0, 0.2);
        border-radius: 15px;
      }

      .custom-card {
        margin-bottom: 20px;
      }

      .card-text-center {
        text-align: center;
      }

      .settled-card {
        text-align: center;
        background-image: url('img/settled.png');
        background-size: cover;
        background-position: center;
        width: 100%;
      }

      .unsettled-card {
        text-align: center;
        background-image: url('img/unsettled.png');
        background-size: cover;
        background-position: center;
        width: 100%;
      }

      .pending-card {
        text-align: center;
        background-image: url('img/pending.png');
        background-size: cover;
        background-position: center;
        width: 100%;
      }

      .summary-card {
        background-color: white;
        text-align: center;
        background-size: cover;
        background-position: center;
        width: 100%;
      }

      .nature-card {
        background-color: white;
        text-align: center;
        background-size: cover;
        background-position: center;
        width: 100%;
      }

      .ref-card {
        background-color: white;
        background-size: cover;
        color: black;
        /* Default link color */
      }

      .ref-card a:hover {
        color: red;
        /* Change color on hover */
      }

      .seven-card {
        background-image: url('img/official.png');
        background-size: cover;
        background-position: center;
        width: 100%;
        padding-bottom: 56.25%;
        /* 9:16 aspect ratio (16 / 9 * 100%) */
      }

      .five-card {
        background-image: url('img/fb.png');
        background-size: cover;
        background-position: center;
        width: 100%;
        padding-bottom: 56.25%;
        /* 9:16 aspect ratio (16 / 9 * 100%) */
      }

      .eight-card {
        background-image: url('img/ig.png');
        background-size: cover;
        background-position: center;
        width: 100%;
        padding-bottom: 56.25%;
        /* 9:16 aspect ratio (16 / 9 * 100%) */

      }
    </style>

    <!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.min.css" />
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  </head>

  <body class="bg-[#E8E8E7]">

    <?php include "user_sidebar_header.php"; ?>

    <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">

        <div class="row">
          <div class="col-md-4">
            <div class="card settled-card">
              <div class="card-body">
                <!-- Card content goes here -->
                <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                  Settled Cases
                </h5>
                <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                  <?php
                  if ($selected_month && $selected_month !== date('F Y')) {
                    echo $s_totalSet; // Display the selected month's value
                  } else {
                    echo $totalSettledCount;
                  }
                  ?>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card unsettled-card">
              <div class="card-body">
                <!-- Card content goes here -->
                <h5
                  class="card-title mb-9 fw-semibold"
                  style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
                  Unsettled Cases
                </h5>

                <p
                  class="mb-9 fw-semibold"
                  style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
                  <?php
                  if ($selected_month && $selected_month !== date('F Y')) {
                    echo $s_totalUnset; // Display the selected month's value
                  } else {
                    echo $totalUnsetCount;
                  }
                  ?>
                </p>

              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card pending-card">
              <div class="card-body">
                <!-- Card content goes here -->
                <h5
                  class="card-title mb-9 fw-semibold"
                  style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                  Pending Cases
                </h5>

                <p
                  class="mb-9 fw-semibold"
                  style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                  <?php if ($selected_month && $selected_month !== date('F Y')) {
                    echo $s_pending; // Display the selected month's value
                  } else {
                    echo $pendingCount;
                  } ?>
                </p>

              </div>
            </div>
          </div>
        </div>
        <!-- New row for the additional card -->
        <div class="row">
          <div class="col-md-6">
            <div class="card summary-card">
              <div class="card-body">
                <!-- Card content goes here -->
                <h5 class="card-title mb-9 fw-semibold" style="color: black;">Summary</h5>
                <canvas id="complaintsChart" width="800" height="400"></canvas>
                <script>
                  var ctx = document.getElementById('complaintsChart').getContext('2d');
                  var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                      labels: ['January', 'February', 'March', 'April', 'May', 'June'],
                      datasets: [{
                        label: 'Number of Complaints',
                        data: [12, 19, 3, 5, 2, 3],
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
            </div>
          </div>
          <div class="col-md-6">
            <div class="card nature-card">
              <div class="card-body">
                <h5 class="card-title mb-9 fw-semibold" style="color: black;">Nature of Cases</h5>
                <canvas id="natureOfCasesChart" width="800" height="400"></canvas>
                <script>
                  // Example data for demonstration
                  var natureOfCases = {
                    "Civil": 10,
                    "Criminal": 5,
                    "Others": 3,
                    // Add more data as needed
                  };

                  var ctx = document.getElementById('natureOfCasesChart').getContext('2d');
                  var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                      labels: Object.keys(natureOfCases),
                      datasets: [{
                        label: 'Count',
                        data: Object.values(natureOfCases),
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
                            display: true,
                          }
                        }
                      }
                    }
                  });
                </script>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-8">
            <div class="card ref-card">
              <div class="card-body">
                <!-- Card content goes here -->
                <h5 class="card-title mb-9 fw-semibold" style="color: black;">References</h5>
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
              </div>
            </div>
          </div>
          <div class="col-md-4">

            <a href="https://www.dilg.gov.ph/" class="card seven-card overflow-hidden" style="text-decoration: none;" target="_blank"></a>

            <a href="https://www.facebook.com/dilglaguna.clustera.7" class="card five-card overflow-hidden" style="text-decoration: none;" target="_blank"></a>

            <a href="https://www.instagram.com/dilgr4a/" class="card eight-card overflow-hidden" style="text-decoration: none;" target="_blank"></a>
          </div>
        </div>
      </div>
      </div>
    </div>
  </body>

  </html>