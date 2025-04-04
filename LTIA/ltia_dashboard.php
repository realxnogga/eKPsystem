<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user' || !isset($_SESSION['barangay_id'])) {
    header("Location: login.php");
    exit;
}
function getPerformanceRating($total) {
    if ($total >= 100) return "Outstanding";
    if ($total >= 90) return "Very Satisfactory";
    if ($total >= 80) return "Fair";
    if ($total >= 70) return "Poor";
    return "Very Poor";
}

$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y'); // Default to the current year

try {
    // Fetch the municipality_id of the logged-in user
    $municipalityQuery = "
        SELECT municipality_id 
        FROM users 
        WHERE id = :user_id
    ";
    $stmt = $conn->prepare($municipalityQuery);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $municipalityRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$municipalityRow) {
        throw new Exception("User's municipality not found.");
    }

    $municipality_id = $municipalityRow['municipality_id'];
    $query = "
        SELECT AVG(m.total) AS avg_total
        FROM movrate m
        WHERE m.barangay = :barangay_id AND m.year = :year
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $avg_total = $row ? number_format($row['avg_total'], 2) : "LTIA";
    $performance = $row && $row['avg_total'] !== null ? getPerformanceRating($row['avg_total']) : "Form 1";

    // Fetch distinct years for dropdown
    $yearQuery = "SELECT DISTINCT `year` FROM `movrate` ORDER BY year DESC";
    $yearResult = $conn->query($yearQuery);
    $years = $yearResult->fetchAll(PDO::FETCH_COLUMN);

    // Add current year to the list if not present
    if (!in_array(date('Y'), $years)) {
        array_unshift($years, date('Y'));
    }

    // Fetch existing barangay officer data
    $query = "SELECT * FROM movbrgy_officers WHERE barangay = :barangay LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay', $_SESSION['barangay_id'], PDO::PARAM_INT);
    $stmt->execute();
    $certification_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $avg_total = "LTIA";
    $performance = "Form 1";
} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage());
    $avg_total = "LTIA";
    $performance = "Form 1";
}

$barangay = $_SESSION['barangay_id']; // Assign barangay from session

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $punong_barangay = $_POST['Punong_Barangay'];
    $barangay_secretary = $_POST['Barangay_Secretary'];
    $barangay_treasurer = $_POST['Barangay_Treasurer'];
    $kagawad1 = $_POST['Kagawad1'];
    $kagawad2 = $_POST['Kagawad2'];
    $kagawad3 = $_POST['Kagawad3'];
    $kagawad4 = $_POST['Kagawad4'];
    $kagawad5 = $_POST['Kagawad5'];
    $kagawad6 = $_POST['Kagawad6'];
    $kagawad7 = $_POST['Kagawad7'];
    $date = date("Y-m-d");

    if ($certification_data) {
        // Update existing record
        $query = "UPDATE movbrgy_officers SET 
            punong_barangay = :punong_barangay, 
            barangay_secretary = :barangay_secretary, 
            barangay_treasurer = :barangay_treasurer, 
            kagawad1 = :kagawad1, 
            kagawad2 = :kagawad2, 
            kagawad3 = :kagawad3, 
            kagawad4 = :kagawad4, 
            kagawad5 = :kagawad5, 
            kagawad6 = :kagawad6, 
            kagawad7 = :kagawad7, 
            date = :date 
            WHERE barangay = :barangay";
    } else {
        // Insert new record
        $query = "INSERT INTO movbrgy_officers (
            barangay, punong_barangay, barangay_secretary, barangay_treasurer, kagawad1, kagawad2, kagawad3, kagawad4, kagawad5, kagawad6, kagawad7, date
        ) VALUES (
            :barangay, :punong_barangay, :barangay_secretary, :barangay_treasurer, :kagawad1, :kagawad2, :kagawad3, :kagawad4, :kagawad5, :kagawad6, :kagawad7, :date
        )";
    }

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay', $barangay, PDO::PARAM_STR); // Now it's correctly bound to $barangay
    $stmt->bindParam(':punong_barangay', $punong_barangay, PDO::PARAM_STR);
    $stmt->bindParam(':barangay_secretary', $barangay_secretary, PDO::PARAM_STR);
    $stmt->bindParam(':barangay_treasurer', $barangay_treasurer, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad1', $kagawad1, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad2', $kagawad2, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad3', $kagawad3, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad4', $kagawad4, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad5', $kagawad5, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad6', $kagawad6, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad7', $kagawad7, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $message = "Barangay Officers Saved.";
    } else {
        $message = "Error saving details.";
    }
header("Location: " . $_SERVER['PHP_SELF'] . "?submitted=" . time());
exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <title>LTIA</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />

    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
  <!-- flowbite component -->
  <script src="../node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="../node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="../node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

 <!-- tailwind cdn -->
<script src="https://cdn.tailwindcss.com"> </script>

</head>
<style>
    .form-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%; /* Make the container take 100% of the width */
        max-width: 100%; /* Ensure it doesn't exceed full width */
        padding: 20px;
        box-sizing: border-box; /* Include padding in the width calculation */
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        display: grid;
        grid-template-columns: 200px 1fr; /* Label and input alignment */
        gap: 15px; /* Space between label and input */
        align-items: center;
        margin-bottom: 20px;
    }

    .form-group label {
        font-weight: bold;
        text-align: right; /* Align labels to the right */
    }

    .underline-input {
        width: 40%;
        border: none;
        border-bottom: 2px solid #ccc;
        padding: 5px;
        outline: none;
        text-align: center; /* Center the text inside the input */
    }

    .underline-input:focus {
        border-bottom: 2px solid #007bff;
    }

    .kagawads-container {
        display: flex;
        flex-wrap: wrap; /* Allow inputs to wrap */
        gap: 15px; /* Space between inputs */
        justify-content: center; /* Center the inputs */
        margin-top: 10px;
    }

    .kagawad-input {
        width: calc(33% - 10px); /* Fit three Kagawad inputs per row */
        border: none;
        border-bottom: 2px solid #ccc;
        padding: 5px;
        text-align: center;
        outline: none;
    }

    .kagawad-input:focus {
        border-bottom: 2px solid #007bff;
    }

    .submit-container {
        width: 100%; /* Full width */
        text-align: right; /* Align the button to the right */
        margin-top: 20px; /* Space from the form content */
    }

    .btn-circle-custom {
        width: 150px;
        height: 150px;
        font-size: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    }

    .btn-circle-custom:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        background-color: #0056b3; /* Change color on hover */
        color: #fff; /* Ensure text color remains white */
    }

    .btn-circle-custom:hover span {
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.8); /* Glowing effect on text */
    }
</style>
  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
        // Pass PHP variable to JavaScript
        const message = "<?php echo isset($message) ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : ''; ?>";

        // Update modal content and show it
        document.addEventListener('DOMContentLoaded', function() {
            if (message) {
                // Update the modal body with the message
                document.getElementById('modalBody').textContent = message;

                // Show the modal
                const myModal = new bootstrap.Modal(document.getElementById('messageModal'), {});
                myModal.show();
            }
        });
    </script>
<body class="bg-[#E8E8E7]">
    <!-- Sidebar -->
    <?php include "../user_sidebar_header.php"; ?>
    <div class="p-4 sm:ml-44">
        <div class="rounded-lg mt-16">
            <div class="card">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center">
                            <div class="dilglogo">
                                <img src="../img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
                            </div>

                            <h1 class="text-xl font-bold flex items-center ml-4">
                                <span>Lupong Tagapamayapa Incentives Award (LTIA)</span>
                                <form method="GET" action="" class="ml-4">
                                    <select name="year" onchange="this.form.submit()">
                                        <?php foreach ($years as $availableYear): ?>
                                            <option value="<?php echo $availableYear; ?>" <?php if ($availableYear == $year) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($availableYear); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </h1>
             
                        </div>
                        <div class="menu">
                            <ul class="flex space-x-2">
                                <li>
                                <button class="bg-blue-500 hover:bg-blue-400 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='formulationcommentsheet.php';" style="margin-left: 0;">
                                    <i class="ti ti-message"></i>
                                    Policy Formulation Comment
                                </button>
                                </li>
                                <li>
                                    <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='form2MOVupload.php';" style="margin-left: 0;">
                                        <i class="ti ti-file-upload"></i> Upload Means of Verification
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        
                    <!-- Circle Button with Conditional Text -->
                                        
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <button type="button" class="btn btn-circle bg-primary text-white d-flex flex-column justify-content-center align-items-center shadow-lg ms-5 btn-circle-custom" onclick="location.href='form2movview.php';">
                        <span class="fw-bold fs-2"><?php echo htmlspecialchars($avg_total); ?></span> 
                        <span class="fs-6"><?php echo htmlspecialchars($performance); ?></span>
                        </button>
                    </div>
 <!-- Modal -->
 <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Message will be inserted here dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="color: #003366;"data-bs-dismiss="modal">Okay</button>
                </div>
            </div>
        </div>
    </div>

                    <!-- Text Section -->          
<div class="flex flex-col text-justify w-75 me-5">
    <p class="h3 fw-bold" style="color: #003366;">The Lupong Tagapamayapa Incentives Award (LTIA)</p>
    <p class="text-muted" style="font-size: 1rem;">
        The Lupong Tagapamayapa Incentives Award (LTIA) was conceptualized and implemented in 1982 and has been elevated to a Presidential Award pursuant to Executive Order No. 394 s. 1997 entitled "Establishing the Lupong Tagapamayapa Incentives Award."
    </p>
    <p class="text-muted" style="font-size: 1rem;">
        This award is an avenue for granting economic and other incentives to the Lupong Tagapamayapa (LT) for their outstanding contributions to attaining the objectives of the Katarungang Pambarangay (KP).
    </p>
</div>
                </div>
                <div class="form-container">
        <form method="post" action="" enctype="multipart/form-data">
            <!-- Punong Barangay -->
            <label for="LTFO" class="form-label" style="font-size: 1rem;">Sangguniang Barangay:</label>
            <div class="form-group">
                <label for="Punong_Barangay">Punong Barangay:</label>
                <input type="text" id="Punong_Barangay" name="Punong_Barangay" class="underline-input" 
                       placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Punong_Barangay'] ?? ''); ?>">
            </div>

            <!-- Barangay Secretary -->
            <div class="form-group">
                <label for="Barangay_Secretary">Barangay Secretary:</label>
                <input type="text" id="Barangay_Secretary" name="Barangay_Secretary" class="underline-input" 
                       placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Secretary'] ?? ''); ?>">
            </div>

            <!-- Barangay Treasurer -->
            <div class="form-group">
                <label for="Barangay_Treasurer">Barangay Treasurer:</label>
                <input type="text" id="Barangay_Treasurer" name="Barangay_Treasurer" class="underline-input" 
                       placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Treasurer'] ?? ''); ?>">
            </div>

            <!-- Kagawads -->
            <hr class="my-3">
            <label for="Kagawads" class="form-label" style="font-size: 1rem;">Kagawads:</label>
            <div class="kagawads-container">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                    <input type="text" name="Kagawad<?php echo $i; ?>" class="kagawad-input" 
                           placeholder="Kagawad <?php echo $i; ?> Name" 
                           value="<?php echo htmlspecialchars($certification_data["Kagawad$i"] ?? ''); ?>">
                <?php endfor; ?>
            </div>

            <br>
            <div class="submit-container">
                <button type="submit" class="btn btn-primary" style="color: #003366;">Save</button>
            </div> 
           </form>
    </div>
                </div>
                <footer class="position-relative">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-100">
                    <path fill="#0099ff" fill-opacity="1" d="M0,224L30,224C60,224,120,224,180,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
                </svg>
                <div class="position-absolute bottom-0 end-0 mb-3 me-3 d-flex justify-content-center">
                    <img src="images/ltialogo.png" alt="LTIA Logo" class="img-fluid" style="max-height: 80px; width: auto;" />
                </div>
                </footer>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Missing Files Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="alertModalLabel">Missing Files!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">Please verify that all required files are uploaded before submitting.</p>
                <p>Ensure each criteria has the necessary files attached.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-dark" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary" id="confirmModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">I confirm that all the criteria are correct.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" style="background-color: #00ace6;" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" style="background-color: #3366ff;" id="confirmSubmit">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="successModalLabel">Submission Successful</h5>
                <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Your submission has been successful.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Show success modal on confirm
document.getElementById('confirmSubmit').addEventListener('click', function () {
    $('#confirmModal').modal('hide');
    setTimeout(function () {
        $('#successModal').modal('show');
    }, 500);
});
</script>

<!-- Modal for Update (modalmov_message) -->
<div class="modal fade" id="modalmov_message" tabindex="-1" aria-labelledby="modalmovMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalmovMessageLabel">
                    <?php echo $_SESSION['modalmov_message'] === 'Files submitted successfully!' ? 'Success' : 'Notice'; ?>
                </h5>
                <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p><?php echo htmlspecialchars($_SESSION['modalmov_message'] ?? ''); ?></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        <?php if (isset($_SESSION['modalmov_message'])): ?>
            var modalmovMessage = new bootstrap.Modal(document.getElementById('modalmov_message'));
            modalmovMessage.show();
            <?php unset($_SESSION['modalmov_message']); ?>
        <?php endif; ?>
    });
</script>
</body>
</html>
