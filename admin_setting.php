<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$usertype = $_SESSION['user_type'];

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch the user's security questions from the database
$stmt = $conn->prepare("SELECT question1, question2, question3 FROM security WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
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

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Setting</title>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

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

</head>

<body class="bg-[#E8E8E7]">

  <?php include "admin_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <!--  Row 1 -->
      <div class="row">
        <div class="col-lg-4 d-flex align-items-strech">
          <div class="card w-100">
            <div class="card-body">
              <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                <div class="mb-3 mb-sm-0">

                  <div class="d-flex align-items-center" class="prof-container">
                    <img src="profile_pictures/<?php echo $user['profile_picture'] ?: 'defaultpic.jpg'; ?>?t=<?php echo time(); ?>" alt="" class="d-block ui-w-80" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
                    <input type="file" id="fileInput" name="profile_pic" style="display: none;">
                    <!--<h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>-->

                    <!-- <button type="button" id="uploadButton" class="bg-blue-500 hover:bg-blue-4  00 px-3 py-2 rounded-md text-white">Upload a picture</button> -->

                    <a href="crop_profile_pic.php">
                      <button class="bg-blue-500 hover:bg-blue-400 px-3 py-2 rounded-md text-white">
                        Upload a picture
                      </button>
                    </a>


                    <div>

                    </div>
                  </div>
                  <br>

                  <h5 class="card-title mb-9 fw-semibold">Account Settings</h5>
                  <hr>
                  <br>
                  <b>
                    <?php

                    if (isset($_GET['update_account_message'])) {
                      if ($_GET['update_account_message'] === 'success') {
                        echo "<div id='alertMessage' class='alert alert-success' role='alert'>Updated successfully.</div>";
                      }
                      if ($_GET['update_account_message'] === 'emailalreadyinuse') {
                        echo "<div id='alertMessage' class='alert alert-danger' role='alert'>Email already in use.</div>";
                      }
                      if ($_GET['update_account_message'] === 'passwordeightlong') {
                        echo "<div id='alertMessage' class='alert alert-danger' role='alert'>Password should be at least 8 characters long.</div>";
                      }
                    }

                    ?>

                    <form id="userSettingsForm" method="post" action="general_handler.php">
                      <div class="form-group">
                        <label for="first_name">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>">
                      </div>

                      <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>">
                      </div>
                      <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>">
                      </div>
                      <div class="form-group">
                        <label for="contact_number">Contact Number:</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo $user['contact_number']; ?>">
                      </div>
                      <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>">
                      </div>
                      <div class="form-group">
                        <label for="new_password">New Password (Leave empty to keep current password):</label>

                        <!-- <input type="password" class="form-control" title="Password must be at least 8 characters long" id="new_password" name="new_password" placeholder=""> -->

                        <div class="input-group">
                          <input type="password" class="form-control" name="new_password" id="new_password" title="Password must be at least 8 characters long">

                          <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="toggle-new-password"><i class="fas fa-eye"></i></button>
                          </div>
                        </div>


                      </div> <input type="hidden" name="active_tab" value="general"><br>
                      <button type="submit" name="general_settings" class="bg-green-500 hover:bg-green-400 px-3 py-2 rounded-md text-white">Save Changes</button>
                    </form>

                </div>
              </div>

            </div>
          </div>
        </div></b>

        <div class="col-lg-4">
          <div class="row">
            <div class="col-lg-12">
              <!-- Yearly Breakup -->
              <div class="card overflow-hidden">
                <div class="card-body p-4">
                  <h5 class="card-title mb-9 fw-semibold">Update Security Settings</h5>
                  <hr>
                  <br>

                  <form id="securityForm" method="post" action="security_handler.php">
                    <div class="tab-pane fade <?php echo !isset($_POST['security_settings']) ? 'active show' : ''; ?>" id="account-security">
                      <b>
                        <h6>

                          <?php
                          if (isset($_GET['update_securityquestion_message'])) {
                            if ($_GET['update_securityquestion_message'] === 'SQupdatedsuccessfully') {
                              echo "<div id='alertMessage' class='alert alert-success' role='alert'>Security answer updated successfully.</div>";
                            }
                            if ($_GET['update_securityquestion_message'] === 'SQupdatederror') {
                              echo "<div id='alertMessage' class='alert alert-danger' role='alert'>Updating security answer failed.</div>";
                            }
                          }
                          ?>

                        </h6>

                        <div class="form-group">
                          <label for="question1">Security Question 1:</label>
                          <select class="form-control" id="question1" name="question1" required>
                            <option value="" <?php echo ($question1 == '') ? 'selected' : ''; ?>>Select a Question</option>
                            <option value="1" <?php echo ($question1 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                            <option value="2" <?php echo ($question1 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                            <option value="3" <?php echo ($question1 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                            <option value="4" <?php echo ($question1 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
                          </select>

                          <label for="answer1">Answer:</label>
                          <input type="password" class="form-control" id="answer1" name="answer1" required>
                        </div>
                        <div class="form-group">
                          <label for="question1">Security Question 2:</label>

                          <select class="form-control" id="question2" name="question2" required>
                            <option value="" <?php echo ($question2 == '') ? 'selected' : ''; ?>>Select a Question</option>
                            <option value="1" <?php echo ($question2 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                            <option value="2" <?php echo ($question2 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                            <option value="3" <?php echo ($question2 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                            <option value="4" <?php echo ($question2 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
                          </select>

                          <label for="answer2">Answer:</label>
                          <input type="password" class="form-control" id="answer2" name="answer2" required>
                        </div>
                        <div class="form-group">
                          <label for="question3">Security Question 3:</label>
                          <select class="form-control" id="question3" name="question3" required>
                            <option value="" <?php echo ($question3 == '') ? 'selected' : ''; ?>>Select a Question</option>

                            <option value="1" <?php echo ($question3 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                            <option value="2" <?php echo ($question3 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                            <option value="3" <?php echo ($question3 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                            <option value="4" <?php echo ($question3 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
                          </select>

                          <label for="answer3">Answer:</label>
                          <input type="password" class="form-control" id="answer3" name="answer3" required>
                        </div><br>
                        <button type="submit" class="bg-green-500 hover:bg-green-400 px-3 py-2 rounded-md text-white" name="security_settings">Save Security Settings</button>
                        <input type="hidden" name="active_tab" value="security">
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
          const fileInput = document.getElementById('fileInput');
          const uploadButton = document.getElementById('uploadButton');
          const profilePic = document.getElementById('profilePic');
          const activeTab = sessionStorage.getItem('activeTab');

          // Handle button click to trigger file input
          uploadButton.addEventListener('click', function() {
            fileInput.click();
          });

          // Handle file input change
          fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
              const reader = new FileReader();
              reader.onload = function(e) {
                profilePic.setAttribute('src', e.target.result);
              };
              reader.readAsDataURL(file);

              // Upload the file using Fetch API
              const formData = new FormData();
              formData.append('profile_pic', file);

              fetch('upload_pic.php', {
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
                  // Handle the response
                  console.log('Upload successful:', data);
                })
                .catch(error => {
                  console.error('Error:', error);
                });
            }
          });


          if (activeTab) {
            $(".account-settings-links a[href='" + activeTab + "']").addClass("active");
            $(".tab-pane").removeClass("active show");
            $(activeTab).addClass("active show");
          }

          $(".account-settings-links a").click(function(e) {
            e.preventDefault();
            $(".account-settings-links a").removeClass("active");
            $(this).addClass("active");
            $(".tab-pane").removeClass("active show").empty(); // Empty the content of unselected tabs
            $($(this).attr("href")).addClass("active show");

            sessionStorage.setItem('activeTab', $(this).attr("href"));
          });

        });
      </script> -->

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