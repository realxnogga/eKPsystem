<?php
session_start();

include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secretaries Corner</title>

  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  
  <style>
    .searchInput {
      display: flex;
      align-items: center;
    }

    .searchInput input[type="text"] {
      flex: 1;
    }

    .searchInput input[type="submit"] {
      margin-left: 5px;
      /* Adjust the margin as needed */
    }
  </style>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "../admin_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
      
    <p>fgdfghfg</p>

    </div>
  </div>

</body>
</html>