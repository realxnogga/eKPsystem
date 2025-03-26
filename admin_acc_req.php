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

function getBarangayData($conn, $currentMunicipalityID,)
{
  $accountRequestsQuery = "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.contact_number, b.barangay_name, u.verified 
  FROM users u 
  LEFT JOIN barangays b ON u.barangay_id = b.id 
  WHERE u.verified = 0 
  AND u.municipality_id = ? 
  AND u.user_type = 'user'
  ORDER BY b.barangay_name"; // Order by barangay_name for readability

  $accountRequestsStatement = $conn->prepare($accountRequestsQuery);

  if ($accountRequestsStatement->execute([$currentMunicipalityID])) {
    return $accountRequestsStatement->fetchAll(PDO::FETCH_ASSOC);
  }
}

function searchBarangayData($conn, $currentMunicipalityID, $searchTerm)
{
  $searchQuery = "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.contact_number, b.barangay_name, u.verified 
                  FROM users u 
                  LEFT JOIN barangays b ON u.barangay_id = b.id 
                  WHERE u.verified = 0 AND u.municipality_id = ? AND u.user_type = 'user' 
                  AND (b.barangay_name LIKE ? OR u.username LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR u.contact_number LIKE ? )
                  ORDER BY b.barangay_name";

  // Prepare the search term for the query
  $searchTerm = "%" . $searchTerm . "%";

  $searchStatement = $conn->prepare($searchQuery);
  if ($searchStatement->execute([$currentMunicipalityID, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm])) {
    return $searchStatement->fetchAll(PDO::FETCH_ASSOC);
  }
  return [];
}




if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['inputField'])) {

    // check if submitted input has value or not. save value to session if it has, delete if not
    if (!empty($_POST['inputField'])) {
      $_SESSION['requestingBarangay'] = $_POST['inputField'];
    } else {
      $_SESSION['requestingBarangay'] = null;
    }

    // check if session has value. call fetchMatchingRows function if it has, call getComplaintData if not
    if (isset($_SESSION['requestingBarangay'])) {
      $accountRequests = searchBarangayData($conn, $currentMunicipalityID, $_SESSION['requestingBarangay']);
    } else {
      $accountRequests = getBarangayData($conn, $currentMunicipalityID);
    }
  }
} else {
  // run when page load. call fetchMatchingRows function if session has value, call getComplaintData if not
  if (isset($_SESSION['requestingBarangay'])) {
    $accountRequests = searchBarangayData($conn, $currentMunicipalityID, $_SESSION['requestingBarangay']);
  } else {
    $accountRequests = getBarangayData($conn, $currentMunicipalityID);
  }
}


?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Account Requests</title>

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

          <h5 class="card-title mb-9 fw-semibold">Account Requests</h5>
          <hr>
          <b>
            <br>

            <!-- <input onkeyup="searchTable();" type="search" id="searchBarangayRequestButton" class="form-control" placeholder="search"> -->

            <form id="myForm" method="POST" action="" class="w-full">
              <input class="form-control w-full" type="text" id="inputField" name="inputField" placeholder="search" value="<?php echo isset($_SESSION['requestingBarangay']) ? $_SESSION['requestingBarangay'] : ''; ?>">
            </form>

            <br>

            <?php

            echo '<div id="account-requests" style="display: block;">';

            if (!empty($accountRequests)) {
              echo '<table id="brgyReqTable" class="table table-striped">';
              echo '<thead>
              <tr>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Username</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Secretary</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Email</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Contact No.</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Barangay</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Actions</th>
              </tr>
            </thead>';
              echo '<tbody>';

              foreach ($accountRequests as $user) {
                echo '<tr>';
                echo '<td>' . $user['username'] . '</td>';
                echo '<td>' . $user['first_name'] . ' ' . $user['last_name'] . '</td>';
                echo '<td>' . $user['email'] . '</td>';
                echo '<td>' . $user['contact_number'] . '</td>';
                echo '<td>' . $user['barangay_name'] . '</td>';

                echo '<td class="flex items-center flex-col">';

                if (!isset($user['verified']) || !$user['verified']) {
                  echo '<form class="flex items-center flex-col" method="post" action="' . $_SERVER['PHP_SELF'] . '">';

                  echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';

                  echo '<button class="w-fit btn btn-success m-1 bg-green-500" type="submit" name="action" value="verify">
                    <span>
                      <i class="ti ti-lock-open-2 text-lg show-icon"></i>
                      <p class="hide-icon hidden">Unlock</p>
                  </span>   
                  </button>';

                  echo '<button class="w-fit btn btn-danger m-1 bg-red-500" type="submit" name="action" value="deny">    
                   <span>
                      <i class="ti ti-circle-off text-lg show-icon"></i>
                      <p class="hide-icon hidden">Deny</p>
                   </span>  
                  </button>';

                  echo '</form>';
                }

                echo '<button class="w-fit btn btn-light m-1 bg-gray-300" onclick="window.location.href=\'admin_manage_acc_req.php?user_id=' . $user['id'] . '\'">
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

      let input = document.getElementById('searchBarangayRequestButton');
      let filter = input.value.toLowerCase();
      let table = document.getElementById('brgyReqTable');
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