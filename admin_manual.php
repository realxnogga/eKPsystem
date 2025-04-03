<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
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

  <?php include "admin_sidebar_header.php"; ?>

  <div class="p-0 sm:p-6 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <div class="bg-white h-fit w-full rounded-md p-4">

        <h2 class="text-[2rem] font-thin">USER MANUAL</h2>

        <br>
        <br>

        <nav class="bg-white overflow-x-auto z-30">
          <ul class="flex">
            <li id="am_registration" onclick="navigateTo('am_registration')" class="whitespace-nowrap menu-item hover:rounded-tr-md hover:rounded-tl-md border-1 border-white hover:bg-blue-100 px-4 py-2 cursor-pointer text-nowrap">Registration/Login</li>
            <li id="am_secretarys_corner" onclick="navigateTo('am_secretarys_corner')" class="whitespace-nowrap menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer text-nowrap">Secretary's Corner</li>
            <li id="am_account_request" onclick="navigateTo('am_account_request')" class="whitespace-nowrap menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer text-nowrap">Account Request</li>
            <li id="am_LTIA" onclick="navigateTo('am_LTIA')" class="whitespace-nowrap menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer text-nowrap">LTIA</li>
            <li id="am_assessor_request" onclick="navigateTo('am_assessor_request')" class="whitespace-nowrap menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer text-nowrap">Assessor Request</li>
            <li id="am_setting" onclick="navigateTo('am_setting')" class="whitespace-nowrap menu-item hover:rounded-tr-md hover:rounded-tl-md hover:bg-blue-100 px-4 py-2 cursor-pointer text-nowrap">Setting</li>
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

  <button id="goToTop" class="fixed bottom-4 right-4 p-3 bg-blue-600 text-white rounded-full hidden">
    ↑ Top
  </button>

  <script src="user_um_reglogin.js"></script>
  <script src="admin_am_functions.js"></script>

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
        case 'am_registration':
          content.innerHTML = loadRegistrationLoginContent();
          break;
        case 'am_secretarys_corner':
          content.innerHTML = loadSecretarysCornerContent();
          break;
        case 'am_account_request':
          content.innerHTML = loadAccountRequestContent();
          break;
        case 'am_LTIA':
          content.innerHTML = loadLTIAContent();
          break;
        case 'am_assessor_request':
          content.innerHTML = loadAssessorRequestContent();
          break;
        case 'am_setting':
          content.innerHTML = loadSettingContent();
          break;
      }
    }

    window.onload = function() {
      const content = document.getElementById('content');
      content.innerHTML = loadRegistrationLoginContent(); // Initial page load content

      // Add active styles to the "Registration/Login" tab on first load
      const registrationTab = document.getElementById('am_registration');
      registrationTab.classList.add('bg-blue-100', 'text-black', 'rounded-tl-lg', 'rounded-tr-lg');
    };
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const goToTopButton = document.getElementById("goToTop");

      // Show the button when the user scrolls down
      window.onscroll = () => {
        if (document.documentElement.scrollTop > 200) {
          goToTopButton.classList.remove("hidden");
        } else {
          goToTopButton.classList.add("hidden");
        }
      };

      // Scroll to the top when the button is clicked
      goToTopButton.onclick = () => {
        window.scrollTo({
          top: 0,
          behavior: "smooth"
        });
      };
    });
  </script>

</body>

</html>