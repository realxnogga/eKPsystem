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

    // Fetch the average total for the specific barangay of the logged-in user
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

    $avg_total = $row ? number_format($row['avg_total'], 2) : "N/A";
    $performance = $row && $row['avg_total'] !== null ? getPerformanceRating($row['avg_total']) : "No Rating yet";

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

    // Fetch the count of remarks for the logged-in user
    $remarkQuery = "
        SELECT COUNT(*) AS remark_count
        FROM movremark
        WHERE barangay = :barangay_id AND user_id = :user_id AND year = :year
    ";
    $stmt = $conn->prepare($remarkQuery);
    $stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $remarkRow = $stmt->fetch(PDO::FETCH_ASSOC);
)) {
    $remark_count = $remarkRow ? $remarkRow['remark_count'] : 0;in user
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $avg_total = "Error";
    $performance = "Error fetching data";id AND user_id = :user_id AND year = :year
} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage());uery);
    $avg_total = "Error";, $_SESSION['barangay_id'], PDO::PARAM_INT);
    $performance = "Error fetching data";SESSION['user_id'], PDO::PARAM_INT);
}, PDO::PARAM_INT);

$barangay = $_SESSION['barangay_id']; // Assign barangay from session    $remarkRow = $stmt->fetch(PDO::FETCH_ASSOC);

// Process form submission$remarkRow['remark_count'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $punong_barangay = $_POST['Punong_Barangay'];
    $barangay_secretary = $_POST['Barangay_Secretary'];
    $barangay_treasurer = $_POST['Barangay_Treasurer'];
    $kagawad1 = $_POST['Kagawad1'];ong_Barangay'];
    $kagawad2 = $_POST['Kagawad2'];Barangay_Secretary'];
    $kagawad3 = $_POST['Kagawad3'];Barangay_Treasurer'];
    $kagawad4 = $_POST['Kagawad4'];;
    $kagawad5 = $_POST['Kagawad5'];;
    $kagawad6 = $_POST['Kagawad6'];;
    $kagawad7 = $_POST['Kagawad7'];;
    $date = date("Y-m-d");agawad5'];

    if ($certification_data) {7 = $_POST['Kagawad7'];
        // Update existing record
        $query = "UPDATE movbrgy_officers SET 
            punong_barangay = :punong_barangay, 
            barangay_secretary = :barangay_secretary, existing record
            barangay_treasurer = :barangay_treasurer, 
            kagawad1 = :kagawad1,  punong_barangay = :punong_barangay, 
            kagawad2 = :kagawad2,        barangay_secretary = :barangay_secretary, 
            kagawad3 = :kagawad3,             barangay_treasurer = :barangay_treasurer, 
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
        )";tary, barangay_treasurer, kagawad1, kagawad2, kagawad3, kagawad4, kagawad5, kagawad6, kagawad7, date
    }        ) VALUES (
ong_barangay, :barangay_secretary, :barangay_treasurer, :kagawad1, :kagawad2, :kagawad3, :kagawad4, :kagawad5, :kagawad6, :kagawad7, :date
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay', $barangay, PDO::PARAM_STR); // Now it's correctly bound to $barangay
    $stmt->bindParam(':punong_barangay', $punong_barangay, PDO::PARAM_STR);
    $stmt->bindParam(':barangay_secretary', $barangay_secretary, PDO::PARAM_STR);stmt = $conn->prepare($query);
    $stmt->bindParam(':barangay_treasurer', $barangay_treasurer, PDO::PARAM_STR);   $stmt->bindParam(':barangay', $barangay, PDO::PARAM_STR); // Now it's correctly bound to $barangay
    $stmt->bindParam(':kagawad1', $kagawad1, PDO::PARAM_STR);  $stmt->bindParam(':punong_barangay', $punong_barangay, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad2', $kagawad2, PDO::PARAM_STR);Param(':barangay_secretary', $barangay_secretary, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad3', $kagawad3, PDO::PARAM_STR);aram(':barangay_treasurer', $barangay_treasurer, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad4', $kagawad4, PDO::PARAM_STR);tmt->bindParam(':kagawad1', $kagawad1, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad5', $kagawad5, PDO::PARAM_STR);awad2', $kagawad2, PDO::PARAM_STR);
    $stmt->bindParam(':kagawad6', $kagawad6, PDO::PARAM_STR);TR);
    $stmt->bindParam(':kagawad7', $kagawad7, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
kagawad6', $kagawad6, PDO::PARAM_STR);
    if ($stmt->execute()) {awad7', $kagawad7, PDO::PARAM_STR);
        $message = "Barangay Officers Saved.";
    } else {
        $message = "Error saving details.";($stmt->execute()) {
    } $message = "Barangay Officers Saved.";
}
?>ror saving details.";
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <title>LTIA</title>ompatible" content="IE=edge">
    <!-- Bootstrap CSS -->idth, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />    <title>LTIA</title>
</head>p CSS -->
<style>//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    .form-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%; /* Make the container take 100% of the width */   display: flex;
        max-width: 100%; /* Ensure it doesn't exceed full width */        flex-direction: column;
        padding: 20px;nter;
        box-sizing: border-box; /* Include padding in the width calculation */ke the container take 100% of the width */
        border: 1px solid #ccc;width */
        border-radius: 10px;   padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);        box-sizing: border-box; /* Include padding in the width calculation */
    }lid #ccc;
us: 10px;
    .form-group { 2px 5px rgba(0, 0, 0, 0.1);
        display: grid;
        grid-template-columns: 200px 1fr; /* Label and input alignment */
        gap: 15px; /* Space between label and input */
        align-items: center;
        margin-bottom: 20px;   grid-template-columns: 200px 1fr; /* Label and input alignment */
    }        gap: 15px; /* Space between label and input */

    .form-group label {
        font-weight: bold;
        text-align: right; /* Align labels to the right */
    }
old;
    .underline-input {ight */
        width: 40%;
        border: none;
        border-bottom: 2px solid #ccc;
        padding: 5px;   width: 40%;
        outline: none;        border: none;
        text-align: center; /* Center the text inside the input */m: 2px solid #ccc;
    }
;
    .underline-input:focus {the text inside the input */
        border-bottom: 2px solid #007bff;
    }
ocus {
    .kagawads-container {   border-bottom: 2px solid #007bff;
        display: flex;    }
        flex-wrap: wrap; /* Allow inputs to wrap */
        gap: 15px; /* Space between inputs */
        justify-content: center; /* Center the inputs */   display: flex;
        margin-top: 10px;        flex-wrap: wrap; /* Allow inputs to wrap */
    }pace between inputs */
enter the inputs */
    .kagawad-input {
        width: calc(33% - 10px); /* Fit three Kagawad inputs per row */
        border: none;
        border-bottom: 2px solid #ccc;    .kagawad-input {
        padding: 5px;- 10px); /* Fit three Kagawad inputs per row */
        text-align: center;
        outline: none; 2px solid #ccc;
    }

    .kagawad-input:focus {   outline: none;
        border-bottom: 2px solid #007bff;    }
    }

    .submit-container {
        width: 100%; /* Full width */
        text-align: right; /* Align the button to the right */
        margin-top: 20px; /* Space from the form content */submit-container {
    }        width: 100%; /* Full width */
 the button to the right */
    .btn-circle-custom {
        width: 150px;
        height: 150px;
        font-size: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
    }
        font-size: 1.5rem;
    .btn-circle-custom:hover {sition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        background-color: #0056b3; /* Change color on hover */    .btn-circle-custom:hover {
        color: #fff; /* Ensure text color remains white */
    }
#0056b3; /* Change color on hover */
    .btn-circle-custom:hover span {/
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.8); /* Glowing effect on text */
    }
</style> {
  <!-- Bootstrap JS and dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
strap JS and dependencies -->
    <script>="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        // Pass PHP variable to JavaScriptn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
        const message = "<?php echo isset($message) ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : ''; ?>";

        // Update modal content and show ito JavaScript
        document.addEventListener('DOMContentLoaded', function() {set($message) ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : ''; ?>";
            if (message) {
                // Update the modal body with the messagew it
                document.getElementById('modalBody').textContent = message;

                // Show the modalmessage
                const myModal = new bootstrap.Modal(document.getElementById('messageModal'), {});
                myModal.show();
            }                // Show the modal
        });ageModal'), {});
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
                            <div class="dilglogo">                <div class="card-body">
                                <img src="../img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">lex justify-between items-center mb-4">
                            </div>           <div class="flex items-center">
iv class="dilglogo">
                            <h1 class="text-xl font-bold flex items-center ml-4">../img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
                                <span>Lupong Tagapamayapa Incentives Award (LTIA)</span>
                                <form method="GET" action="" class="ml-4">
                                    <select name="year" onchange="this.form.submit()">
                                        <?php foreach ($years as $availableYear): ?>
                                            <option value="<?php echo $availableYear; ?>" <?php if ($availableYear == $year) echo 'selected'; ?>>"GET" action="" class="ml-4">
                                                <?php echo htmlspecialchars($availableYear); ?>select name="year" onchange="this.form.submit()">
                                            </option>       <?php foreach ($years as $availableYear): ?>
                                        <?php endforeach; ?>              <option value="<?php echo $availableYear; ?>" <?php if ($availableYear == $year) echo 'selected'; ?>>
                                    </select>                      <?php echo htmlspecialchars($availableYear); ?>
                                </form>
                <?php endforeach; ?>
                            </h1>
             
                        </div>
                        <div class="menu">
                            <ul class="flex space-x-4">
                                <li>
                                    <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='form2MOVupload.php';" style="margin-left: 0;">s="menu">
                                        <i class="ti ti-file-upload mr-2"></i> Upload Means of Verification  <ul class="flex space-x-4">
                                    </button>                 <li>
                                </li> flex items-center" onclick="location.href='form2MOVupload.php';" style="margin-left: 0;">
                            </ul>file-upload mr-2"></i> Upload Means of Verification aria-labelledby="messageModalLabel" aria-hidden="true">
                        </div>utton>dialog-centered">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-5">
                          </div>utton type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <!-- Circle Button with Conditional Text -->
                                        tems-center mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-5">on" class="btn btn-circle bg-primary text-white d-flex flex-column justify-content-center align-items-center shadow-lg ms-5 btn-circle-custom position-relative" onclick="location.href='form2movview.php';">-- Message will be inserted here dynamically -->
                        <button type="button" class="btn btn-circle bg-primary text-white d-flex flex-column justify-content-center align-items-center shadow-lg ms-5 btn-circle-custom" onclick="location.href='form2movview.php';">cho htmlspecialchars($avg_total); ?></span> 
                        <span class="fw-bold fs-2"><?php echo htmlspecialchars($avg_total); ?></span> 
                        <span class="fs-6"><?php echo htmlspecialchars($performance); ?></span>ark_count > 0): ?>utton type="button" class="btn btn-secondary" style="color: #003366;"data-bs-dismiss="modal">Okay</button>
                        </button>class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">div>
                    </div>  <?php echo $remark_count; ?>div>
 <!-- Modal -->  </span>div>
 <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">        <?php endif; ?>    </div>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>red">he Lupong Tagapamayapa Incentives Award (LTIA)</p>
                </div>
                <div class="modal-body" id="modalBody">        <div class="modal-header">The Lupong Tagapamayapa Incentives Award (LTIA) was conceptualized and implemented in 1982 and has been elevated to a Presidential Award pursuant to Executive Order No. 394 s. 1997 entitled "Establishing the Lupong Tagapamayapa Incentives Award."
                    <!-- Message will be inserted here dynamically -->n-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-footer">        <div class="modal-body" id="modalBody">This award is an avenue for granting economic and other incentives to the Lupong Tagapamayapa (LT) for their outstanding contributions to attaining the objectives of the Katarungang Pambarangay (KP).
                    <button type="button" class="btn btn-secondary" style="color: #003366;"data-bs-dismiss="modal">Okay</button>              <!-- Message will be inserted here dynamically -->p>
                </div>
            </div>
        </div>style="color: #003366;"data-bs-dismiss="modal">Okay</button>
    </div>tipart/form-data">

                    <!-- Text Section -->          nt-size: 1rem;">Sangguniang Barangay:</label>
<div class="flex flex-col text-justify w-75 me-5">
    <p class="h3 fw-bold" style="color: #003366;">The Lupong Tagapamayapa Incentives Award (LTIA)</p>
    <p class="text-muted" style="font-size: 1rem;">
        The Lupong Tagapamayapa Incentives Award (LTIA) was conceptualized and implemented in 1982 and has been elevated to a Presidential Award pursuant to Executive Order No. 394 s. 1997 entitled "Establishing the Lupong Tagapamayapa Incentives Award."lex-col text-justify w-75 me-5">     placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Punong_Barangay'] ?? ''); ?>">
    </p>    <p class="h3 fw-bold" style="color: #003366;">The Lupong Tagapamayapa Incentives Award (LTIA)</p>            </div>
    <p class="text-muted" style="font-size: 1rem;">ize: 1rem;">
        This award is an avenue for granting economic and other incentives to the Lupong Tagapamayapa (LT) for their outstanding contributions to attaining the objectives of the Katarungang Pambarangay (KP).ntives Award (LTIA) was conceptualized and implemented in 1982 and has been elevated to a Presidential Award pursuant to Executive Order No. 394 s. 1997 entitled "Establishing the Lupong Tagapamayapa Incentives Award."-->
    </p>
</div>
                </div>tions to attaining the objectives of the Katarungang Pambarangay (KP).
                <div class="form-container">holder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Secretary'] ?? ''); ?>">
        <form method="post" action="" enctype="multipart/form-data"></div>            </div>
            <!-- Punong Barangay -->
            <label for="LTFO" class="form-label" style="font-size: 1rem;">Sangguniang Barangay:</label>tainer">-->
            <div class="form-group">
                <label for="Punong_Barangay">Punong Barangay:</label>
                <input type="text" id="Punong_Barangay" name="Punong_Barangay" class="underline-input" 
                       placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Punong_Barangay'] ?? ''); ?>">lass="form-group">     placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Treasurer'] ?? ''); ?>">
            </div>                <label for="Punong_Barangay">Punong Barangay:</label>            </div>
text" id="Punong_Barangay" name="Punong_Barangay" class="underline-input" 
            <!-- Barangay Secretary -->older="Enter Name" value="<?php echo htmlspecialchars($certification_data['Punong_Barangay'] ?? ''); ?>">
            <div class="form-group">
                <label for="Barangay_Secretary">Barangay Secretary:</label>/label>
                <input type="text" id="Barangay_Secretary" name="Barangay_Secretary" class="underline-input" 
                       placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Secretary'] ?? ''); ?>">
            </div>el>lass="kagawad-input" 
" 
            <!-- Barangay Treasurer -->er="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Secretary'] ?? ''); ?>">="<?php echo htmlspecialchars($certification_data["Kagawad$i"] ?? ''); ?>">
            <div class="form-group">php endfor; ?>
                <label for="Barangay_Treasurer">Barangay Treasurer:</label>            </div>
                <input type="text" id="Barangay_Treasurer" name="Barangay_Treasurer" class="underline-input"  Barangay Treasurer -->
                       placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Treasurer'] ?? ''); ?>">
            </div>
put type="text" id="Barangay_Treasurer" name="Barangay_Treasurer" class="underline-input" tton type="submit" class="btn btn-primary" style="color: #003366;">Save</button>
            <!-- Kagawads -->     placeholder="Enter Name" value="<?php echo htmlspecialchars($certification_data['Barangay_Treasurer'] ?? ''); ?>"> 
            <hr class="my-3">  </div> </form>
            <label for="Kagawads" class="form-label" style="font-size: 1rem;">Kagawads:</label>
            <div class="kagawads-container">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                    <input type="text" name="Kagawad<?php echo $i; ?>" class="kagawad-input" 
                           placeholder="Kagawad <?php echo $i; ?> Name" ="kagawads-container">ath fill="#0099ff" fill-opacity="1" d="M0,224L30,224C60,224,120,224,180,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
                           value="<?php echo htmlspecialchars($certification_data["Kagawad$i"] ?? ''); ?>">
                <?php endfor; ?>
            </div>     placeholder="Kagawad <?php echo $i; ?> Name" mg src="images/ltialogo.png" alt="LTIA Logo" class="img-fluid" style="max-height: 80px; width: auto;" />
  value="<?php echo htmlspecialchars($certification_data["Kagawad$i"] ?? ''); ?>">
            <br>php endfor; ?>footer>
            <div class="submit-container">div>div>
                <button type="submit" class="btn btn-primary" style="color: #003366;">Save</button>
            </div>       <br>div>
           </form>            <div class="submit-container"></div>
    </div>on type="submit" class="btn btn-primary" style="color: #003366;">Save</button>
                </div>
                <footer class="position-relative">npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-100">
                    <path fill="#0099ff" fill-opacity="1" d="M0,224L30,224C60,224,120,224,180,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>abel" aria-hidden="true">
                </svg>tion-relative">dialog-centered">
                <div class="position-absolute bottom-0 end-0 mb-3 me-3 d-flex justify-content-center">.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-100">
                    <img src="images/ltialogo.png" alt="LTIA Logo" class="img-fluid" style="max-height: 80px; width: auto;" />0,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
                </div>
                </footer>iv class="position-absolute bottom-0 end-0 mb-3 me-3 d-flex justify-content-center">utton type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>g" alt="LTIA Logo" class="img-fluid" style="max-height: 80px; width: auto;" />
        </div>
    </div>
</div>>Ensure each criteria has the necessary files attached.</p>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>"btn btn-dark" data-bs-dismiss="modal">OK</button>
<!-- Missing Files Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">trap JS -->div>
    <div class="modal-dialog modal-dialog-centered">t src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>div>
        <div class="modal-content"><!-- Missing Files Modal --></div>
            <div class="modal-header">"alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
                <h5 class="modal-title text-danger" id="alertModalLabel">Missing Files!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>by="confirmModalLabel" aria-hidden="true">
            </div>r">dialog-centered">
            <div class="modal-body text-center"> text-danger" id="alertModalLabel">Missing Files!</h5>
                <p class="mb-0">Please verify that all required files are uploaded before submitting.</p>lose"></button>
                <p>Ensure each criteria has the necessary files attached.</p>
            </div>lass="modal-body text-center">utton type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-footer justify-content-center">at all required files are uploaded before submitting.</p>
                <button class="btn btn-dark" data-bs-dismiss="modal">OK</button>
            </div> class="mb-0">I confirm that all the criteria are correct.</p>
        </div>
    </div>
</div>
n type="button" class="btn btn-success" style="background-color: #3366ff;" id="confirmSubmit">Confirm</button>
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content"><!-- Confirmation Modal --></div>
            <div class="modal-header">nfirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <h5 class="modal-title text-primary" id="confirmModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>by="successModalLabel" aria-hidden="true">
            </div>r">dialog-centered">
            <div class="modal-body text-center"> text-primary" id="confirmModalLabel">Confirmation</h5>
                <p class="mb-0">I confirm that all the criteria are correct.</p>utton>
            </div>
            <div class="modal-footer justify-content-center">lass="modal-body text-center">utton type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>
                <button type="button" class="btn btn-secondary" style="background-color: #00ace6;" data-bs-dismiss="modal">Cancel</button>ll the criteria are correct.</p>
                <button type="button" class="btn btn-success" style="background-color: #3366ff;" id="confirmSubmit">Confirm</button>
            </div>lass="modal-footer justify-content-center">>Your submission has been successful.</p>
        </div>y" style="background-color: #00ace6;" data-bs-dismiss="modal">Cancel</button>
    </div>rm</button>
</div>utton class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>
div>
<!-- Success Message Modal -->div>
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">div>
    <div class="modal-dialog modal-dialog-centered"></div>
        <div class="modal-content">cess Message Modal -->
            <div class="modal-header">essModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <h5 class="modal-title text-success" id="successModalLabel">Submission Successful</h5>
                <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>).addEventListener('click', function () {
            </div>l-header">('hide');
            <div class="modal-body text-center">xt-success" id="successModalLabel">Submission Successful</h5>
                <p>Your submission has been successful.</p>    <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>successModal').modal('show');
            </div>         </div> }, 500);
            <div class="modal-footer justify-content-center">   <div class="modal-body text-center">
                <button class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>                <p>Your submission has been successful.</p></script>
            </div>
        </div>
    </div>e="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>ex="-1" aria-labelledby="modalmovMessageLabel" aria-hidden="true">
</div>

<script>
// Show success modal on confirm
document.getElementById('confirmSubmit').addEventListener('click', function () {modalmov_message'] === 'Files submitted successfully!' ? 'Success' : 'Notice'; ?>
    $('#confirmModal').modal('hide');
    setTimeout(function () {dal on confirmutton type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>
        $('#successModal').modal('show');tListener('click', function () {
    }, 500);
});ction () {><?php echo htmlspecialchars($_SESSION['modalmov_message'] ?? ''); ?></p>
</script>

<!-- Modal for Update (modalmov_message) -->n btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>
<div class="modal fade" id="modalmov_message" tabindex="-1" aria-labelledby="modalmovMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">odal for Update (modalmov_message) -->div>
            <div class="modal-header"><div class="modal fade" id="modalmov_message" tabindex="-1" aria-labelledby="modalmovMessageLabel" aria-hidden="true"></div>
                <h5 class="modal-title" id="modalmovMessageLabel"> class="modal-dialog modal-dialog-centered">
                    <?php echo $_SESSION['modalmov_message'] === 'Files submitted successfully!' ? 'Success' : 'Notice'; ?>
                </h5>
                <button type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>SSION['modalmov_message'] === 'Files submitted successfully!' ? 'Success' : 'Notice'; ?>ew bootstrap.Modal(document.getElementById('modalmov_message'));
            <div class="modal-body text-center">
                <p><?php echo htmlspecialchars($_SESSION['modalmov_message'] ?? ''); ?></p> type="button" class="btn-close" style="background-color: #2eb8b8;" data-bs-dismiss="modal" aria-label="Close"></button>($_SESSION['modalmov_message']); ?>
            </div>     </div> <?php endif; ?>
            <div class="modal-footer justify-content-center">   <div class="modal-body text-center">
                <button class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>         <p><?php echo htmlspecialchars($_SESSION['modalmov_message'] ?? ''); ?></p>t>
            </div>     </div>
        </div>            <div class="modal-footer justify-content-center"></html>















</html></body></script>    });        <?php endif; ?>            <?php unset($_SESSION['modalmov_message']); ?>            modalmovMessage.show();            var modalmovMessage = new bootstrap.Modal(document.getElementById('modalmov_message'));        <?php if (isset($_SESSION['modalmov_message'])): ?>    document.addEventListener("DOMContentLoaded", function () {<script></div>    </div>

















</html></body></script>    });        <?php endif; ?>            <?php unset($_SESSION['modalmov_message']); ?>            modalmovMessage.show();            var modalmovMessage = new bootstrap.Modal(document.getElementById('modalmov_message'));        <?php if (isset($_SESSION['modalmov_message'])): ?>    document.addEventListener("DOMContentLoaded", function () {<script></div>    </div>        </div>            </div>                <button class="btn btn-primary" style="background-color: #2eb8b8;" data-bs-dismiss="modal">Close</button>