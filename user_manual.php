<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Manual</title>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

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

<body class="bg-gray-200">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-0 sm:p-6 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <div class="bg-white h-fit w-full rounded-md p-4">

        <h2 class="text-[2rem] font-thin">USER MANUAL</h2>

        <br>
        <br>

        <nav class="bg-white overflow-x-auto">
          <ul class="flex">
            <li id="um_registration" onclick="navigateTo('um_registration')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md border-1 border-white hover:bg-blue-100 px-4 py-2 cursor-pointer whitespace-nowrap">Registration/Login</li>
            <li id="um_dashboard" onclick="navigateTo('um_dashboard')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer whitespace-nowrap">Dashboard</li>
            <li id="um_lupon" onclick="navigateTo('um_lupon')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer">Lupon</li>
            <li id="um_complaint" onclick="navigateTo('um_complaint')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer whitespace-nowrap">Complaint</li>
            <li id="um_archive" onclick="navigateTo('um_archive')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer whitespace-nowrap">Archive</li>
            <li id="um_report" onclick="navigateTo('um_report')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer whitespace-nowrap">Reports</li>
            <li id="um_LTIA" onclick="navigateTo('um_LTIA')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer">LTIA</li>
            <li id="um_userlogs" onclick="navigateTo('um_userlogs')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer whitespace-nowrap">User logs</li>
            <li id="um_settings" onclick="navigateTo('um_settings')" class="menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer whitespace-nowrap">Settings</li>
          </ul>
        </nav>

        <hr>

        <br>

        <div id="content" class="flex flex-col gap-12">
          <!-- Content will be dynamically updated here based on navigation -->
        </div>

      </div>

    </div>
  </div>

  <script src="user_um_reglogin.js"></script>
  <script src="user_um_functions.js"></script>

  <script>
    function navigateTo(page) {
      const content = document.getElementById('content');

      const menuItems = document.querySelectorAll('.menu-item');
      menuItems.forEach(item => {
        item.classList.remove('bg-blue-100', 'text-black');
      });

      const activeItem = document.getElementById(page);
      activeItem.classList.add('bg-blue-100', 'text-black', 'rounded-tl-lg', 'rounded-tr-lg'); // Active styles

      switch (page) {
        case 'um_registration':
          content.innerHTML = loadRegistrationLoginContent();
          break;
        case 'um_dashboard':
          content.innerHTML = loadDashboardContent();
          break;
        case 'um_lupon':
          content.innerHTML = loadLuponContent();
          break;
        case 'um_complaint':
          content.innerHTML = loadComplaintContent();
          break;
        case 'um_archive':
          content.innerHTML = loadArchiveContent();
          break;
        case 'um_report':
          content.innerHTML = loadReportContent();
          break;
        case 'um_LTIA':
          content.innerHTML = loadLTIAContent();
          break;
        case 'um_userlogs':
          content.innerHTML = loadUserlogContent();
          break;
        case 'um_settings':
          content.innerHTML = loadSettingContent();
          break;
      }
    }

    window.onload = function() {
      const content = document.getElementById('content');
      content.innerHTML = loadRegistrationLoginContent(); // Initial page load content
    };

    // Add active styles to the "Registration/Login" tab on first load
    const registrationTab = document.getElementById('um_registration');
    registrationTab.classList.add('bg-blue-100', 'text-black', 'rounded-tl-lg', 'rounded-tr-lg');
  </script>


</body>

</html>