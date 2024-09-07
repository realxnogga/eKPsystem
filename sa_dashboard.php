<?php
session_start();
include 'connection.php';

include 'functions.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}

// Fetch the data from your database and assign it to $user
// Replace the following lines with your actual database query
$stmt = $conn->prepare("SELECT u.id, u.municipality_id, u.first_name, u.last_name, u.contact_number, u.email, m.municipality_name FROM users u
                        INNER JOIN municipalities m ON u.municipality_id = m.id
                        WHERE u.user_type = 'admin'");
$stmt->execute();
$user = $stmt->fetchAll(PDO::FETCH_ASSOC);

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



$selectedMonth = ''; // Initialize the variable

$searchedMunicipality = '';


?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <!-- Add this script tag to include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


  <style>
    .card {
      box-shadow: 0 0 0.3cm rgba(0, 0, 0, 0.2);
      border-radius: 15px;
      margin-bottom: 20px;
    }

    .card-text-center {
      text-align: center;
    }

    .custom-card {
      width: 100%;
    }

    .alaminos-card {
      background-color: red;
    }

    .bay-card {
      background-color: blue;
    }

    .biñan-card {
      background-color: yellow;
    }

    .cabuyao-card {
      background-color: pink;
    }

    .calamba-card {
      background-color: purple;
    }

    .calauan-card {
      background-color: orange;
    }

    .baños-card {
      background-color: green;
    }

    .pablo-card {
      background-color: maroon;
    }

    .pedro-card {
      background-color: lightblue;
    }

    .rosa-card {
      background-color: grey;
    }
  </style>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <div class="row">
        <div class="col-md-4">
          <div class="card alaminos-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                Alaminos
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
          <div class="card bay-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
                Bay</h5>
              <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
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
          <div class="card biñan-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                Biñan
              </h5>
              <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
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
      <div class="row">
        <div class="col-md-4">
          <div class="card cabuyao-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                Cabuyao
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
          <div class="card calamba-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
                Calamba</h5>
              <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
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
          <div class="card calauan-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                Calauan
              </h5>
              <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
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
      <div class="row">
        <div class="col-md-4">
          <div class="card baños-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                Los Baños
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
          <div class="card pablo-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
                San Pablo</h5>
              <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
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
          <div class="card pedro-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
                San Pedro
              </h5>
              <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5);">
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
      <div class="row">

        <div class="col-md-4">
          <div class="card rosa-card">
            <div class="card-body">
              <!-- Card content goes here -->
              <h5 class="card-title mb-9 fw-semibold" style="color: white; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
                Santa Rosa</h5>
              <p class="mb-9 fw-semibold" style="color: white; font-size: 40px; text-shadow: 0 0 0.2cm rgba(0, 0, 0, 0.5)">
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

      </div>

    </div>

  </div>

</body>

</html>