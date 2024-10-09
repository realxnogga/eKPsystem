<?php
session_start();
include '../connection.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lupong Tagapamayapa Incentives Award</title>
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <link rel="stylesheet" href="assets/css/styles.min.css" />


    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>

<body class="bg-[#E8E8E7]">
    <!-- Sidebar -->
    <?php include "../sa_sidebar_header.php"; ?>     
  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
      <div class="card">
        <div class="card-body">
            Municipalities Boxes here*

        </div>
    </div>
</body>

</html>