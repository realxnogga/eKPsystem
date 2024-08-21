<?php
include 'connection.php';
include('header.php');
session_start();
$errors = [];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
        exit;
}
if (!isset($_SESSION['verification_complete']) || !$_SESSION['verification_complete']) {
    // If the verification is not complete, redirect to login or verification page
    header("Location: login.php"); // Redirect to login page
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $new_password = $_POST['new_password'];

    // Check if the password meets certain criteria (e.g., minimum length)
    if (strlen($new_password) < 8) {
        $errors[] = "Password should be at least 8 characters long.";
    } else {
        // Hash the new password with bcrypt
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update the user's password in the database
        $update_password_query = "UPDATE users SET password = :password WHERE id = :user_id";
        $stmt = $conn->prepare($update_password_query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':user_id', $user_id);
        $result = $stmt->execute();

        if ($result) {
            // Password updated successfully, redirect to login page
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Failed to update the password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <?php
    // Display error messages
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
    ?>
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="password" placeholder="New Password" name="new_password" required><br>
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
        <input type="submit" value="Reset Password">
    </form>
    <a href="forgot_pass.php">Back to Forgot Password</a>
</body>
</html>
<script>
window.addEventListener('beforeunload', function(event) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'logout.php', false); // Update this URL to match your logout script
    xhr.send();
});
</script>