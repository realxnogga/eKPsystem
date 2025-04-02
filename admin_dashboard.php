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

function searchVerifiedUserData($conn, $currentMunicipalityID, $searchTerm)
{
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

function unverifyFunc($conn, $userId)
{
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

<!-- filepath: c:\xampp\htdocs\eKPsystem\admin_dashboard.php -->
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secretaries Corner</title>
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
    <div class="rounded-lg mt-16 bg-white shadow-md p-6">

      <div class="flex items-center mb-6">
        <img src="img/cluster.png" alt="Logo" class="w-24 h-24 mr-4">
        <div>
          <h5 class="text-lg font-semibold">Department of the Interior and Local Government</h5>
        </div>
      </div>

      <h5 class="text-lg font-semibold mb-4">Secretaries Corner</h5>
      <hr class="mb-6">

      <form id="myForm" method="POST" action="" class="w-full mb-6">
        <input class="w-full p-2 border border-gray-300" type="text" id="inputField" name="inputField" placeholder="Search" value="<?php echo isset($_SESSION['verifiedBarangay']) ? $_SESSION['verifiedBarangay'] : ''; ?>">
      </form>

      <div class="overflow-x-auto">
        <table id="barangayTable" class="border table-auto lg:table-fixed w-full bg-white rounded-lg">
          <thead>
            <tr class="bg-gray-200">
              <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Username</th>
              <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Name</th>
              <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Email</th>
              <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Contact No#</th>
              <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Barangay Name</th>
              <th class="whitespace-nowrap p-3 text-sm font-semibold text-gray-700 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($verifiedUsers as $user): ?>
              <tr class="text-center">
                <td class="px-4 py-2 break-all"><?= htmlspecialchars($user['username']) ?></td>
                <td class="px-4 py-2 break-all"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                <td class="px-4 py-2 break-all"><?= htmlspecialchars($user['email']) ?></td>
                <td class="px-4 py-2 break-all"><?= htmlspecialchars($user['contact_number']) ?></td>
                <td class="px-4 py-2 break-all"><?= htmlspecialchars($user['barangay_name'] ?? '(NA)assessor') ?></td>

                <td class="py-4">

                  <form method="post" class="mb-2" action="<?= $_SERVER['PHP_SELF'] ?>">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button class="bg-red-500 hover:bg-red-400 w-fit px-3 py-1 ml-2 rounded-md text-white" type="submit" name="action" value="unverify">
                      <span>
                        <i class="ti ti-lock text-sm show-icon"></i>
                        <p class="whitespace-nowrap text-sm hide-icon hidden">Lock</p>
                      </span>
                    </button>
                  </form>

                  <form method="post" action="admin_viewreport.php">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="barangay_id" value="<?= $user['barangay_id'] ?>">
                    <button class="bg-blue-500 hover:bg-blue-400 px-3 py-1 ml-2 rounded-md text-white <?= $user['user_type'] === 'assessor' ? 'disabled:opacity-50 disabled:cursor-not-allowed' : '' ?>"
                      type="submit"
                      name="viewreport"
                      formaction="admin_viewreport.php?user_id=<?= $user['id'] ?>"
                      <?= $user['user_type'] === 'assessor' ? 'disabled' : '' ?>>
                      <span>
                        <i class="ti ti-report-search text-sm show-icon"></i>
                        <p class="whitespace-nowrap text-sm hide-icon hidden">View Report</p>
                      </span>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>

</html>