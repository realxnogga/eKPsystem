<?php
session_start();

// Ensure the user is a superadmin
include 'connection.php';



// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header("Location: login.php");
    exit;
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

            <section class="bg-white rounded-xl h-[30rem]">
                 <form action="">
                    <input type="text" placeholder="Enter Question">
                    <input type="submit" value="Submit">
                 </form>
            </section>

        </div>
    </div>

</body>

</html>