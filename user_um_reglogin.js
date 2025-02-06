
function loadRegistrationLoginContent() {
    return `
        <section class="flex flex-col gap-y-2">
          <h2 class="text-[2rem]">1. Register</h2>
          <p>To register, go to the link <a class="text-blue-500 underline" href="https://ekpsystem.online/ekpsys/registration.php">https://ekpsystem.online/ekpsys/registration.php</a></p>
          <img class="w-[40rem]" src="user_manual_pic/registration.PNG" alt="registration picture">
          <p>1. Select your Municipality.</p>
          <p>2. For username, enter "brgy" and then your barangay name. (Ex. brgybatongmalake)</p>
          <p>3. On first name, enter the word "Barangay" and last name is your barangay name. (Ex. Barangay Batong Malake)</p>
          <img class="w-[40rem]" src="user_manual_pic/select_muni.png" alt="select municipality picture">
          <p>4. Enter your email in this format: "clustera" underscore your barangay, underscore "ekp" (Ex. clustera_batongmalake_ekp@gmail.com)</p>
          <img class="w-[40rem]" src="user_manual_pic/email.png" alt="email field picture">
          <p>5. Enter your '11' digit number.</p>
          <img class="w-[40rem]" src="user_manual_pic/contact_number.png" alt="contact number field picture">
          <p>6. Enter a password with minimum of '8' characters including uppercase (A-Z), lowercase (a-z), number (0-9), and special character (!@#$%^&*).</p>
          <img class="w-[40rem]" src="user_manual_pic/password.png" alt="password field picture">
          <p>7. Select your position. For user, choose 'Barangay Secretary' if an admin, choose 'C/LMGOOs' or Assessor.</p>
          <img class="w-[40rem]" src="user_manual_pic/iama.png" alt="iama field picture">
          <p>8. When done filling up the form, click the 'Register' button.</p>
        </section>

        <section class="flex flex-col gap-y-2">
          <h2 class="text-[2rem]">2. Login</h2>
          <p>To login, go to the link <a class="text-blue-500 underline" href="https://ekpsystem.online/ekpsys/login.php">https://ekpsystem.online/ekpsys/login.php</a></p>
          <img class="w-[40rem]" src="user_manual_pic/login.png" alt="login picture">
        </section>

        <section class="flex flex-col gap-y-2">
          <h2 class="text-[2rem]">3. Forgot Password</h2>
          <p>1. Enter your email to be able to change your password.</p>
          <img class="w-[40rem]" src="user_manual_pic/forgot_password.png" alt="forgot password picture">
          <p>2. Answer the 3 security questions that you have already answered before.</p>
          <img class="w-[40rem]" src="user_manual_pic/security_question.png" alt="security question picture">
          <p>3. Enter your new password</p>
          <img class="w-[40rem]" src="user_manual_pic/reset_password.png" alt="reset password picture">
        </section>
    `;
  }