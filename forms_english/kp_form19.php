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
$formUsed = 19;

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

// Check if formID exists in the URL
if (!empty($id)) {
    // Fetch data based on the provided formID
    $query = "SELECT appear_date, made_date, received_date, resp_date FROM hearings WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $rowCount = $stmt->rowCount();

    if ($rowCount > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // Extract and format the timestamp values
        $appearDate = new DateTime($row['appear_date']);
        $appear_day = $appearDate->format('j');

        $appear_month = $appearDate->format('F');
        $appear_year = $appearDate->format('Y');
        $appear_time = $appearDate->format('H:i'); // Format for the time input

        $madeDate = new DateTime($row['made_date']);
        $receivedDate = new DateTime($row['received_date']);
        $respDate = new DateTime($row['resp_date']);

        // Populate form inputs with the extracted values
        $currentDay = $appearDate->format('j');
        $currentMonth = $appearDate->format('F');
        $currentYear = $appearDate->format('Y');

        $existingMadeDay = $madeDate->format('j');
        $existingMadeMonth = $madeDate->format('F');
        $existingMadeYear = $madeDate->format('Y');

        $existingReceivedDay = $receivedDate->format('j');
        $existingReceivedMonth = $receivedDate->format('F');
        $existingReceivedYear = $receivedDate->format('Y');

        $existingRespDay = $respDate->format('j');
        $existingRespMonth = $respDate->format('F');
        $existingRespYear = $respDate->format('Y');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $madeDay = $_POST['made_day'] ?? '';
    $madeMonth = $_POST['made_month'] ?? '';
    $madeYear = $_POST['made_year'] ?? '';

    $receivedDay = $_POST['received_day'] ?? '';
    $receivedMonth = $_POST['received_month'] ?? '';
    $receivedYear = $_POST['received_year'] ?? '';

    $respDay = $_POST['resp_day'] ?? '';
    $respMonth = $_POST['resp_month'] ?? '';
    $respYear = $_POST['resp_year'] ?? '';
    

    $day = $_POST['day'] ?? '';
    $month = $_POST['month'] ?? '';
    $year = $_POST['year'] ?? '';
    $time = $_POST['time'] ?? '';

$dateTimeString = "$year-$month-$day $time";
$appearTimestamp = DateTime::createFromFormat('Y-F-j H:i', $dateTimeString);


if ($appearTimestamp !== false) {
    $appearTimestamp = $appearTimestamp->format('Y-m-d H:i:s');

    // Logic to handle date and time inputs
    $madeDate = createDateFromInputs($madeDay, $madeMonth, $madeYear);
    $receivedDate = createDateFromInputs($receivedDay, $receivedMonth, $receivedYear);
    $respDate = createDateFromInputs($respDay, $respMonth, $respYear);



    // Insert or update the appear_date in the hearings table
    $query = "INSERT INTO hearings (complaint_id, hearing_number, form_used, appear_date, made_date, received_date, resp_date)
              VALUES (:complaintId, :currentHearing, :formUsed, :appearDate, :madeDate, :receivedDate, :respDate)
              ON DUPLICATE KEY UPDATE
              hearing_number = VALUES(hearing_number),
              form_used = VALUES(form_used),
              appear_date = VALUES(appear_date),
              made_date = VALUES(made_date),
              received_date = VALUES(received_date), 
              resp_date = VALUES(resp_date)";


     $stmt = $conn->prepare($query);
    $stmt->bindParam(':complaintId', $complaintId);
    $stmt->bindParam(':currentHearing', $currentHearing);
    $stmt->bindParam(':formUsed', $formUsed);
    $stmt->bindParam(':appearDate', $appearTimestamp);
    $stmt->bindParam(':madeDate', $madeDate);
    $stmt->bindParam(':receivedDate', $receivedDate);
    $stmt->bindParam(':respDate', $respDate);

    if ($stmt->execute()) {
        header("Location: ../user_manage_case.php?id=$complaintId");
        exit;
    } else {
        $message = "Form submit failed.";
    }
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

function createTimestampFromInputs($day, $month, $year, $time) {
    if (!empty($day) && !empty($month) && !empty($year) && !empty($time)) {
        return date('Y-m-d H:i:s', strtotime("$year-$month-$day $time"));
    } else {
        return null; 
    }
}


include '../form_logo.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>KP Form 19 English</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <!-- here angle the link for responsive paper -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="formstyles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
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
        height: auto;
    }

    .paper[layout="landscape"] {
        width: 29.7cm;
        height: auto;
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

            </div>      <h5> <b style="font-family: 'Times New Roman', Times, serif;">KP Form No. 19 </b></h5>

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
        <h5 class="header" style="font-size: 18px;">OFFICE OF THE LUPONG TAGAPAMAYAPA</h5>

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
             
       

       
                <h3 style="text-align: center;"><b style="font-size: 18px;"> NOTICE OF HEARING <br>
(RE: FAILURE TO APPEAR)</b> </h3>
<form method="POST">
<div class="form-group" style="text-align: justify; text-indent: 0em;">

<div class="input-field">
        <p style="font-size: 18px;"> TO:     <span style="text-align: center; min-width: 200px; font-size: 18px; border-bottom: 1px solid black; display: inline-block;">
    <?php echo !empty($rspndtNames) ? $rspndtNames : '&nbsp;'; ?></span></p> </div>
                <div>
<p style="font-size: 18px; margin-left: 80px;"> Respondent/s </p> </div>

    <div>
     <p style="font-size: 18px;  text-indent: 2.8em; text-align: justify; ">
  You are hereby required to appear before me on the
  <input type="number" name="day" placeholder="day" min="1" max="31" style="width: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $appear_day; ?>" required>  of
                <select name="month" style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" required>

                    <?php foreach ($months as $m): ?>
        <?php if ($id > 0): ?>
            <option style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;"value="<?php echo $appear_month; ?>" <?php echo ($m === $appear_month) ? 'selected' : ''; ?>><?php echo $appear_month; ?></option>
        <?php else: ?>
            <option style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
                </select>,
                
                <input type="text" name="year" placeholder="year" size="1" style="font-size: 18px; text-align: center; border: none; border-bottom: 1px solid black;" value="<?php echo isset($appear_year) ? $appear_year : date('Y'); ?>" required> at <input type="time" id="time" name="time" size="5" style="text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;"  value="<?php echo $appear_time; ?>"required> o'clock in the morning/afternoon to explain why you failed to appear for mediation/conciliation scheduled on
                <input type="number" name="resp_day" placeholder="day" min="1" max="31" style="width: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $existingRespDay ?? ''; ?>" required>  of
                <select name="resp_month" style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" required>
    <?php foreach ($months as $m): ?>
        <?php if ($id > 0): ?>
            <option value="<?php echo $existingRespMonth; ?>" <?php echo ($m === $existingRespMonth) ? 'selected' : ''; ?>><?php echo $existingRespMonth; ?></option>
        <?php else: ?>
            <option value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>,
                
                <input type="text" name="resp_year" placeholder="year" size="1" style="font-size: 18px; text-align: center; border: none; border-bottom: 1px solid black;" value="<?php echo isset($existingRespYear) ? $existingRespYear : date('Y'); ?>" required>
    
and why your counterclaim (if any) arising from the complaint should not be dismissed, a certificate to bar the filing of said counterclaim in
court/government office should not be issued, and contempt proceedings should not be initiated in court for willful failure or refusal to
appear before the Punong Barangay/Pangkat ng Tagapagkasundo.
<br>
<br>
<p style="text-align: left; font-size: 18px; text-indent: 2em; "> This <input type="number" name="made_day" placeholder="day" min="1" max="31" style="width: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $existingMadeDay; ?>"> day of
    <select name="made_month" style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" required>
    <?php foreach ($months as $m): ?>
        <?php if ($id > 0): ?>
            <option style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $existingMadeMonth; ?>" <?php echo ($m === $existingMadeMonth) ? 'selected' : ''; ?>><?php echo $existingMadeMonth; ?></option>
        <?php else: ?>
            <option style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>,
                
<input type="number" name="made_year" size="1" style="font-size: 18px; text-align: center; border: none; border-bottom: 1px solid black;" placeholder="year" min="<?php echo date('Y') - 100; ?>" max="<?php echo date('Y'); ?>" value="<?php echo isset($existingMadeYear) ? $existingMadeYear : date('Y'); ?>">. 
              <div id="nameInput" style="display: none;">
  <input type="text" id="name" name="name" placeholder="Enter Name" oninput="updateOptionText(this.value)" onkeydown="checkEnterKey(event)">
</div>

        <br>
        <br>
        <p class="important-warning-text" style="text-align: center; font-size: 18px; margin-left: 350px; margin-right: auto;">
    <span style="min-width: 182px; font-size: 18px; border-bottom: 1px solid black; display: inline-block;">
        <?php echo !empty($punong_barangay) ? $punong_barangay : '&nbsp;'; ?>
    </span></p>
    <label id="punongbrgy" name="punongbrgy" size="25" style="text-align: center; margin-left: 420px; font-size: 18px; font-weight: normal; white-space: nowrap; max-width: 200px;">Punong Barangay/Pangkat Chairman</label>
               

<div style="text-align: left; font-size: 18px; text-indent: 2em; ">

Notified this <input type="text" name="received_day" placeholder="day" size="5" style="width: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $existingReceivedDay ?? ''; ?>"> day of
  <select name="received_month" style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" required>
    <?php foreach ($months as $m): ?>
        <?php if ($id > 0): ?>
            <option style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $existingReceivedMonth; ?>" <?php echo ($m === $existingReceivedMonth) ? 'selected' : ''; ?>><?php echo $existingReceivedMonth; ?></option>
        <?php else: ?>
            <option style="height: 30px; text-align: center; font-size: 18px; border: none; border-bottom: 1px solid black;" value="<?php echo $m; ?>" <?php echo ($m === $currentMonth) ? 'selected' : ''; ?>><?php echo $m; ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>,
<input type="number" name="received_year" placeholder="year" style="font-size: 18px; text-align: center; border: none; border-bottom: 1px solid black;" min="<?php echo date('Y') - 100; ?>" max="<?php echo date('Y'); ?>" value="<?php echo isset($existingReceivedYear) ? $existingReceivedYear : date('Y'); ?>">.
    
        <?php if (!empty($message)) : ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <br>
<br>
<div style="font-family: 'Times New Roman', Times, serif;">
    <!-- Section for Names and Labels -->
    <div style="display: flex; justify-content: space-around; align-items: center; margin-top: 20px;">
        <!-- Complainant Name and Label Container -->
        <div style="text-align: center;">
        <div style="font-size: 18px; margin-bottom: 10px;">
                Complainant/s
            </div>
            <span style="width:280px; border-bottom: 1px solid black; font-size: 18px; display: inline-block;">
                <?php echo !empty($cNames) ? nl2br(htmlspecialchars($cNames)) : '&nbsp;'; ?>
            </span>
           
        </div>
        

        <!-- Respondent Name and Label Container -->
        <div style="text-align: center;">
        <div style="font-size: 18px; margin-bottom: 10px;">
                Respondent/s
            </div>
            <span style="width:280px;  border-bottom: 1px solid black; font-size: 18px; display: inline-block;">
                <?php echo !empty($rspndtNames) ? nl2br(htmlspecialchars($rspndtNames)) : '&nbsp;'; ?>
            </span>
           
        </div>
    </div>
</div>
 
</div>
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
        filename: 'kp_form19_' + barangayCaseNumber + '.pdf', // Dynamic filename
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