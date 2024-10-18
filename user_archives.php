<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'];

# function to get all unarchive case from database
function getAllUnarchive($conn, $userID) {
  $query = "SELECT * FROM complaints WHERE UserID = '$userID' AND IsArchived = 1";
  return $conn->query($query);
}
# always get unarchive case hwen the page rendered
$result = getAllUnarchive($conn, $userID);


if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $inputData = json_decode(file_get_contents('php://input'), true);

  if (isset($POST[$inputData['selected_id']])) {

    $unarchiveID = $inputData['selected_id'];
    
    $unarchiveQuery = "UPDATE complaints SET IsArchived = 0 WHERE id = $unarchiveID";
    $stmt = $conn->prepare($unarchiveQuery);
    
    if ($stmt->execute()) {
       $result = getAllUnarchive($conn, $userID);
       echo json_encode($result);
       exit;
    }   
}
}

// include 'report_handler.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Archives</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script>
    // Send data via POST using fetch API
    async function sendData(unarchiveId) {
      try {
        const response = await fetch("", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            selected_id: unarchiveId
          })
        });

        const result = await response.json();

        // Clear previous table content
        const tableBody = document.querySelector("tbody");
        tableBody.innerHTML = '';

        // Populate table with new data
        result.forEach(row => {
          const newRow = `<tr>
                                <td>${row.CNum}</td>
                                <td>${row.ForTitle}</td>
                                <td>${row.CNames}</td>
                                <td>${row.RspndtNames}</td>
                                <td>${row.Mdate}</td>
                               
                          </tr>`;
                          
          tableBody.innerHTML += newRow;
        });

      } catch (error) {
        console.error("Error:", error);
      }
    }

    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('formArchive');
      form.onsubmit = function(event) {
        event.preventDefault();
        const unarchiveId = document.querySelector('input[name="unarchive_id"]').value;

        if (navigator.onLine) {
          sendData(unarchiveId);
        } else {
          localStorage.setItem('unarchiveId', unarchiveId);
          alert('No internet. Data will be inserted once the internet is restored.');
        }
      };

      // Sync when back online
      window.addEventListener('online', function() {
        const unarchiveId = localStorage.getItem('unarchiveId');
        if (unarchiveId) {
          sendData(unarchiveId);
          localStorage.removeItem('unarchiveId');
        }
      });
    });
  </script>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "user_sidebar_header.php"; ?>

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

          <h5 class="card-title mb-9 fw-semibold">Barangay Complaint Archives</h5>
          <b>

            <form method="GET" action="" class="searchInput">
              <div style="display: flex; align-items: center;">
                <input type="text" class="form-control" name="search" id="search" placeholder="Search by Case No., Title, Complainants, or Respondents" onkeyup="liveSearch()" class="searchInput" style="flex: 1; margin-right: 5px;">
              </div>
            </form>

            <br>
            <table class="table table-striped">
              <thead class="thead-dark">
                <tr>
                  <th>No.</th>
                  <th>Title</th>
                  <th>Complainants</th>
                  <th>Respondents</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>

                <?php foreach($result as $row) { ?>
                  <tr>
                    <td><?php echo $row['CNum']; ?></td>
                    <td><?php echo $row['ForTitle']; ?></td>
                    <td><?php echo $row['CNames']; ?></td>
                    <td><?php echo $row['RspndtNames']; ?></td>
                    <td><?php echo $row['Mdate']; ?></td>

                    <td>
                      <form id="formArchive">
                        <input
                          type="hidden"
                          name="unarchive_id"
                          value="<?php echo $row['id'] ?>">

                        <input
                          type="submit"
                          value="unarchive"
                          name="submit_unarchive"
                          class="p-2 bg-red-400 text-white rounded-md">
                      </form>
                    </td>

                  </tr>
                <?php } ?>

              </tbody>
            </table>

          </b>

        </div>
      </div>
    </div>
  </div>

</body>

</html>