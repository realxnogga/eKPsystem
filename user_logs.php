<?php

session_start();
include 'connection.php';

$userID = $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

// Fetch today's date logs by default
$query = "SELECT user_id, timestamp, activity FROM user_logs WHERE user_id = :user_id AND DATE(timestamp) = CURDATE() ORDER BY timestamp DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $inputData = json_decode(file_get_contents('php://input'), true);

  $selectedDate = $inputData['selected_date'];

  $query = "SELECT user_id, timestamp, activity FROM user_logs WHERE user_id = :user_id AND DATE(timestamp) = :selected_date ORDER BY timestamp DESC";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
  $stmt->bindParam(':selected_date', $selectedDate, PDO::PARAM_STR); // Bind as string
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Return the result as JSON
  echo json_encode($result);
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Logs</title>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />
 <!-- tailwind cdn -->
<link rel="stylesheet" href="output.css">
</head>

<body class="sm:bg-gray-200 bg-white">

  <?php include "user_sidebar_header.php"; ?>

  <div class="sm:ml-44 p-0 sm:p-6 text-gray-700">
    <div class="rounded-lg mt-16">
      <div class="bg-white sm:shadow-md rounded-lg p-6 shadow-none">
        <form id="formUserLog" class="flex items-center space-x-4">
          <input type="date" class="w-full form-input border-gray-300 shadow-sm" name="selected_date" id="selected_date">
          <input type="submit" class="bg-blue-500 hover:bg-blue-400 px-4 py-2 rounded-md text-white cursor-pointer" value="Go">
        </form>

        <br>

        <table class="table-auto table-fixed w-full border">
          <thead>
            <tr class="bg-gray-200">
              <th class="px-4 py-2 text-left">Timestamp</th>
              <th class="px-4 py-2 text-left">Activity</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($result as $row) { ?>
              <tr class="hover:bg-gray-100">
                <td class="border px-4 py-2"><?php echo $row['timestamp'] ?></td>
                <td class="border px-4 py-2"><?php echo $row['activity'] ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    // Send data via POST using fetch API
    async function sendData(selectedDate) {
      try {
        const response = await fetch("", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            selected_date: selectedDate
          })
        });

        const result = await response.json();

        // Clear previous table content
        const tableBody = document.querySelector("tbody");
        tableBody.innerHTML = '';

        // Populate table with new data
        result.forEach(row => {
          const newRow = `<tr class="hover:bg-gray-100">
                            <td class="border border-gray-300 px-4 py-2">${row.timestamp}</td>
                            <td class="border border-gray-300 px-4 py-2">${row.activity}</td>
                          </tr>`;
          tableBody.innerHTML += newRow;
        });

      } catch (error) {
        console.error("Error:", error);
      }
    }

    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('formUserLog');
      form.onsubmit = function(event) {
        event.preventDefault();
        const selectedDate = document.querySelector('input[name="selected_date"]').value;

        if (navigator.onLine) {
          sendData(selectedDate);
        } else {
          localStorage.setItem('selectedDate', selectedDate);
          alert('No internet. Data will be inserted once the internet is restored.');
        }
      };

      function syncWhenOnline() {
        const selectedDate = localStorage.getItem('selectedDate');
        if (selectedDate) {
          sendData(selectedDate);
          localStorage.removeItem('selectedDate');
        }
      }

      if (navigator.onLine) {
        syncWhenOnline();
      }

      window.addEventListener('online', syncWhenOnline);

    });
  </script>

</body>

</html>