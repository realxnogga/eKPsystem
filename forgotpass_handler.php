
<?php 

include 'header.php';

$email = $_POST['email'];

// Prepare the SQL statement to check if the email exists in the users table
$stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

// Fetch the result
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Email not found, redirect back to forgotpass.php with an error parameter
    header("Location: forgot_pass.php?error=email_not_found");
    exit;
}


 ?>

