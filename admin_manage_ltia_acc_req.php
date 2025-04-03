<?php
session_start();
include 'connection.php';
//include 'admin-navigation.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Check if the user_id parameter is set in the URL
if (isset($_GET['user_id'])) {
  $userId = $_GET['user_id'];



  // Fetch user data based on the provided user_id
  $stmt = $conn->prepare("
    SELECT u.id, u.municipality_id, u.first_name, u.last_name, u.contact_number, u.email, m.municipality_name
    FROM users u
    INNER JOIN municipalities m ON u.municipality_id = m.id
    WHERE u.user_type = 'assessor' AND u.id = :user_id
");
  $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);


  if (!$user) {
    // User not found, handle this case
    header("Location: admin_ltia_assessor_req.php");
    exit;
  }
} else {
  // user_id is not provided in the URL, handle this case
  header("Location: admin_dashboard.php");
  exit;
}

// Process form submissions for updating user data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Retrieve and sanitize form data
  $newFirstName = $_POST['first_name'];
  $newLastName = $_POST['last_name'];
  $newContactNumber = $_POST['contact_number'];
  $newEmail = $_POST['email'];

  // Check if a new password is provided
  if (!empty($_POST['new_password'])) {
    $newPassword = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Perform SQL update to save the changes, including the new password
    $updateStmt = $conn->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, contact_number = :contact_number, email = :email, password = :password WHERE id = :user_id");
    $updateStmt->bindParam(':password', $newPassword, PDO::PARAM_STR); // Bind the new password
  } else {
    // Perform SQL update without changing the password
    $updateStmt = $conn->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, contact_number = :contact_number, email = :email WHERE id = :user_id");
  }

  $updateStmt->bindParam(':first_name', $newFirstName, PDO::PARAM_STR);
  $updateStmt->bindParam(':last_name', $newLastName, PDO::PARAM_STR);
  $updateStmt->bindParam(':contact_number', $newContactNumber, PDO::PARAM_STR);
  $updateStmt->bindParam(':email', $newEmail, PDO::PARAM_STR);
  $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

  if ($updateStmt->execute()) {

    header("Location: admin_manage_ltia_acc_req.php?user_id=" . urlencode($userId) . "&manage_assessor_message=success");
    exit();
  } else {

    header("Location: admin_manage_ltia_acc_req.php?user_id=" . urlencode($userId) . "&manage_assessor_message=error");
    exit();
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Manage Account</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <script src="node_modules/jquery/dist/jquery.min.js"></script>

  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />

  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">

  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

  <link href="output.css" rel="stylesheet">

  <link rel="stylesheet" href="hide_show_icon.css">

</head>

<body class="bg-gray-200">

  <?php include "admin_sidebar_header.php"; ?>

  <div class="p-4 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16 bg-white shadow-md">

      <!-- Row 1 -->
      <div class="bg-white shadow-md rounded-lg">
        <div class="p-6">

          <div class="flex items-center mb-6">
            <img src="img/cluster.png" alt="Logo" class="w-24 h-24 mr-4">
            <div>
              <h5 class="text-lg font-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>


          <h5 class="text-lg font-semibold mb-4">LTIA Manage Account</h5>
          <hr class="mb-6">

          <?php
          if (isset($_GET['manage_assessor_message'])) {
            if ($_GET['manage_assessor_message'] === 'success') {
              echo "<div id='alertMessage' class='bg-green-100 text-green-700 p-4 rounded-md' role='alert'>Updated successfully.</div>";
            }
            if ($_GET['manage_assessor_message'] === 'error') {
              echo "<div id='alertMessage' class='bg-red-100 text-red-700 p-4 rounded-md' role='alert'>Failed to update.</div>";
            }
          }
          ?>


          <form method="post">
            <div class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 mb-1" for="municipality_name">Municipality Name:</label>
              <input type="text" class="border rounded-md p-2" id="municipality_name" name="municipality_name" value="<?php echo $user['municipality_name']; ?>" readonly>
            </div>
            <div class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name:</label>
              <input type="text" class="border rounded-md p-2" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
            </div>
            <div class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name:</label>
              <input type="text" class="border rounded-md p-2" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
            </div>
            <div class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number:</label>
              <input type="text" class="border rounded-md p-2" id="contact_number" name="contact_number" value="<?php echo $user['contact_number']; ?>" required>
            </div>
            <div class="flex flex-col">
              <label class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
              <input type="email" class="border rounded-md p-2" id="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>

            <div class="flex flex-col space-y-2">
              <label class="text-sm font-medium text-gray-700">New Password (leave empty to keep current password):</label>
              <div class="relative">
                <input type="password" class="border rounded-md p-2 w-full" name="new_password" id="new_password">
                <button type="button" id="toggle-new-password" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <br>

            <button type="submit" class="bg-green-500 hover:bg-green-400 px-3 py-2 rounded-md text-white">Save Changes</button>
          </form>
          </b>
        </div>
      </div>
    </div>
  </div>

  <script src="hide_toast.js"></script>

  <script>
    $(document).ready(function() {
      // // Initially disable the toggle button
      // $('#toggle-login-password').prop('disabled', true);

      // // Enable the toggle button only if there is text in the password field
      // $('#login-password').keyup(function() {
      //   $('#toggle-login-password').prop('disabled', this.value === "" ? true : false);
      // });

      // Password toggle for the login password field
      $('#toggle-new-password').click(function() {
        var passwordInput = document.getElementById('new_password');
        var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Selecting the icon within the button that was clicked
        var eyeIcon = $(this).find('i');

        // Toggle the class to change the icon
        eyeIcon.toggleClass('fa-eye fa-eye-slash');
      });
    });
  </script>

</body>

</html>