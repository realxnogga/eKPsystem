<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'];

// Function to get the ordinal suffix
function getOrdinalSuffix($number)
{
  if ($number % 100 >= 11 && $number % 100 <= 13) {
    return 'th';
  }
  switch ($number % 10) {
    case 1:
      return 'st';
    case 2:
      return 'nd';
    case 3:
      return 'rd';
    default:
      return 'th';
  }
}
// ---------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['inputField'])) {

    // check if submitted input has value or not. save value to session if it has, delete if not
    if (!empty($_POST['inputField'])) {
      $_SESSION['searchvalue'] = $_POST['inputField'];
    } else {
      $_SESSION['searchvalue'] = null;
    }

    // check if session has value. call fetchMatchingRows function if it has, call getComplaintData if not
    if (isset($_SESSION['searchvalue'])) {
      $selectedYear = isset($_SESSION['cy_complaintyear']) ? $_SESSION['cy_complaintyear'] : date('Y');
      $result = fetchMatchingRows($conn, $userID, $_SESSION['searchvalue'], $selectedYear);
    } else {
      $selectedYear = isset($_SESSION['cy_complaintyear']) ? $_SESSION['cy_complaintyear'] : date('Y');
      $result = getComplaintData($conn, $userID, "complaint_created_date", null, $selectedYear);
    }
  }

  if (isset($_POST['seeUpdateRecently'])) {
    $selectedYear = isset($_SESSION['cy_complaintyear']) ? $_SESSION['cy_complaintyear'] : date('Y');
    $result = getComplaintData($conn, $userID, "complaint_updated_date", true, $selectedYear);
  }

  if (isset($_POST['yearfilter'])) {
    $selectedYear = $_POST['yearfilter'];
    $_SESSION['cy_complaintyear'] = $selectedYear;
    $result = getComplaintData($conn, $userID, "complaint_created_date", null, $selectedYear);
  }
} else {

  // run when page load. call fetchMatchingRows function if session has value, call getComplaintData if not
  if (isset($_SESSION['searchvalue'])) {
    $selectedYear = isset($_SESSION['cy_complaintyear']) ? $_SESSION['cy_complaintyear'] : date('Y');
    $result = fetchMatchingRows($conn, $userID, $_SESSION['searchvalue'], $selectedYear);
  } else {
    $selectedYear = isset($_SESSION['cy_complaintyear']) ? $_SESSION['cy_complaintyear'] : date('Y');
    $result = getComplaintData($conn, $userID, "complaint_created_date", null, $selectedYear);
  }
}

function getComplaintData($conn, $userID, $whatCol, $condition, $whatYear)
{
  $query = "SELECT * FROM complaints WHERE UserID = :userID AND IsArchived = 0 AND YEAR(Mdate) = :whatYear";

  if (!is_null($condition)) $query .= " AND complaint_updated_date IS NOT NULL";

  $query .= " ORDER BY $whatCol DESC";

  $stmt = $conn->prepare($query);
  $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
  $stmt->bindParam(':whatYear', $whatYear, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt;
}

function fetchMatchingRows($conn, $userID, $searchText, $whatYear)
{
  $query = "SELECT * FROM complaints WHERE UserID = :userID AND IsArchived = 0 AND YEAR(Mdate) = :whatYear AND (CNum LIKE :searchText OR ForTitle LIKE :searchText OR CNames LIKE :searchText OR RspndtNames LIKE :searchText OR Mdate LIKE :searchText OR CMethod LIKE :searchText)";
  $stmt = $conn->prepare($query);
  $searchText = "%$searchText%";
  $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
  $stmt->bindParam(':searchText', $searchText, PDO::PARAM_STR);
  $stmt->bindParam(':whatYear', $whatYear, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Complaints</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

  <!-- tailwind cdn -->
  <link rel="stylesheet" href="output.css">

  <link rel="stylesheet" href="hide_show_icon.css">

</head>

<body class="sm:bg-gray-200 bg-white">

  <?php include "user_sidebar_header.php"; ?>
  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">

    <div class="mt-16">
      <div class="bg-white shadow-none rounded-0 sm:shadow-md sm:rounded-lg p-6">
        <details class="mb-6">
          <summary class="cursor-pointer text-lg font-semibold">Color Legend</summary>
          <div class="mt-4 flex flex-wrap justify-between items-center gap-4">
            <!-- Department Information -->
            <div class="flex items-center">
              <img src="img/cluster.png" alt="Department Logo" class="w-24 h-24 mr-4">
              <h5 class="text-lg font-semibold">Department of the Interior and Local Government</h5>
            </div>

            <!-- Color Legend Section -->
            <div class="p-4 border border-gray-300 rounded-lg w-full sm:w-auto">
              <h4 class="text-base font-bold mb-2">Color Legend</h4>
              <ul class="space-y-2">
                <li class="flex items-center">
                  <span class="w-5 h-5 bg-green-200 border border-gray-400 rounded-full mr-2" aria-hidden="true"></span>
                  <span class="text-sm text-gray-600">Settled</span>
                </li>
                <li class="flex items-center">
                  <span class="w-5 h-5 bg-yellow-100 border border-gray-400 rounded-full mr-2" aria-hidden="true"></span>
                  <span class="text-sm text-gray-600">Pending (10-14 days)</span>
                </li>
                <li class="flex items-center">
                  <span class="w-5 h-5 bg-red-300 border border-gray-400 rounded-full mr-2" aria-hidden="true"></span>
                  <span class="text-sm text-gray-600">Unsettled (15-30 days)</span>
                </li>
              </ul>
            </div>
          </div>
        </details>

        <div class="flex gap-4 mb-6">
          <form id="myForm" method="POST" action="" class="flex-grow">
            <input class="w-full p-2 border border-gray-300" type="text" id="inputField" name="inputField" placeholder="Search by No, Title, Complainants, Respondents, Date, Status..." value="<?php echo isset($_SESSION['searchvalue']) ? $_SESSION['searchvalue'] : ''; ?>">
          </form>

          <form method="POST" action="">
            <select title="View complaints by year" id="yearfilter" name="yearfilter" onchange="this.form.submit()" class="p-2 border border-gray-300">
              <?php
              $currentYear = date('Y');
              $startYear = $currentYear - 5;
              for ($year = $startYear; $year <= $currentYear; $year++) {
                echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
              }
              ?>
            </select>
          </form>

          <form method="POST" action="" class="flex items-center">
            <button title="View recently updated complaints" type="submit" name="seeUpdateRecently" class="p-2 bg-gray-200 hover:bg-gray-300">
              <i class="ti ti-clock-24 text-xl"></i>
            </button>
          </form>

          <button type="button" class="p-2 px-3 bg-blue-500 text-white rounded-md hover:bg-blue-600" onclick="location.href='user_add_complaint.php';">
            <i class="ti ti-plus text-lg show-icon"></i>
            <p style="white-space: nowrap;" class="hide-icon hidden">Add complaint</p>
          </button>
        </div>

        <div class="sm:max-h-[30rem] overflow-y-auto">
          <table class="table-auto lg:table-fixed  w-full bg-white rounded-lg">
            <thead class="bg-gray-200 sticky top-0">
              <tr>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">No.</th>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">Title</th>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">Complainants</th>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">Respondents</th>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">Date</th>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">Status</th>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">Hearing</th>
                <th class="p-3 text-sm font-semibold text-gray-700 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                <?php
                $isSettled = in_array($row['CMethod'], ['Mediation', 'Conciliation', 'Arbitration']);
                $dateAdded = strtotime($row['Mdate']);
                $currentDate = strtotime(date('Y-m-d'));
                $elapsedDays = ($currentDate - $dateAdded) / (60 * 60 * 24);
                $borderColor = $isSettled ? 'bg-green-200' : ($elapsedDays >= 10 && $elapsedDays <= 13 ? 'bg-yellow-100' : ($elapsedDays >= 14 && $elapsedDays <= 30 ? 'bg-red-300' : ''));
                ?>
                <tr class="<?= $borderColor; ?>">
                  <td class="p-3 text-sm text-gray-600 text-center"><?= str_pad($row['CNum'], 11, '0', STR_PAD_LEFT) ?></td>
                  <td class="p-3 text-sm text-gray-600 text-center"><?= $row['ForTitle'] ?></td>
                  <td class="p-3 text-sm text-gray-600 text-center"><?= $row['CNames'] ?></td>
                  <td class="p-3 text-sm text-gray-600 text-center"><?= $row['RspndtNames'] ?></td>
                  <td class="p-3 text-sm text-gray-600 text-center"><?= date('Y-m-d', strtotime($row['Mdate'])) ?></td>
                  <td class="p-3 text-sm text-gray-600 text-center"><?= $row['CMethod'] ?></td>
                  <?php
                  $complaintId = $row['id'];
                  $caseProgressQuery = "SELECT current_hearing FROM case_progress WHERE complaint_id = $complaintId";
                  $caseProgressResult = $conn->query($caseProgressQuery);
                  $caseProgressRow = $caseProgressResult->fetch(PDO::FETCH_ASSOC);
                  ?>
                  <td class="p-3 text-sm text-gray-600 text-center">
                    <?php if ($caseProgressRow): ?>
                      <?php $currentHearing = $caseProgressRow['current_hearing']; ?>
                      <?php if ($currentHearing === '0'): ?>
                        Not Set
                      <?php else: ?>
                        <?php $ordinalHearing = str_replace('th', getOrdinalSuffix((int)$currentHearing), $currentHearing); ?>
                        <?= $ordinalHearing ?> Hearing
                      <?php endif; ?>
                    <?php else: ?>
                      Not Set
                    <?php endif; ?>
                  </td>
                  <td class="p-3 text-sm text-gray-600 text-center">
                    <div class="flex justify-center gap-2">
                      <form action="user_edit_complaint.php" method="get">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="p-1 bg-blue-500 text-white rounded-sm hover:bg-blue-600" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                      </form>
                      <form action="archive_complaint.php" method="get">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="p-1 bg-red-500 text-white rounded-sm hover:bg-red-600" title="Archive">
                          <i class="fas fa-archive"></i>
                        </button>
                      </form>
                      <form action="user_manage_case.php" method="get">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="p-1 bg-yellow-400 text-white rounded-sm hover:bg-yellow-500" title="Manage">
                          <i class="fas fa-folder"></i>
                        </button>
                      </form>
                    </div>
                  </td>

                </tr>



              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>

</html>