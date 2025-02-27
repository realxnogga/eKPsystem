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
    $result = fetchArchiveFunc($conn,$userID, $selectedYear);
  }
}

$selectedYear = isset($_SESSION['ay_archiveyear']) ? $_SESSION['ay_archiveyear'] : date('Y');
$result = fetchArchiveFunc($conn, $userID, $selectedYear);

function fetchArchiveFunc($conn, $userID, $whatYear)
{
  $query = "SELECT * FROM complaints WHERE UserID = :userID AND YEAR(Mdate) = :year AND IsArchived = 1";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':userID', $userID);
  $stmt->bindParam(':year', $whatYear, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Archives</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

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
              <tr class="flex w-full text-sm border">                 
                <td class="flex-1 px-2">${row.CNum}</td>
                <td class="flex-1 px-2">${row.ForTitle}</td>
                <td class="flex-1 px-2">${row.CNames}</td>
                <td class="flex-1 px-2">${row.RspndtNames}</td>
                <td class="flex-1 px-2">${row.Mdate}</td>
                <td class="flex-1 px-2"><input type="checkbox" class="case-checkbox mx-4" value="${row.id}"></td>
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

</head>

<body class="bg-[#E8E8E7]">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <!--  Row 1 -->
      <div class="card">
        <div class="card-body">

          <section class="flex justify-between gap-x-4">

            <input type="text" class="form-control" name="search" id="searchUnarchive" placeholder="search" onkeyup="searchTable()" class="searchInput" style="flex: 1; margin-right: 5px;">

            <form method="POST" action="">
              <select id="yearfilter" name="yearfilter" onchange="this.form.submit()">
                <?php
                $currentYear = date('Y');
                $startYear = $currentYear - 5; // Start 5 years before the current year

                // Loop to generate options from startYear to endYear
                for ($year = $startYear; $year <= $currentYear; $year++) {
                  echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
                }
                ?>
              </select>
            </form>

          </section>


          <br>
          <table id="UnarchiveTable" class="<?php echo empty($result) ? 'hidden' : ''; ?> table table-striped w-full">
            <thead>
              <tr class="flex w-full text-sm">
                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;" class="flex-1 px-2">No.</th>
                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;" class="flex-1 px-2">Title</th>
                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;" class="flex-1 px-2">Complainants</th>
                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;" class="flex-1 px-2">Respondents</th>
                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;" class="flex-1 px-2">Date</th>
                <th style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;" class="flex-1 px-2">Unarchive</th>
              </tr>
            </thead>
            <tbody class="flex flex-col max-h-[28rem] overflow-y-scroll">
              <?php foreach ($result as $row) { ?>
                <tr class="flex w-full text-sm border">
                  <td class="flex-1 px-2"><?php echo $row['CNum']; ?></td>
                  <td class="flex-1 px-2"><?php echo $row['ForTitle']; ?></td>
                  <td class="flex-1 px-2"><?php echo $row['CNames']; ?></td>
                  <td class="flex-1 px-2"><?php echo $row['RspndtNames']; ?></td>
                  <td class="flex-1 px-2"><?php echo $row['Mdate']; ?></td>
                  <td class="flex-1 px-2"><input type="checkbox" class="case-checkbox mx-4" value="<?php echo $row['id']; ?>"></td>
                </tr>
              <?php } ?>

            </tbody>
          </table>

          <p class="text-center text-lg mt-4">
            <?php echo empty($result) ? 'No data available' : ''; ?>
          </p>

          <section id="unarchiveButtonSection" class="<?php echo empty($result) ? 'hidden' : ''; ?> w-full flex justify-end">
            <button id="unarchiveSelected" class="p-2 bg-red-400 text-white rounded-md">Unarchive</button>
          </section>

        </div>
      </div>
    </div>
  </div>

  <script>
    function searchTable() {
      // Declare variables
      let input = document.getElementById('searchUnarchive');
      let filter = input.value.toLowerCase();
      let table = document.getElementById('UnarchiveTable');
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