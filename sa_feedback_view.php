<?php
session_start();

// Ensure the user is a superadmin
include 'connection.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}


$fq_id_url = isset($_GET['fq_id_url']) ? $_GET['fq_id_url'] : null;


$answerTemp = $conn->query("SELECT * FROM feedback_answers WHERE fa_id = $fq_id_url")->fetchAll(PDO::FETCH_ASSOC);

$questionTemp = $conn->query("SELECT * FROM feedback_questions WHERE fq_id = $fq_id_url")->fetchAll(PDO::FETCH_ASSOC);

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

  <style>
    tr,
    td {
      border-color: white;
    }
  </style>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <section class="p-4 bg-white rounded-xl h-fit">


      <p class="text-center text-lg"><?php echo empty($answerTemp) ? 'No Response Yet!' : ''; ?></p>
       

          <?php foreach ($answerTemp as $row) { ?>

            <p class="font-bold text-lg"><?php echo getAnyFunc($conn, 'barangays', 'id = ' . $row['barangay_id']); ?></p>


            <table class="w-full table table-sm" style="table-layout: fixed;">
              <tr>
                <th class="bg-primary text-white">Question</th>
                <th class="bg-primary text-white">Answer</th>
                <th class="bg-primary text-white">Comment</th>
              </tr>
              <?php foreach ($questionTemp as $t) { ?>
                <tr>
                  <td><?php echo $t['fq1']; ?></td>
                  <td><?php echo $row['fa1']; ?></td>
                  <td rowspan="5"><?php echo $row['comment']; ?></td>
                </tr>
                <tr>
                  <td><?php echo $t['fq2']; ?></td>
                  <td><?php echo $row['fa2']; ?></td>

                </tr>
                <tr>
                  <td><?php echo $t['fq3']; ?></td>
                  <td><?php echo $row['fa3']; ?></td>

                </tr>
                <tr>
                  <td><?php echo $t['fq4']; ?></td>
                  <td><?php echo $row['fa4']; ?></td>

                </tr>
                <tr>
                  <td><?php echo $t['fq5']; ?></td>
                  <td><?php echo $row['fa5']; ?></td>

                </tr>
              <?php } ?>
            </table>

            <hr class="my-3 <?php echo count($answerTemp) === 1 ? 'hidden' : ''; ?>">

          <?php } ?>

        </section>
    </div>
  </div>

  <script src="hide_toast.js"></script>
</body>

</html>