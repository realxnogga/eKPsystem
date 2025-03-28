<?php
session_start();

include 'connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'];

include 'lupon_handler.php';

if (!isset($_SESSION['language'])) {
  $_SESSION['language'] = 'english'; // Set default language to English
}

$folderName =  ($_SESSION['language'] === 'english') ? 'forms_english' : 'forms_tagalog';

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lupon</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script src="node_modules/jquery/dist/jquery.min.js"></script>
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

  <div class="sm:ml-44 sm:p-6">
    <div class="rounded-lg mt-16">

      <!-- First Card -->
      <div class="bg-white shadow-md rounded-lg">
        <div class="flex flex-col gap-y-3 p-6">

          <div class="flex items-center">
            <img src="img/cluster.png" alt="Logo" class="w-24 h-24 mr-4">
            <h5 class="text-xl font-semibold">Department of the Interior and Local Government</h5>
          </div>

          <h5 class="text-2xl font-semibold text-center sm:text-left"><?php echo ucfirst($_SESSION['language']); ?> Forms</h5>

          <div class="text-center sm:text-left">
            <!-- Language Selection Buttons -->
            <button type="button" onclick="setLanguage('english')" class="<?php echo ($_SESSION['language'] === 'english') ? 'bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded-md text-white' : 'bg-gray-300 hover:bg-gray-200 px-4 py-2 rounded-md text-black'; ?> m-1">English</button>

            <button type="button" onclick="setLanguage('tagalog')" class="<?php echo ($_SESSION['language'] === 'tagalog') ? 'bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded-md text-white' : 'bg-gray-300 hover:bg-gray-200 px-4 py-2 rounded-md text-black'; ?> m-1">Tagalog</button>
          </div>

          <script>
            function setLanguage(language) {
              $.ajax({
                url: 'update_language.php',
                method: 'POST',
                data: {
                  language: language
                },
                success: function(response) {
                  location.reload();
                },
                error: function(xhr, status, error) {
                  console.error(error);
                }
              });
            }
          </script>

          <div class="flex w-full flex-row overflow-x-auto gap-2">
            <?php
            $formButtons = [
              'KP 1' => 'kp_form1.php',
              'KP 2' => 'kp_form2.php',
              'KP 3' => 'kp_form3.php',
              'KP 4' => 'kp_form4.php',
              'KP 5' => 'kp_form5.php',
              'KP 6' => 'kp_form6.php',
            ];

            foreach ($formButtons as $buttonText => $formFileName) {
              $languageFolder = ($_SESSION['language'] === 'english') ? 'forms_english/' : 'forms_tagalog/';
              $formPath = $languageFolder . $formFileName;

              echo '<a href="' . $formPath . '" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-400 text-nowrap">' . $buttonText . '</a>';
            }
            ?>
          </div>
        </div>
        <a href="user_used_forms.php" class="block bg-gray-900 text-white text-center py-2 sm:rounded-b-lg mt-4">Used Forms</a>
      </div>

      <!-- Second Card -->
      <div class="bg-white shadow-md rounded-lg p-6 mt-6">
        <h5 class="text-xl font-semibold mb-4">Lupon</h5>
        <form method="post">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            for ($i = 1; $i <= 20; $i++) {
              $iFormatted = str_pad($i, 2, '0', STR_PAD_LEFT); // Add leading 0
              $nameKey = "name$i";
              $nameValue = $linkedNames[$nameKey] ?? '';
              echo "<div>
                      <label class='block text-sm font-medium text-gray-700'>$iFormatted</label>
                      <input type='text' name='linked_name[]' class='mt-1 block w-full p-2 border border-gray-300 rounded-md' value='$nameValue' placeholder='Name $iFormatted'>
                    </div>";
            }
            ?>
          </div>

          <div class="mt-4">
            <label for="punong_barangay" class="block text-sm font-medium text-gray-700">Punong Barangay:</label>
            <input type="text" name="punong_barangay" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" value="<?= strtoupper($linkedNames['punong_barangay'] ?? '') ?>">

            <label for="lupon_chairman" class="block text-sm font-medium text-gray-700 mt-4">Lupon Chairman:</label>
            <input type="text" name="lupon_chairman" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" value="<?= strtoupper($linkedNames['lupon_chairman'] ?? '') ?>">
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-6">
            <button type="submit" name="save" class="text-nowrap px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-400">Appoint</button>
            <button type="submit" name="appoint" class="text-nowrap px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-400">Notice</button>
            <button type="button" name="clear" class="text-nowrap px-4 py-2 bg-gray-300 text-black rounded-md hover:bg-gray-200">Clear All</button>
            <a href="user_uploadfile_lupon.php" class="text-center text-nowrap px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-400"><i class="ti ti-upload"></i> Upload</a>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>

</html>