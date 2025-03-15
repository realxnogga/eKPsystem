<?php
session_start();

include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$currentMunicipalityID = $_SESSION['municipality_id'] ?? null;

function getVerifiedUsers($conn, $currentMunicipalityID)
{
  $verifiedUsersQuery = "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.contact_number, u.user_type, u.barangay_id, b.barangay_name 
  FROM users u 
  LEFT JOIN barangays b ON u.barangay_id = b.id 
  WHERE u.verified = 1 
  AND u.municipality_id = ?";
  $verifiedUsersStatement = $conn->prepare($verifiedUsersQuery);
  $verifiedUsersStatement->execute([$currentMunicipalityID]);
  return $verifiedUsersStatement->fetchAll(PDO::FETCH_ASSOC);
}

function searchVerifiedUserData($conn, $currentMunicipalityID, $searchTerm) {
  $searchQuery = "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.contact_number, u.barangay_id, u.user_type, b.barangay_name, u.verified 
                  FROM users u 
                  LEFT JOIN barangays b ON u.barangay_id = b.id 
                  WHERE u.verified = 1 AND u.municipality_id = ? AND u.user_type = 'user' 
                  AND (u.username LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR u.contact_number LIKE ? OR b.barangay_name LIKE ?)
                  ORDER BY b.barangay_name";
  
  // Prepare the search term for the query
  $searchTerm = "%" . $searchTerm . "%";
  
  $searchStatement = $conn->prepare($searchQuery);
  if ($searchStatement->execute([$currentMunicipalityID, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm])) {
      return $searchStatement->fetchAll(PDO::FETCH_ASSOC);
  }
  return [];
}

function unverifyFunc($conn, $userId) {
  $unverifyQuery = "UPDATE users SET verified = 0 WHERE id = ?";
  $unverifyStatement = $conn->prepare($unverifyQuery);
  $unverifyStatement->execute([$userId]);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $action = $_POST["action"] ?? null;
  $userId = $_POST["user_id"] ?? null;

  if ($action === "unverify") {
    $unverifyQuery = "UPDATE users SET verified = 0 WHERE id = ?";
    $unverifyStatement = $conn->prepare($unverifyQuery);
    

    if ($unverifyStatement->execute([$userId])) {
      $verifiedUsers = getVerifiedUsers($conn, $currentMunicipalityID);
    }
  }

  if (isset($_POST['inputField'])) {

    // check if submitted input has value or not. save value to session if it has, delete if not
    if (!empty($_POST['inputField'])) {
      $_SESSION['verifiedBarangay'] = $_POST['inputField'];
    } else {
      $_SESSION['verifiedBarangay'] = null;
    }

    // check if session has value. call fetchMatchingRows function if it has, call getComplaintData if not
    if (isset($_SESSION['verifiedBarangay'])) {
      $verifiedUsers = searchVerifiedUserData($conn, $currentMunicipalityID, $_SESSION['verifiedBarangay']);
    } else {
      $verifiedUsers = getVerifiedUsers($conn, $currentMunicipalityID);
    }
  }


} else {
  // run when page load
  if (isset($_SESSION['verifiedBarangay'])) {
      $verifiedUsers = searchVerifiedUserData($conn, $currentMunicipalityID, $_SESSION['verifiedBarangay']);
    } else {
      $verifiedUsers = getVerifiedUsers($conn, $currentMunicipalityID);
    }
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secretaries Corner</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <link rel="stylesheet" href="hide_show_icon.css">
  <style>
    table {
      width: 100%;
      table-layout: fixed;
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
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
            <div>
              <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>
          <br>
          <h5 class="card-title mb-9 fw-semibold">Secretaries Corner</h5>
          <hr>
          <b>
            <br>

            <form id="myForm" method="POST" action="" class="w-full">
              <input class="form-control w-full" type="text" id="inputField" name="inputField" placeholder="search" value="<?php echo isset($_SESSION['verifiedBarangay']) ? $_SESSION['verifiedBarangay'] : ''; ?>">
            </form>

            <!-- <input type="search" id="searchBarangayButton" onkeyup="searchTable()" class="form-control" placeholder="search"> -->
            <br>
            <table id="barangayTable" class="table table-striped">
              <thead>
                <tr>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Username</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Name</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Email</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Contact No#</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Barangay Name</th>
                  <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($verifiedUsers as $user): ?>
                  <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['contact_number']) ?></td>
                    <td><?= htmlspecialchars($user['barangay_name'] ?? '(NA)assessor') ?></td>
                    <td>

                      <form method="post" class="mb-2" action="<?= $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button class="bg-red-500 hover:bg-red-400 w-fit px-3 py-2 ml-2 rounded-md text-white" type="submit" name="action" value="unverify">
                          <span>
                            <i class="ti ti-lock text-lg show-icon"></i>
                            <p class="whitespace-nowrap hide-icon hidden">Lock</p>
                          </span>
                        </button>
                      </form>

                      <form method="post" action="admin_viewreport.php">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="barangay_id" value="<?= $user['barangay_id'] ?>">
                        <button class="bg-blue-500 hover:bg-blue-400 px-3 py-2 ml-2 rounded-md text-white <?= $user['user_type'] === 'assessor' ? 'disabled:opacity-50 disabled:cursor-not-allowed' : '' ?>"
                          type="submit"
                          name="viewreport"
                          formaction="admin_viewreport.php?user_id=<?= $user['id'] ?>"
                          <?= $user['user_type'] === 'assessor' ? 'disabled' : '' ?>>
                          <span>
                            <i class="ti ti-report-search text-lg show-icon"></i>
                            <p class="whitespace-nowrap hide-icon hidden">View Report</p>
                          </span>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </b>
        </div>
      </div>
    </div>
  </div>

  <!-- <script>
    function searchTable() {
      let input = document.getElementById('searchBarangayButton');
      let filter = input.value.toLowerCase();
      let table = document.getElementById('barangayTable');
      let tr = table.getElementsByTagName('tr');

      for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let rowText = '';

        for (let j = 0; j < td.length - 1; j++) {
          rowText += td[j].textContent || td[j].innerText;
        }

        tr[i].style.display = rowText.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
      }
    }
  </script> -->

</body>

</html>