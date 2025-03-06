<?php
session_start();

include 'connection.php';
//include 'admin-navigation.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

include 'admin_func.php';


$currentMunicipalityID = $_SESSION['municipality_id'] ?? null;

$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$accountRequestsQuery = "SELECT u.id, u.username, u.first_name, u.last_name, u.assessor_type, u.email, u.contact_number, b.barangay_name, u.verified 
                       FROM users u 
                       LEFT JOIN barangays b ON u.barangay_id = b.id 
                       WHERE u.verified = 0 
                       AND u.municipality_id = ? 
                       AND u.user_type = 'assessor'
                       AND (u.first_name LIKE ? OR u.last_name LIKE ? OR b.barangay_name LIKE ?)
                       ORDER BY b.barangay_name"; // Order by barangay_name for readability

$search_query_like = '%' . $search_query . '%';
$accountRequestsStatement = $conn->prepare($accountRequestsQuery);
$accountRequestsStatement->execute([$currentMunicipalityID, $search_query_like, $search_query_like, $search_query_like]);
$accountRequests = $accountRequestsStatement->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assessor Account Requests</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <link rel="stylesheet" href="hide_show_icon.css">

  <style>
    table {
      width: 100%;
      table-layout: fixed;
      /* Ensures all columns have equal width */
    }

    th,
    td {

      padding: 8px;
      text-align: center;
    }
  </style>
</head>

<body class="bg-[#E8E8E7]">

  <?php include "admin_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <!--  Row 1 -->
      <div class="card">
        <div class="card-body">

          <div class="d-flex align-items-center">
            <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
            <div>
              <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>
          <br>

          <h5 class="card-title mb-9 fw-semibold">Assessor Account Requests</h5>
          <hr>
          <b>
            <br>

            <input onkeyup="searchTable();" type="search" id="searchAssessorRequestButton" class="form-control" placeholder="search">

            <br>

            <!-- <form method="GET" action="" class="searchInput" style="display: flex; align-items: center;">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Search by Name or Barangay Name" class="searchInput" required>
                            <input type="submit" class="bg-gray-800 hover:bg-gray-700 px-3 py-2 ml-2 rounded-md text-white" value="Search" class="refresh-button">
                        </form> -->


            <?php

            echo '<div id="account-requests" style="display: block;">';

            if (!empty($accountRequests)) {
              echo '<table id="assessorReqTable" class="table table-striped">';
              echo '<thead>
                            <tr>
                                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Username</th>
                                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Name</th>
                                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Department</th>
                                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Email</th>
                                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Contact No#</th>
                                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Actions</th>
                            </tr>
                          </thead>';
              echo '<tbody>';

              foreach ($accountRequests as $user) {
                echo '<tr>';
                echo '<td>' . $user['username'] . '</td>';
                echo '<td>' . $user['last_name'] . ' ' . $user['first_name'] . '</td>';
                echo '<td>' . $user['assessor_type'] . '</td>';
                echo '<td>' . $user['email'] . '</td>';
                echo '<td>' . $user['contact_number'] . '</td>';

                echo '<td class="">';

                if (!isset($user['verified']) || !$user['verified']) {
                  echo '<form class="flex items-center flex-col" method="post" action="' . $_SERVER['PHP_SELF'] . '">';

                  echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';

                  echo '<button class="btn btn-success m-1 bg-green-500" type="submit" name="action" value="verify">
                    <span>
                      <i class="ti ti-lock-open-2 text-lg show-icon"></i>
                      <p class="hide-icon hidden">Unlock</p>
                  </span> 
                  </button>';

                  echo '<button class="btn btn-danger m-1 bg-red-500" type="submit" name="action" value="deny">
                      <span>
                      <i class="ti ti-circle-off text-lg show-icon"></i>
                      <p class="hide-icon hidden">Deny</p>
                   </span> 
                                    </button>';
                  echo '</form>';
                }

                echo '<button class="w-fit btn btn-light m-1 bg-gray-300" onclick="window.location.href=\'admin_manage_ltia_acc_req.php?user_id=' . $user['id'] . '\'">
                <span>
                 <i class="ti ti-user-cog text-lg show-icon"></i>
                 <p class="hide-icon hidden">Manage</p>
                </span>  
                </button>';


                echo '</td>';
                echo '</tr>';
              }

              echo '</tbody>';
              echo '</table>';
            } else {
              echo '<p class="text-center text-lg">There are no account requests as of the moment.</p>';
            }

            echo '</div>';
            ?>

          </b>

        </div>
      </div>
    </div>
  </div>

  <script>
   function searchTable() {
    
    let input = document.getElementById('searchAssessorRequestButton');
    let filter = input.value.toLowerCase();
    let table = document.getElementById('assessorReqTable');
    let tr = table.getElementsByTagName('tr');

    // Loop through all table rows, excluding the header
    for (let i = 1; i < tr.length; i++) {
      let td = tr[i].getElementsByTagName('td');
      let rowText = '';

      // Concatenate all text content from each cell
      for (let j = 0; j < td.length - 1; j++) {
        rowText += td[j].textContent || td[j].innerText;
      }

      // If the row matches the search term, show it, otherwise hide it
      if (rowText.toLowerCase().indexOf(filter) > -1) {
        tr[i].style.display = '';
      } else {
        tr[i].style.display = 'none';
      }
    }
  }
  </script>

</body>

</html>