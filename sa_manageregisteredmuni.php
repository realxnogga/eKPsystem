<?php
session_start();
include 'connection.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}

$error = '';

// Check if the admin user ID is provided in the URL
if (isset($_GET['admin_id'])) {
  $adminId = $_GET['admin_id'];

  // Fetch admin user data based on the provided ID
  $stmt = $conn->prepare("SELECT u.id, u.municipality_id, u.first_name, u.last_name, u.contact_number, u.email, u.password, m.municipality_name FROM users u
                            INNER JOIN municipalities m ON u.municipality_id = m.id
                            WHERE u.user_type = 'admin' AND u.id = :admin_id");
  $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
  $stmt->execute();
  $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$adminUser) {
    // Admin user not found, handle this case
    header("Location: sa_registeredmuni.php");

    exit;
  }
} else {
  // Admin user ID is not provided in the URL, handle this case
  header("Location: sa_registeredmuni.php");

  exit;
}

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
    $updateStmt = $conn->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, contact_number = :contact_number, email = :email, password = :password WHERE id = :admin_id");
    $updateStmt->bindParam(':password', $newPassword, PDO::PARAM_STR);
  } else {
    // Perform SQL update without changing the password
    $updateStmt = $conn->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, contact_number = :contact_number, email = :email WHERE id = :admin_id");
  }

  $updateStmt->bindParam(':first_name', $newFirstName, PDO::PARAM_STR);
  $updateStmt->bindParam(':last_name', $newLastName, PDO::PARAM_STR);
  $updateStmt->bindParam(':contact_number', $newContactNumber, PDO::PARAM_STR);
  $updateStmt->bindParam(':email', $newEmail, PDO::PARAM_STR);
  $updateStmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);

  if ($updateStmt->execute()) {
    // Redirect back to the superadmin dashboard after successful update
    header("Location: sa_manageregisteredmuni.php?admin_id=" . urlencode($adminId) . "&update_muni_message=success");
    exit;
  } else {
    // Handle the case where the update fails

    header("Location: sa_manageregisteredmuni.php?admin_id=" . urlencode($adminId) . "&update_muni_message=error");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Manage Account</title>
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>

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


  <style>
    /* Style for the background color */
    body {
      background-color: #e9ecf3;
      /* Light gray background color */
    }

    .columns-container {
      display: flex;
      justify-content: flex-start;
      /* Aligns children to the start (left side) of the container */
    }

    .left-column {
      width: 100%;
      /* Adjust this as needed */
    }

    .card {
      border-radius: 20px;
      /* Set the radius of the card's corners to 20px */
      width: 40%;
      /* Adjust the width as needed */
      margin-left: 0;
      /* Align the card to the left */
      padding: 20px;
      /* Optional: Add some padding inside the card */
      box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
      /* Optional: Add some shadow for better aesthetics */
    }

    /* Add additional styles for responsive design */
    @media (max-width: 768px) {
      .card {
        width: 90%;
        /* Larger width for smaller screens */
      }
    }

    .form-row {
      display: flex;
      align-items: center;
      /* Aligns items vertically in the center */
      margin-bottom: 10px;
      /* Adds space between each row */
    }

    .form-row label {
      margin-right: 10px;
      /* Adds some space between the label and the input */
      width: 40%;
      /* Adjusts the width of the label */
    }

    .form-row .form-control {
      flex-grow: 1;
      /* Allows the input to take up the remaining space */
    }

    .back-button i {
      margin-right: 5px;
      /* Space between icon and text */
    }

    /* Adjust the left-column layout to align items to the start */
    .left-column {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }
  </style>

</head>

<body class="bg-[#E8E8E7]">
  <?php include "sa_sidebar_header.php"; ?>
  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">

      <div class="columns-container">
        <div class="left-column">

          <div class="card">
            <h4><b>Manage Account</b></h4>
            <br>
            <hr>
            <br>


            <?php
            if (isset($_GET['update_muni_message'])) {
              if ($_GET['update_muni_message'] === 'success') {
                echo '<div id="alertMessage" class="alert alert-success" role="alert">Updated successfully.</div>';
              }
              if ($_GET['update_muni_message'] === 'error') {
                echo '<div id="alertMessage" class="alert alert-danger" role="alert">Failed to update.</div>';
              }
            }
            ?>



            <form method="post">

              <form method="post">
                <div class="form-row">
                  <label for="first_name"><b>First Name:</label></b>
                  <input type="text" placeholder="Enter First Name" class="form-control" id="first_name" name="first_name" value="<?php echo $adminUser['first_name']; ?>" required>
                </div>
                <div class="form-row">
                  <label for="last_name"><b>Last Name:</label></b>
                  <input type="text" placeholder="Enter Last Name" class="form-control" id="last_name" name="last_name" value="<?php echo $adminUser['last_name']; ?>" required>
                </div>
                <div class="form-row">
                  <label for="contact_number"><b>Contact Number:</label></b>
                  <input type="text" placeholder="Enter Contact Number" class="form-control" id="contact_number" name="contact_number" value="<?php echo $adminUser['contact_number']; ?>" required>
                </div>
                <div class="form-row">
                  <label for="email"><b>Email:</label></b>
                  <input type="email" placeholder="Enter Email" class="form-control" id="email" name="email" value="<?php echo $adminUser['email']; ?>" required>
                </div>
                <br>
                <div class="form-row">
                  <label for="new_password"><b>New Password </b>(leave empty to keep current password):</label>

                  <!-- <input type="password" placeholder="Enter New Password" class="form-control" id="new_password" name="new_password"> -->

                  <div class="input-group">
                    <input type="password" class="form-control" name="new_password" id="new_password">

                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" id="toggle-new-password"><i class="fas fa-eye"></i></button>
                    </div>
                  </div>

                </div>
                <br>
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">Save Changes</button>
              </form>

          </div>
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