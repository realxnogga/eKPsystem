
<?php
session_start();
include 'connection.php';
include 'user_set_timezone.php';

include 'user_set_timezone.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the user inputs from the form
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Prepare the SQL statement to select the user with the given email
        $stmt = $conn->prepare("SELECT id, email, password, user_type, assessor_type, contact_number, municipality_id, verified, first_name, last_name, barangay_id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (!in_array($user['user_type'], ['user', 'assessor']) || $user['verified']) {
                // Check if another user with the same barangay is already logged in

                // if ($_SESSION['user_id'] === $user['id'] && $_SESSION['isloggedin'] === true) {
                //     // Another user from the same barangay is already logged in, prevent login
                //     header("Location: login.php?login_message=account_already_open");
                //     exit;
                // }



                // Check if the provided password matches the hashed password
                if (password_verify($password, $user['password'])) {
                    // Start the session and store user information in session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['assessor_type'] = $user['assessor_type'];
                    $_SESSION['municipality_id'] = $user['municipality_id'];
                    $_SESSION['first_name'] = $user['first_name']; // Store first name
                    $_SESSION['last_name'] = $user['last_name'];   // Store last name
                    $_SESSION['barangay_id'] = $user['barangay_id']; // Store barangay ID
                    $_SESSION['isloggedin'] = true;

                    // Log user activity
                    $logcurdate = date('Y-m-d H:i:s');
                    logUserActivity($user['id'], $logcurdate, "User logged in");

                    // Fetch additional user information like municipality_name and barangay_name
                    $additionalInfoStmt = $conn->prepare("SELECT municipality_name FROM municipalities WHERE id = :municipality_id");
                    $additionalInfoStmt->bindParam(':municipality_id', $user['municipality_id'], PDO::PARAM_INT);
                    $additionalInfoStmt->execute();
                    $municipality = $additionalInfoStmt->fetch(PDO::FETCH_ASSOC);

                    $additionalInfoStmt = $conn->prepare("SELECT barangay_name FROM barangays WHERE id = :barangay_id");
                    $additionalInfoStmt->bindParam(':barangay_id', $user['barangay_id'], PDO::PARAM_INT);
                    $additionalInfoStmt->execute();
                    $barangay = $additionalInfoStmt->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['municipality_name'] = $municipality['municipality_name'];
                    $_SESSION['barangay_name'] = $barangay['barangay_name'];

                    // identify user already in the db fofr today
                    $countrowquery = "SELECT COUNT(*) FROM lspu_attendance_sheet WHERE DAY(login_date) = DAY(CURDATE()) AND user_id = :user_id";
                    $checkRowStmt = $conn->prepare($countrowquery);
                    $checkRowStmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                    $checkRowStmt->execute();
                    $rowCount = (int) $checkRowStmt->fetchColumn();

                    // for lspu attendace
                    if ($rowCount === 0) {
                        $u_id = $user['id'];
                        $name = $user['first_name'] . " " . $user['last_name'];
                        $address = $municipality['municipality_name'] . '/' . $barangay['barangay_name'];
                        $con_num = $user['contact_number'];
                        $logdate = date('Y-m-d H:i:s');
                        LSPUAttendanceSheet($u_id, $name, $address, $con_num, $logdate);
                    }


                    // Redirect the user based on their user_type
                    if ($user['user_type'] === 'admin') {
                        header("Location: admin_dashboard.php");
                        exit;
                    } elseif ($user['user_type'] === 'assessor') {
                        header("Location: LTIA/assessor_ltia_admin_dashboard.php");
                        exit;
                    } elseif ($user['user_type'] === 'user') {
                        header("Location: user_dashboard.php");
                        exit;
                    } elseif ($user['user_type'] === 'superadmin') {
                        header("Location: sa_dashboard.php");
                        exit;
                    }
                } else {
                    // Invalid credentials, redirect back to the login page with an error message
                    header("Location: login.php?login_message=invalid_credentials");
                    exit;
                }
            } else {
                // User is not verified, redirect back to login with an error message
                header("Location: login.php?login_message=not_verified");
                exit;
            }
        } else {
            // Invalid credentials, redirect back to the login page with an error message
            header("Location: login.php?login_message=invalid_credentials");
            exit;
        }
    } catch (PDOException $e) {
        // Handle database connection or query errors
        echo "Error: " . $e->getMessage();
    }
} else {
    // If the form is not submitted, redirect back to the login page
    header("Location: login.php");
    exit;
}

// Function to log user activity
function logUserActivity($user_id, $logcurdate, $activity)
{
    global $conn; // Assuming $conn is your database connection variable

    $query = "INSERT INTO user_logs (user_id, timestamp, activity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $logcurdate, PDO::PARAM_STR);
    $stmt->bindParam(3, $activity, PDO::PARAM_STR);
    $stmt->execute();
}

function LSPUAttendanceSheet($u_id, $name, $address, $con_num, $logdate)
{
    global $conn; // Assuming $conn is your database connection variable

    $query = "INSERT INTO lspu_attendance_sheet (user_id, name, address, contact_number, login_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $u_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $name, PDO::PARAM_STR);
    $stmt->bindParam(3, $address, PDO::PARAM_STR);
    $stmt->bindParam(4, $con_num, PDO::PARAM_STR);
    $stmt->bindParam(5, $logdate, PDO::PARAM_STR);
    $stmt->execute();
}

?>
