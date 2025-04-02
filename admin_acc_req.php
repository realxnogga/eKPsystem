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

<!-- filepath: c:\xampp\htdocs\eKPsystem\admin_acc_req.php -->
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
 
          <h5 class="text-lg font-semibold mb-4">Account Request</h5>
          <hr class="mb-6">

            <form id="myForm" method="POST" action="" class="w-full">
              <input class="form-control w-full border border-gray-300 p-2" type="text" id="inputField" name="inputField" placeholder="Search" value="<?php echo isset($_SESSION['requestingBarangay']) ? $_SESSION['requestingBarangay'] : ''; ?>">
            </form>

            <br>

            <?php

            echo '<div id="account-requests" class="block">';

            if (!empty($accountRequests)) {
              echo '<div class="overflow-x-auto">';
              echo '<table id="brgyReqTable" class="border table-auto lg:table-fixed w-full bg-white rounded-lg"';
              echo '<thead>
              <tr class="bg-gray-200">
                  <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Username</th>
                  <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Secretary</th>
                  <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Email</th>
                  <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Contact No.</th>
                  <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Barangay</th>
                  <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Actions</th>
              </tr>
            </thead>';
              echo '<tbody>';

              foreach ($accountRequests as $user) {
                echo '<tr class="text-center">';
                echo '<td class="break-all px-4 py-2">' . $user['username'] . '</td>';
                echo '<td class="break-all px-4 py-2">' . $user['first_name'] . ' ' . $user['last_name'] . '</td>';
                echo '<td class="break-all px-4 py-2">' . $user['email'] . '</td>';
                echo '<td class="break-all px-4 py-2">' . $user['contact_number'] . '</td>';
                echo '<td class="break-all px-4 py-2">' . $user['barangay_name'] . '</td>';

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
                      <button class="bg-gray-500 hover:bg-gray-400 w-fit px-3 py-1 rounded-md text-white" type="button" onclick="window.location.href='admin_manage_acc_req.php?user_id={$user['id']}'">
                          <span class="flex items-center space-x-2">
                              <i class="ti ti-user-cog text-sm"></i>
                              <p class="hide-icon hidden text-sm">Manage</p>
                          </span>
                      </button>
                  </form>
                  HTML;
                }

                echo '</td>';
                echo '</tr>';
              }

              echo '</tbody>';
              echo '</table>';
              echo '</div>';
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

</body>

</html>