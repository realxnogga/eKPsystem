<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'];

# function to get all unarchived cases from the database
// function getAllUnarchive($conn, $userID)
// {
//   $query = "SELECT * FROM complaints WHERE UserID = :userID AND IsArchived = 1";
//   $stmt = $conn->prepare($query);
//   $stmt->bindParam(':userID', $userID);
//   $stmt->execute();
//   return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $inputData = json_decode(file_get_contents('php://input'), true);

  if (isset($inputData['selected_id']) && is_array($inputData['selected_id'])) {
    $unarchiveIDs = $inputData['selected_id'];

    // Prepare the query to unarchive multiple rows
    $unarchiveQuery = "UPDATE complaints SET IsArchived = 0 WHERE id IN (" . implode(",", array_map('intval', $unarchiveIDs)) . ")";
    $stmt = $conn->prepare($unarchiveQuery);

    if ($stmt->execute()) {
      $selectedYear = isset($_SESSION['ay_archiveyear']) ? $_SESSION['ay_archiveyear'] : date('Y');
      $updatedResult = fetchArchiveFunc($conn, $userID, $selectedYear);  // Fetch updated data
      echo json_encode($updatedResult);  // Return updated data in JSON
      exit;
    }
  }
  // -----------------------------------------------------------------------------------
  if (isset($_POST['yearfilter'])) {
    $selectedYear = $_POST['yearfilter'];

    $_SESSION['ay_archiveyear'] = $selectedYear;
    $result = fetchArchiveFunc($conn, $userID, $selectedYear);
  }
}

$selectedYear = isset($_SESSION['ay_archiveyear']) ? $_SESSION['ay_archiveyear'] : date('Y');
$result = fetchArchiveFunc($conn, $userID, $selectedYear);

function fetchArchiveFunc($conn, $userID, $whatYear)
{
  $query = "SELECT * FROM complaints WHERE UserID = :userID AND YEAR(Mdate) = :year AND IsArchived = 1 ORDER BY archive_updated_date DESC";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':userID', $userID);
  $stmt->bindParam(':year', $whatYear, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- filepath: /c:/xampp/htdocs/eKPsystem/user_archives.php -->
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Archives</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />
  
  <link href="./output.css" rel="stylesheet">

</head>

<body class="sm:bg-gray-200 bg-white">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-0 sm:rounded-lg mt-16 sm:mt-16 bg-white shadow-none sm:shadow-lg p-4 sm:p-6">

      <!-- Search and Filter Section -->
      <div class="flex flex-col sm:flex-row items-center gap-4 mb-6">
        <input type="text" id="searchUnarchive" placeholder="Search" onkeyup="searchTable()" class="w-full sm:w-auto flex-grow border border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">

        <form method="POST" action="" class="w-full sm:w-auto">
          <select id="yearfilter" name="yearfilter" onchange="this.form.submit()" class="w-full sm:w-auto border border-gray-300 p-2 focus:ring-blue-500 focus:border-blue-500">
            <?php
            $currentYear = date('Y');
            $startYear = $currentYear - 5;

            for ($year = $startYear; $year <= $currentYear; $year++) {
              echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
            }
            ?>
          </select>
        </form>
      </div>

      <!-- Table Section -->
      <div class="overflow-x-auto">
        <table id="UnarchiveTable" class="table-auto lg:table-fixed w-full bg-white rounded-lg border <?php echo empty($result) ? 'hidden' : ''; ?>">
          <thead>
            <tr class="bg-gray-200 text-gray-700 text-sm">
              <th class="p-3 text-sm font-semibold text-gray-700 text-center">No.</th>
              <th class="p-3 text-sm font-semibold text-gray-700 text-center">Title</th>
              <th class="p-3 text-sm font-semibold text-gray-700 text-center">Complainants</th>
              <th class="p-3 text-sm font-semibold text-gray-700 text-center">Respondents</th>
              <th class="p-3 text-sm font-semibold text-gray-700 text-center">Date</th>
              <th class="p-3 text-sm font-semibold text-gray-700 text-center">Unarchive</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php foreach ($result as $row) { ?>
              <tr class="hover:bg-gray-100 text-sm">
                <td class="px-2 py-1 text-center"><?php echo $row['CNum']; ?></td>
                <td class="px-2 py-1 text-center"><?php echo $row['ForTitle']; ?></td>
                <td class="px-2 py-1 text-center"><?php echo $row['CNames']; ?></td>
                <td class="px-2 py-1 text-center"><?php echo $row['RspndtNames']; ?></td>
                <td class="px-2 py-1 text-center"><?php echo $row['Mdate']; ?></td>
                <td class="px-2 py-1 text-center">
                  <input type="checkbox" class="case-checkbox" value="<?php echo $row['id']; ?>">
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <!-- No Data Message -->
      <p class="text-center text-lg mt-4 text-gray-500">
        <?php echo empty($result) ? 'No data available' : ''; ?>
      </p>

      <!-- Unarchive Button -->
      <section id="unarchiveButtonSection" class="<?php echo empty($result) ? 'hidden' : ''; ?> w-full flex justify-end mt-4">
        <button id="unarchiveSelected" class="p-2 bg-red-400 text-white rounded-md hover:bg-red-500 transition">Unarchive</button>
      </section>

    </div>
  </div>

  <script>
    async function sendData(selectedIds) {

      const response = await fetch("", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          selected_id: selectedIds
        })
      });

      const result = await response.json();

      // Clear previous table content
      const tableBody = document.querySelector("tbody"); // Get the actual element
      tableBody.innerHTML = '';

      // Populate table with new data
      result.forEach(row => {
        const newRow = `
              <tr class="hover:bg-gray-100 text-sm">                 
                <td class="px-2 py-1 text-center">${row.CNum}</td>
                <td class="px-2 py-1 text-center">${row.ForTitle}</td>
                <td class="px-2 py-1 text-center">${row.CNames}</td>
                <td class="px-2 py-1 text-center">${row.RspndtNames}</td>
                <td class="px-2 py-1 text-center">${row.Mdate}</td>
                <td class="px-2 py-1 text-center"><input type="checkbox" class="case-checkbox mx-4" value="${row.id}"></td>
              </tr>`;
        tableBody.innerHTML += newRow;
      });

      if (result.length === 0) {
        document.getElementById('unarchiveButtonSection').classList.add('hidden');
      }

    }
  </script>

  <script>
    // Handle form submission for unarchiving multiple rows
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('unarchiveSelected').addEventListener('click', function(event) {
        event.preventDefault();

        // Get all selected checkboxes
        const checkboxes = document.querySelectorAll('.case-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(checkbox => checkbox.value);

        if (selectedIds.length > 0) {
          if (navigator.onLine) {
            sendData(selectedIds); // Send selected IDs
          } else {
            localStorage.setItem('unarchiveIds', JSON.stringify(selectedIds));
            alert('No internet. Your request will be executed once the internet is restored.');
          }
        } else {
          alert('Please select at least one case to unarchive.');
        }
      });

      // Sync function when back online
      function syncWhenOnline() {
        const unarchiveIds = JSON.parse(localStorage.getItem('unarchiveIds'));
        if (unarchiveIds && unarchiveIds.length > 0) {
          sendData(unarchiveIds);
          localStorage.removeItem('unarchiveIds');
        }
      }

      if (navigator.onLine) {
        syncWhenOnline();
      }

      window.addEventListener('online', syncWhenOnline);

    });
  </script>


  <script>
    function searchTable() {
      let input = document.getElementById('searchUnarchive');
      let filter = input.value.toLowerCase();
      let table = document.getElementById('UnarchiveTable');
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
  </script>

</body>

</html>