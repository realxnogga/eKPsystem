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
  $accountRequestsQuery = "SELECT u.id, u.username, u.first_name, u.last_name,  u.assessor_type, u.email, u.contact_number, b.barangay_name, u.verified 
  FROM users u 
  LEFT JOIN barangays b ON u.barangay_id = b.id 
  WHERE u.verified = 0 
  AND u.municipality_id = ? 
  AND u.user_type = 'assessor'
  ORDER BY b.barangay_name"; // Order by barangay_name for readability

  $accountRequestsStatement = $conn->prepare($accountRequestsQuery);

  if ($accountRequestsStatement->execute([$currentMunicipalityID])) {
    return $accountRequestsStatement->fetchAll(PDO::FETCH_ASSOC);
  }
}

function searchBarangayData($conn, $currentMunicipalityID, $searchTerm)
{
  $searchQuery = "SELECT u.id, u.username, u.first_name, u.last_name,  u.assessor_type, u.email, u.contact_number, b.barangay_name, u.verified 
                  FROM users u 
                  LEFT JOIN barangays b ON u.barangay_id = b.id 
                  WHERE u.verified = 0 AND u.municipality_id = ? AND u.user_type = 'assessor' 
                  AND (b.barangay_name LIKE ? OR u.username LIKE ? OR u.assessor_type LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR u.contact_number LIKE ? )
                  ORDER BY b.barangay_name";

  // Prepare the search term for the query
  $searchTerm = "%" . $searchTerm . "%";

  $searchStatement = $conn->prepare($searchQuery);
  if ($searchStatement->execute([$currentMunicipalityID, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm])) {
    return $searchStatement->fetchAll(PDO::FETCH_ASSOC);
  }
  return [];
}




if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['inputField'])) {

    // check if submitted input has value or not. save value to session if it has, delete if not
    if (!empty($_POST['inputField'])) {
      $_SESSION['requestingAssessor'] = $_POST['inputField'];
    } else {
      $_SESSION['requestingAssessor'] = null;
    }

    // check if session has value. call fetchMatchingRows function if it has, call getComplaintData if not
    if (isset($_SESSION['requestingAssessor'])) {
      $accountRequests = searchBarangayData($conn, $currentMunicipalityID, $_SESSION['requestingAssessor']);
    } else {
      $accountRequests = getBarangayData($conn, $currentMunicipalityID);
    }
  }
} else {
  // run when page load. call fetchMatchingRows function if session has value, call getComplaintData if not
  if (isset($_SESSION['requestingAssessor'])) {
    $accountRequests = searchBarangayData($conn, $currentMunicipalityID, $_SESSION['requestingAssessor']);
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
  <title>Assessor Account Requests</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script src="node_modules/jquery/dist/jquery.min.js"></script>

  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />

  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">

  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

  <link href="output.css" rel="stylesheet">

  <link rel="stylesheet" href="hide_show_icon.css">

</head>

<body class="bg-gray-200">

  <?php include "admin_sidebar_header.php"; ?>

  <div class="p-4 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16 bg-white shadow-md">

      <!-- Row 1 -->
      <div class="bg-white shadow-md rounded-lg">
        <div class="p-6">

          <div class="flex items-center mb-6">
            <img src="img/cluster.png" alt="Logo" class="w-24 h-24 mr-4">
            <div>
              <h5 class="text-lg font-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>


          <h5 class="text-lg font-semibold mb-4">Assessor Account Request</h5>
          <hr class="mb-6">

          <form id="myForm" method="POST" action="" class="w-full">
            <input class="form-control w-full border border-gray-300 p-2" type="text" id="inputField" name="inputField" placeholder="Search" value="<?php echo isset($_SESSION['requestingAssessor']) ? $_SESSION['requestingAssessor'] : ''; ?>">
          </form>

          <!-- <input onkeyup="searchTable();" type="search" id="searchAssessorRequestButton" class="form-control" placeholder="search"> -->

          <br>

          <!-- <form method="GET" action="" class="searchInput" style="display: flex; align-items: center;">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Search by Name or Barangay Name" class="searchInput" required>
                            <input type="submit" class="bg-gray-800 hover:bg-gray-700 px-3 py-2 ml-2 rounded-md text-white" value="Search" class="refresh-button">
                        </form> -->


          <?php

          echo '<div id="account-requests" class="block">';

          if (!empty($accountRequests)) {
            echo '<div class="overflow-x-auto">';
            echo '<table id="brgyReqTable" class="border table-auto lg:table-fixed w-full bg-white rounded-lg"';
            echo '<thead>
                            <tr class="bg-gray-200">
                                <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Username</th>
                                <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Name</th>
                                <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Department</th>
                                <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Email</th>
                                <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Contact No#</th>
                                <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Actions</th>
                            </tr>
                          </thead>';
            echo '<tbody>';

            foreach ($accountRequests as $user) {
              echo '<tr class="text-center">';
              echo '<td class="break-all px-4 py-2">' . $user['username'] . '</td>';
              echo '<td class="break-all px-4 py-2">' . $user['first_name'] . ' ' . $user['last_name'] . '</td>';
              echo '<td class="break-all px-4 py-2">' . $user['assessor_type'] . '</td>';
              echo '<td class="break-all px-4 py-2">' . $user['email'] . '</td>';
              echo '<td class="break-all px-4 py-2">' . $user['contact_number'] . '</td>';

              echo '<td class="py-4">';

              if (!isset($user['verified']) || !$user['verified']) {
                echo <<<HTML
                <form class="flex flex-col items-center space-y-2" method="post" action="{$_SERVER['PHP_SELF']}">
                    <input type="hidden" name="user_id" value="{$user['id']}">
                    <button class="bg-green-500 hover:bg-green-400 w-fit px-3 py-1 rounded-md text-white" type="submit" name="action" value="verify">
                        <span class="flex items-center space-x-2">
                        <i class="ti ti-lock-open-2 text-sm"></i>
                              <p class="hide-icon hidden text-sm">Unlock</p>
                        </span>
                    </button>
                    <button class="bg-red-500 hover:bg-red-400 w-fit px-3 py-1 rounded-md text-white" type="submit" name="action" value="deny">
                        <span class="flex items-center space-x-2">
                        <i class="ti ti-circle-off text-sm"></i>
                              <p class="hide-icon hidden text-sm">Deny</p>
                        </span>
                    </button>
                    <button class="bg-gray-500 hover:bg-gray-400 w-fit px-3 py-1 rounded-md text-white" type="button" onclick="window.location.href='admin_manage_ltia_acc_req.php?user_id={$user['id']}'">
                        <span class="flex items-center space-x-2">
                        <i class="ti ti-user-cog text-sm"></i>
                        <p class="hide-icon hidden text-sm">Manage</p>
                        </span>
                    </button>
                </form>
                HTML;

                // echo '<form class="flex items-center flex-col" method="post" action="' . $_SERVER['PHP_SELF'] . '">';

                // echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';

                // echo '<button class="btn btn-success m-1 bg-green-500" type="submit" name="action" value="verify">
                //     <span>
                //       <i class="ti ti-lock-open-2 text-lg show-icon"></i>
                //       <p class="hide-icon hidden">Unlock</p>
                //   </span> 
                //   </button>';

                // echo '<button class="btn btn-danger m-1 bg-red-500" type="submit" name="action" value="deny">
                //       <span>
                //       <i class="ti ti-circle-off text-lg show-icon"></i>
                //       <p class="hide-icon hidden">Deny</p>
                //    </span> 
                //                     </button>';
                // echo '</form>';
              }

              // echo '<button class="w-fit btn btn-light m-1 bg-gray-300" onclick="window.location.href=\'admin_manage_ltia_acc_req.php?user_id=' . $user['id'] . '\'">
              //   <span>
              //    <i class="ti ti-user-cog text-lg show-icon"></i>
              //    <p class="hide-icon hidden">Manage</p>
              //   </span>  
              //   </button>';


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