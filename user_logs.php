<?php
session_start();
include 'connection.php';


$userID = $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

// Check if the dropdown form is submitted
if (isset($_POST['view_logs'])) {
  $selected_date = $_POST['selected_date'];
  $query = "SELECT user_id, timestamp, activity FROM user_logs WHERE user_id = :user_id AND DATE(timestamp) = :selected_date ORDER BY timestamp DESC";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
  $stmt->bindParam(':selected_date', $selected_date);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Fetch today's date logs by default
  $query = "SELECT user_id, timestamp, activity FROM user_logs WHERE user_id = :user_id AND DATE(timestamp) = CURDATE() ORDER BY timestamp DESC";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Logs</title>
  
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

  <style>
    .card {
      box-shadow: 0 0 0.3cm rgba(0, 0, 0, 0.2);
      border-radius: 15px;
    }

    tr:hover {
      background-color: #D6EEEE;
    }
  </style>

</head>

<body class="bg-[#E8E8E7]">

<?php include "user_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
  <div class="rounded-lg mt-16">

      <div class="card">
        <div class="card-body">

          <h5 class="card-title mb-9 fw-semibold">User Activity Logs</h5>

          <form method="post" class="mb-3">
            <label for="selected_date">Select Date:</label>
            <div class="d-flex">
              <input type="date" class="form-control" name="selected_date" id="selected_date" value="<?php echo date('Y-m-d'); ?>">

              <input type="submit" class="bg-gray-800 hover:bg-gray-700 ml-2 px-3 py-2 rounded-md text-white" name="view_logs" value="Go" class="ml-2">
            </div>
          </form>

          <table class="table table-striped">
            <thead class="thead-dark">
              <tr>
                <th>Timestamp</th>
                <th>Activity</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($result as $row) { ?>

                <tr>
                  <td><?php echo $row['timestamp'] ?></td>
                  <td><?php echo $row['activity'] ?></td>
                </tr>

              <?php  } ?>

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>

</html>