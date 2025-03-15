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
  <link rel="stylesheet" href="hide_show_icon.css">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Complaints</title>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
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
      place-items: center;
    }

    .legend-color {
      display: inline-block;
      width: 20px;
      height: 20px;
      margin-right: 5px;
      border-radius: 50%;
    }
  </style>
</head>

<body class="bg-[#E8E8E7]">
  <?php include "user_sidebar_header.php"; ?>
  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
      <div class="card">
        <div class="card-body">
          <section></section>
          <details>
            <summary>Color Legend</summary>
            <br>
            <div class="flex justify-between">
             
              <div class="flex items-center">
              <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
                <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
              </div>

              <div style="padding: 15px; border: 1px solid #ccc; border-radius: 8px; font-family: Arial, sans-serif;">
                <h4 class="mb-2" style="color: #333; font-size: 16px; font-weight: bold;">Color Legend</h4>
                <ul style="list-style: none; margin: 0; padding: 0;">
                  <li style="margin-bottom: 8px; display: flex; align-items: center;"> <span style="width: 20px; height: 20px; background-color: #dcfadf; border: 1px solid #999; display: inline-block; margin-right: 8px; border-radius: 4px;"></span> <span style="font-size: 14px; color: #555;">Settled</span> </li>
                  <li style="margin-bottom: 8px; display: flex; align-items: center;"> <span style="width: 20px; height: 20px; background-color: #FFE181; border: 1px solid #999; display: inline-block; margin-right: 8px; border-radius: 4px;"></span> <span style="font-size: 14px; color: #555;">Pending (10-14 days)</span> </li>
                  <li style="display: flex; align-items: center;"> <span style="width: 20px; height: 20px; background-color: #F88D96; border: 1px solid #999; display: inline-block; margin-right: 8px; border-radius: 4px;"></span> <span style="font-size: 14px; color: #555;">Unsettled (15-30 days)</span> </li>
                </ul>
              </div>

            </div>
          </details>
          <br>
          <b>
            <div class="flex gap-x-3">

              <form id="myForm" method="POST" action="" class="w-full">
                <input class="form-control w-full" type="text" id="inputField" name="inputField" placeholder="search by No, Title, Complainants, Respondents, Date, Status..." value="<?php echo isset($_SESSION['searchvalue']) ? $_SESSION['searchvalue'] : ''; ?>">
              </form>

              <form method="POST" action="">
                <select id="yearfilter" name="yearfilter" onchange="this.form.submit()">
                  <?php
                  $currentYear = date('Y');
                  $startYear = $currentYear - 5;
                  for ($year = $startYear; $year <= $currentYear; $year++) {
                    echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
                  }
                  ?>
                </select>
              </form>

              <form method="POST" action="" class="flex">
                <button type="submit" name="seeUpdateRecently">
                  <i class="ti ti-clock-24 text-[2rem]"></i>
                </button>
              </form>

              <button type="button" class="btn btn-primary bg-blue-500" value="Add complaint" onclick="location.href='user_add_complaint.php';">
                <span>
                  <i class="ti ti-plus text-lg show-icon"></i>
                  <p style="white-space: nowrap;" class="hide-icon hidden">Add complaint</p>
                </span>
              </button>
            </div>
            <br>
            <div class="max-h-[30rem] overflow-y-scroll">
              <table id="complaintTable" class="table">
                <thead class="sticky top-0">
                  <tr>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">No.</th>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Title</th>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Complainants</th>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Respondents</th>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Date</th>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Status</th>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Hearing</th>
                    <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap; text-align: center;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                    <?php
                    $isSettled = in_array($row['CMethod'], ['Mediation', 'Conciliation', 'Arbitration']);
                    $isUnsettled = in_array($row['CMethod'], ['Pending', 'Repudiated', 'Dismissed', 'Certified to file action in court', 'Dropped/Withdrawn']);
                    $dateAdded = strtotime($row['Mdate']);
                    $currentDate = strtotime(date('Y-m-d'));
                    $elapsedDays = ($currentDate - $dateAdded) / (60 * 60 * 24);
                    if ($isSettled) {
                      $borderColor = 'bg-green-200';
                    } elseif ($elapsedDays >= 10 && $elapsedDays <= 13) {
                      $borderColor = 'bg-yellow-100';
                    } elseif ($elapsedDays >= 14 && $elapsedDays <= 30 && !$isSettled) {
                      $borderColor = 'bg-red-300';
                    } else {
                      $borderColor = 'border-none';
                    }
                    ?>
                    <tr class="<?= $borderColor; ?>">
                      <td><?= str_pad($row['CNum'], 11, '0', STR_PAD_LEFT) ?></td>
                      <td><?= $row['ForTitle'] ?></td>
                      <td><?= $row['CNames'] ?></td>
                      <td><?= $row['RspndtNames'] ?></td>
                      <td><?= date('Y-m-d', strtotime($row['Mdate'])) ?></td>
                      <td><?= $row['CMethod'] ?></td>
                      <?php
                      $complaintId = $row['id'];
                      $caseProgressQuery = "SELECT current_hearing FROM case_progress WHERE complaint_id = $complaintId";
                      $caseProgressResult = $conn->query($caseProgressQuery);
                      $caseProgressRow = $caseProgressResult->fetch(PDO::FETCH_ASSOC);
                      ?>
                      <td>
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

                      <td>
                        <div class="h-full w-full flex flex-col items-center gap-x-2">
                          <form action="user_edit_complaint.php" method="get" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-secondary bg-blue-500 h-7" title="Edit" data-placement="top" style="width: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 5px;">
                              <i class="fas fa-edit show-icon"></i>
                              <p class="hide-icon">Edit</p>
                            </button>
                          </form>
                          <form action="archive_complaint.php" method="get" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger bg-red-500 h-7" title="Archive" data-placement="top" style="width: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 5px;">
                              <i class="fas fa-archive show-icon"></i>
                              <p class="hide-icon">Archive</p>
                            </button>
                          </form>
                          <form action="user_manage_case.php" method="get" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-warning bg-yellow-400 h-7" title="Manage" data-placement="top" style="width: 60px; display: flex; align-items: center; justify-content: center; margin-bottom: 5px;">
                              <i class="fas fa-folder show-icon"></i>
                              <p class="hide-icon">Manage</p>
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
  </div>
</body>

</html>