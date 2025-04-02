<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the user's security questions from the database
$stmt = $conn->prepare("SELECT question1, question2, question3 FROM security WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
$stmt->execute();
$securityQuestions = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if security questions exist for the user
if ($securityQuestions) {
  $question1 = $securityQuestions['question1'];
  $question2 = $securityQuestions['question2'];
  $question3 = $securityQuestions['question3'];
} else {
  // Set empty values if no questions are found
  $question1 = '';
  $question2 = '';
  $question3 = '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Check if LGU logo is uploaded
  if (isset($_FILES['lgulogo']) && $_FILES['lgulogo']['error'] === UPLOAD_ERR_OK) {
    // Get the uploaded LGU logo information
    $lgulogo_name = $_FILES['lgulogo']['name'];
    $lgulogo_tmp_name = $_FILES['lgulogo']['tmp_name'];

    // Move the uploaded LGU logo to the "lgu_logo" folder
    move_uploaded_file($lgulogo_tmp_name, 'lgu_logo/' . $lgulogo_name);

    // Update the LGU logo filename in the database for the current user
    $stmt = $conn->prepare("UPDATE users SET lgu_logo = :lgulogo_name WHERE id = :user_id");
    $stmt->bindParam(':lgulogo_name', $lgulogo_name);
    $stmt->bindParam(':user_id', $userID);
    $stmt->execute();

    // Optionally, you can show a success message
    echo "LGU Logo saved successfully!";
  }

  // Check if KP logo is uploaded
  if (isset($_FILES['kplogo']) && $_FILES['kplogo']['error'] === UPLOAD_ERR_OK) {
    // Get the uploaded KP logo information
    $kplogo_name = $_FILES['kplogo']['name'];
    $kplogo_tmp_name = $_FILES['kplogo']['tmp_name'];

    // Move the uploaded KP logo to the "city_logo" folder
    move_uploaded_file($kplogo_tmp_name, 'city_logo/' . $kplogo_name);

    // Update the KP logo filename in the database for the current user
    $stmt = $conn->prepare("UPDATE users SET city_logo = :kplogo_name WHERE id = :user_id");
    $stmt->bindParam(':kplogo_name', $kplogo_name);
    $stmt->bindParam(':user_id', $userID);
    $stmt->execute();

    // Optionally, you can show a success message
    echo "KP Logo saved successfully!";
  }
}

function uploadFile($file, $directory)
{
  $allowed_types = array('image/jpeg', 'image/png');
  $max_size = 5 * 1024 * 1024; // 5MB

  if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
    $filename = uniqid() . '_' . $file['name'];
    $destination = $directory . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
      return $filename;
    }
  }

  return false;
}

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Settings</title>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <!-- <link rel="stylesheet" href="assets/css/styles.min.css" /> -->

  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

  <script src="node_modules/jquery/dist/jquery.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <!-- tailwind cdn -->


  <!-- tailwind cdn -->
<link rel="stylesheet" href="output.css">

  <style>
    /* Hide the reveal password button in Internet Explorer */
    input[type='password']::-ms-reveal {
      display: none;
    }

    /* Adjust the button height to match the input field */
    .input-group-append .btn {
      height: calc(2.0em + .55rem + 6px);
      /* Adjust the calc() as needed to match your input height */
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
    }

    /* Vertically center the eye icon within the button */
    .input-group-append i {
      vertical-align: middle;
    }
  </style>

  <!-- 
  <style>
    .card {
      box-shadow: 0 0 0.3cm rgba(0, 0, 0, 0.2);
      border-radius: 15px;
    }
  </style> -->

</head>

<body class="sm:bg-gray-200 bg-white">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">

      <!-- Row 1 -->
      <!-- filepath: /c:/xampp/htdocs/eKPsystem/user_setting.php -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Settings -->
        <div class="bg-white shadow-none sm:shadow-md rounded-lg p-6 h-fit">
          <div class="flex items-center space-x-4">
            <img
              src="profile_pictures/<?php echo $user['profile_picture'] ?: 'defaultpic.jpg'; ?>?t=<?php echo time(); ?>"
              alt=""
              class="w-24 h-24 object-contain">
            <input type="file" id="fileInput" name="profile_pic" class="hidden">
            <a href="crop_profile_pic.php">
              <button class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-md">
                Upload a picture
              </button>
            </a>
          </div>
          <h5 class="text-lg font-semibold mt-6">Account Settings</h5>
          <hr class="my-4">
          <?php if (isset($_GET['update_account_message'])): ?>
            <div id="alertMessage" class="alert <?php echo $_GET['update_account_message'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> p-4 rounded-md">
              <?php
              if ($_GET['update_account_message'] === 'success') echo "Updated successfully.";
              if ($_GET['update_account_message'] === 'emailalreadyinuse') echo "Email already in use.";
              if ($_GET['update_account_message'] === 'passwordeightlong') echo "Password should be at least 8 characters long.";
              ?>
            </div>
          <?php endif; ?>
          <form id="userSettingsForm" method="post" action="general_handler.php" class="space-y-1">
            <div>
              <label for="username" class="block text-sm font-medium">Username:</label>
              <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" class="w-full border rounded-md p-2">
            </div>
            <div>
              <label for="first_name" class="block text-sm font-medium">First Name:</label>
              <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" class="w-full border rounded-md p-2">
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium">Last Name:</label>
              <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" class="w-full border rounded-md p-2">
            </div>
            <div>
              <label for="contact_number" class="block text-sm font-medium">Contact Number:</label>
              <input type="text" id="contact_number" name="contact_number" value="<?php echo $user['contact_number']; ?>" class="w-full border rounded-md p-2">
            </div>
            <div>
              <label for="email" class="block text-sm font-medium">Email:</label>
              <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="w-full border rounded-md p-2">
            </div>
            <div>
              <label for="new_password" class="block text-sm font-medium">New Password (Leave empty to keep current password):</label>
              <div class="relative">
                <input type="password" id="new_password" name="new_password" class="w-full border rounded-md p-2">
                <button type="button" id="toggle-new-password" class="absolute right-2 top-2 text-gray-500">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <br>
            <div>
              <input type="hidden" name="active_tab" value="general">
              <button type="submit" name="general_settings" class="bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-md">
                Save Changes
              </button>
            </div>

          </form>
        </div>

        <!-- Security Settings -->
        <div class="bg-white shadow-none sm:shadow-md rounded-lg p-6 h-fit">
          <h5 class="text-lg font-semibold">Update Security Settings</h5>
          <hr class="my-4">
          <?php if (isset($_GET['update_securityquestion_message'])): ?>
            <div id="alertMessage" class="alert <?php echo $_GET['update_securityquestion_message'] === 'SQupdatedsuccessfully' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> p-4 rounded-md">
              <?php
              if ($_GET['update_securityquestion_message'] === 'SQupdatedsuccessfully') echo "Security answer updated successfully.";
              if ($_GET['update_securityquestion_message'] === 'SQupdatederror') echo "Updating security answer failed.";
              ?>
            </div>
          <?php endif; ?>
          <form id="securityForm" method="post" action="security_handler.php" class="space-y-6">
            <div>
              <label for="question1" class="block text-sm font-medium">Security Question 1:</label>
              <select id="question1" name="question1" class="w-full border rounded-md p-2" required>
                <option value="" <?php echo ($question1 == '') ? 'selected' : ''; ?>>Select a Question</option>
                <option value="1" <?php echo ($question1 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                <option value="2" <?php echo ($question1 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                <option value="3" <?php echo ($question1 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                <option value="4" <?php echo ($question1 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
              </select>
              <label for="answer1" class="block text-sm font-medium">Answer:</label>
              <input type="password" id="answer1" name="answer1" class="w-full border rounded-md p-2" required>
            </div>
            <div>
              <label for="question2" class="block text-sm font-medium">Security Question 2:</label>
              <select id="question2" name="question2" class="w-full border rounded-md p-2" required>
                <option value="" <?php echo ($question2 == '') ? 'selected' : ''; ?>>Select a Question</option>
                <option value="1" <?php echo ($question2 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                <option value="2" <?php echo ($question2 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                <option value="3" <?php echo ($question2 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                <option value="4" <?php echo ($question2 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
              </select>
              <label for="answer2" class="block text-sm font-medium">Answer:</label>
              <input type="password" id="answer2" name="answer2" class="w-full border rounded-md p-2" required>
            </div>
            <div>
              <label for="question3" class="block text-sm font-medium">Security Question 3:</label>
              <select id="question3" name="question3" class="w-full border rounded-md p-2" required>
                <option value="" <?php echo ($question3 == '') ? 'selected' : ''; ?>>Select a Question</option>
                <option value="1" <?php echo ($question3 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                <option value="2" <?php echo ($question3 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                <option value="3" <?php echo ($question3 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                <option value="4" <?php echo ($question3 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
              </select>
              <label for="answer3" class="block text-sm font-medium">Answer:</label>
              <input type="password" id="answer3" name="answer3" class="w-full border rounded-md p-2" required>
            </div>

            <div>
              <input type="hidden" name="active_tab" value="security">
              <button type="submit" name="security_settings" class="bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-md">
                Save Security Settings
              </button>
            </div>


          </form>
        </div>

        <!-- Logo Upload -->
        <div class="space-y-6">
          <form id="lgulogoForm" enctype="multipart/form-data" action="user_setting.php" method="POST" class="bg-white shadow-none sm:shadow-md rounded-lg p-6">
            <h5 class="text-lg font-semibold">Upload LGU Logo</h5>
            <hr class="my-4">
            <div class="flex items-center space-x-4">
              <input type="file" id="lgulogoInput" name="lgulogo" class="hidden">
              <img id="lgulogoPreview" src="lgu_logo/<?php echo $user['lgu_logo'] ?: 'defaultpic.jpg'; ?>" alt="LGU Logo" class="w-24 h-24 object-cover">
              <button type="button" id="uploadLGUButton" class="bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-md">
                Upload
              </button>
              <button type="submit" id="saveLGUButton" class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-md">
                Save
              </button>
            </div>
          </form>
          <form id="kplogoForm" enctype="multipart/form-data" action="user_setting.php" method="POST" class="bg-white shadow-md rounded-lg p-6">
            <h5 class="text-lg font-semibold">Upload KP Logo</h5>
            <hr class="my-4">
            <div class="flex items-center space-x-4">
              <input type="file" id="kplogoInput" name="kplogo" class="hidden">
              <img id="kplogoPreview" src="city_logo/<?php echo $user['city_logo'] ?: 'defaultpic.jpg'; ?>" alt="KP Logo" class="w-24 h-24 object-cover">
              <button type="button" id="uploadKPButton" class="bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-md">
                Upload
              </button>
              <button type="submit" id="saveKPButton" class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-md">
                Save
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {

        // Profile Picture upload
        // const profileFileInput = document.getElementById('fileInput');
        // const profileUploadButton = document.getElementById('uploadButton');
        // const profilePic = document.getElementById('profilePic');

        // profileUploadButton.addEventListener('click', function() {
        //   profileFileInput.click();
        // });

        // profileFileInput.addEventListener('change', function() {
        //   const file = this.files[0];
        //   if (file) {
        //     const reader = new FileReader();

        //     reader.onload = function(e) {
        //       profilePic.setAttribute('src', e.target.result);
        //     };
        //     reader.readAsDataURL(file);

        //     const formData = new FormData();
        //     formData.append('profile_pic', file);

        //     fetch('upload_pic.php', {
        //         method: 'POST',
        //         body: formData
        //       })
        //       .then(response => {
        //         if (!response.ok) {
        //           throw new Error('Network response was not ok.');
        //         }
        //         return response.text();
        //       })
        //       .then(data => {
        //         console.log('Profile Picture Upload successful:', data);
        //       })
        //       .catch(error => {
        //         console.error('Profile Picture Upload Error:', error);
        //       });
        //   }
        // });

        // LGU Logo upload
        const lguFileInput = document.getElementById('lgulogoInput');
        const lguUploadButton = document.getElementById('uploadLGUButton');
        const lguSaveButton = document.getElementById('saveLGUButton');
        const lguLogoPreview = document.getElementById('lgulogoPreview');

        lguUploadButton.addEventListener('click', function() {
          lguFileInput.click();
        });

        lguFileInput.addEventListener('change', function() {
          const file = this.files[0];
          if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
              lguLogoPreview.setAttribute('src', e.target.result);
            };
            reader.readAsDataURL(file);
          }
        });

        lguSaveButton.addEventListener('click', function() {
          const file = lguFileInput.files[0];
          if (file) {
            const formData = new FormData();
            formData.append('lgulogo', file);

            fetch('user_setting.php', {
                method: 'POST',
                body: formData
              })
              .then(response => {
                if (!response.ok) {
                  throw new Error('Network response was not ok.');
                }
                return response.text();
              })
              .then(data => {
                console.log('LGU Logo Save successful:', data);
                // Scroll to the "#account-general" section
                document.querySelector("#account-general").scrollIntoView();
              })
              .catch(error => {
                console.error('LGU Logo Save Error:', error);
              });
          }
        });

        // KP Logo upload
        const kpFileInput = document.getElementById('kplogoInput');
        const kpUploadButton = document.getElementById('uploadKPButton');
        const kpSaveButton = document.getElementById('saveKPButton');
        const kpLogoPreview = document.getElementById('kplogoPreview');

        kpUploadButton.addEventListener('click', function() {
          kpFileInput.click();
        });

        kpFileInput.addEventListener('change', function() {
          const file = this.files[0];
          if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
              kpLogoPreview.setAttribute('src', e.target.result);
            };
            reader.readAsDataURL(file);
          }
        });

        kpSaveButton.addEventListener('click', function() {
          const file = kpFileInput.files[0];
          if (file) {
            const formData = new FormData();
            formData.append('kplogo', file);

            fetch('user_setting.php', {
                method: 'POST',
                body: formData
              })
              .then(response => {
                if (!response.ok) {
                  throw new Error('Network response was not ok.');
                }
                return response.text();
              })
              .then(data => {
                console.log('KP Logo Save successful:', data);
                // Scroll to the "#account-general" section
                document.querySelector("#account-general").scrollIntoView();
              })
              .catch(error => {
                console.error('KP Logo Save Error:', error);
              });
          }
        });
      });
    </script>

    <script>
      const toggleButton = document.getElementById('toggleButton');
      const infoMessage = document.getElementById('info_message');

      toggleButton.addEventListener('click', () => {
        infoMessage.classList.toggle('hidden');
      });
    </script>

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

<script src="https://cdn.tailwindcss.com"></script>
</body>

</html>