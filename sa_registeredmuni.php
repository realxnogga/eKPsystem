<?php
session_start();
include 'connection.php';
//include 'superadmin-navigation.php';


// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}

$searchedMunicipality = '';

// Handling search functionality
if (isset($_POST['search'])) {
  $searchedMunicipality = $_POST['municipality']; // Get the searched municipality
  $stmt = $conn->prepare("SELECT u.id, u.municipality_id, u.first_name, u.last_name, u.contact_number, u.email, m.municipality_name FROM users u
                            INNER JOIN municipalities m ON u.municipality_id = m.id
                            WHERE u.user_type = 'admin' AND m.municipality_name LIKE :municipality");
  $stmt->bindValue(':municipality', '%' . $searchedMunicipality . '%', PDO::PARAM_STR);
  $stmt->execute();
  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Fetch all municipalities if no search is performed
  $stmt = $conn->prepare("SELECT u.id, u.municipality_id, u.first_name, u.last_name, u.contact_number, u.email, m.municipality_name FROM users u
                            INNER JOIN municipalities m ON u.municipality_id = m.id
                            WHERE u.user_type = 'admin'");
  $stmt->execute();
  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registered Municipalities</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="assets/css/styles.min.css" />


  <link rel="stylesheet" href="hide_show_icon.css">

  <style>
    table {
      width: 100%;
      table-layout: fixed;
      /* Ensures all columns have equal width */
    }

    th,
    td {

      padding: 8px;
      text-align: center;
    }
  </style>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

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

          <h5 class="card-title mb-9 fw-semibold">Registered Municipalities</h5>
          <hr>
          <b>
            <br>

            <input type="search" id="searchAny" class="form-control" placeholder="Search Municipality">

            <br>


            <table class="table table-striped">
              <thead>
                <tr>
                  <th class="municipality-column" style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;">Municipality</th>
                  <th class="admin-column" style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;">Admin</th>
                  <th class="contact-column" style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;">Contact No#</th>
                  <th class="email-column" style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;">Email</th>
                  <th class="actions-column" style="padding: 8px; background-color: #d3d3d3; white-space: nowrap;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($user as $row) { ?>
                  <tr>
                    <td><?php echo $row['municipality_name']; ?></td>
                    <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                    <td><?php echo $row['contact_number']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td>


                    <button class="w-fit btn btn-info m-1 bg-blue-500" onclick="window.location.href='sa_manageregisteredmuni.php?admin_id=<?php echo $row['id']; ?>'">
                    <span>
                     <i class="ti ti-user-cog text-lg show-icon text-white"></i>
                     <p class="hide-icon hidden">Manage</p>
                    </span>
                    </button>

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

  <script>
    document.getElementById("searchAny").addEventListener("keyup", function() {
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