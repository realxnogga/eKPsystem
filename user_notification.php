<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
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

</head>

<body class="bg-[#E8E8E7] h-screen w-screen flex flex-col gap-y-2 items-center justify-start">

    <section class="w-[60rem] max-w-[90%] flex justify-end">
        <a class="bg-blue-500 hover:bg-blue-400 px-3 py-2 rounded-md text-white" href="user_dashboard.php">back to dashboard</a>
    </section>
    <section class="bg-white shadow rounded-lg h-[5rem] w-[60rem] max-w-[90%]">

    </section>
    <section class="bg-white shadow rounded-lg h-[30rem] w-[60rem] max-w-[90%]">

    </section>
</body>

</html>