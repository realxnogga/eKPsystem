<?php
session_start();

include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

include 'admin_func.php';


$action_submitted = isset($_GET['search']);
$currentMunicipalityID = $_SESSION['municipality_id'] ?? null;
// Prepare the search query if search form is submitted
if ($action_submitted) {
  $search_query = $_GET['search'];
  $searchUsersQuery = "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.contact_number, b.barangay_name 
                    FROM users u 
                    LEFT JOIN barangays b ON u.barangay_id = b.id 
                    WHERE u.verified = 1 
                    AND u.municipality_id = ? 
                    AND (u.first_name LIKE '%$search_query%' 
                        OR u.last_name LIKE '%$search_query%' 
                        OR b.barangay_name LIKE '%$search_query%')";

  $searchUsersStatement = $conn->prepare($searchUsersQuery);
  $searchUsersStatement->execute([$currentMunicipalityID]);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secretaries Corner</title>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <!-- delete later -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">

  <link rel="stylesheet" href="hide_show_icon.css">

  <style>
        table {
            width: 100%;
            table-layout: fixed; /* Ensures all columns have equal width */
        }
        th, td {
        
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

          <h5 class="card-title mb-9 fw-semibold">Secretaries Corner</h5>
          <hr>
          <b>
            <br>

            <input type="search" id="searchAny" class="form-control" placeholder="search">

            <br>

            <!-- <form method="GET" action="" class="searchInput">
              <input type="text" class="form-control" name="search" id="search" placeholder="Search by Name or Barangay Name" required>
              <input type="submit" class="bg-gray-800 hover:bg-gray-700 px-3 py-2 ml-2 rounded-md text-white" value="Search">
            </form> -->


            <?php // Your code before the table structure
            $verifiedUsersQuery = "SELECT id, username, first_name, last_name, email, contact_number, user_type, barangay_id 
                        FROM users 
                        WHERE verified = 1 
                        AND municipality_id = ?";

            $verifiedUsersStatement = $conn->prepare($verifiedUsersQuery);
            $verifiedUsersStatement->execute([$currentMunicipalityID]);
            ?>




            <table class="table table-striped">
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

              <?php
              echo '<tbody>';
            
              if ($action_submitted) {
                while ($verifiedUser = $searchUsersStatement->fetch(PDO::FETCH_ASSOC)) {
                  // Fetch barangay name for the current user if the key exists
                  $barangayName = $verifiedUser['barangay_name'] ?? '';
                  if (array_key_exists('barangay_id', $verifiedUser)) {
                    $barangayNameQuery = "SELECT barangay_name FROM barangays WHERE id = ?";
                    $barangayStatement = $conn->prepare($barangayNameQuery);
                    $barangayStatement->execute([$verifiedUser['barangay_id']]);
                    $barangayName = $barangayStatement->fetchColumn();
                  }

                  // Displaying table rows for search results
                  echo '<tr>';
                  echo '<td class="max-w-[10ch]">' . $verifiedUser['username'] . '</td>';
                  echo '<td>' . $verifiedUser['first_name'] . ' ' . $verifiedUser['last_name'] . '</td>';
                  echo '<td>' . $verifiedUser['email'] . '</td>';
                  echo '<td>' . $verifiedUser['contact_number'] . '</td>';
                  echo '<td>' . $barangayName . '</td>';
                  echo '<td class="flex flex-col gap-y-2">';
                  // Your actions/buttons for search results
                  echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
                  echo '<input type="hidden"  name="user_id" value="' . $verifiedUser['id'] . '">';
                  // echo '<button class="bg-green-500 hover:bg-green-400 px-3 py-2 ml-2 rounded-md text-white" type="submit" name="action" value="unverify">1Lock</button>';
                
                  echo '<button class="bg-red-500 hover:bg-red-400 px-3 py-2 ml-2 rounded-md text-white" type="submit" name="action" value="unverify">
                  <span>
                  <i class="ti ti-lock text-lg show-icon"></i>
                  <p class="whitespace-nowrap hide-icon hidden">Lock</p>
                  </span>    
                  </button>';

                  echo '</form>';
                 
                  // Your actions/buttons for search results
                  echo '<form method="post" action="admin_viewreport.php">';
                  echo '<input type="hidden" name="user_id" value="' . $verifiedUser['id'] . '">';
                  // Fetch barangay_id and include it as a hidden input
                  $barangayIdQuery = "SELECT barangay_id FROM users WHERE id = ?";
                  $barangayStatement = $conn->prepare($barangayIdQuery);
                  $barangayStatement->execute([$verifiedUser['id']]);
                  $barangayId = $barangayStatement->fetchColumn();
                  echo '<input type="hidden" name="barangay_id" value="' . $barangayId . '">';
                  // echo '<button class="btn btn-success m-1" type="submit" name="viewreport">View Report</button>';

                  echo '<button class="bg-blue-500 hover:bg-blue-400 px-3 py-2 ml-2 rounded-md text-white" type="submit" name="viewreport">
                  <span>
                      <i class="ti ti-report-search text-lg show-icon"></i>
                      <p class="whitespace-nowrap hide-icon hidden hide-icon">View Report</p>
                      </span>
                  </button>';

                  echo '</form>';
                  echo '</td>';
                  echo '</tr>';
                }
              } else {

                while ($verifiedUser = $verifiedUsersStatement->fetch(PDO::FETCH_ASSOC)) {
                  // Fetch barangay name for the current user if the key exists
                  $barangayName = '';
                  if (isset($verifiedUser['barangay_id'])) {
                    $barangayNameQuery = "SELECT barangay_name FROM barangays WHERE id = ?";
                    $barangayStatement = $conn->prepare($barangayNameQuery);
                    $barangayStatement->execute([$verifiedUser['barangay_id']]);
                    $barangayName = $barangayStatement->fetchColumn();
                  }
                  // Displaying table rows for verified users
                  echo '<tr>';
                  echo '<td>' . $verifiedUser['username'] . '</td>';
                  echo '<td>' . $verifiedUser['first_name'] . ' ' . $verifiedUser['last_name'] . '</td>';
                  echo '<td>' . $verifiedUser['email'] . '</td>';
                  echo '<td>' . $verifiedUser['contact_number'] . '</td>';
                  echo '<td>' . ($barangayName === '' ? '(NA)assessor' : $barangayName) . '</td>';
                  echo '<td class="flex flex-col gap-y-2">';
                  // Your actions/buttons for verified users
                  echo '<form method="post"  action="' . $_SERVER['PHP_SELF'] . '">';
                  echo '<input type="hidden" name="user_id" value="' . $verifiedUser['id'] . '">';

                  echo '<button class="bg-red-500 hover:bg-red-400 w-fit px-3 py-2 ml-2 rounded-md text-white" type="submit" name="action" value="unverify">
  <span>
    <i class="ti ti-lock text-lg show-icon"></i>
    <p class="whitespace-nowrap hide-icon hidden">Lock</p>
  </span>
  
</button>
';

                  echo '</form>';
                
                
                  // Your actions/buttons for verified users
                  echo '<form method="post"  action="admin_viewreport.php">';
                  echo '<input type="hidden" name="user_id" value="' . $verifiedUser['id'] . '">';
                  // Fetch barangay_id and include it as a hidden input
                  $barangayIdQuery = "SELECT barangay_id FROM users WHERE id = ?";
                  $barangayStatement = $conn->prepare($barangayIdQuery);
                  $barangayStatement->execute([$verifiedUser['id']]);
                  $barangayId = $barangayStatement->fetchColumn();

                  if ($verifiedUser['user_type'] === 'assessor') {
                    echo '<button class="bg-gray-500 hover:bg-gray-400 px-3 py-2 ml-2 rounded-md text-white disabled:opacity-50 disabled:cursor-not-allowed" 
                    type="submit" 
                    name="viewreport" 
                    formaction="admin_viewreport.php?user_id=' . $verifiedUser['id'] . '&barangay_id=' . $barangayId . '" disabled>
                   N/A
                  </button>';
                  } else {
                    echo '<button class="bg-blue-500 hover:bg-blue-400 px-3 py-2 ml-2 rounded-md text-white disabled:opacity-50 disabled:cursor-not-allowed" 
                    type="submit" 
                    name="viewreport" 
                    formaction="admin_viewreport.php?user_id=' . $verifiedUser['id'] . '&barangay_id=' . $barangayId . '" 
                    ' . ($verifiedUser['user_type'] === 'assessor' ? 'disabled' : '') . '>
                      <span>
                      <i class="ti ti-report-search text-lg show-icon"></i>
                      <p class="whitespace-nowrap hide-icon hidden hide-icon">View Report</p>
                      </span>
                  </button>';
                  }
                  echo '<input type="hidden" name="barangay_id" value="' . $barangayId . '">';

                  echo '</form>';
                  echo '</td>';
                  echo '</tr>';
                }
              }
              // Closing table structure
              echo '</tbody>';
              echo '</table>';
              echo '</div>';
              echo '</div>';
              ?>
              </tbody>
            </table>
          </b>

        </div>
      </div>

    </div>

  </div>

  <script>
  document.getElementById("searchAny").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
      let temp = row.cells[0].textContent.toLowerCase();
      if (temp.includes(filter)) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  });
</script>

</body>

</html>