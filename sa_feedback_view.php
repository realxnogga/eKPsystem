<?php
session_start();

// Ensure the user is a superadmin
include 'connection.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}


$id = isset($_GET['fq_id_url']);


$questionTemp = $conn->query("SELECT * FROM feedback_answers WHERE fa_id = $id")->fetchAll(PDO::FETCH_ASSOC);

$answerTemp = $conn->query("SELECT * FROM feedback_questions WHERE fq_id = $id")->fetchAll(PDO::FETCH_ASSOC);

function getAFunc($conn, $whatTable, $condition)
{
  return $conn->query("SELECT * FROM $whatTable WHERE $condition")->fetchColumn(PDO::FETCH_ASSOC);
}

function getAnyFunc($conn, $whatTable, $condition)
{
  return $conn->query("SELECT * FROM $whatTable WHERE $condition")->fetchColumn(PDO::FETCH_ASSOC);
}


?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="assets/css/styles.min.css" />

  <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <section class="p-4 bg-white rounded-xl h-fit">


        <section>
          <?php foreach ($questionTemp as $row) { ?>


           
            <p class="font-bold text-lg"><?php echo getAnyFunc($conn, 'barangays', 'id = ' . $row['barangay_id']); ?></p>


            <div class="w-50 flex justify-between items-center">

              <div class="flex flex-col mb-5">
              <p><?php echo $row['fq1']; ?></p>
              </div>



              <div class="flex flex-col mb-5">
                <p><?php echo $row['fa1']; ?></p>
                <p><?php echo $row['fa2']; ?></p>
                <p><?php echo $row['fa3']; ?></p>
                <p><?php echo $row['fa4']; ?></p>
                <p><?php echo $row['fa5']; ?></p>
                <p><?php echo $row['comment']; ?></p>
              </div>


            </div>


          <?php } ?>
        </section>

      </section>
    </div>
  </div>

  <script src="hide_toast.js"></script>
</body>

</html>