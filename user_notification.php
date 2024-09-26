<?php
session_start();

include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

include 'user_notification_handler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['submit_read'])) {
    $notif_id = $_POST['notif_id'];
    $query = "UPDATE complaints SET seen = 1 WHERE userID = :user_id AND id = :notif_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':notif_id', $notif_id);
    $stmt->execute();
    $notifData = getAllNotificationData($conn, $user_id); // refresh data
  }

  if (isset($_POST['submit_readAll'])) {
    $query = "UPDATE complaints SET seen = 1 WHERE userID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $notifData = getAllNotificationData($conn, $user_id); // refresh data
  }

  if (isset($_POST['submit_unread'])) {
    $query = "SELECT * FROM complaints WHERE userID = :user_id AND seen = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $notifData = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  if (isset($_POST['submit_all'])) {
    $notifData = getAllNotificationData($conn, $user_id); // fetch all notifications again
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
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- tailwind link -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- daisy ui link -->
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />

  <!-- tabler icon link -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

</head>

<body class="bg-[#E8E8E7] h-screen w-screen flex flex-col gap-y-2 items-center justify-start">

  <section class="w-[60rem] max-w-[90%] flex justify-end mt-2">
    <a class="bg-blue-500 hover:bg-blue-400 px-3 py-2 rounded-md text-white" href="user_dashboard.php">back to dashboard</a>
  </section>

  <section class="bg-white shadow rounded-lg h-[5rem] w-[60rem] max-w-[90%] flex items-center justify-between p-5">
    <form action="" method="POST">
      <input type="submit" value="All" name="submit_all" class="p-1 border border-gray-400">
      <input type="submit" value="Unread" name="submit_unread" class="p-1 border border-gray-400">
    </form>

    <form action="" method="POST">
      <input type="submit" value="Mark all as read" name="submit_readAll" class="p-1 border border-gray-400">
    </form>
  </section>

  <section class="bg-white shadow h-[30rem] w-[60rem] max-w-[90%]">
    <?php if (!empty($notifData)) { ?>
      <?php foreach ($notifData as $row) { ?>
        <div class="relative <?php echo $row['seen'] === 1 ? 'bg-white' : 'bg-blue-100' ?> h-fit w-full border p-5 flex justify-between">
          <p>The case
            <span id="textToCopy<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['CNum']);?></span>
            has lapse
            <?php echo $row['CMethod'] == 'Mediation' ? '15 days for mediation' : ($row['CMethod'] == 'Conciliation' ? '30 days for conciliation' : ''); ?>
          </p>

          <section class="flex items-center gap-x-3">

            <div class="tooltip tooltip-close tooltip-info" data-tip="copy case#">
              <i class="ti ti-copy text-2xl" onclick="copyText(<?php echo $row['id']; ?>)"></i>
            </div>

            <form action="" method="POST">
              <input type="hidden" name="notif_id" value="<?php echo $row['id']; ?>">
              <input <?php echo $row['seen'] === 1 ? 'disabled' : '' ?> type="submit" value="read" name="submit_read" class="<?php echo $row['seen'] === 1 ? 'text-gray-400 border-gray-300' : '' ?> p-1 border border-gray-400">
            </form>

          </section>

        </div>
      <?php } ?>
    <?php } else { ?>
      <section class="h-full w-full flex flex-col items-center justify-center gap-y-2">
        <img src="https://lollypop.design/wp-content/uploads/2022/01/emotional-design_mascots.png" alt="empty image" class="h-[14rem]">
        <p class="text-3xl text-gray-500 font-bold">No Notification yet!</p>
      </section>
    <?php } ?>
  </section>

  <script>
    function copyText(index) {
      // Get the text from the <p> tag
      var text = document.getElementById("textToCopy" + index).innerText;

      // Use Clipboard API to copy the text
      navigator.clipboard.writeText(text).then(function() {
        alert("Text copied to clipboard: " + text);
      }).catch(function(error) {
        alert("Failed to copy text: " + error);
      });
    }
  </script>
</body>

</html>