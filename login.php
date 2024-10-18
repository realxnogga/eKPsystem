<?php
session_start();
include 'connection.php';

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/styles.min.css" />
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <!-- jquery link -->
  <script src="node_modules/jquery/dist/jquery.min.js"></script>

  <script src="service-worker-registration.js"></script>

  <script>
    // Send data via POST using fetch API
    async function sendData(email, password) {
      try {
        const response = await fetch("http://localhost/eKPsystem/login_handler.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
                email: email,
                password: password
            })
        });

        const result = await response.json();

        if (result.redirect && result.redirect != '') {
          window.location.href = result.redirect;
        }

        if (result.error && result.error != '') {
          document.getElementById('error').textContent = result.error;
        }
    
      } catch (error) {
        console.error("Error:", error);
      }
    }

    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      form.onsubmit = function(event) {
        event.preventDefault();
        const email = document.querySelector('input[name="email"]').value;
        const password = document.querySelector('input[name="password"]').value;

        if (navigator.onLine) {
          sendData(email, password);
        } else {
          localStorage.setItem('pendingData', JSON.stringify({
            email,
            password
          }));
          alert('No internet. Data will be inserted once the internet was restored.');
        }
      };

      // Sync when back online
      window.addEventListener('online', function() {
        const pendingData = JSON.parse(localStorage.getItem('pendingData'));
        if (pendingData) {
          sendData(pendingData.email, pendingData.password);
          localStorage.removeItem('pendingData');
        }
      });
    });
  </script>

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
</style>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">

                <a href="#" class="text-nowrap logo-img text-center d-block py-3 w-100">
                </a>

                <div class="text-center">
                  <img
                    src="img/cluster.png"
                    alt="Logo"
                    style="max-width: 120px; max-height: 120px; margin-right: 10px;"
                    class="align-middle">
                  <h5 class="card-title my-9 fw-semibold">Login</h5>
                </div>


                <section>
                  <p id="error"></p>
                </section>

                <form>

                  <div class="form-row">
                    <div class="col">
                      <label for="email" class="form-label">Email Address</label>
                      <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                  </div>

                  <br>

                  <div class="form-row">
                    <div class="col">
                      <label for="login-password" class="form-label">Password</label>
                      <div class="input-group">
                        <input type="password" class="form-control" name="password" id="login-password" required>

                        <div class="input-group-append">
                          <button class="btn btn-outline-secondary" type="button" id="toggle-login-password"><i class="fas fa-eye"></i></button>
                        </div>
                      </div>
                    </div>

                    <br>

                  </div>

                  <br>

                  <b>
                    <p>Don't have an account? <a href="registration.php">Sign up here</a>.</p>

                    <div><input type="submit" class="btn btn-primary w-100"></div><br>

                    <b>
                      <p> <a href="javascript:void(0);" onclick="location.href='forgot_pass.php';" style="font-size:16px;">Forgot Password?</a></p>
                    </b>
                  </b>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

<script>
  $(document).ready(function() {
    // // Initially disable the toggle button
    // $('#toggle-login-password').prop('disabled', true);

    // // Enable the toggle button only if there is text in the password field
    // $('#login-password').keyup(function() {
    //   $('#toggle-login-password').prop('disabled', this.value === "" ? true : false);
    // });

    // Password toggle for the login password field
    $('#toggle-login-password').click(function() {
      var passwordInput = document.getElementById('login-password');
      var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);

      // Selecting the icon within the button that was clicked
      var eyeIcon = $(this).find('i');

      // Toggle the class to change the icon
      eyeIcon.toggleClass('fa-eye fa-eye-slash');
    });
  });
</script>

</html>