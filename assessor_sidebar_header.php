<?php
require 'connection.php';
require "include/custom-scrollbar.php";

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$assessor = $stmt->fetch(PDO::FETCH_ASSOC);

function isActive($path)
{
  $currentPage = basename($_SERVER['SCRIPT_NAME']);
  $targetPage = basename($path);
  return $currentPage == $targetPage ? '!bg-blue-400 text-white' : '';
}

function getFullUrl()
{
  $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'];
  $requestUri = $_SERVER['REQUEST_URI'];

  return $scheme . '://' . $host . $requestUri;
}

function containsWord($haystack, $needle)
{
  return strpos($haystack, $needle) !== false;
}

function traverseDirectory()
{
  return containsWord(getFullUrl(), 'LTIA') ? '../' : '';
}

?>

<link rel="stylesheet" href="<?php echo traverseDirectory(); ?>assets/css/styles.min.css" />

<script src="https://cdn.tailwindcss.com"></script>

<script src="<?php echo traverseDirectory(); ?>node_modules/jquery/dist/jquery.min.js"></script>

<script src="<?php echo traverseDirectory(); ?>node_modules/flowbite/dist/flowbite.min.js"></script>
<link href="<?php echo traverseDirectory(); ?>node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />

<!-- tabler icon -->
<link rel="stylesheet" href="<?php echo traverseDirectory(); ?>node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">

<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
  <div class="px-3 py-3 lg:px-5 lg:pl-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center justify-start rtl:justify-end">

        <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
          <span class="sr-only">Open sidebar</span>
          <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
          </svg>
        </button>

        <a href="<?php echo traverseDirectory(); ?>LTIA/assessor_ltia_admin_dashboard.php" class="flex ms-2 md:me-24">
          <p class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">
            EKPsys
          </p>
        </a>

      </div>
      <div class="flex items-center">
        <div class="flex items-center ms-3">

          <div>
            <button type="button" class="flex text-sm rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" aria-expanded="false" data-dropdown-toggle="dropdown-user">
              <span class="sr-only">Open user menu</span>

              <section class="h-8 w-8 border rounded-full overflow-hidden relative">
                <img class="absolute inset-0 object-contain w-full h-full" src="<?php echo traverseDirectory(); ?>profile_pictures/<?php echo $assessor['profile_picture'] ?: 'defaultpic.jpg'; ?>?t=<?php echo time(); ?>" alt="user photo">
              </section>

            </button>
          </div>

          <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow" id="dropdown-user">
            <div class="px-4 py-3" role="none">
              <p class="text-sm text-gray-900 dark:text-white" role="none">
                <?php
                echo $assessor['first_name'];
                echo $assessor['last_name'];
                ?>
              </p>
              <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300" role="none">
                <?php echo $assessor['email']; ?>
              </p>
            </div>
            <ul class="py-1" role="none">
              <li>
                <a href="<?php echo traverseDirectory(); ?>assessor_setting.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Settings</a>
              </li>

              <li>
                <a href="<?php echo traverseDirectory(); ?>logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Sign out</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>

<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-44 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0" aria-label="Sidebar">
  <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">

    <div class="w-full flex flex-col gap-y-1 items-center mb-3">

      <section class="h-20 w-20 border rounded-full overflow-hidden relative">
        <img class="absolute inset-0 object-contain w-full h-full" src="<?php echo traverseDirectory(); ?>profile_pictures/<?php echo $assessor['profile_picture'] ?: 'defaultpic.jpg'; ?>?t=<?php echo time(); ?>" alt="user photo">
      </section>

      <p>
        <?php
        echo $assessor['first_name'] . ' ' . $assessor['last_name'];
        ?>
      </p>

    </div>


    <ul class="font-medium">

      <li>
        <a href="<?php echo traverseDirectory(); ?>LTIA/assessor_ltia_admin_dashboard.php" class="<?php echo isActive('LTIA/assessor_ltia_admin_dashboard.php') . ' ' . isActive('LTIA/assessor_ltia_adminform2evaluate.php'); ?> flex gap-x-2 items-center p-2 rounded-lg hover:bg-gray-100 group">
          <i class="ti ti-certificate-2 text-2xl"></i>
          <span>LTIA Barangay Assessment</span>
        </a>
      </li>

      <li>
        <a href="<?php echo traverseDirectory(); ?>assessor_setting.php" class="<?php echo isActive('assessor_setting.php'); ?> flex gap-x-2 items-center p-2 rounded-lg hover:bg-gray-100 group">
          <i class="ti ti-settings text-2xl"></i>
          <span>Settings</span>
        </a>
      </li>
    </ul>
  </div>
</aside>

<?php include "assessor_security_question_modal.php"; ?>