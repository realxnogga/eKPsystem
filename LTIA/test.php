<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Step 1: Get the municipality ID for the logged-in user
    $query = "SELECT municipality_id FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_row && isset($user_row['municipality_id'])) {
        $municipality_id = $user_row['municipality_id'];

        // Step 2: Fetch municipality name
        $query = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();

        $municipality_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $municipality_name = $municipality_row ? strtoupper($municipality_row['municipality_name']) : 'No municipality found';

        // Step 3: Fetch available years from movrate table for dropdown
        $query = "SELECT DISTINCT year FROM movrate ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get selected year from request or default to the latest year
        $selectedYear = $_GET['year'] ?? $years[0];
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

function getAdjectivalRating($total)
{
    if ($total >= 100) {
        return "Outstanding";
    } elseif ($total >= 90 && $total <= 99) {
        return "Very Satisfactory";
    } elseif ($total >= 80 && $total <= 89) {
        return "Fair";
    } elseif ($total >= 70 && $total <= 79) {
        return "Poor";
    } else {
        return "Very Poor";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LTIA Form 3</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <style>
        .spacingtabs {
            padding-left: 2em;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        .print-content, .print-content * {
            visibility: visible;
        }
        .print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: auto;
            font-size: 12px;
            margin: 0;
            box-sizing: border-box;
        }
        .headerwiwit {
            height: fit-content;
            display: flex;
            flex-direction: row;
            background-color: #000035;
        }
        .headerwiwit div {
            padding: none;
        }
        .headerwiwit div h1 {
            font-size: medium;
        }
        .headerwiwit div img {
            height: 4rem;
            width: 4rem;
        }
        .print-content .card {
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            box-shadow: none;
        }
        .print-content table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black; /* Ensure borders are black */
        }
        .print-content th,
        .print-content td {
            padding: 2px; /* Reduce padding */
            font-size: 12px; /* Adjust font-size */
            line-height: 1; /* Adjust line-height */
            border: 1px solid black; /* Ensure borders are black */
        }
        .btn,
        .btn-save,
        .text-right {
            display: none;
        }
        .print-content h1 {
            font-size: 14pt;
        }
        .print-content p,
        .print-content b {
            font-size: 12px;
            line-height: 1; /* Adjust line-height */
        }
        .print-content .spacingtabs {
            display: inline-block;
            width: 6em;
            text-align: center;
        }
        .print-content p {
            word-wrap: break-word;
        }
        .print-content strong {
            color: black; /* Ensure the text is black */
        }
    }
    @media (max-width: 768px) {
        .headerwiwit {
            flex-direction: column;
            align-items: center;
        }
        .headerwiwit .dilglogo {
            margin-bottom: 10px;
        }
        .headerwiwit .text-left {
            text-align: center;
        }
    }
    .underline-input {
        text-align: center;
        border: none;
        border-bottom: 1px solid #5A5A5A;
        outline: none;
        background-color: transparent;
        width: 100%;
        font-size: 10px;
        padding: 5px 0;
    }
    .underline-input:focus {
        border-bottom-color: #007bff;
    }
    .custom-hr {
        border-top: 3px dashed black;
    }
    .docscode table {
        font-size: 10px; /* Reduce the font size */
    }
    .docscode table h1 {
        font-size: 10px; /* Reduce the font size of h1 */
    }
    .docscode table input {
        font-size: 10px; /* Reduce the font size of input elements */
    }
</style>
</head>

<body class="bg-[#E8E8E7]">
    <?php include "../admin_sidebar_header.php"; ?>
    <div class="p-4 sm:ml-44 ">
        <div class="rounded-lg mt-16">
            <div class="card">
                <div class="card-body">
                    <div class="menu flex items-center justify-between">
                        <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_admin_overallsummary.php';">
                            <i class="ti ti-building-community mr-2"></i> Back
                        </button>
                        <div class="flex items-center space-x-4">
                            <form method="get" action="">
                                <select name="year" onchange="this.form.submit()" class="form-select">
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>" <?php if ($year == $selectedYear) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($year); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <button onclick="printSecondCard()" class="btn btn-primary">Print</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="print-content">
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="headerwiwit flex items-center justify-between gap-x-5">
                            <div class="dilglogo flex justify-center">
                                    <img src="../img/dilg.png" alt="DILG Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
                            </div>
                            <div class="text-left flex-1">
                                <strong>DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</strong>
                                   <h1 class="text-xl font-bold">
                                    POLICY FORMULATION COMMENT SHEET
                                </h1>
                            </div>
                            <div class="docscode flex justify-end text-xs">
                                <table class="table-auto border border-black w-auto text-xs">
                                    <tr>
                                        <td colspan="3" class="bg-black text-white p-1"><b>Document Code</b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="p-1"><h1 class="text-sm">FM-OP-DILG-CO-41-03</h1></td>
                                    </tr>
                                    <tr class="bg-[#f1d8f0]">
                                        <td class="p-1 text-center border border-black">Rev. No.</td>
                                        <td class="text-center border border-black">Eff. Date</td>
                                        <td class="text-center border border-black">Page</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center border border-black"><input type="number" class="underline-input"></td>
                                        <td class="text-center border border-black"><input type="date" class="underline-input"></td>
                                        <td class="text-center border border-black">1 of 1</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            Please accomplish the comment sheet by indicating the <b>(a)</b> policy provision you wish to comment on, <b>(b)</b> your corresponding feedback (e.g. queries, recommendations, comments, etc.), and <b>(c)</b> the rationale behind the feedback you provided (e.g. legal basis, studies, relevant experiences, etc.). An example is provided below for your guidance. Thank you.
                        </div>

                        <div class="overflow-x-auto mt-4 print:hidden">
                        <b>EXAMPLE:</b><br>
                            <table class="table table-bordered w-full border border-black">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-center">PROVISIONS</th>
                                        <th class="px-4 py-2 text-center">FEEDBACK</th>
                                        <th class="px-4 py-2 text-center">BASIS FOR FEEDBACK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Par. (b) of Sec.</td>
                                        <td>Why is it phrased as...? If possible, we suggest that... such that this provision becomes consistent with...</td>
                                        <td>Sec. 10 of R.A... provides that... hence, the... stated in Par. (b) of Sec. 13 of your proposed policy may be in conflict with the...</td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr class="custom-hr">
                        </div>
                        
                        <h3>
                            <strong>NAME OF POLICY REVIEWED:</strong> <input type="text" class="underline-input">
                        </h3><br>
                        <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-1 text-center"><h2>PROVISIONS</h2></th>
                                        <th class="px-2 py-1 text-center"><h2>FEEDBACK</h2></th>
                                        <th class="px-2 py-1 text-center"><h2>BASIS FOR FEEDBACK</h2></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                        <td><input type="text" class="underline-input" style="margin: 0; padding: 0px; width: 100%;"></td>
                                    </tr>
                                       
                                </tbody>
                            </table>
                            <br>
                            PREPARED BY:
                            <br>
                            <input type="text" class="underline-input" placeholder="Name of Policy Reviewed"><br>
                            <p>[Name and Position]</p>
                            <p>[Name of Office]</p>
                    </div>
                </div>
            </div>
            <script>
                function printSecondCard() {
                    window.print();
                }
            </script>
        </div>
    </div>
</body>
</html>


<!--assessor-->
<?php
session_start();

include '../connection.php'; // Ensure this file is using a PDO connection

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['admin', 'assessor'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID from the session

try {
    // Step 1: Retrieve the municipality_id from the logged-in user's record
    $query = "SELECT municipality_id FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_row && isset($user_row['municipality_id'])) {
        $municipality_id = $user_row['municipality_id'];
        
        // Step 2: Use the municipality_id to fetch the corresponding municipality_name
        $query = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $municipality_row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Step 3: Set the municipality name for use in the form
        if ($municipality_row && isset($municipality_row['municipality_name'])) {
            $municipality_name = $municipality_row['municipality_name'];
        } else {
            $municipality_name = 'No municipality found for this user';
        }
        // Step 4: Fetch barangays associated with this municipality
        $query = "SELECT id, barangay_name FROM barangays WHERE municipality_id = :municipality_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        $barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $municipality_name = 'No municipality ID found for this user';
        $barangays = []; // Empty array if no barangays found
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

try {
  // Fetch the `barangay_id` based on the selected barangay name
  $query = "SELECT id FROM barangays WHERE barangay_name = :barangay_name";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':barangay_name', $selected_barangay_name, PDO::PARAM_STR);
  $stmt->execute();
  $barangay_row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($barangay_row && isset($barangay_row['id'])) {
      $barangay_id = $barangay_row['id'];

      // Fetch the `mov_id` from the `mov` table based on the `barangay_id`
      $query = "SELECT id FROM mov WHERE barangay_id = :barangay_id LIMIT 1";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_INT);
      $stmt->execute();
      $mov_row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($mov_row && isset($mov_row['id'])) {
          $mov_id = $mov_row['id']; // Retrieved `mov_id`
      } else {
          $mov_id = null; // No `mov_id` found
      }
  } else {
      $mov_id = null; // No `barangay_id` found
  }
} catch (PDOException $e) {
  echo "Error fetching mov_id: " . $e->getMessage();
}

?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
<style>
/* CSS to ensure alerts fit well within the table cells */
.alert {
    display: flex;           /* Use flex to center content */
    align-items: center;    /* Vertically center the content */
    justify-content: center; /* Horizontally center the content */
    padding: 0.5rem 1rem;   /* Adjust padding for better spacing */
    margin: 0;              /* Remove margin to prevent overflow */
    border-radius: 0.25rem; /* Optional: make the corners rounded */
    font-size: 0.875rem;    /* Optional: adjust font size for better readability */
}

.file-column {
    overflow: hidden;       /* Prevent overflow of alert */
    text-overflow: ellipsis; /* Add ellipsis if text is too long */
    white-space: nowrap;    /* Prevent wrapping */
}

</style>
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
 <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <link rel="stylesheet" href="css/td_hover.css">


 <script>
$(document).ready(function () {
    // Function to show modal with message
    function showModal(message) {
        $('#alertMessage').text(message);
        $('#alertModal').modal('show');
    }

    // Function to highlight empty rating inputs only
    function highlightEmptyInputs() {
        $('input[type="number"].score-input').each(function() {
            if ($(this).val() === '') {
                $(this).css({
                    'background-color': '#ffebee',  // Light red background
                    'border-color': '#ef5350'       // Red border
                });
            } else {
                $(this).css({
                    'background-color': '',  // Reset to default
                    'border-color': ''       // Reset to default
                });
            }
        });
    }

    // Call the function on page load
    highlightEmptyInputs();

    // Split the event handlers for ratings and remarks
    $('input[type="number"].score-input').on('change', function() {
        // Check if a barangay is selected
        var selectedBarangay = $('#barangay_select').val();
        if (!selectedBarangay) {
            showModal('Please select a barangay first');
            $(this).val(''); // Clear the input
            $(this).css({
                'background-color': '#ffebee',
                'border-color': '#ef5350'
            });
            return;
        }
        
        // Validate min/max
        var min = parseFloat($(this).attr('min'));
        var max = parseFloat($(this).attr('max'));
        var value = parseFloat($(this).val());
        
        if (value < min || value > max) {
            showModal(`Please enter a number between ${min} and ${max}`);
            $(this).val(''); // Clear invalid input
            $(this).css({
                'background-color': '#ffebee',
                'border-color': '#ef5350'
            });
            return;
        }
        
        // Update input styling based on value
        if ($(this).val() !== '') {
            $(this).css({
                'background-color': '',
                'border-color': ''
            });
        } else {
            $(this).css({
                'background-color': '#ffebee',
                'border-color': '#ef5350'
            });
        }
        
        // Automatically submit the form
        $('form').submit();
    });

    // Separate handler for remarks - no highlighting
    $('textarea[placeholder="Remarks"]').on('change', function() {
        var selectedBarangay = $('#barangay_select').val();
        if (!selectedBarangay) {
            showModal('Please select a barangay first');
            $(this).val(''); // Clear the input
            return;
        }
        
        // Automatically submit the form
        $('form').submit();
    });

    // Update clearRates function
    function clearRates() {
        // Clear all rate inputs
        $('input[type="number"].score-input').val('');
        
        // After clearing rates, highlight empty inputs
        highlightEmptyInputs();
        
        // Reset only the rating input styling
        $('input[type="number"].score-input').css({
            'background-color': '#ffebee',
            'border-color': '#ef5350'
        });
    }

    // Handle barangay selection
    $('#barangay_select').on('change', function () {
        var selectedBarangayName = $(this).val();
        $('#selected_barangay').val(selectedBarangayName);

        if (selectedBarangayName) {
            $.ajax({
                url: 'fetch_files.php',
                method: 'POST',
                data: { barangay_name: selectedBarangayName },
                dataType: 'json',
                success: function (data) {
                    console.log('Returned data:', data);

                    // Check for an error response
                    if (data.error) {
                        showModal(data.error); // Display the error message
                        resetAllFields(); // Clear fields if an error occurs
                        $('#mov_year').text(' '); // Reset the year display
                        return;
                    }

                    // Extract and set barangay_id and mov_id
                    $('#barangay_id').val(data.barangay_id || '');
                    $('#mov_id').val(data.mov_id || '');

                    if (data.year) {
                        $('#mov_year').text(data.year); // Set the year in the h1 element
                    } else {
                        $('#mov_year').text(''); // Reset the year display
                    }

                    if (!data.mov_id) {
                        resetAllFields(); // Clear fields if no mov_id is found
                        return;
                    }

                    // Handle each PDF file from the returned data
                    var fileTypes = [
                        'IA_1a', 'IA_1b', 'IA_2a', 'IA_2b', 'IA_2c', 'IA_2d', 'IA_2e',
                        'IB_1forcities', 'IB_1aformuni', 'IB_1bformuni', 'IB_2', 'IB_3',
                        'IB_4', 'IC_1', 'IC_2', 'ID_1', 'ID_2', 'IIA', 'IIB_1', 'IIB_2',
                        'IIC', 'IIIA', 'IIIB', 'IIIC_1forcities', 'IIIC_1forcities2',
                        'IIIC_1forcities3', 'IIIC_2formuni1', 'IIIC_2formuni2',
                        'IIIC_2formuni3', 'IIID', 'IV_forcities', 'IV_muni',
                        'V_1', 'threepeoplesorg'
                    ];

                    // Clear previous file columns
                    $('.file-column').html('');

                    // Populate or clear file columns based on available data
                    fileTypes.forEach(function (type) {
                        var fileColumn = $('.file-column[data-type="' + type + '"]');
                        var fileKey = type + '_pdf_File';
                        if (data[fileKey]) {
                            var filePath = 'movfolder/' + data[fileKey];
                            $('.view-pdf[data-type="' + type + '"]').attr('data-file', filePath).show();
                            fileColumn.html('<button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-type="' + type + '" data-file="' + filePath + '">View</button>');
                        } else {
                            fileColumn.html('<div class="alert alert-warning mb-0">No uploaded file</div>');
                        }
                    });

                    // Handle rates
                    if (data.rates) {
                        $('input[name="IA_1a_pdf_rate"]').val(data.rates.IA_1a_pdf_rate || '');
                        $('input[name="IA_1b_pdf_rate"]').val(data.rates.IA_1b_pdf_rate || '');
                        $('input[name="IA_2a_pdf_rate"]').val(data.rates.IA_2a_pdf_rate || '');
                        $('input[name="IA_2b_pdf_rate"]').val(data.rates.IA_2b_pdf_rate || '');
                        $('input[name="IA_2c_pdf_rate"]').val(data.rates.IA_2c_pdf_rate || '');
                        $('input[name="IA_2d_pdf_rate"]').val(data.rates.IA_2d_pdf_rate || '');
                        $('input[name="IA_2e_pdf_rate"]').val(data.rates.IA_2e_pdf_rate || '');
                        $('input[name="IB_1forcities_pdf_rate"]').val(data.rates.IB_1forcities_pdf_rate || '');
                        $('input[name="IB_1aformuni_pdf_rate"]').val(data.rates.IB_1aformuni_pdf_rate || '');
                        $('input[name="IB_1bformuni_pdf_rate"]').val(data.rates.IB_1bformuni_pdf_rate || '');
                        $('input[name="IB_2_pdf_rate"]').val(data.rates.IB_2_pdf_rate || '');
                        $('input[name="IB_3_pdf_rate"]').val(data.rates.IB_3_pdf_rate || '');
                        $('input[name="IB_4_pdf_rate"]').val(data.rates.IB_4_pdf_rate || '');
                        $('input[name="IC_1_pdf_rate"]').val(data.rates.IC_1_pdf_rate || '');
                        $('input[name="IC_2_pdf_rate"]').val(data.rates.IC_2_pdf_rate || '');
                        $('input[name="ID_1_pdf_rate"]').val(data.rates.ID_1_pdf_rate || '');
                        $('input[name="ID_2_pdf_rate"]').val(data.rates.ID_2_pdf_rate || '');
                        $('input[name="IIA_pdf_rate"]').val(data.rates.IIA_pdf_rate || '');
                        $('input[name="IIB_1_pdf_rate"]').val(data.rates.IIB_1_pdf_rate || '');
                        $('input[name="IIB_2_pdf_rate"]').val(data.rates.IIB_2_pdf_rate || '');
                        $('input[name="IIC_pdf_rate"]').val(data.rates.IIC_pdf_rate || '');
                        $('input[name="IIIA_pdf_rate"]').val(data.rates.IIIA_pdf_rate || '');
                        $('input[name="IIIB_pdf_rate"]').val(data.rates.IIIB_pdf_rate || '');
                        $('input[name="IIIC_1forcities_pdf_rate"]').val(data.rates.IIIC_1forcities_pdf_rate || '');
                        $('input[name="IIIC_1forcities2_pdf_rate"]').val(data.rates.IIIC_1forcities2_pdf_rate || '');
                        $('input[name="IIIC_1forcities3_pdf_rate"]').val(data.rates.IIIC_1forcities3_pdf_rate || '');
                        $('input[name="IIIC_2formuni1_pdf_rate"]').val(data.rates.IIIC_2formuni1_pdf_rate || '');
                        $('input[name="IIIC_2formuni2_pdf_rate"]').val(data.rates.IIIC_2formuni2_pdf_rate || '');
                        $('input[name="IIIC_2formuni3_pdf_rate"]').val(data.rates.IIIC_2formuni3_pdf_rate || '');
                        $('input[name="IIID_pdf_rate"]').val(data.rates.IIID_pdf_rate || '');
                        $('input[name="IV_forcities_pdf_rate"]').val(data.rates.IV_forcities_pdf_rate || '');
                        $('input[name="IV_muni_pdf_rate"]').val(data.rates.IV_muni_pdf_rate || '');
                        $('input[name="V_1_pdf_rate"]').val(data.rates.V_1_pdf_rate || '');
                        $('input[name="threepeoplesorg_rate"]').val(data.rates.threepeoplesorg_rate || '');
                        $('#status_rate').text(data.rates.status_rate || 'Rate Status: Pending');
                        
                        // After setting all the rates, check for empty ones and highlight them
                        $('input[type="number"].score-input').each(function() {
                            var value = $(this).val();
                            if (value === '' || value === null) {
                                $(this).css('background-color', '#ffebee'); // Light red background
                                $(this).css('border-color', '#ef5350'); // Red border
                            } else {
                                $(this).css('background-color', ''); // Reset background
                                $(this).css('border-color', ''); // Reset border
                            }
                        });
                    } else {
                        clearRates();
                    }

                    // Handle remarks
                    if (data.remarks) {
                        $('textarea[name="IA_1a_pdf_remark"]').val(data.remarks.IA_1a_pdf_remark || '');
                        $('textarea[name="IA_1b_pdf_remark"]').val(data.remarks.IA_1b_pdf_remark || '');
                        $('textarea[name="IA_2a_pdf_remark"]').val(data.remarks.IA_2a_pdf_remark || '');
                        $('textarea[name="IA_2b_pdf_remark"]').val(data.remarks.IA_2b_pdf_remark || '');
                        $('textarea[name="IA_2c_pdf_remark"]').val(data.remarks.IA_2c_pdf_remark || '');
                        $('textarea[name="IA_2d_pdf_remark"]').val(data.remarks.IA_2d_pdf_remark || '');
                        $('textarea[name="IA_2e_pdf_remark"]').val(data.remarks.IA_2e_pdf_remark || '');
                        $('textarea[name="IB_1forcities_pdf_remark"]').val(data.remarks.IB_1forcities_pdf_remark || '');
                        $('textarea[name="IB_1aformuni_pdf_remark"]').val(data.remarks.IB_1aformuni_pdf_remark || '');
                        $('textarea[name="IB_1bformuni_pdf_remark"]').val(data.remarks.IB_1bformuni_pdf_remark || '');
                        $('textarea[name="IB_2_pdf_remark"]').val(data.remarks.IB_2_pdf_remark || '');
                        $('textarea[name="IB_3_pdf_remark"]').val(data.remarks.IB_3_pdf_remark || '');
                        $('textarea[name="IB_4_pdf_remark"]').val(data.remarks.IB_4_pdf_remark || '');
                        $('textarea[name="IC_1_pdf_remark"]').val(data.remarks.IC_1_pdf_remark || '');
                        $('textarea[name="IC_2_pdf_remark"]').val(data.remarks.IC_2_pdf_remark || '');
                        $('textarea[name="ID_1_pdf_remark"]').val(data.remarks.ID_1_pdf_remark || '');
                        $('textarea[name="ID_2_pdf_remark"]').val(data.remarks.ID_2_pdf_remark || '');
                        $('textarea[name="IIA_pdf_remark"]').val(data.remarks.IIA_pdf_remark || '');
                        $('textarea[name="IIB_1_pdf_remark"]').val(data.remarks.IIB_1_pdf_remark || '');
                        $('textarea[name="IIB_2_pdf_remark"]').val(data.remarks.IIB_2_pdf_remark || '');
                        $('textarea[name="IIC_pdf_remark"]').val(data.remarks.IIC_pdf_remark || '');
                        $('textarea[name="IIIA_pdf_remark"]').val(data.remarks.IIIA_pdf_remark || '');
                        $('textarea[name="IIIB_pdf_remark"]').val(data.remarks.IIIB_pdf_remark || '');
                        $('textarea[name="IIIC_1forcities_pdf_remark"]').val(data.remarks.IIIC_1forcities_pdf_remark || '');
                        $('textarea[name="IIIC_1forcities2_pdf_remark"]').val(data.remarks.IIIC_1forcities2_pdf_remark || '');
                        $('textarea[name="IIIC_1forcities3_pdf_remark"]').val(data.remarks.IIIC_1forcities3_pdf_remark || '');
                        $('textarea[name="IIIC_2formuni1_pdf_remark"]').val(data.remarks.IIIC_2formuni1_pdf_remark || '');
                        $('textarea[name="IIIC_2formuni2_pdf_remark"]').val(data.remarks.IIIC_2formuni2_pdf_remark || '');
                        $('textarea[name="IIIC_2formuni3_pdf_remark"]').val(data.remarks.IIIC_2formuni3_pdf_remark || '');
                        $('textarea[name="IIID_pdf_remark"]').val(data.remarks.IIID_pdf_remark || '');
                        $('textarea[name="IV_forcities_pdf_remark"]').val(data.remarks.IV_forcities_pdf_remark || '');
                        $('textarea[name="IV_muni_pdf_remark"]').val(data.remarks.IV_muni_pdf_remark || '');
                        $('textarea[name="V_1_pdf_remark"]').val(data.remarks.V_1_pdf_remark || '');
                        $('textarea[name="threepeoplesorg_remark"]').val(data.remarks.threepeoplesorg_remark || '');
                    } else {
                        clearRemarks();
                    }
                },
                error: function (xhr, status, error) {
                    console.log('Error fetching files:', xhr.responseText);
                    resetAllFields(); // Clear fields if AJAX call fails
                }
            });
        } else {
            // Clear fields if no barangay is selected
            resetAllFields();
        }
    });

    function resetAllFields() {
    $('.file-column').html('<div class="alert alert-info mb-0">Select barangay</div>');
    $('#barangay_id').val('');
    $('#mov_id').val('');
    $('#mov_year').text(''); // Reset the year display to blank
    $('#status_rate').text(''); // Reset the rate status to blank
    clearRates();
    clearRemarks();
}

    // Function to clear remarks
    function clearRemarks() {
        $('textarea[name="IA_1a_pdf_remark"]').val('');
        $('textarea[name="IA_1b_pdf_remark"]').val('');
        $('textarea[name="IA_2a_pdf_remark"]').val('');
        $('textarea[name="IA_2b_pdf_remark"]').val('');
        $('textarea[name="IA_2c_pdf_remark"]').val('');
        $('textarea[name="IA_2d_pdf_remark"]').val('');
        $('textarea[name="IA_2e_pdf_remark"]').val('');
        $('textarea[name="IB_1forcities_pdf_remark"]').val('');
        $('textarea[name="IB_1aformuni_pdf_remark"]').val('');
        $('textarea[name="IB_1bformuni_pdf_remark"]').val('');
        $('textarea[name="IB_2_pdf_remark"]').val('');
        $('textarea[name="IB_3_pdf_remark"]').val('');
        $('textarea[name="IB_4_pdf_remark"]').val('');
        $('textarea[name="IC_1_pdf_remark"]').val('');
        $('textarea[name="IC_2_pdf_remark"]').val('');
        $('textarea[name="ID_1_pdf_remark"]').val('');
        $('textarea[name="ID_2_pdf_remark"]').val('');
        $('textarea[name="IIA_pdf_remark"]').val('');
        $('textarea[name="IIB_1_pdf_remark"]').val('');
        $('textarea[name="IIB_2_pdf_remark"]').val('');
        $('textarea[name="IIC_pdf_remark"]').val('');
        $('textarea[name="IIIA_pdf_remark"]').val('');
        $('textarea[name="IIIB_pdf_remark"]').val('');
        $('textarea[name="IIIC_1forcities_pdf_remark"]').val('');
        $('textarea[name="IIIC_1forcities2_pdf_remark"]').val('');
        $('textarea[name="IIIC_1forcities3_pdf_remark"]').val('');
        $('textarea[name="IIIC_2formuni1_pdf_remark"]').val('');
        $('textarea[name="IIIC_2formuni2_pdf_remark"]').val('');
        $('textarea[name="IIIC_2formuni3_pdf_remark"]').val('');
        $('textarea[name="IIID_pdf_remark"]').val('');
        $('textarea[name="IV_forcities_pdf_remark"]').val('');
        $('textarea[name="IV_muni_pdf_remark"]').val('');
        $('textarea[name="V_1_pdf_remark"]').val('');
        $('textarea[name="threepeoplesorg_remark"]').val('');
    }

    // Add event listeners for rate inputs and remark textareas
    $('input[type="number"].score-input, textarea[placeholder="Remarks"]').on('change input', function(event) {
        // Check if a barangay is selected
        var selectedBarangay = $('#barangay_select').val();
        if (!selectedBarangay) {
            showModal('Please select a barangay first');
            $(this).val(''); // Clear the input
            return;
        }
        
        // For number inputs, validate min/max
        if ($(this).attr('type') === 'number') {
            var min = parseFloat($(this).attr('min'));
            var max = parseFloat($(this).attr('max'));
            var value = parseFloat($(this).val());
            
            if (isNaN(value) || value < min || value > max) {
                showModal('Please enter a number between ' + min + ' and ' + max);
                $(this).val('');
                return;
            }
        }

        // For remarks, only submit if it's a change event (not input)
        if ($(this).is('textarea') && event.type === 'input') {
            // Just enable the submit button for textarea input
            $('input[type="submit"]').prop('disabled', false).css({
                'opacity': '1',
                'cursor': 'pointer',
                'background-color': '#000033'
            });
            return;
        }

        // Remove any existing messages
        $('.save-success, .save-error').remove();

        // Automatically submit the form
        var form = $(this).closest('form');
        var formData = new FormData(form[0]);
        
        $.ajax({
            url: 'adminevaluate_handler.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Raw Response:', response);
                
                // Parse response if it's a string
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.log('Failed to parse response:', e);
                    }
                }
                
                if (response && response.status === 'success') {
                    // Show success message next to the changed input
                    var successMsg = $(`
                        <div class="save-success" style="
                            position: absolute;
                            background-color: #90EE90;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 12px;
                            margin-left: 10px;
                            display: inline-block;
                            z-index: 1000;">
                            <span style="color: #006400;">✓</span> Saved
                        </div>
                    `);
                    $(event.target).after(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 2000);

                    if ($(event.target).val() !== '') {
                        $(event.target).css({
                            'background-color': '',
                            'border-color': ''
                        });
                    } else {
                        $(event.target).css({
                            'background-color': '#ffebee',
                            'border-color': '#ef5350'
                        });
                    }
                } else {
                    showModal('Error saving changes');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', error);
                showModal('Error saving changes');
            }
        });
    });

    // Add form submission handler
    $('form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        
        // Get the last modified input
        var lastModifiedInput = $('input[type="number"]:focus');
        if (!lastModifiedInput.length) {
            lastModifiedInput = $('input[type="number"]').filter(function() {
                return $(this).val() !== '';
            }).last();
        }
        
        // Get required IDs
        var movId = $('#mov_id').val();
        var barangayId = $('#barangay_id').val();
        
        // Debug: Log the values
        console.log('MOV ID:', movId);
        console.log('Barangay ID:', barangayId);
        
        // Validate required fields
        if (!movId || !barangayId) {
            var errorMsg = $(`
                <div class="save-error" style="
                    position: absolute;
                    background-color: #ffebee;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    margin-left: 10px;
                    display: inline-block;
                    color: #c62828;
                    z-index: 1000;">
                    Please select a barangay first
                </div>`);
            
            // Show error next to barangay select
            $('.save-error').remove();
            errorMsg.insertAfter('#barangay_select');
            
            setTimeout(function() {
                errorMsg.fadeOut(200, function() {
                    $(this).remove();
                });
            }, 1000);
            
            return;
        }
        
        // Collect form data
        var formData = new FormData(form);
        
        // Debug: Log form data
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Submit form via AJAX
        $.ajax({
            url: 'adminevaluate_handler.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Raw Response:', response);
                
                // Parse response if it's a string
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.log('Failed to parse response:', e);
                    }
                }
                
                console.log('Parsed Response:', response);
                
                // Remove any existing messages
                $('.save-success, .save-error').remove();
                
                if (response && response.status === 'success') {
                    var successMsg = $(`
                        <div class="save-success" style="
                            position: absolute;
                            background-color: #90EE90;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 12px;
                            margin-left: 10px;
                            display: inline-block;
                            z-index: 1000;">
                            <span style="color: #006400;">✓</span> Saved
                        </div>`);
                    
                    if (lastModifiedInput.length) {
                        successMsg.insertAfter(lastModifiedInput);
                        setTimeout(function() {
                            successMsg.fadeOut(200, function() {
                                $(this).remove();
                            });
                        }, 1000);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
                
                // Remove any existing messages
                $('.save-success, .save-error').remove();
                
                var errorMsg = $(`
                    <div class="save-error" style="
                        position: absolute;
                        background-color: #ffebee;
                        padding: 4px 8px;
                        border-radius: 4px;
                        font-size: 12px;
                        margin-left: 10px;
                        display: inline-block;
                        color: #c62828;
                        z-index: 1000;">
                        Failed to save changes
                    </div>`);
                
                if (lastModifiedInput.length) {
                    errorMsg.insertAfter(lastModifiedInput);
                    setTimeout(function() {
                        errorMsg.fadeOut(200, function() {
                            $(this).remove();
                        });
                    }, 1000);
                }
            }
        });
    });
});

    // Handle PDF viewing inside the modal
    $(document).on('click', '.view-pdf', function () {
        var file = $(this).data('file'); // Get the file URL
        console.log('PDF URL:', file); // Debug the file path

        if (file) {
            // Set the source of the iframe in the modal to the PDF URL
            $('#pdfViewer').attr('src', file);

            // Show the modal by removing the hidden class
            $('#large-modal').removeClass('hidden');
        } else {
            alert('No file available to view.');
        }
    });

    // Close the modal when the close button is clicked
    $('[data-modal-hide="large-modal"]').on('click', function () {
        $('#large-modal').addClass('hidden'); // Hide the modal
        $('#pdfViewer').attr('src', ''); // Clear the iframe src when modal is closed
    });

</script>
</head>
<body class="bg-[#E8E8E7]">
<?php include "../assessor_sidebar_header.php"; ?>
  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
    <div class="card">
    <div class="card-body">
    <div class="flex justify-between items-center mb-4">
    <div class="flex items-center">
        <div class="dilglogo">
            <img src="../img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
        </div>
        <div class="ml-4">
            <h1 class="text-xl font-bold">
                Lupong Tagapamayapa Incentives Award (LTIA)
            </h1>
            <hr class="my-2">
            <h2 class="text-lg font-semibold">
            <div>
        <span id="details-municipality-type" style="display: inline-block; white-space: nowrap; width: auto; text-transform: uppercase;"></span> 
        OF <?php  echo strtoupper(htmlspecialchars($municipality_name)); ?>
            </h2>
        </div>
    </div>

            <div class="menu">
              <ul class="flex space-x-4">
                <li>
                  <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_admin_dashboard.php';" style="margin-left: 0;">
                  <i class="ti ti-building-community mr-2"> </i> 
                      Back
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="border border-gray-800 rounded-md p-4 mt-4">
    <b>A. IDENTIFYING INFORMATION</b>
    <p style="padding-left: 5em;">
        City/Municipality: 
        <span id="details-municipality-type" style="display: inline-block; white-space: nowrap; width: auto; text-transform: uppercase;"></span> 
        <?php  echo strtoupper(htmlspecialchars($municipality_name)); ?>
    </p>
    <p style="padding-left: 5em;">
        Region <span style="display: inline-block; width: 3em; text-align: center;">:</span> IVA
    </p>
    <p style="padding-left: 5em;">
        Province <span style="display: inline-block; width: 3em; text-align: center;">:</span> LAGUNA
    </p>
    <p style="padding-left: 5em;">
        Category <span style="display: inline-block; white-space: nowrap; width: auto; text-transform: uppercase;"></span>: 
        <span id="municipality-category" style="text-transform: uppercase;"></span>
    </p>
</div>
<script>
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Lists of cities and municipalities
    const cities = ["Calamba", "Biñan", "San Pedro", "Sta Rosa", "Cabuyao", "San Pablo"];
    const municipalities = ["Bay", "Alaminos", "Calauan", "Los Baños"];

    /**
     * Normalize names for consistent comparison
     * @param {string} name - Name to normalize
     * @returns {string} Normalized name
     */
    function normalizeName(name) {
        return name.toLowerCase().replace(/\s+/g, "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    /**
     * Classify a municipality name as "City" or "Municipality"
     * @param {string} municipalityName - Name to classify
     * @returns {string} "City", "Municipality", or "Unknown"
     */
    function classifyMunicipality(municipalityName) {
        const normalized = normalizeName(municipalityName);
        const normalizedCities = cities.map(normalizeName);
        const normalizedMunicipalities = municipalities.map(normalizeName);

        if (normalizedCities.includes(normalized)) {
            return "City";
        } else if (normalizedMunicipalities.includes(normalized)) {
            return "Municipality";
        } else {
            return "Unknown";
        }
    }

   // Get municipality name from PHP and classify
const municipalityName = <?php echo json_encode($municipality_name); ?>;
const classification = classifyMunicipality(municipalityName);

// Update header and details with the classification
document.getElementById("details-municipality-type").textContent = classification;
document.getElementById("municipality-category").textContent = classification.toUpperCase();

// Toggle visibility based on classification
if (classification === "City") {
    // Display city rows and hide municipality rows
    let cityRows = document.querySelectorAll('#city-row');
    let municipalityRows = document.querySelectorAll('#municipality-row');

    cityRows.forEach(row => row.style.display = '');
    municipalityRows.forEach(row => row.style.display = 'none');
} else if (classification === "Municipality") {
    // Display municipality rows and hide city rows
    let cityRows = document.querySelectorAll('#city-row');
    let municipalityRows = document.querySelectorAll('#municipality-row');

    cityRows.forEach(row => row.style.display = 'none');
    municipalityRows.forEach(row => row.style.display = '');
}
});
</script>

            <h2 class="text-left text-2xl font-semibold" id="mov_year" hidden></h2>
          <div class="form-group mt-4">
                    <label for="barangay_select" class="block text-lg font-medium text-gray-700">Select Barangay</label>
                        <select id="barangay_select" name="barangay" class="form-control">
                            <option value="">Select Barangay</option>
                            <?php foreach ($barangays as $barangay): ?>
                                <option value="<?php echo htmlspecialchars($barangay['barangay_name']); ?>">
                                    <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <br>
    <form method="post" action="adminevaluate_handler.php" enctype="multipart/form-data">
    <input type="hidden" id="selected_barangay" name="selected_barangay" value="" />
    <input type="hidden" id="mov_id" name="mov_id" value="" />
    <input type="hidden" id="barangay_id" name="barangay_id" value="" />
    <h2 class="text-left text-2xl font-semibold" id="status_rate" hidden></h2>
    <table class="table table-bordered">
            <thead>
              <tr>
                <th>CRITERIA</th>
                <th>Assigned Points</th>
                <th>File</th>
                <th>Verification Actions</th>
                <th>Rate</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              <tr>
            <td><b>I. EFFICIENCY IN OPERATION</b>

            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
        <td><b>A. Observance of Settlement Procedure and Settlement Deadlines</b>
        </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td>
            <details>
                <summary><b>1. a) Proper Recording of Every Dispute/Complaint - Evaluation Criteria</b></summary>
                <p><br>
                    <b>Scoring Details:</b> <br><br>
                    <b>5 points</b> - Submitted/presented the record book or logbook reflecting all the required details.<br>
                    <b>2.5 points</b> - Submitted/presented the record book or logbook reflecting some of the necessary details.<br>
                    <b>0 points</b> - No presented record book or logbook.<br><br>

                    <b>Note:</b> Check if the record contains the following:
                    <ul>
                    <li>Docket number</li>
                    <li>Names of the parties</li>
                    <li>Date and time filed</li>
                    <li>Nature of the case</li>
                    <li>Disposition</li>
                    </ul>
                </p>
                </details>
        </td>
            <td>5</td>
            <td class="file-column" data-type="IA_1a">
              <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
               </td>
               <td>button</td>
            <td>  
              <input type="number" value="" name="IA_1a_pdf_rate" min="0" max="5" class="score-input"placeholder="Ratings">
            <div class="error-message" style="color: red; display: none;">Please enter a number between 0 and 5.</div>
          </td>
            <td><textarea name="IA_1a_pdf_remark" placeholder="Remarks"></textarea></td>
          </tr>
          <tr>
            <td><details>
        <summary><b>b) Sending of Notices/Summons to Parties within the Prescribed Period (within the next working day upon receipt of complaint)</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>5 points</b> - Submitted/presented 80-100% of summons with complete and accurate information issued within the prescribed period.<br>
          <b>3 points</b> - Submitted/presented 50-79% of summons with complete and accurate information issued within the prescribed period.<br>
          <b>2 points</b> - Submitted/presented 1-49% of summons with complete and accurate information issued within the prescribed period.<br>
          <b>0 points</b> - Have not submitted/presented any summons/notices.<br><br>

          <b>Note:</b> Scores will be given only when file copies of the summons issued within the next working day are stamped with the date and time of receipt.
        </p>
      </details>
        </td>
            <td>5</td>
            <td class="file-column" data-type="IA_1b">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td>
            <input type="number" value="" name="IA_1b_pdf_rate" min="0" max="5" class="score-input"placeholder="Ratings">
          <div class="error-message" style="color: red; display: none;">Please enter a number between 0 and 5.</div>
            </td>
            <td><textarea name="IA_1b_pdf_remark" placeholder="Remarks"></textarea></td>
                    </tr>
          <tr>
  <td>
  <details>
  <summary><b>2. Settlement and Award Period (with at least 10 settled cases within the assessment period)</b></summary>
  <p><br>
    <b>10 points</b> – 80-100% of cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period.<br><br>
    <b>8 points</b> – 60-79% of cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period.<br><br>
    <b>6 points</b> – 40-59% of cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period.<br><br>
    <b>4 points</b> – 20-39% of cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period.<br><br>
    <b>2 points</b> – 1-19% of cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period.<br><br>
    <b>0 points</b> – 0 cases were resolved using any mode of ADR (supported by minutes of proceedings) within the prescribed period.
  </p>

</details>
  </td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
</tr>
               <tr>
                <td>a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2a">
              <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
          </td>
          <td>button</td>
            <td><input type="number" value="" name="IA_2a_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2a_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2b">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
            </td>
            <td>button</td>
            <td><input type="number" value="" name="IA_2b_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2b_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>c) Conciliation (with extended period not to exceed another 15 days)</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2c">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IA_2c_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2c_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2d">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IA_2d_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2d_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2e">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IA_2e_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2e_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td><b>B. Systematic Maintenance of Records</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td><b>1. Record of Cases </b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row" style="display:none;">
              <td>
              <details>
        <summary><b>For Cities - computer database with searchable case information</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>2 points</b> - if presented photos of the computer database with searchable engine. During onsite, to check the actual database and try the search engine
          <br>
          <b>Note:</b> To check the actual document during onsite validation<br>
            To check the quality of the file management - Record of dockets cases should be arranged in chronological order, folders properly labelled.
        </p>
      </details>
              </td>
                <td>2</td>
                <td class="file-column" data-type="IB_1forcities">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
                </td>
                <td>button</td>
            <td><input type="number" value="" name="IB_1forcities_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td ><textarea name="IB_1forcities_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr id="municipality-row" style="display:none;">
              <td><td>For Municipalities:
              <details>
        <summary><b>For Municipalities:</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>1 point</b> - if presented copy/photo of the manual record.          <br>
          <b>1 point</b> - if presented digital record filing          <br>
          <b>Note:</b> To check the actual document during onsite validation <br>
            To check the quality of the file management - Record of dockets cases should be arranged in chronological order, folders properly labelled.
        </p>
      </details>

              </td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row" style="display:none;">
                <td>a. Manual Records</td>
                <td>1</td>
                <td class="file-column" data-type="IB_1aformuni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IB_1aformuni_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IB_1aformuni_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr id="municipality-row" style="display:none;">
                <td>b. Digital Record Filing</td>
                <td>1</td>
                <td class="file-column" data-type="IB_1bformuni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IB_1bformuni_pdf_rate" min="0" max="1" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IB_1bformuni_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td>1</td>
                <td class="file-column" data-type="IB_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IB_2_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IB_2_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td>1</td>
                <td class="file-column" data-type="IB_3">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IB_3_pdf_rate" min="0" max="1" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IB_3_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td>1</td>
                <td class="file-column" data-type="IB_4">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IB_4_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IB_4_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>C. Timely Submissions to the Court and the DILG</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>
                <details>
              <summary><b>1. To the Court: Submitted/presented copies of settlement agreement to the Court</b></summary>
              <p><br>
                <b>Criteria Description:</b> <br>
                Copies of the settlement agreement must be submitted to the Court within the following periods: 
                <ul>
                  <li>After the lapse of the ten-day period repudiating the mediation/conciliation settlement agreement</li>
                  <li>Or within five (5) calendar days from the date of the arbitration award</li>
                </ul>
                <br>
                
                <b>Scoring Details:</b> <br><br>
                <b>5.0 points</b> - 80%-100% of the settlement agreements were submitted on time.<br>
                <b>4.0 points</b> - 60%-79% of the settlement agreements were submitted on time.<br>
                <b>3.0 points</b> - 40%-59% of the settlement agreements were submitted on time.<br>
                <b>2.0 points</b> - 20%-39% of the settlement agreements were submitted on time.<br>
                <b>1.0 point</b> - 1%-19% of the settlement agreements were submitted on time.<br>
                <b>0 points</b> - 0% of the reports were submitted on time.<br><br>

                <b>Note:</b> Timeliness is critical. Submission is considered on time only if it adheres strictly to the ten-day period after mediation/conciliation or five (5) days post-arbitration award.
              </p>
             </details>
                </td>
                <td>5</td>
                <td class="file-column" data-type="IC_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IC_1_pdf_rate" min="0" max="5" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IC_1_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td><details>
                <summary><b>2. To the DILG (Monthly): Submission of required report to the DILG</b></summary>
                <p><br>
                  2 points - Submitted/presented the required report to the DILG within the prescribed period<br>
                  1 point - Submitted/presented a partial report to the DILG within the prescribed period<br>
                  0 point - The required report to the DILG was not submitted or was submitted beyond the prescribed period
                </p>
              </details>
              </td>
                <td>2</td>
                <td class="file-column" data-type="IC_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
            </td>
            <td>button</td>
            <td><input type="number" value="" name="IC_2_pdf_rate" min="0" max="2" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IC_2_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>D. Conduct of monthly meetings for administration of the Katarungang Pambarangay (KP)</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>
                <details>
              <summary><b>Notice of Meeting</b></summary>
              <p><br>
                <b>2.0 points</b> - Minimum of 12 meetings with KP-related matters, complete details, each Lupon member must sign (indicating their name, date, and time of receipt) when receiving notices of the meeting.<br>
                <b>1.0 point</b> - Anything beyond the compliance document, with incomplete details.<br>
                <b>0 point</b> - No data presented.
              </p>
            </details>
                    </td>
                <td>2</td>
                <td class="file-column" data-type="ID_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="ID_1_pdf_rate" min="0" max="2" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="ID_1_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>
                <details>
                <summary><b>Minutes of the Meeting</b></summary>
                <p><br>
                  <b>Number of months with KP-related meetings with minutes and attendance sheets conducted:</b><br><br>
                  <b>8.0 points</b> - 12 months.<br>
                  <b>6.0 points</b> - 9-11 months.<br>
                  <b>4.0 points</b> - 6-8 months.<br>
                  <b>2.0 points</b> - 3-5 months.<br>
                  <b>1.0 point</b> - 1-2 months.<br>
                  <b>0 point</b> - No meeting.
                </p>
              </details>

                </td>
                <td>8</td>
                <td class="file-column" data-type="ID_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="ID_2_pdf_rate" min="0" max="8" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="ID_2_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>II. EFFECTIVENESS IN SECURING THE SETTLEMENT OF INTERPERSONAL DISPUTE OBJECTIVE OF THE KATARUNGANG PAMBARANGAY</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>
                <details>
              <summary><b>A. Quantity of settled cases against filed</b></summary>
              <p><br>
                <b>With a minimum of 10 cases settled, the percentage of cases received by the Lupon resulting in settlement:</b><br><br>
                <b>10.0 points</b> - 100%.<br>
                <b>8.0 points</b> - 80%-99%.<br>
                <b>6.0 points</b> - 60%-79%.<br>
                <b>4.0 points</b> - 40%-59%.<br>
                <b>2.0 points</b> - 1%-39%.<br>
                <b>0 point</b> - 0%.
              </p>
            </details>
                    </td>
                <td>10</td>
                <td class="file-column" data-type="IIA">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIA_pdf_rate" min="0" max="10" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIA_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>
                <details>
              <summary><b>B. Quality of Settlement of Cases</b></summary>
              <p><br>
                <b>1 point</b> - for non-recurrence and zero cases repudiated (out of the total number of settled cases).<br>
                <b>0 point</b> - at least one (1) case repudiated (out of the total number of settled cases).
              </p>
              </details>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>1. Zero cases repudiated</td>
                <td>1</td>
                <td class="file-column" data-type="IIB_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIB_1_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIB_1_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>
                <td>1</td>
                <td class="file-column" data-type="IIB_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIB_2_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIB_2_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>
                <details>
              <summary><b>C. At least 80% compliance with the terms of settlement or award after the cases have been settled </b></summary>
              <p><br>
                <b>8 points</b> - 80%-100% compliance with the terms of settlement or award.<br>
                <b>6 points</b> - 70%-79% compliance with the terms of settlement or award.<br>
                <b>4 points</b> - 60%-69% compliance with the terms of settlement or award.<br>
                <b>2 points</b> - 50%-51% compliance with the terms of settlement or award.<br>
                <b>1 point</b> - 49% and below compliance with the terms of settlement or award.
              </p>
            </details>
                </td>
                <td>8</td>
                <td class="file-column" data-type="IIC">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIC_pdf_rate" min="0" max="8" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIC_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>III. CREATIVITY AND RESOURCEFULNESS OF THE LUPONG TAGAPAMAYAPA</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>
                <details>
              <summary><b>A. Settlement Technique Utilized by the Lupon</b></summary>
              <p><br>
                <b>10 points</b> – Five or more settlement techniques utilized.<br>
                <b>8 points</b> – At least four settlement techniques utilized.<br>
                <b>6 points</b> – At least three settlement techniques utilized.<br>
                <b>4 points</b> – At least two settlement techniques utilized.<br>
                <b>2 points</b> – At least one settlement technique utilized.<br>
                <b>0 points</b> – No report submitted.
              </p>
              <p><b>Note:</b> Settlement techniques to be considered are those that are within the KP process and procedures.</p>
            </details>
                </td>
                <td>10</td>
                <td class="file-column" data-type="IIIA">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIA_pdf_rate" min="0" max="10" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIIA_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>
                <details>
                <summary><b>B. Coordination with Concerned Agencies Relating to Disputes Filed</b></summary>
                <p><br>
                  <b>5 points</b> – With proof of coordination relative to the filed disputes.<br>
                  <b>0 points</b> – Without proof of coordination relative to the filed disputes.
                </p>
              </details>
                </td>
                <td>5</td>
                <td class="file-column" data-type="IIIB">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIB_pdf_rate" min="0" max="5" class="score-input" placeholder="Ratings" ></td>
            <td><textarea name="IIIB_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>C. Sustained information drive to promote Katarungang Pambarangay</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row" style="display:none;">
                <td><details>
        <summary><b>1. For Cities</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>2 points</b> - IEC materials produced <br>
          <b>1 points</b> - IEC activities conducted<br>
          <b>2 points</b> - Innovative Campaign Strategy
          <br>
        </p>
      </details></td>
                <td></td>
              </tr>
              <tr id="city-row" style="display:none;">
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td>2</td>
                <td class="file-column" data-type="IIIC_1forcities">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIC_1forcities_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIIC_1forcities_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="city-row" style="display:none;">
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td>1</td>
                <td class="file-column" data-type="IIIC_1forcities2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIC_1forcities2_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIIC_1forcities2_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="city-row" style="display:none;">
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td>2</td>
                <td class="file-column" data-type="IIIC_1forcities3">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIC_1forcities3_pdf_rate" min="0" max="2" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IIIC_1forcities3_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="municipality-row" style="display:none;">
                <td><details>
        <summary><b>2. For Municipalities</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>2 points</b> - IEC materials produced          <br>
          <b>2 points</b> - IEC activities conducted<br>
          <b>1 points</b> - Innovative Campaign Strategy
          <br>
        </p>
      </details></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row" style="display:none;">
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td>2</td>
                <td class="file-column" data-type="IIIC_2formuni1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIC_2formuni1_pdf_rate" min="0" max="2" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IIIC_2formuni1_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="municipality-row" style="display:none;">
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td>2</td>
                <td class="file-column" data-type="IIIC_2formuni2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIC_2formuni2_pdf_rate" min="0" max="2" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IIIC_2formuni2_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="municipality-row" style="display:none;">
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td>1</td>
                <td class="file-column" data-type="IIIC_2formuni3">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIIC_2formuni3_pdf_rate" min="0" max="1" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IIIC_2formuni3_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr>
                <td><details>
              <summary><b>D. KP Training or Seminar Participated Within the Assessment Period</b></summary>
              <p><br>
                Organized skills training participated by the Lupong Tagapamayapa. Trainings or seminars should cover the following, and the Lupon should be able to articulate during validation their learnings therefrom:
                <ul>
                  <li>1) General/basic orientation or review of the KP system</li>
                  <li>2) Skills training on conduct of KP proceedings (e.g., relevant ADR systems, KP case management)</li>
                  <li>3) Advanced knowledge on laws, policies, and standards in relation to the KP system (e.g., gender and human rights, criminal/civil justice)</li>
                </ul>
                <b>10 points</b> = At least 6 qualified trainings/seminars.<br>
                <b>8 points</b> = At least 5 qualified trainings/seminars.<br>
                <b>6 points</b> = At least 4 qualified trainings/seminars.<br>
                <b>4 points</b> = At least 3 qualified trainings/seminars.<br>
                <b>2 points</b> = At least 2 qualified trainings/seminars.<br>
                <b>0 points</b> = No qualified information on training/seminar.
              </p>
            </details>
            </td>
                <td>10</td>
                <td class="file-column" data-type="IIID">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IIID_pdf_rate" min="0" max="10" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIID_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>IV. AREA OR FACILITY FOR KP ACTIVITIES</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>Building structure or space:</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row" style="display:none;">
                <td><details>
        <summary><b>For Cities - the office or space should be exclusive for KP matters</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>5 points</b> - office or room for exclusive use during KP proceedings with tables, chairs and other furniture and necessary equipment          <br>
          <b>2 points</b> - office or room for shared use during KP proceedings with tables, chairs and other furniture and necessary equipment
          <br>
        </p>
      </details></td>
                <td>5</td>
                <td class="file-column" data-type="IV_forcities">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IV_forcities_pdf_rate" min="0" max="5" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IV_forcities_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="municipality-row" style="display:none;">
                <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td>5</td>
                <td class="file-column" data-type="IV_muni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="IV_muni_pdf_rate" min="0" max="5" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IV_muni_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <th>V. FINANCIAL OR NON-FINANCIAL SUPPORT</th>
                <th></th>
                <th></th>
                <td></td>
                <th></th>
                <th></th>
              </tr>
              <tr >
                <td> <details>
        <summary><b>1. From City, Municipal, Provincial or NGAs</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>2 points</b> - received support from the both National &   Local Government<br>
          <b>1.5 points</b> - received support from the either National & Local Government          <br>
          <b>0 points</b> - no support <br>
          <b>Note:</b> 1. Excluding the regular appropriations for the barangay and incentives from the DILG programs<br>
          2. Acknowledgement receipt should be signed by both the donor and recipient. <br>
          3. Additional honoraria or support from the Lupon on top of the mandatory allocations
        </p>
      </details></td>
                <td>2</td>
                <td class="file-column" data-type="V_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="V_1_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="V_1_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td><details>
        <summary><b>3 From People's Organizations, NGOs or Private Sector</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>1 points</b> -  received support from either the organization or individual<br>
          <b>0 points</b> - no support received from either the organization or individual
          <br>
        </p>
      </details></td>
              <td>1</td>
              <td class="file-column" data-type="threepeoplesorg">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>button</td>
            <td><input type="number" value="" name="threepeoplesorg_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="threepeoplesorg_remark" placeholder="Remarks"></textarea></td>
              </tr>
            </tbody>
          </table>
      <input type="submit" value="Save" hidden style="background-color: #000033;"  class="btn btn-dark mt-3" />
    </form>
        </div>
      </div> 
    </div>
  </div>

  <!-- Modal structure -->
<div id="responseModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification</h5>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="large-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"  style="color: blue;" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
  // Close the modal when the close button is clicked
//   $(document).mouseup(function (e) {
//     var modalContent = $(".relative.bg-white.shadow.rounded-lg.h-full"); // Adjust selector as necessary
//     if (!modalContent.is(e.target) && modalContent.has(e.target).length === 0) {
//         closeModal(); // Close modal when clicking outside of content
//     }
// });

  // Close the modal when the close button is clicked
$(document).on('click', '[data-modal-hide="large-modal"]', function () {
    $('#large-modal').addClass('hidden'); // Hide the modal
    $('#pdfViewer').attr('src', ''); // Clear the iframe src when modal is closed
});

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');
    
    if (status && message) {
        document.getElementById('modalMessage').innerText = decodeURIComponent(message);
        $('#responseModal').modal('show');
    }
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Main modal for PDF viewing -->
<div id="large-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto fixed inset-0 z-50 justify-center items-center w-full h-full">
    <div class="relative p-4 w-full max-w-6xl h-[85%]">
        <!-- Modal content -->
        <div class="relative bg-white shadow rounded-lg h-full dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">PDF Viewer</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="large-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-4 h-full">
                <iframe id="pdfViewer" src="" class="w-full h-full rounded-md border"></iframe>
            </div>
        </div>
    </div>
</div>
<!-- Modal structure -->
<div id="alertModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="alertMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" style="background-color: #000033;" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>



<!--________________________________-->
<?php
session_start();
include '../connection.php';

// Define selected year first
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : $currentYear;

// First, let's make sure the year column exists and is properly created in all tables
try {
    // Update mov table
    $conn->exec("ALTER TABLE mov DROP COLUMN IF EXISTS `year`");
    $conn->exec("ALTER TABLE mov ADD COLUMN `year` YEAR(4) NOT NULL DEFAULT YEAR(CURRENT_DATE)");
    $conn->exec("UPDATE mov SET `year` = YEAR(CURRENT_DATE)");
    
    // Update movrate table
    $conn->exec("ALTER TABLE movrate DROP COLUMN IF EXISTS `year`");
    $conn->exec("ALTER TABLE movrate ADD COLUMN `year` YEAR(4) NOT NULL DEFAULT YEAR(CURRENT_DATE)");
    $conn->exec("UPDATE movrate SET `year` = YEAR(CURRENT_DATE)");
    
    // Update movremark table
    $conn->exec("ALTER TABLE movremark DROP COLUMN IF EXISTS `year`");
    $conn->exec("ALTER TABLE movremark ADD COLUMN `year` YEAR(4) NOT NULL DEFAULT YEAR(CURRENT_DATE)");
    $conn->exec("UPDATE movremark SET `year` = YEAR(CURRENT_DATE)");
    
    // Force a table refresh
    $conn->exec("FLUSH TABLES mov, movrate, movremark");
    
    // Close and reopen the connection
    $conn = null;
    include '../connection.php';
} catch (PDOException $e) {
    die("Failed to update table structures: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'], $_SESSION['user_type'], $_SESSION['barangay_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: ../login.php?error=session_expired");
    exit;
}

// First check if a record exists for this year
$checkSql = "SELECT COUNT(*) FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id AND `year` = :year";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$checkStmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$checkStmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$checkStmt->execute();

if ($checkStmt->fetchColumn() == 0) {
    // No record exists for this year, so create one
    $insertSql = "INSERT INTO mov (user_id, barangay_id, `year`) VALUES (:user_id, :barangay_id, :year)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $insertStmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
    $insertStmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    $insertStmt->execute();
}

// Now fetch the record
$sql = "SELECT * FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id AND `year` = :year";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Fetch rates and remarks
$rate_sql = "SELECT * FROM movrate WHERE barangay = :barangay_id AND `year` = :year";
$remark_sql = "SELECT * FROM movremark WHERE barangay = :barangay_id AND `year` = :year";

$rate_stmt = $conn->prepare($rate_sql);
$remark_stmt = $conn->prepare($remark_sql);

$rate_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$rate_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$remark_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$remark_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);

$rate_stmt->execute();
$remark_stmt->execute();

$rate_row = $rate_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$remark_row = $remark_stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Fetch years
$yearQuery = "SELECT DISTINCT `year` FROM mov";
$yearResult = $conn->query($yearQuery);
$years = $yearResult->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($currentYear, $years)) {
    $years[] = $currentYear;
}
rsort($years);

// Define allowed file columns
$allowed_columns = [
    'IA_1a_pdf_File', 'IA_1b_pdf_File', 'IA_2a_pdf_File', 'IA_2b_pdf_File',
    'IA_2c_pdf_File', 'IA_2d_pdf_File', 'IA_2e_pdf_File', 'IB_1forcities_pdf_File',
    'IB_1aformuni_pdf_File', 'IB_1bformuni_pdf_File', 'IB_2_pdf_File', 'IB_3_pdf_File',
    'IB_4_pdf_File', 'IC_1_pdf_File', 'IC_2_pdf_File', 'ID_1_pdf_File', 'ID_2_pdf_File',
    'IIA_pdf_File', 'IIB_1_pdf_File', 'IIB_2_pdf_File', 'IIC_pdf_File', 'IIIA_pdf_File',
    'IIIB_pdf_File', 'IIIC_1forcities_pdf_File', 'IIIC_1forcities2_pdf_File',
    'IIIC_1forcities3_pdf_File', 'IIIC_2formuni1_pdf_File', 'IIIC_2formuni2_pdf_File',
    'IIIC_2formuni3_pdf_File', 'IIID_pdf_File', 'IV_forcities_pdf_File', 'IV_muni_pdf_File',
    'V_1_pdf_File', 'threepeoplesorg_pdf_File'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = 'movfolder/';
    foreach ($allowed_columns as $column) {
        if (isset($_FILES[$column]) && $_FILES[$column]['error'] === UPLOAD_ERR_OK) {
            $file_name = time() . '_' . basename($_FILES[$column]['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES[$column]['tmp_name'], $file_path)) {
                $row[$column] = $file_name;
            }
        } else {
            $row[$column] = $_POST[$column . '_hidden'] ?? null;
        }
    }

    $update_sql = "UPDATE mov SET ";
    foreach ($allowed_columns as $column) {
        $update_sql .= "$column = :$column, ";
    }
    $update_sql = rtrim($update_sql, ', ') . " WHERE user_id = :user_id AND barangay_id = :barangay_id AND `year` = :year";

    $update_stmt = $conn->prepare($update_sql);
    foreach ($allowed_columns as $column) {
        $update_stmt->bindValue(":$column", $row[$column] ?? null, PDO::PARAM_STR);
    }
    $update_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $update_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
    $update_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?year=" . $selectedYear);
        exit;
    } else {
        error_log("Update failed: " . print_r($update_stmt->errorInfo(), true));
    }
}

// Define user and barangay ID from session
$userID = $_SESSION['user_id'];
$barangayID = $_SESSION['barangay_id'] ?? '';

// Initialize variables
$submissionExists = false;
$barangayName = '';
$municipalityName = '';
$municipalityID = '';

// Query to check if the user's barangay has a submission
$checkQuery = "SELECT COUNT(*) FROM movdraft_file WHERE barangay_id = :barangay_id";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bindParam(':barangay_id', $barangayID, PDO::PARAM_INT);
$checkStmt->execute();
if ($checkStmt->fetchColumn() > 0) {
    $submissionExists = true;
}
// Query to fetch the barangay name and municipality ID
if (!empty($barangayID)) {
    $barangayQuery = "SELECT barangay_name, municipality_id FROM barangays WHERE id = :barangay_id";
    $barangayStmt = $conn->prepare($barangayQuery);
    $barangayStmt->bindParam(':barangay_id', $barangayID, PDO::PARAM_INT);
    $barangayStmt->execute();
    $barangayResult = $barangayStmt->fetch(PDO::FETCH_ASSOC);

    if ($barangayResult) {
        $barangayName = $barangayResult['barangay_name'];
        $municipalityID = $barangayResult['municipality_id'];
    }
}
// Query to fetch the municipality name
if (!empty($municipalityID)) {
    $municipalityQuery = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
    $municipalityStmt = $conn->prepare($municipalityQuery);
    $municipalityStmt->bindParam(':municipality_id', $municipalityID, PDO::PARAM_INT);
    $municipalityStmt->execute();
    $municipalityName = $municipalityStmt->fetchColumn() ?: 'Unknown';
}
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <link rel="stylesheet" href="css/td_hover.css">
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const cities = ["Calamba", "Biñan", "San Pedro", "Sta Rosa", "Cabuyao", "San Pablo"];
      const municipalities = ["Bay", "Alaminos", "Calauan", "Los Baños"];

      function normalizeName(name) {
        return name.toLowerCase().replace(/\s+/g, "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
      }

      function classifyMunicipality(municipalityName) {
        const normalized = normalizeName(municipalityName);
        const normalizedCities = cities.map(normalizeName);
        const normalizedMunicipalities = municipalities.map(normalizeName);

        if (normalizedCities.includes(normalized)) {
          return "City";
        } else if (normalizedMunicipalities.includes(normalized)) {
          return "Municipality";
        } else {
          return "Unknown";
        }
      }

      const municipalityName = <?php echo json_encode($municipalityName); ?>;
      const classification = classifyMunicipality(municipalityName);

      document.getElementById("details-municipality-type").textContent = classification;

      if (classification === "City") {
        document.querySelectorAll('#city-row').forEach(row => row.style.display = '');
        document.querySelectorAll('#municipality-row').forEach(row => row.style.display = 'none');
      } else if (classification === "Municipality") {
        document.querySelectorAll('#city-row').forEach(row => row.style.display = 'none');
        document.querySelectorAll('#municipality-row').forEach(row => row.style.display = '');
      }
    });
  </script>
</head>

<body class="bg-[#E8E8E7]">
<?php include "../user_sidebar_header.php"; ?>

<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
        <div class="card">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                <div class="flex items-center space-x-4">
                        <div class="dilglogo">
                            <img src="images/dilglogo.png" alt="DILG Logo" class="h-20" />
                        </div>
                        <h1 class="text-xl font-bold">
                Lupong Tagapamayapa Incentives Award (LTIA)
                        <form method="get" action=""  class="inline-block">
                        <select name="year" id="year" onchange="this.form.submit()">
                            <?php foreach ($years as $year) : ?>
                                <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form> 
                    <hr class="my-2">
            <span>Barangay </span> 
                  <span ><?= htmlspecialchars($barangayName, ENT_QUOTES, 'UTF-8') ?></span>, 
                  <span id="details-municipality-type" class="ml-2"></span>
                  <span>of <?= htmlspecialchars($municipalityName, ENT_QUOTES, 'UTF-8') ?></span>
               </h2>
               <?php
$sql = "SELECT * FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id AND `year` = :year";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Fetch rates from the movrate table for the selected year
$rate_sql = "SELECT * FROM movrate WHERE barangay = :barangay_id AND `year` = :year";
$rate_stmt = $conn->prepare($rate_sql);
$rate_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$rate_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$rate_stmt->execute();
$rate_row = $rate_stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize as an empty array if no records found

// Fetch remarks from the movremark table for the selected year
$remark_sql = "SELECT * FROM movremark WHERE barangay = :barangay_id AND `year` = :year";
$remark_stmt = $conn->prepare($remark_sql);
$remark_stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$remark_stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
$remark_stmt->execute();
$remark_row = $remark_stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize as an empty array if no records found
?>
                        </h1>
                    </div>
                    <div class="menu">
                        <ul class="flex space-x-4">
                            <li>
                            <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_dashboard.php';" style="margin-left: 0;">
                          <i class="ti ti-arrow-narrow-left-dashed mr-2"></i>
                          Back
                          </button>
                            </li>
                        </ul>
                    </div>  
                </div>
                
                <div class="container mt-5">
                <div id="noChangesMessage" class="text-red-500 font-semibold"></div>
                    <h2 class="text-left text-2xl font-semibold">FORM 1</h2>
                    <form method="post" action="" enctype="multipart/form-data">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>CRITERIA</th>
                                    <th>Means Of Verification</th>
                                    <th>Rate</th>
                                    <th>Remarks</th>
                                    <th>Choose Files to Replace MOV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example for IA_1a -->
                                <tr>
            <td><b>1. a) Proper Recording of every dispute/complaint</b></td>
            <td>
              <?php if (!empty($row['IA_1a_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_1a_pdf_rate']) ? $rate_row['IA_1a_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_1a_pdf_remark']) ? $remark_row['IA_1a_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IA_1a_pdf_File" name="IA_1a_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit1', 'cancel1')" />
              <input type="hidden" name="IA_1a_pdf_File_hidden" id="IA_1a_pdf_File_hidden" value="<?php echo !empty($row['IA_1a_pdf_File']) ? htmlspecialchars($row['IA_1a_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_1a_pdf_File" id="submit1" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel1" onclick="clearInput('IA_1a_pdf_File', 'submit1', 'cancel1')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
          <tr>
            <td>b) Sending of Notices and Summons</td>
            <td>
              <?php if (!empty($row['IA_1b_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_1b_pdf_rate']) ? $rate_row['IA_1b_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_1b_pdf_remark']) ? $remark_row['IA_1b_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IA_1b_pdf_File" name="IA_1b_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit2', 'cancel2')" />
              <input type="hidden" name="IA_1b_pdf_File_hidden" id="IA_1b_pdf_File_hidden" value="<?php echo !empty($row['IA_1b_pdf_File']) ? htmlspecialchars($row['IA_1b_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_1b_pdf_File" id="submit2" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel2" onclick="clearInput('IA_1b_pdf_File', 'submit2', 'cancel2')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
          <tr>
                <td>2. Settlement and Award Period (with at least 10 settled cases within the assessment period)</td>
                <td> </td>
                <td> </td>
            <td></td>
            <td></td>
              </tr>
               <tr>
                <td>a) Mediation (within 15 days from initial confrontation with the Lupon Chairman)</td>
                <td>
                <?php if (!empty($row['IA_2a_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2a_pdf_rate']) ? $rate_row['IA_2a_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2a_pdf_remark']) ? $remark_row['IA_2a_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IA_2a_pdf_File" name="IA_2a_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit3', 'cancel3')" />
              <input type="hidden" name="IA_2a_pdf_File_hidden" id="IA_2a_pdf_File_hidden" value="<?php echo !empty($row['IA_2a_pdf_File']) ? htmlspecialchars($row['IA_2a_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2a_pdf_File" id="submit3" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel3" onclick="clearInput('IA_2a_pdf_File', 'submit3', 'cancel3')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td>
                <?php if (!empty($row['IA_2b_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2b_pdf_rate']) ? $rate_row['IA_2b_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2b_pdf_remark']) ? $remark_row['IA_2b_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IA_2b_pdf_File" name="IA_2b_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit4', 'cancel4')" />
              <input type="hidden" name="IA_2b_pdf_File_hidden" id="IA_2b_pdf_File_hidden" value="<?php echo !empty($row['IA_2b_pdf_File']) ? htmlspecialchars($row['IA_2b_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2b_pdf_File" id="submit4" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel4" onclick="clearInput('IA_2b_pdf_File', 'submit4', 'cancel4')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
              </tr>
              <tr>
                <td>c) Conciliation (with extended period not to exceed another 15 days)</td>
                <td>
                <?php if (!empty($row['IA_2c_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2c_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2c_pdf_rate']) ? $rate_row['IA_2c_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2c_pdf_remark']) ? $remark_row['IA_2c_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IA_2c_pdf_File" name="IA_2c_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit5', 'cancel5')" />
              <input type="hidden" name="IA_2c_pdf_File_hidden" id="IA_2c_pdf_File_hidden" value="<?php echo !empty($row['IA_2c_pdf_File']) ? htmlspecialchars($row['IA_2c_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2c_pdf_File" id="submit5" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel5" onclick="clearInput('IA_2c_pdf_File', 'submit5', 'cancel5')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td>
                <?php if (!empty($row['IA_2d_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2d_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2d_pdf_rate']) ? $rate_row['IA_2d_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2d_pdf_remark']) ? $remark_row['IA_2d_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IA_2d_pdf_File" name="IA_2d_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit6', 'cancel6')" />
              <input type="hidden" name="IA_2d_pdf_File_hidden" id="IA_2d_pdf_File_hidden" value="<?php echo !empty($row['IA_2d_pdf_File']) ? htmlspecialchars($row['IA_2d_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2d_pdf_File" id="submit6" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel6" onclick="clearInput('IA_2d_pdf_File', 'submit6', 'cancel6')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
             <td>
                <?php if (!empty($row['IA_2e_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_2e_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IA_2e_pdf_rate']) ? $rate_row['IA_2e_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IA_2e_pdf_remark']) ? $remark_row['IA_2e_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IA_2e_pdf_File" name="IA_2e_pdf_File" accept=".pdf" onchange="toggleSubmitButton(this, 'submit7', 'cancel7')" />
              <input type="hidden" name="IA_2e_pdf_File_hidden" id="IA_2e_pdf_File_hidden" value="<?php echo !empty($row['IA_2e_pdf_File']) ? htmlspecialchars($row['IA_2e_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IA_2e_pdf_File" id="submit7" style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel7" onclick="clearInput('IA_2e_pdf_File', 'submit7', 'cancel7')" style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
              </tr>
              <tr>
                <th>B. Systematic Maintenance of Records</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>1. Record of Cases </b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row">
                <td>For Cities - computer database with searchable case information</td>
                <td>
                <?php if (!empty($row['IB_1forcities_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_1forcities_pdf_rate']) ? $rate_row['IB_1forcities_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_1forcities_pdf_remark']) ? $remark_row['IB_1forcities_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IB_1forcities_pdf_File" name="IB_1forcities_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit8', 'cancel8')" />
              <input type="hidden" name="IB_1forcities_pdf_File_hidden" id="IB_1forcities_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_1forcities_pdf_File']) ? htmlspecialchars($row['IB_1forcities_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_1forcities_pdf_File" id="submit8" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel8" onclick="clearInput('IB_1forcities_pdf_File', 'submit8', 'cancel8')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>For Municipalities:</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row">
                <td>a. Manual Records</td>
                <td>
                <?php if (!empty($row['IB_1aformuni_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1aformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_1aformuni_pdf_rate']) ? $rate_row['IB_1aformuni_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_1aformuni_pdf_remark']) ? $remark_row['IB_1aformuni_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IB_1aformuni_pdf_File" name="IB_1aformuni_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit9', 'cancel9')" />
              <input type="hidden" name="IB_1aformuni_pdf_File_hidden" id="IB_1aformuni_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_1aformuni_pdf_File']) ? htmlspecialchars($row['IB_1aformuni_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_1aformuni_pdf_File" id="submit9" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel9" onclick="clearInput('IB_1aformuni_pdf_File', 'submit9', 'cancel9')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>b. Digital Record Filing</td>
                <td>
                  <?php if (!empty($row['IB_1bformuni_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;"class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_1bformuni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_1bformuni_pdf_rate']) ? $rate_row['IB_1bformuni_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_1bformuni_pdf_remark']) ? $remark_row['IB_1bformuni_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IB_1bformuni_pdf_File" name="IB_1bformuni_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit10', 'cancel10')" />
              <input type="hidden" name="IB_1bformuni_pdf_File_hidden" id="IB_1bformuni_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_1bformuni_pdf_File']) ? htmlspecialchars($row['IB_1bformuni_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_1bformuni_pdf_File" id="submit10" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel10" onclick="clearInput('IB_1bformuni_pdf_File', 'submit10', 'cancel10')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td>
                <?php if (!empty($row['IB_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_2_pdf_rate']) ? $rate_row['IB_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_2_pdf_remark']) ? $remark_row['IB_2_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IB_2_pdf_File" name="IB_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit11', 'cancel11')" />
              <input type="hidden" name="IB_2_pdf_File_hidden" id="IB_2_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_2_pdf_File']) ? htmlspecialchars($row['IB_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_2_pdf_File" id="submit11" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel11" onclick="clearInput('IB_2_pdf_File', 'submit11', 'cancel11')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td>
                <?php if (!empty($row['IB_3_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_3_pdf_rate']) ? $rate_row['IB_3_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_3_pdf_remark']) ? $remark_row['IB_3_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IB_3_pdf_File" name="IB_3_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit12', 'cancel12')" />
              <input type="hidden" name="IB_3_pdf_File_hidden" id="IB_3_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_3_pdf_File']) ? htmlspecialchars($row['IB_3_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_3_pdf_File" id="submit12" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel12" onclick="clearInput('IB_3_pdf_File', 'submit12', 'cancel12')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td>
                <?php if (!empty($row['IB_4_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IB_4_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IB_4_pdf_rate']) ? $rate_row['IB_4_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IB_4_pdf_remark']) ? $remark_row['IB_4_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IB_4_pdf_File" name="IB_4_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit13', 'cancel13')" />
              <input type="hidden" name="IB_4_pdf_File_hidden" id="IB_4_pdf_File_hidden" 
               value="<?php echo !empty($row['IB_4_pdf_File']) ? htmlspecialchars($row['IB_4_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IB_4_pdf_File" id="submit13" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel13" onclick="clearInput('IB_4_pdf_File', 'submit13', 'cancel13')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>C. Timely Submissions to the Court and the DILG</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. <b>To the Court:</b> Submitted/ presented copies of settlement agreement to the Court from the lapse of the ten-day period repudiating the mediation/ conciliation settlement agreement, or within five (5) calendar days from the date of the arbitration award</td>
                <td>
                <?php if (!empty($row['IC_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IC_1_pdf_rate']) ? $rate_row['IC_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IC_1_pdf_remark']) ? $remark_row['IC_1_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IC_1_pdf_File" name="IC_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit14', 'cancel14')" />
              <input type="hidden" name="IC_1_pdf_File_hidden" id="IC_1_pdf_File_hidden" 
               value="<?php echo !empty($row['IC_1_pdf_File']) ? htmlspecialchars($row['IC_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IC_1_pdf_File" id="submit14" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel14" onclick="clearInput('IC_1_pdf_File', 'submit14', 'cancel14')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>2. To the DILG (Quarterly)</td>
                <td>
                <?php if (!empty($row['IC_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IC_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IC_2_pdf_rate']) ? $rate_row['IC_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IC_2_pdf_remark']) ? $remark_row['IC_2_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IC_2_pdf_File" name="IC_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit15', 'cancel15')" />
              <input type="hidden" name="IC_2_pdf_File_hidden" id="IC_2_pdf_File_hidden" 
               value="<?php echo !empty($row['IC_2_pdf_File']) ? htmlspecialchars($row['IC_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IC_2_pdf_File" id="submit15" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel15" onclick="clearInput('IC_2_pdf_File', 'submit15', 'cancel15')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>D. Conduct of monthly meetings for administration of the Katarungang Pambarangay (KP)</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. Notice of Meeting</td>
                <td>
                <?php if (!empty($row['ID_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['ID_1_pdf_rate']) ? $rate_row['ID_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['ID_1_pdf_remark']) ? $remark_row['ID_1_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="ID_1_pdf_File" name="ID_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit16', 'cancel16')" />
              <input type="hidden" name="ID_1_pdf_File_hidden" id="ID_1_pdf_File_hidden" 
               value="<?php echo !empty($row['ID_1_pdf_File']) ? htmlspecialchars($row['ID_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="ID_1_pdf_File" id="submit16" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel16" onclick="clearInput('ID_1_pdf_File', 'submit16', 'cancel16')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>2. Minutes of the Meeting</td>
                <td>
                <?php if (!empty($row['ID_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['ID_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['ID_2_pdf_rate']) ? $rate_row['ID_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['ID_2_pdf_remark']) ? $remark_row['ID_2_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="ID_2_pdf_File" name="ID_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit17', 'cancel17')" />
              <input type="hidden" name="ID_2_pdf_File_hidden" id="ID_2_pdf_File_hidden" 
               value="<?php echo !empty($row['ID_2_pdf_File']) ? htmlspecialchars($row['ID_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="ID_2_pdf_File" id="submit17" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel17" onclick="clearInput('ID_2_pdf_File', 'submit17', 'cancel17')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>II. EFFECTIVENESS IN SECURING THE SETTLEMENT OF INTERPERSONAL DISPUTE OBJECTIVE OF THE KATARUNGANG PAMBARANGAY</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Quantity of settled cases against filed</td>
                <td>
                <?php if (!empty($row['IIA_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIA_pdf_rate']) ? $rate_row['IIA_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIA_pdf_remark']) ? $remark_row['IIA_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIA_pdf_File" name="IIA_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit18', 'cancel18')" />
              <input type="hidden" name="IIA_pdf_File_hidden" id="IIA_pdf_File_hidden" 
               value="<?php echo !empty($row['IIA_pdf_File']) ? htmlspecialchars($row['IIA_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIA_pdf_File" id="submit18" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel18" onclick="clearInput('IIA_pdf_File', 'submit18', 'cancel18')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>B. Quality of Settlement of Cases</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>1. Zero cases repudiated</td>
                <td>
                <?php if (!empty($row['IIB_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIB_1_pdf_rate']) ? $rate_row['IIB_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIB_1_pdf_remark']) ? $remark_row['IIB_1_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIB_1_pdf_File" name="IIB_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit19', 'cancel19')" />
              <input type="hidden" name="IIB_1_pdf_File_hidden" id="IIB_1_pdf_File_hidden" 
               value="<?php echo !empty($row['IIB_1_pdf_File']) ? htmlspecialchars($row['IIB_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIB_1_pdf_File" id="submit19" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel19" onclick="clearInput('IIB_1_pdf_File', 'submit19', 'cancel19')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>
                <td>
                <?php if (!empty($row['IIB_2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIB_2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIB_2_pdf_rate']) ? $rate_row['IIB_2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIB_2_pdf_remark']) ? $remark_row['IIB_2_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIB_2_pdf_File" name="IIB_2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit20', 'cancel20')" />
              <input type="hidden" name="IIB_2_pdf_File_hidden" id="IIB_2_pdf_File_hidden" 
               value="<?php echo !empty($row['IIB_2_pdf_File']) ? htmlspecialchars($row['IIB_2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIB_2_pdf_File" id="submit20" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel20" onclick="clearInput('IIB_2_pdf_File', 'submit20', 'cancel20')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>C. At least 80% compliance with the terms of settlement or award after the cases have been settled</td>
                <td>
                <?php if (!empty($row['IIC_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIC_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIC_pdf_rate']) ? $rate_row['IIC_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIC_pdf_remark']) ? $remark_row['IIC_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIC_pdf_File" name="IIC_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit21', 'cancel21')" />
              <input type="hidden" name="IIC_pdf_File_hidden" id="IIC_pdf_File_hidden" 
               value="<?php echo !empty($row['IIC_pdf_File']) ? htmlspecialchars($row['IIC_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIC_pdf_File" id="submit21" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel21" onclick="clearInput('IIC_pdf_File', 'submit21', 'cancel21')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>III. CREATIVITY AND RESOURCEFULNESS OF THE LUPONG TAGAPAMAYAPA</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>A. Settlement Technique utilized by the Lupon</td>

                <td>
                <?php if (!empty($row['IIIA_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIA_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIA_pdf_rate']) ? $rate_row['IIIA_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIA_pdf_remark']) ? $remark_row['IIIA_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIA_pdf_File" name="IIIA_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit22', 'cancel22')" />
              <input type="hidden" name="IIIA_pdf_File_hidden" id="IIIA_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIA_pdf_File']) ? htmlspecialchars($row['IIIA_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIA_pdf_File" id="submit22" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel22" onclick="clearInput('IIIA_pdf_File', 'submit22', 'cancel22')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>B. Coordination with Concerned Agencies relating to disputes filed (PNP, DSWD, DILG, DAR, DENR, Office of the Prosecutor, Court, DOJ, CHR, etc.)</td>

                <td>
                <?php if (!empty($row['IIIB_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIB_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIB_pdf_rate']) ? $rate_row['IIIB_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIB_pdf_remark']) ? $remark_row['IIIB_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIB_pdf_File" name="IIIB_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit23', 'cancel23')" />
              <input type="hidden" name="IIIB_pdf_File_hidden" id="IIIB_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIB_pdf_File']) ? htmlspecialchars($row['IIIB_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIB_pdf_File" id="submit23" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel23" onclick="clearInput('IIIB_pdf_File', 'submit23', 'cancel23')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>C. Sustained information drive to promote Katarungang Pambarangay</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row">
                <td>1. For Cities</td>
                <td></td>
              </tr>
              <tr id="city-row">
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_1forcities_pdf_rate']) ? $rate_row['IIIC_1forcities_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_1forcities_pdf_remark']) ? $remark_row['IIIC_1forcities_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIC_1forcities_pdf_File" name="IIIC_1forcities_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit24', 'cancel24')" />
              <input type="hidden" name="IIIC_1forcities_pdf_File_hidden" id="IIIC_1forcities_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_1forcities_pdf_File']) ? htmlspecialchars($row['IIIC_1forcities_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_1forcities_pdf_File" id="submit24" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel24" onclick="clearInput('IIIC_1forcities_pdf_File', 'submit24', 'cancel24')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="city-row">
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_1forcities2_pdf_rate']) ? $rate_row['IIIC_1forcities2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_1forcities2_remark']) ? $remark_row['IIIC_1forcities2_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIC_1forcities2_pdf_File" name="IIIC_1forcities2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit25', 'cancel25')" />
              <input type="hidden" name="IIIC_1forcities2_pdf_File_hidden" id="IIIC_1forcities2_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_1forcities2_pdf_File']) ? htmlspecialchars($row['IIIC_1forcities2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_1forcities2_pdf_File" id="submit25" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel25" onclick="clearInput('IIIC_1forcities2_pdf_File', 'submit25', 'cancel25')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="city-row">
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_1forcities3_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_1forcities3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_1forcities3_pdf_rate']) ? $rate_row['IIIC_1forcities3_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_1forcities3_pdf_remark']) ? $remark_row['IIIC_1forcities3_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIC_1forcities3_pdf_File" name="IIIC_1forcities3_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit26', 'cancel26')" />
              <input type="hidden" name="IIIC_1forcities3_pdf_File_hidden" id="IIIC_1forcities3_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_1forcities3_pdf_File']) ? htmlspecialchars($row['IIIC_1forcities3_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_1forcities3_pdf_File" id="submit26" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel26" onclick="clearInput('IIIC_1forcities3_pdf_File', 'submit26', 'cancel26')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>2. For Municipalities</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="municipality-row">
                <td>
                  <ul>
                    <li>IEC materials developed</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_2formuni1_pdf_rate']) ? $rate_row['IIIC_2formuni1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_2formuni1_pdf_remark']) ? $remark_row['IIIC_2formuni1_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIC_2formuni1_pdf_File" name="IIIC_2formuni1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit27', 'cancel27')" />
              <input type="hidden" name="IIIC_2formuni1_pdf_File_hidden" id="IIIC_2formuni1_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_2formuni1_pdf_File']) ? htmlspecialchars($row['IIIC_2formuni1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_2formuni1_pdf_File" id="submit27" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel27" onclick="clearInput('IIIC_2formuni1_pdf_File', 'submit27', 'cancel27')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>
                  <ul>
                    <li>IEC activities conducted</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni2_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni2_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_2formuni2_pdf_rate']) ? $rate_row['IIIC_2formuni2_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_2formuni2_pdf_remark']) ? $remark_row['IIIC_2formuni2_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIC_2formuni2_pdf_File" name="IIIC_2formuni2_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit28', 'cancel28')" />
              <input type="hidden" name="IIIC_2formuni2_pdf_File_hidden" id="IIIC_2formuni2_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_2formuni2_pdf_File']) ? htmlspecialchars($row['IIIC_2formuni2_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_2formuni2_pdf_File" id="submit28" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel28" onclick="clearInput('IIIC_2formuni2_pdf_File', 'submit28', 'cancel28')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>
                  <ul>
                    <li>Innovative Campaign Strategy</li>
                  </ul>
                </td>
                <td>
                <?php if (!empty($row['IIIC_2formuni3_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIIC_2formuni3_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIIC_2formuni3_pdf_rate']) ? $rate_row['IIIC_2formuni3_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIIC_2formuni3_pdf_remark']) ? $remark_row['IIIC_2formuni3_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIIC_2formuni3_pdf_File" name="IIIC_2formuni3_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit29', 'cancel29')" />
              <input type="hidden" name="IIIC_2formuni3_pdf_File_hidden" id="IIIC_2formuni3_pdf_File_hidden" 
               value="<?php echo !empty($row['IIIC_2formuni3_pdf_File']) ? htmlspecialchars($row['IIIC_2formuni3_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIIC_2formuni3_pdf_File" id="submit29" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel29" onclick="clearInput('IIIC_2formuni3_pdf_File', 'submit29', 'cancel29')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <td>D. KP Training or seminar within the assessment period<br />
                  Organized skills training participated by the Lupong Tagapamayapa</td>
                <td>
                <?php if (!empty($row['IIID_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IIID_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IIID_pdf_rate']) ? $rate_row['IIID_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IIID_pdf_remark']) ? $remark_row['IIID_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IIID_pdf_File" name="IIID_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit30', 'cancel30')" />
              <input type="hidden" name="IIID_pdf_File_hidden" id="IIID_pdf_File_hidden" 
               value="<?php echo !empty($row['IIID_pdf_File']) ? htmlspecialchars($row['IIID_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IIID_pdf_File" id="submit30" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel30" onclick="clearInput('IIID_pdf_File', 'submit30', 'cancel30')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr>
                <th>IV. AREA OR FACILITY FOR KP ACTIVITIES</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td><b>Building structure or space:</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr id="city-row">
                <td>For Cities - the office or space should be exclusive for KP matters</td>
                <td>
                <?php if (!empty($row['IV_forcities_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_forcities_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IV_forcities_pdf_rate']) ? $rate_row['IV_forcities_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IV_forcities_pdf_remark']) ? $remark_row['IV_forcities_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IV_forcities_pdf_File" name="IV_forcities_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit31', 'cancel31')" />
              <input type="hidden" name="IV_forcities_pdf_File_hidden" id="IV_forcities_pdf_File_hidden" 
               value="<?php echo !empty($row['IV_forcities_pdf_File']) ? htmlspecialchars($row['IV_forcities_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IV_forcities_pdf_File" id="submit31" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel31" onclick="clearInput('IV_forcities_pdf_File', 'submit31', 'cancel31')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>
          </tr>
              <tr id="municipality-row">
                <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td>
                <?php if (!empty($row['IV_muni_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IV_muni_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['IV_muni_pdf_rate']) ? $rate_row['IV_muni_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['IV_muni_pdf_remark']) ? $remark_row['IV_muni_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="IV_muni_pdf_File" name="IV_muni_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit32', 'cancel32')" />
              <input type="hidden" name="IV_muni_pdf_File_hidden" id="IV_muni_pdf_File_hidden" 
               value="<?php echo !empty($row['IV_muni_pdf_File']) ? htmlspecialchars($row['IV_muni_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="IV_muni_pdf_File" id="submit32" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel32" onclick="clearInput('IV_muni_pdf_File', 'submit32', 'cancel32')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>  
          </tr>
              <tr>
                <th>V. FINANCIAL OR NON-FINANCIAL SUPPORT</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
              <tr>
                <td>1. From City, Municipal, Provincial or NGAs</td>
                <td>
                <?php if (!empty($row['V_1_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['V_1_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>
            <td><?php echo isset($rate_row['V_1_pdf_rate']) ? $rate_row['V_1_pdf_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['V_1_pdf_remark']) ? $remark_row['V_1_pdf_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="V_1_pdf_File" name="V_1_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit33', 'cancel33')" />
              <input type="hidden" name="V_1_pdf_File_hidden" id="V_1_pdf_File_hidden" 
               value="<?php echo !empty($row['V_1_pdf_File']) ? htmlspecialchars($row['V_1_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="V_1_pdf_File" id="submit33" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel33" onclick="clearInput('V_1_pdf_File', 'submit33', 'cancel33')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>  
          </tr>
              <tr>
                <td>3 From People's Organizations, NGOs or Private Sector</td>
                <td>
                <?php if (!empty($row['threepeoplesorg_pdf_File'])) : ?>
                <button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['threepeoplesorg_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No MOV Submitted</span>
              <?php endif; ?>
            </td>   
            <td><?php echo isset($rate_row['threepeoplesorg_rate']) ? $rate_row['threepeoplesorg_rate'] : 'Not rated'; ?></td>
            <td><?php echo isset($remark_row['threepeoplesorg_remark']) ? $remark_row['threepeoplesorg_remark'] : 'No remarks'; ?></td>
            <td class="text-center align-middle"><input type="file" id="threepeoplesorg_pdf_File" name="threepeoplesorg_pdf_File" accept=".pdf" 
               onchange="toggleSubmitButton(this, 'submit34', 'cancel34')" />
              <input type="hidden" name="threepeoplesorg_pdf_File_hidden" id="threepeoplesorg_pdf_File_hidden" 
               value="<?php echo !empty($row['threepeoplesorg_pdf_File']) ? htmlspecialchars($row['threepeoplesorg_pdf_File']) : ''; ?>">
          <button type="submit" name="update" value="threepeoplesorg_pdf_File" id="submit34" 
                style="display: none; background-color: #000033;" class="btn btn-primary btn-sm">Update</button>
          <button type="button" id="cancel34" onclick="clearInput('threepeoplesorg_pdf_File', 'submit34', 'cancel34')" 
                style="display: none; background-color: #FF0000;" class="btn btn-danger btn-sm">Cancel</button>
    </td>   
          </tr>
              <tr>
              <th>Total</th>
                <td></td>
                <td>            
            </td>
            <th><?php echo isset($rate_row['total']) ? $rate_row['total'] : ' '; ?></th>
            <td></td>
              </tr>
            </tbody>
          </table>
    </form>
    <!-- Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="responseModalLabel">Notification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($message)) echo htmlspecialchars($message); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Main modal -->
<div id="large-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-4xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                    PDF Viewer
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="large-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="md:p-5 space-y-4">
               <iframe id="pdfViewer" src="" class="h-[28rem] w-full"></iframe>
            </div>
        </div>
    </div>
</div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
    <?php if (!empty($message)) : ?>
        var responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
        responseModal.show();
    <?php endif; ?>
});
    $(document).ready(function() {
        $('.view-pdf').attr('data-modal-target', 'large-modal');
        $('.view-pdf').attr('data-modal-toggle', 'large-modal');

        $('.view-pdf').click(function() {
            var pdfFile = $(this).data('file'); // Get the PDF file path from data attribute
            $('#pdfViewer').attr('src', pdfFile); // Set the file path in the iframe   

        });
    });
    function toggleSubmitButton(input, submitId, cancelId) {
    const submitButton = document.getElementById(submitId);
    const cancelButton = document.getElementById(cancelId);

    if (input.files.length > 0) {
        if (submitButton) submitButton.style.display = 'inline-block'; // Show Update button
        if (cancelButton) cancelButton.style.display = 'inline-block'; // Show Cancel button
    } else {
        if (submitButton) submitButton.style.display = 'none'; // Hide Update button
        if (cancelButton) cancelButton.style.display = 'none'; // Hide Cancel button
    }
}

function clearInput(inputId, submitId, cancelId) {
    const input = document.getElementById(inputId);
    const submitButton = document.getElementById(submitId);
    const cancelButton = document.getElementById(cancelId);

    if (input) input.value = ''; // Clear file input
    if (submitButton) submitButton.style.display = 'none'; // Hide Update button
    if (cancelButton) cancelButton.style.display = 'none'; // Hide Cancel button
}

function validateFileType(input) {
    const file = input.files[0];
    if (file && !file.name.endsWith('.pdf')) {
        alert('Please upload a valid PDF file.');
        input.value = ''; // Clear the invalid file
    }
}
  
  </script>
</body>
</html>
