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
  $stmt = $conn->prepare("SELECT u.id, u.municipality_id, u.first_name, u.last_name, u.contact_number, u.email, m.municipality_name, b.barangay_name
  FROM users u
  INNER JOIN municipalities m ON u.municipality_id = m.id
  INNER JOIN barangays b ON u.barangay_id = b.id
  WHERE u.user_type = 'user' AND u.id = :user_id");
  $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    // User not found, handle this case
    header("Location: admin_acc_req.php");
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

    header("Location: admin_manage_acc_req.php?user_id=" . urlencode($userId) . "&manage_brgy_message=success");
    exit();
  } else {

    header("Location: admin_manage_acc_req.php?user_id=" . urlencode($userId) . "&manage_brgy_message=error");
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
  <link rel="shortcut icon" type="image/png" href=".assets/images/logos/favicon.png" />
 
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
      <div class="card">
        <div class="card-body">

          <div class="d-flex align-items-center">
            <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
            <div>
              <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>
          <br>

          <h5 class="card-title mb-9 fw-semibold">User Manage Account</h5>
          <hr>
          <br>
          <b>

            <?php
            if (isset($_GET['manage_brgy_message'])) {
              if ($_GET['manage_brgy_message'] === 'success') {
                echo "<div id='alertMessage' class='alert alert-success' role='alert'>Updated successfully.</div>";
              }
              if ($_GET['manage_brgy_message'] === 'error') {
                echo "<div id='alertMessage' class='alert alert-danger' role='alert'>Failed to update.</div>";
              }
            }
            ?>


            <form method="post">
              <!-- Display user information in form fields -->
              <div class="form-group">
                <label for="municipality_name">Municipality Name:</label>
                <input type="text" class="form-control" id="municipality_name" name="municipality_name" value="<?php echo $user['municipality_name']; ?>" readonly>
              </div>
              <div class="form-group">
                <label for="barangay_name">Barangay Name:</label>
                <input type="text" class="form-control" id="barangay_name" name="barangay_name" value="<?php echo $user['barangay_name']; ?>" readonly>
              </div>
              <div class="form-group">
                <label>First Name:</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
              </div>
              <div class="form-group">
                <label>Last Name:</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
              </div>
              <div class="form-group">
                <label>Contact Number:</label>
                <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo $user['contact_number']; ?>" required>
              </div>
              <div class="form-group">
                <label>Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
              </div>
              <div class="form-group">
                <label>New Password (leave empty to keep current password):</label>
                <!-- <input type="password" class="form-control" id="new_password" name="new_password"> -->

                <div class="input-group">
                  <input type="password" class="form-control" name="new_password" id="new_password">

                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="toggle-new-password"><i class="fas fa-eye"></i></button>
                  </div>
                </div>

              </div><br>
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