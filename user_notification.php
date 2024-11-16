<?php
session_start();

include 'connection.php';
include 'include/custom-scrollbar.php';

include 'user_set_timezone.php';


$userID = $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

date_default_timezone_set('Asia/Manila');

include 'user_notification_handler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['submit_read'])) {
    $notif_id = $_POST['notif_id'];
    updateNotifStatus($conn, $userID, "seen = 1", "id = $notif_id");
    $notifData = getAllNotificationData($conn, $userID);

    header("location: user_edit_complaint.php?id=$notif_id&page=1");
  }

  if (isset($_POST['submit_readAll'])) {
    updateNotifStatus($conn, $userID, "seen = 1");
    $notifData = getAllNotificationData($conn, $userID);
  }

  if (isset($_POST['submit_unread'])) {
    $notifData = getAllNotificationData($conn, $userID, "seen = 0");
  }

  if (isset($_POST['submit_all'])) {
    $notifData = getAllNotificationData($conn, $userID);
  }

  if (isset($_POST['submit_remove_notif'])) {

    $notif_id = $_POST['notif_id'];
    updateNotifStatus($conn, $userID, "removenotif = 1", "id = $notif_id");
    $notifData = getAllNotificationData($conn, $userID);
  }


  if (isset($_POST['submit_filter'])) {
    $filter = $_POST['filter_period'];
    $extraCondition = "";

    switch ($filter) {
      case 'today':
        // Notifications where Mdate + 14 days is today
        $extraCondition = "DATE(DATE_ADD(Mdate, INTERVAL 14 DAY)) = CURDATE()";
        break;
      case 'week':
        // Notifications where Mdate + 14 days is within the current week
        $extraCondition = "
                YEAR(DATE_ADD(Mdate, INTERVAL 14 DAY)) = YEAR(CURDATE()) 
                AND WEEK(DATE_ADD(Mdate, INTERVAL 14 DAY)) = WEEK(CURDATE())";
        break;
      case 'month':
        // Notifications where Mdate + 14 days is within the current month
        $extraCondition = "
                YEAR(DATE_ADD(Mdate, INTERVAL 14 DAY)) = YEAR(CURDATE()) 
                AND MONTH(DATE_ADD(Mdate, INTERVAL 14 DAY)) = MONTH(CURDATE())";
        break;
    }

    $notifData = getAllNotificationData($conn, $userID, $extraCondition);
  }
}

// -------------------------------------


function get_time_ago($time)
{
  $time_difference = time() - $time;

  if ($time_difference < 1) {
    return 'less than 1 second ago';
  }
  $condition = array(
    12 * 30 * 24 * 60 * 60 =>  'year',
    30 * 24 * 60 * 60       =>  'month',
    24 * 60 * 60            =>  'day',
    60 * 60                 =>  'hour',
    60                      =>  'minute',
    1                       =>  'second'
  );

  foreach ($condition as $secs => $str) {
    $d = $time_difference / $secs;

    if ($d >= 1) {
      $t = round($d);
      return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
    }
  }
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Notification</title>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <script src="node_modules/flowbite/dist/flowbite.min.css"></script>

  <!-- tailwind link -->
  <link href="output.css" rel="stylesheet">

  <!-- tabler icon link -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">

</head>

<body class="bg-[#E8E8E7] h-screen w-screen flex flex-col gap-y-2 items-center justify-start">

  <section class="w-[60rem] max-w-[90%] flex justify-between mt-3">
    <h3 class="text-4xl font-bold text-gray-700">Notifications</h3>
    <a href="user_dashboard.php" class="bg-blue-500 hover:bg-blue-400 px-3 py-2 rounded-md text-white">back to dashboard</a>
  </section>

  <section class="bg-white shadow rounded-lg h-[4rem] w-[60rem] max-w-[90%] flex items-center justify-between p-3">

    <section class="flex gap-x-2">
      <form action="" method="POST" class="m-0 p-0">
        <input
          type="submit"
          value="All"
          name="submit_all"
          class="p-1 rounded-sm hover:bg-gray-100 border border-gray-400 px-3 cursor-pointer">
      </form>

      <form action="" method="POST" class="m-0 p-0">
        <input
          type="submit"
          value="Unread"
          name="submit_unread"
          class="p-1 rounded-sm hover:bg-gray-100 border border-gray-400 px-3 cursor-pointer">
      </form>

      <form action="" method="POST" class="m-0 p-0">
        <input
          type="submit"
          value="Mark all as read"
          name="submit_readAll"
          class="p-1 rounded-sm hover:bg-gray-100 border border-gray-400 px-3 cursor-pointer">
      </form>
    </section>


    <!-- ----------------------------- -->
    <form action="" method="POST" class="m-0 p-0">
      <select name="filter_period" class="p-1 rounded-sm bg-gray-100 border border-gray-300">
        <option value="today">Today</option>
        <option value="week">This Week</option>
        <option value="month">This Month</option>
      </select>
      <button type="submit" name="submit_filter" class="p-1 rounded-sm hover:bg-gray-100 border border-gray-400 px-3 cursor-pointer">Filter</button>
    </form>
  </section>
  <!-- ----------------------------- -->


  </section>

  <section class="bg-white shadow h-[31rem] w-[60rem] max-w-[90%] overflow-y-auto">
    <?php if (!empty($notifData)) { ?>
      <?php foreach ($notifData as $row) { ?>

        <div class="relative <?php echo $row['seen'] === 1 ? 'bg-white' : 'bg-blue-100' ?> hover:bg-gray-100 h-fit w-full border p-3 pl-4 flex items-center justify-between">


          <div class="flex flex-col gap-y-1 items-start cursor-default">

            <div class="flex">
              <p clas>The case </p>
              <form action="" method="POST" class="m-0 p-0">
                <input
                  type="hidden"
                  name="notif_id"
                  value="<?php echo $row['id']; ?>">
                <input
                  id="textToCopy<?php echo $row['id']; ?>"
                  type="submit"
                  value="<?php echo htmlspecialchars($row['CNum']); ?>"
                  name="submit_read"
                  class="px-1 underline text-blue-500 cursor-pointer">
              </form>
              <p>has lapsed 14 days</p>
            </div>

            <button data-tooltip-placement="right" data-tooltip-target="tooltip-light_<?php echo $row['id']; ?>" data-tooltip-style="light" type="button" class="cursor-default">
              <p class="text-sm text-gray-500">
                <?php
                $adjustedDate = strtotime($row['Mdate'] . ' +14 days');
                echo get_time_ago($adjustedDate);
                ?>
              </p>
            </button>

            <div id="tooltip-light_<?php echo $row['id']; ?>" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-1 bg-white border border-gray-200 rounded-lg shadow-xl opacity-0 tooltip">
              <p class="text-sm text-gray-500">
                <?php
                $adjustedDate = strtotime($row['Mdate'] . ' +14 days');
                echo date('M d, h:i A', $adjustedDate);
                ?>
              </p>
              <div class="tooltip-arrow" data-popper-arrow></div>
            </div>


          </div>

          <!-- --------------------------- -->
          <section class="flex gap-x-4 items-center">

            <i class="ti ti-copy text-2xl cursor-pointer" onclick="copyText(<?php echo $row['id']; ?>)"></i>

            <form action="" method="POST" class="m-0 p-0">
              <input
                type="hidden"
                name="notif_id"
                value="<?php echo $row['id']; ?>">

              <button type="submit" id="notiftoremove<?php echo $row['id']; ?>" name="submit_remove_notif">
                <i class="ti ti-trash-x text-2xl text-red-500 cursor-pointer"></i>
              </button>

            </form>

          </section>
          <!-- ----------------------------- -->


        </div>

      <?php } ?>
    <?php } else { ?>
      <section class="h-full w-full flex flex-col items-center justify-center gap-y-2">
        <img
          src="https://cdni.iconscout.com/illustration/premium/thumb/no-notification-illustration-download-in-svg-png-gif-file-formats--notifications-mail-e-commerce-pack-shopping-illustrations-6743718.png?f=webp"
          alt="empty image"
          class="h-[14rem]">
        <p class="text-3xl text-gray-500 font-bold">No notification yet!</p>
      </section>
    <?php } ?>
  </section>

  <!-- for custom alert -->
  <div class="hidden bg-red-500 text-white p-4 absolute top-5 left-1/2 transform -translate-x-1/2 rounded-lg z-[1000]" id="customAlert"></div>

  <script>
    function copyText(index) {

      var text = document.getElementById("textToCopy" + index).value;

      navigator.clipboard.writeText(text).then(function() {

        var alertBox = document.getElementById("customAlert");
        alertBox.style.display = "block";
        alertBox.style.backgroundColor = "#34b4eb";
        document.getElementById("customAlert").innerText = 'case#' + ' ' + text + ' ' + 'copied';

        setTimeout(function() {
          alertBox.style.display = "none";
        }, 1000);

      }).catch(function(error) {
        var alertBox = document.getElementById("customAlert");
        alertBox.style.display = "block";
        alertBox.style.backgroundColor = "#f57f81";
        document.getElementById("customAlert").innerText = 'error occured';
        setTimeout(function() {
          alertBox.style.display = "none";
        }, 1000);

      });
    }
  </script>
</body>

</html>