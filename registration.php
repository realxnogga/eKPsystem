<?php
session_start();
include 'connection.php';
include 'registration_handler.php';

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <!-- font awesome icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <!-- jquery link -->
  <script src="node_modules/jquery/dist/jquery.min.js"></script>

  <script src="service-worker-registration.js"></script>

  <!-- select2 for dropdown -->
  <link href="node_modules/select2/dist/css/select2.min.css" rel="stylesheet" />
  <script src="node_modules/select2/dist/js/select2.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />


</head>

<style>
  /* Hide the reveal password button in Internet Explorer */
  input[type='password']::-ms-reveal {
    display: none;
  }

  /* Adjust the button height to match the input field */
  .input-group-append .btn {
    height: calc(2.0em + .55rem + 2px);
    /* Adjust the calc() as needed to match your input height */
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }

  /* Vertically center the eye icon within the button */
  .input-group-append i {
    vertical-align: middle;
  }

  /* Additional styles to hide up and down arrows in number inputs */
  /* Chrome, Safari, Edge, Opera */
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  /* Firefox */
  input[type='number'] {
    -moz-appearance: textfield;
  }
</style>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-5">
            <div class="card mb-0">
              <div class="card-body">

                <div class="text-center">
                  <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle"><br><br>
                  <b>
                    <h5 class="card-title mb-9 fw-semibold">Create Account</h5>
                  </b>
                </div>
                <b>

                  <?php
                  if (isset($errors)) {
                    if (strpos($errors, 'Password does not match') !== false) {
                      echo "<div class='alert alert-danger' role='alert'>Password does not match the confirmed password. Please try again.</div>";
                    }
                    if (strpos($errors, 'Email already exists') !== false) {
                      echo "<div class='alert alert-danger' role='alert'>Email already exists. Please choose a different email address.</div>";
                    }
                    if (strpos($errors, 'The selected Municipality has already been registered') !== false) {
                      echo "<div class='alert alert-danger' role='alert'>The selected Municipality has already been registered.<br> Please contact Admin.</div>";
                    }
                    if (strpos($errors, 'The selected Barangay is already exists') !== false) {
                      echo "<div class='alert alert-danger' role='alert'>The selected Barangay is already existing for that Municipality.</div>";
                    }
                    if (strpos($errors, 'Municipality could not be found or has not registered yet') !== false) {
                      echo "<div class='alert alert-danger' role='alert'>Municipality could not be found or has not registered yet. Please check your selected Municipality.</div>";
                    }
                    // -----
                    if (strpos($errors, 'User registration failed') !== false) {
                      echo "<div class='alert alert-danger' role='alert'>User registration failed. Please try again later.</div>";
                    }
                    if (strpos($errors, 'User registration successful') !== false) {
                      echo "<div class='alert alert-success' role='alert'>User registration successful. Proceed to Login.</div>";
                    }

                  }
                  ?>


                  <b>

                    <form action="" method="POST">
                      <label for="first-dropdown">Select Municipality:</label>

                      <select class="form-select" id="first-dropdown" onchange="populateSecondDropdown()" name="municipality_name" required>
                        <option value="" disabled selected>Select</option>
                        <option value="Alaminos">Alaminos</option>
                        <option value="Bay">Bay</option>
                        <option value="Binan">Biñan</option>
                        <option value="Cabuyao">Cabuyao</option>
                        <option value="Calamba">Calamba</option>
                        <option value="Calauan">Calauan</option>
                        <option value="Los Baños">Los Baños</option>
                        <option value="San Pablo">San Pablo</option>
                        <option value="San Pedro">San Pedro</option>
                        <option value="Sta Rosa">Sta Rosa</option>
                      </select>

                      <div class="form-row">
                        <div class="col">
                          <label>Username:</label>
                          <input type="text" class="form-control" required name="username" placeholder="Enter username" value="">
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6 mb-6">
                          <label>First Name:</label>
                          <input type="text" class="form-control" required name="first_name" placeholder="Enter Name" value="<?php echo isset($fname) ? $fname : ''; ?>">
                        </div>

                        <div class="col-md-6 mb-6">
                          <label>Last Name:</label>
                          <input type="text" class="form-control" required name="last_name" placeholder="Enter Name" value="<?php echo isset($lname) ? $lname : ''; ?>">
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6 mb-6">
                          <label>Email:</label>
                          <input type="email" class="form-control" required name="email" placeholder="Enter Email" value="<?php echo isset($email) ? $email : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-6">
                          <label>Contact Number:</label>
                          <input type="number" class="form-control" required name="contact_number" placeholder="Enter Number" value="<?php echo isset($cont_num) ? $cont_num : ''; ?>">
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="password">Password:</label>
                          <div class="input-group">
                            <input type="password" class="form-control" required name="password" id="password" placeholder="Enter Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}" title="Password must contain at least 8 characters, including uppercase(A-Z), lowercase (a-z), number(0-9), and special character (!@#$%^&*). Example: Cluster-A2024">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="button" id="toggle-password"><i class="fas fa-eye"></i></button>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label for="cpass">Confirm Password:</label>
                          <div class="input-group">
                            <input type="password" class="form-control" required name="cpass" id="cpass" placeholder="Enter Password">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="button" id="toggle-confirm-password"><i class="fas fa-eye"></i></button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <label for="exampleFormControlSelect1">I am a:</label>
                      <select class="form-select" id="exampleFormControlSelect1" name="utype" onchange="toggleSecretaryField()" required>
                        <option value="" disabled selected>Select</option>
                        <option value="user">Barangay Secretary</option>
                        <option value="admin">C/MLGOOs</option>
                        <option value="assessor">Assessor Member Commitee</option>
                      </select>
                      <br>

                      <div style="display: none;" id="assessorField">
                        <label for="ForAssessor" class="assessor">Select Sectors/Department:</label>
                        <select class="form-select" id="ForAssessor" name="ForAssessor">
                          <option value="" disabled selected></option>
                        </select>
                      </div>

                      <div style="display: none;" id="barangay-secretary-field">
                        <label for="second-dropdown">Select Barangay:</label>
                        <select class="form-select" id="second-dropdown" name="barangay_name">
                          <option disabled selected>Select</option>
                        </select>
                      </div>

                      <p>Already have an account?<a href="login.php"> Login here</a>.</p>
                      <input type="submit" name="register" class="btn btn-primary m1" value="Register"><br><br>
                    </form>
                  </b><br>
                </b>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <script>
    $(document).ready(function() {
      var suggestions = [
        "Department of Justice",
        "Philippine National Police",
        "Local Trial Court",
        "Liga ng mga Barangay Chapter President",
        "Representative of the Local Peace and Order Council",
        "Representative of a Civil Society Organization",
      ];

      // Initialize Select2
      $('#ForAssessor').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select or start typing...',
        data: suggestions.map(function(item) {
          return {
            id: item,
            text: item
          };
        }),
        tags: true,
        createTag: function(params) {
          var term = $.trim(params.term);
          if (term === '') {
            return null;
          }
          return {
            id: term,
            text: term,
            newTag: true
          };
        },
        tokenSeparators: [','],
        closeOnSelect: false
      });

      // Handle keyup event to update the input value with the typed text
      $('#ForAssessor').on('keyup', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          var value = $(this).val();
          // Add the typed text as a tag
          if (value.trim() !== '') {
            $(this).append(new Option(value, value, true, true)).trigger('change');
          }
          // Clear the input
          $(this).val(null);
        }
      });
    });
  </script>


  <script>
    $(document).ready(function() {
      // Initially disable the toggle button
      $('#toggle-password').prop('disabled', true);
      $('#toggle-confirm-password').prop('disabled', true);

      // Enable the toggle button only if there is text in the respective password field
      $('#password').keyup(function() {
        $('#toggle-password').prop('disabled', this.value === "" ? true : false);
      });

      $('#cpass').keyup(function() {
        $('#toggle-confirm-password').prop('disabled', this.value === "" ? true : false);
      });

      // Password toggle for the password field
      $('#toggle-password').click(function() {
        togglePassword('password', 'toggle-password');
      });

      // Password toggle for the confirm password field
      $('#toggle-confirm-password').click(function() {
        togglePassword('cpass', 'toggle-confirm-password');
      });
    });

    function togglePassword(inputId, toggleId) {
      var passwordInput = document.getElementById(inputId);
      var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);

      // Selecting the icon within the button that was clicked
      var eyeIcon = $('#' + toggleId).find('i');

      // Toggle the class to change the icon
      eyeIcon.toggleClass('fa-eye fa-eye-slash');
    }

    function toggleSecretaryField() {
      const userTypeSelect = document.getElementById("exampleFormControlSelect1");
      const barangaySecretaryField = document.getElementById("barangay-secretary-field");

      const assessorField = document.getElementById("assessorField");

      const BrgySelect = document.getElementById("second-dropdown");
      const AssessorSelect = document.getElementById("ForAssessor");
      

      if (userTypeSelect.value === "user") {
        barangaySecretaryField.style.display = "block";
        BrgySelect.setAttribute("required", "true");

        assessorField.style.display = "none";
        AssessorSelect.removeAttribute("required");

      } else if (userTypeSelect.value === "assessor") {
        assessorField.style.display = "block";
        AssessorSelect.setAttribute("required", "true");

        barangaySecretaryField.style.display = "none";
        BrgySelect.removeAttribute("required");

      } else {
        barangaySecretaryField.style.display = "none";
        assessorField.style.display = "none";
        BrgySelect.removeAttribute("required");
        AssessorSelect.removeAttribute("required");
      }
    }
  </script>
  <script src="populateBrgyscript.js"></script>
</body>

</html>