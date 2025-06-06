<?php
session_start();
include '../connection.php';
$forTitle = $_SESSION['forTitle'] ?? '';
$cNames = $_SESSION['cNames'] ?? '';
$rspndtNames = $_SESSION['rspndtNames'] ?? '';
$cDesc = $_SESSION['cDesc'] ?? '';
$petition = $_SESSION['petition'] ?? '';
$cNum = $_SESSION['cNum'] ?? '';

$punong_barangay = $_SESSION['punong_barangay'] ?? '';

$complaintId = $_SESSION['current_complaint_id'] ?? '';
$currentHearing = $_SESSION['current_hearing'] ?? '';
$formUsed = 26;

// Fetch existing row values if the form has been previously submitted
$query = "SELECT * FROM hearings WHERE complaint_id = :complaintId AND form_used = :formUsed";
$stmt = $conn->prepare($query);
$stmt->bindParam(':complaintId', $complaintId);
$stmt->bindParam(':formUsed', $formUsed);
$stmt->execute();
$rowCount = $stmt->rowCount();

$currentYear = date('Y'); // Get the current year
// Array of months
$months = array(
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
);

$currentMonth = date('F'); 
$currentDay = date('j');

$id = $_GET['formID'] ?? '';

if (!empty($id)) {
    // Fetch data based on the provided formID
    $query = "SELECT made_date, fraud_check, violence_check, fourth_check, officer, settlement FROM hearings WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $rowCount = $stmt->rowCount();

    if ($rowCount > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // Extract and format the timestamp values
     
        $madeDate = new DateTime($row['made_date']);
       
        $existingMadeDay = $madeDate->format('j');
        $existingMadeMonth = $madeDate->format('F');
        $existingMadeYear = $madeDate->format('Y');

        $existingFraudCheck = $row['fraud_check'];
        $existingViolenceCheck = $row['violence_check'];
        $existingFourthCheck = $row['fourth_check'];

        $existingOfficer = $row['officer'];
        $existingSettlement = $row['settlement'];
        
        
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $madeDay = $_POST['made_day'] ?? '';
    $madeMonth = $_POST['made_month'] ?? '';
    $madeYear = $_POST['made_year'] ?? '';

    $fraudCheck = isset($_POST['fraudcheck']) ? 1 : 0;
    $violenceCheck = isset($_POST['violencecheck']) ? 1 : 0;
    $fourthCheck = isset($_POST['fourthcheck']) ? 1 : 0;
    
    $officer = $_POST['officer'] ?? '';
    $settlement = $_POST['settlement'] ?? '';
    
    // Logic to handle date and time inputs
    $madeDate = createDateFromInputs($madeDay, $madeMonth, $madeYear);

    // Insert or update the appear_date in the hearings table
    $query = "INSERT INTO hearings (complaint_id, hearing_number, form_used, made_date, fraud_check, violence_check, fourth_check, officer, settlement)
              VALUES (:complaintId, :currentHearing, :formUsed, :madeDate, :fraudCheck, :violenceCheck, :fourthCheck, :officer, :settlement)
              ON DUPLICATE KEY UPDATE
              hearing_number = VALUES(hearing_number),
              form_used = VALUES(form_used),
              made_date = VALUES(made_date),
              fraud_check = VALUES(fraud_check),
              violence_check = VALUES(violence_check),
              fourth_check = VALUES(fourth_check),
              officer = VALUES(officer),
              settlement = VALUES(settlement)
              ";


     $stmt = $conn->prepare($query);
    $stmt->bindParam(':complaintId', $complaintId);
    $stmt->bindParam(':currentHearing', $currentHearing);
    $stmt->bindParam(':formUsed', $formUsed);
    $stmt->bindParam(':madeDate', $madeDate);

    $stmt->bindParam(':fraudCheck', $fraudCheck);
    $stmt->bindParam(':violenceCheck', $violenceCheck);
    $stmt->bindParam(':fourthCheck', $fourthCheck);
    $stmt->bindParam(':officer', $officer);
    $stmt->bindParam(':settlement', $settlement);
   
    
    if ($stmt->execute()) {
        header("Location: ../user_manage_case.php?id=$complaintId");
        exit;
    } else {
        $message = "Form submit failed.";
    }
}

// Function to create a date from day, month, and year inputs
function createDateFromInputs($day, $month, $year) {
    if (!empty($day) && !empty($month) && !empty($year)) {
        $monthNum = date('m', strtotime("$month 1"));
        return date('Y-m-d', mktime(0, 0, 0, $monthNum, $day, $year));
    } else {
        return date('Y-m-d');
    }
}

include '../form_logo.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>KP Form 20-A English</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <!-- here angle the link for responsive paper -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="formstyles.css">
</head>
<style>
    /* Hide the number input arrows */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Hide the number input arrows for Firefox */
    input[type=number] {
        appearance: textfield;
        border: none;
        width: 40px;
        text-align: center;

    }
    h5 {
        margin: 0;
        padding: 0;
    }
    h3 {
        margin: 0;
        padding: 0;
    }
    .centered-line {
        border-bottom: 1px ridge black;
        display: inline-block;
        min-width: 350px;
        text-align: center;
    }
        
.profile-img{
   width: 3cm;
}

.header {
   text-align: center;
   padding-inline: 4cm;
}
h5 {
       margin: 0;
       padding: 0;
   }
   body {
    background: rgb(204, 204, 204);
}

.container {
    margin: 0 auto;
}

.paper {
    background: white;
    margin: 0 auto;
    margin-bottom: 0.5cm;
    box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
}

/* Add Bootstrap responsive classes for different screen sizes */
@media (min-width: 992px) {
    .paper {
        width: 21cm;
        height: 29.7cm;
    }

    .paper[layout="landscape"] {
        width: 29.7cm;
        height: 21cm;
    }
}

@media (min-width: 1200px) {
    .paper[size="A3"] {
        width: 29.7cm;
        height: 42cm;
    }

    .paper[size="A3"][layout="landscape"] {
        width: 42cm;
        height: 29.7cm;
    }

    .paper[size="A5"] {
        width: 14.8cm;
        height: 21cm;
    }

    .paper[size="A5"][layout="landscape"] {
        width: 21cm;
        height: 14.8cm;
    }
}

@media print {
    body, .paper {
        background: white;
        margin: 0;
        box-shadow: 0;
    }
  /* Adjust print styles here */
  .input-field {
    /* Example: Ensure input fields do not expand beyond their containers */
    max-width: 100%;
  }
  input[name="saveForm"] {
            display: none;
        }
  
  input[type="text"] {
        border-bottom: 1px solid black !important;
    }
        /* Hide elements that should not be printed */
        .btn, .top-right-buttons {
            display: none !important;
    }
}
</style>
<body>
<div class="container">
        <div class="paper">
        <div class="top-right-buttons">
    <button class="btn btn-primary print-button common-button" onclick="window.print()" style="position:fixed; right: 20px;">
        <i class="fas fa-print button-icon"></i> Print
    </button>
    <button class="btn btn-success download-button common-button" id="downloadButton" style="position:fixed; right: 20px; top: 75px; ">
        <i class="fas fa-file button-icon"></i> Download
    </button>

    <a href="../user_manage_case.php?id=<?php echo $_SESSION['current_complaint_id']; ?>">
        <button class="btn common-button" style="position:fixed; right: 20px; top: 177px;">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </a>


            </div>      <h5> <b style="font-family: 'Times New Roman', Times, serif;">KP Form No. 20-A </b></h5>

           <div style="display:inline-block;text-align: center;">
<img class="profile-img" src="<?php echo $profilePicture; ?>" alt="Profile Picture" style="height: 80px; width: 80px;">
<img class="profile-img" src="<?php echo $lgulogo; ?>" alt="Lgu Logo" style="height: 80px; width: 80px;">
<img class="profile-img" src="<?php echo $citylogo; ?>" alt="City Logo" style="height: 80px; width: 80px;">
<div style="text-align: center; font-family: 'Times New Roman', Times, serif;">
<br>
        <h5 class="header" style="font-size: 18px;">Republic of the Philippines</h5>
        <h5 class="header" style="font-size: 18px;">Province of Laguna</h5>
        <h5 class="header" style="text-align: center; font-size: 18px;">
        <?php
$municipality = $_SESSION['municipality_name'];
$isCity = in_array($municipality, ['Biñan', 'Calamba', 'Cabuyao', 'San Pablo', 'San Pedro', 'Santa Rosa']);
$isMunicipality = !$isCity;

if ($isCity) {
    echo 'City of ' . $municipality;
} elseif ($isMunicipality) {
    echo 'Municipality of ' . $municipality;
} else {
    echo 'City/Municipality of ' . $municipality;
}
?>
</h5>
        <h5 class="header" style="font-size: 18px;">Barangay <?php echo $_SESSION['barangay_name']; ?></h5>
        <h5 class="header" style="font-size: 18px; margin-top: 5px;">OFFICE OF THE LUPONG TAGAPAMAYAPA</h5>
   
</div>
<br>
<br>

            <?php
            $months = [
                'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            $currentYear = date('Y');
            ?>

<div class="form-group" style="text-align: justify; font-family: 'Times New Roman', Times, serif;" >
    <div class="input-field" style="float: right; width: 50%;">
        <!-- case num here -->
        <p style="text-align: left; margin-left:30px; font-size: 18px;">Barangay Case No.<span style="min-width: 182px; font-size: 18px; border-bottom: 1px solid black; display: inline-block;">
    <?php echo !empty($cNum) ? $cNum : '&nbsp;'; ?></span></p>

        <p style="text-align: left; margin-left:30px; margin-top: 0; font-size: 18px;"> For:  <span style="border-bottom: 1px solid black; font-size: 18px;"><?php echo !empty($forTitle) ? nl2br(htmlspecialchars($forTitle)) : '&nbsp;'; ?></span> </p>
    </div>
</div>
<div class="form-group" style="text-align: justify; text-indent: 0em; font-family: 'Times New Roman', Times, serif;">
    <div class="label"></div>
    <span style="border-bottom: 1px solid black; font-size: 18px;"><?php echo !empty($cNames) ? nl2br(htmlspecialchars($cNames)) : '&nbsp;'; ?></span>
  
              
<p style="font-size: 18px;"> Complainant/s </p>
<p style="font-size: 18px;">- against -</p>
                </div>

<div class="form-group" style="text-align: justify; text-indent: 0em; font-family: 'Times New Roman', Times, serif;">
    <div class="label"></div>
    <span style="border-bottom: 1px solid black; font-size: 18px;"><?php echo !empty($rspndtNames) ? nl2br(htmlspecialchars($rspndtNames)) : '&nbsp;'; ?></span>
   
<p style="font-size: 18px;"> Respondent/s </p> 
             
       
       
             <form method="POST">
                <h3 style="text-align: center;"><b style="font-size: 18px;"> CERTIFICATION TO FILE ACTION</b> </h3>
                <div style="text-align: left;">
    <p style="text-align: justify; margin-top: 0; font-size: 18px;">This is to certify that:</p>
    
    <div class="form-group" style="text-align: justify;">
        <div class="checkbox" style="text-align: left; text-indent: 2em;">
            <input type="checkbox" id="checkbox1" name="fraudcheck" <?php if(isset($existingFraudCheck) && $existingFraudCheck == 1) echo "checked"; ?>>
            <label for="checkbox1" style="text-indent: 0em; margin-left: 1px; font-size: 18px;">
                1. There has been a personal confrontation between the parties before the Punong Barangay but mediation failed;
            </label>
        </div>
    </div>

    <div class="form-group" style="text-align: justify;">
        <div class="checkbox" style="text-align: left; text-indent: 2em;">
            <input type="checkbox" id="checkbox2" name="violencecheck" <?php if(isset($existingViolenceCheck) && $existingViolenceCheck == 1) echo "checked"; ?>>
            <label for="checkbox2" style="text-indent: 0em; margin-left: 1px; font-size: 18px;">
                2. The Pangkat ng Tagapagkasundo was constituted but the personal confrontation before the Pangkat likewise did not result in a settlement; and
            </label>
        </div>
    </div>
    <div class="form-group" style="text-align: justify;">
        <div class="checkbox" style="text-align: left; text-indent: 2em;">
        <input type="checkbox" id="checkbox3" name="fourthcheck" <?php if(isset($existingFourthCheck) && $existingFourthCheck == 1) echo "checked"; ?>>
        <label for="checkbox3" style="text-indent: 0em; margin-left: 1px; font-size: 18px;">
            3. Therefore, the corresponding complaint for the dispute may now be filed in court/government office.
        </label>
    </p>
</div>

            <br>

            <div style="text-align: justify; text-indent: 0em; margin-left: 38.5px;font-size: 18px;"> This <input style="border: none; border-bottom: 1px solid black; border-bottom: 1px solid black;  font-size: 18px;" type="number" name="made_day" placeholder="day" min="1" max="31" value="<?php echo $existingMadeDay; ?>"> day of
    <select style="height: 30px; border: none; border-bottom: 1px solid black; border-bottom: 1px solid black;  font-size: 18px;" name="made_month" required>
    <?php foreach ($months as $m): ?>
        <?php if ($id > 0): ?>
            <option style="border: none; border-bottom: 1px solid black; border-bottom: 1px solid black;  font-size: 18px;" value="<?php echo $existingMadeMonth; ?>" <?php echo ($m === $existingMadeMonth) ? 'selected' : ''; ?>><?php echo $existingMadeMonth; ?></option>
        <?php else: ?>
            <option style="border: none; border-bottom: 1px solid black; border-bottom: 1px solid black;  font-size: 18px;" value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>,
                
                <input style="border: none; border-bottom: 1px solid black; border-bottom: 1px solid black; width:44px; font-size: 18px;" type="number" name="made_year" size="1" placeholder="year" min="<?php echo date('Y') - 100; ?>" max="<?php echo date('Y'); ?>" value="<?php echo isset($existingMadeYear) ? $existingMadeYear : date('Y'); ?>">.

</div>

        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <p class="important-warning-text" style="text-align: center; font-size: 18px; margin-left: 500px; margin-right: auto;">
        <input type="text" name="officer" style="text-align:center;min-width: 182px; font-size: 18px; border:none; border-bottom: 1px solid black; display: inline-block;" 
        value="<?php echo isset($existingOfficer) ? $existingOfficer : ''; ?>   "> Pangkat Secretary
    </p>
    <br>
</div>
</p>
<br>
    <p style="text-align: left; margin-top: 0;font-size: 18px; text-indent: 0em;">
        Attested by:</p>
        <p class="important-warning-text" style="text-align: center; font-size: 18px; margin-left: -530px; margin-right: auto;">
        <input type="text" name="settlement" style="min-width: 182px; font-size: 18px; border: none; border-bottom: 1px solid black; display: inline-block;" 
        value="<?php echo isset($existingSettlement) ? $existingSettlement : ''; ?>">
</p>
    <label id="pChairman" name="pChairman" size="25" style="text-align: center; margin-left:  30px;   font-size: 18px; font-weight: normal; white-space: nowrap; max-width: 200px;">Pangkat Chairman</label>
          

    <?php if (!empty($message)) : ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
  
            
    <input type="submit" name="saveForm" value="Save" class="btn btn-primary print-button common-button" style="position: fixed; right: 20px; top: 130px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
</form>
<script>
var barangayCaseNumber = "<?php echo $cNum; ?>"; // Assume $cNum is your case number variable
document.getElementById('downloadButton').addEventListener('click', function () {
    // Elements to hide during PDF generation
    var buttonsToHide = document.querySelectorAll('.top-right-buttons button');
    var saveButton = document.querySelector('input[name="saveForm"]');

// Hide the specified buttons
buttonsToHide.forEach(function (button) {
    button.style.display = 'none';
});

// Hide the Save button
saveButton.style.display = 'none';

// Ensure input borders are visible for PDF generation
var toInputs = document.querySelectorAll('input[name^="to"]');
toInputs.forEach(function(input) {
    input.style.borderBottom = '1px solid black';
});

var pdfContent = document.querySelector('.paper');
var downloadButton = document.getElementById('downloadButton');

// Hide the download button
downloadButton.style.display = 'none';

     // Modify the filename option to include the barangay case number
     html2pdf(pdfContent, {
        margin: [10, 10, 10, 10],
        filename: 'kp_form20A_' + barangayCaseNumber + '.pdf', // Dynamic filename
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: {
        scale: 2, // Adjust the scale as necessary
        width: pdfContent.clientWidth, // Set a fixed width based on the on-screen width of the content
        windowWidth: document.documentElement.offsetWidth // Set the window width to match the document width
    },
    jsPDF: {
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait'
    }
}).then(function () {
    // Show the download button after PDF generation
    downloadButton.style.display = 'inline-block';

    // Show the Save button after PDF generation
    saveButton.style.display = 'inline-block';

    // Show the other buttons after PDF generation
    buttonsToHide.forEach(function (button) {
        button.style.display = 'inline-block';
    });

    // Restore borders for all input types and select
    inputFields.forEach(function (field) {
        field.style.border = ''; // Use an empty string to revert to default border
        });
    });
});
</script>
</div>
</div>
</body>
</html>
    