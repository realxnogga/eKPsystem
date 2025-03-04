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
  .verify-btn {
    min-width: 80px;
}
.verify-btn.btn-primary {
    background-color: #ff0000;
    color: white;
}
.verify-btn.btn-success {
    background-color: #28a745;
    color: white;
}
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

.btn-secondary {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: white !important;
}

.btn-secondary:hover {
    opacity: 0.65 !important;
    cursor: not-allowed !important;
}

.verify-btn[disabled],
input[disabled],
textarea[disabled] {
    opacity: 0.65;
    cursor: not-allowed !important;
}

input[disabled],
textarea[disabled] {
    background-color: #e9ecef !important;
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
                        var verifyBtn = $('button[data-field="' + type + '_pdf_verify"]');
                        var $row = fileColumn.closest('tr');
                        var $rateInput = $row.find(`input[name="${type}_pdf_rate"]`);
                        var $remarkTextarea = $row.find(`textarea[name="${type}_pdf_remark"]`);
                        
                        if (data[fileKey]) {
                            // File exists - enable verification and show View button
                            var filePath = 'movfolder/' + data[fileKey];
                            $('.view-pdf[data-type="' + type + '"]').attr('data-file', filePath).show();
                            fileColumn.html('<button type="button" style="background-color: #000033;" class="btn btn-primary view-pdf" data-type="' + type + '" data-file="' + filePath + '">View</button>');
                            
                            // Enable verify button with normal styling
                            verifyBtn
                                .prop('disabled', false)
                                .text('Verify')  // Reset to 'Verify' text
                                .removeClass('btn-secondary')  // Remove gray styling
                                .addClass('btn-primary')  // Add primary styling
                                .css('cursor', 'pointer')
                                .attr('title', '');

                            // Enable rate input and remark textarea
                            $rateInput.prop('disabled', false)
                                .css({
                                    'background-color': '',
                                    'cursor': 'pointer'
                                })
                                .attr('title', '');
                            $remarkTextarea.prop('disabled', false)
                                .css({
                                    'background-color': '',
                                    'cursor': 'pointer'
                                })
                                .attr('title', '');
                        } else {
                            // No file - show warning and disable verification
                            fileColumn.html('<div class="alert alert-warning mb-0">No uploaded file</div>');
                            
                            // Update verify button to "Upload MOV" and gray styling
                            verifyBtn
                                .prop('disabled', true)
                                .text('Upload MOV')  // Change text to Upload MOV
                                .removeClass('btn-primary btn-success')  // Remove other button styles
                                .addClass('btn-secondary')  // Add gray styling
                                .css({
                                    'cursor': 'not-allowed',
                                    'opacity': '0.65'  // Make it look more disabled
                                })
                                .attr('title', 'Please upload MOV first');

                            // Disable and clear rate input and remark textarea
                            $rateInput
                                .prop('disabled', true)
                                .val('')
                                .css({
                                    'background-color': '#e9ecef',
                                    'cursor': 'not-allowed'
                                })
                                .attr('title', 'Cannot rate - No file uploaded');
                            $remarkTextarea
                                .prop('disabled', true)
                                .val('')
                                .css({
                                    'background-color': '#e9ecef',
                                    'cursor': 'not-allowed'
                                })
                                .attr('title', 'Cannot add remarks - No file uploaded');
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

                    // Update verification buttons
                    if (data.verifications) {
                        updateVerificationButtons(data.verifications);
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

    // Function to update verification button states
    function updateVerificationButtons(verifications) {
        if (!verifications) return;
        
        // Loop through all verify buttons
        $('.verify-btn').each(function() {
            var field = $(this).data('field');
            if (field && verifications[field] !== undefined) {
                var isVerified = verifications[field] === 1;
                var $row = $(this).closest('tr');
                
                // Update button appearance
                $(this)
                    .text(isVerified ? 'Verified' : 'Verify')
                    .removeClass('btn-primary btn-success')
                    .addClass(isVerified ? 'btn-success' : 'btn-primary');
                
                // Get the corresponding rate input and remark textarea
                var baseFieldName = field.replace('_verify', '');
                var $rateInput = $row.find(`input[name="${baseFieldName}_rate"]`);
                var $remarkTextarea = $row.find(`textarea[name="${baseFieldName}_remark"]`);
                
                // Enable/disable based on verification status
                if (!isVerified) {
                    $rateInput
                        .prop('disabled', true)
                        .css({
                            'background-color': '#e9ecef',
                            'cursor': 'not-allowed'
                        });
                    $remarkTextarea
                        .prop('disabled', true)
                        .css({
                            'background-color': '#e9ecef',
                            'cursor': 'not-allowed'
                        });
                } else {
                    $rateInput
                        .prop('disabled', false)
                        .css({
                            'background-color': '',
                            'cursor': ''
                        });
                    $remarkTextarea
                        .prop('disabled', false)
                        .css({
                            'background-color': '',
                            'cursor': ''
                        });
                }
            }
        });
    }

    // Add click handler for verify buttons
    $(document).on('click', '.verify-btn', function() {
        if ($(this).prop('disabled')) {
            return; // Exit if button is disabled
        }
        
        var btn = $(this);
        var field = btn.data('field');
        var movId = $('#mov_id').val();
        var barangayId = $('#barangay_id').val();
        var $row = btn.closest('tr');
        var fileColumn = $row.find('.file-column');

        // Check if there's a file uploaded
        if (fileColumn.find('.alert-warning').length > 0) {
            showModal('Cannot verify - No file uploaded');
            return;
        }

        if (!field || !movId || !barangayId) {
            showModal('Missing required data for verification');
            return;
        }

        $.ajax({
            url: 'verify_mov_handler.php',
            method: 'POST',
            data: {
                field: field,
                mov_id: movId,
                barangay_id: barangayId
            },
            success: function(response) {
                if (response.status === 'success') {
                    btn.text(response.verified ? 'Verified' : 'Verify')
                       .removeClass('btn-primary btn-success')
                       .addClass(response.verified ? 'btn-success' : 'btn-primary');
                    
                    // Refresh verification data
                    var selectedBarangay = $('#barangay_select').val();
                    if (selectedBarangay) {
                        $.ajax({
                            url: 'fetch_files.php',
                            method: 'POST',
                            data: { barangay_name: selectedBarangay },
                            dataType: 'json',
                            success: function(data) {
                                if (data.verifications) {
                                    updateVerificationButtons(data.verifications);
                                }
                            },
                            error: function() {
                                console.error('Error fetching verification data');
                            }
                        });
                    }
                } else {
                    showModal('Failed to update verification status');
                }
            },
            error: function() {
                showModal('Error occurred while updating verification status');
            }
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

$(document).on('click', '.verify-btn', function() {
    const fieldName = $(this).data('field');
    const movId = $('#mov_id').val();
    // Add verification button click handler
    // $(document).on('click', '.verify-btn', function() {
    //     const fieldName = $(this).data('field');
    //     const movId = $('#mov_id').val();
        // const barangayId = $('#barangay_id').val();
        
        if (!movId || !barangayId) {
            showModal('Please select a barangay first');
            return;
        }

        $.ajax({
            url: 'verify_mov_handler.php',
            type: 'POST',
            data: {
                field: fieldName,
                mov_id: movId,
                barangay_id: barangayId,
                action: 'verify'
            },
            success: function(response) {
                if (response.status === 'success') {
                    // Update button appearance
                    const btn = $(`.verify-btn[data-field="${fieldName}"]`);
                    btn.removeClass('btn-primary').addClass('btn-success');
                    btn.html('Verified');
                    showModal('Successfully verified ' + fieldName);
                } else {
                    showModal('Error: ' + response.message);
                }
            },
            error: function() {
                showModal('Error occurred while verifying');
            }
        });
    });
</script>
</head>
<body class="bg-[#E8E8E7]">
  <?php include "../admin_sidebar_header.php"; ?>
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
    <form id="evaluationForm" method="post" action="adminevaluate_handler.php" enctype="multipart/form-data">
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
               <td>
                <button type="button" class="btn btn-sm verify-btn" data-field="IA_1a_pdf_verify">
                    Verify
                </button>
               </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IA_1b_pdf_verify">
            Verify
        </button>
    </td>
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
          <td>
            <button type="button" class="btn btn-sm verify-btn" data-field="IA_2a_pdf_verify">
                Verify
            </button>
          </td>
            <td><input type="number" value="" name="IA_2a_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2a_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>b) Conciliation (15 days from initial confrontation with the Pangkat)</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2b">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
            </td>
            <td>
            <button type="button" class="btn btn-sm verify-btn" data-field="IA_2b_pdf_verify">
                Verify
            </button>
            </td>
            <td><input type="number" value="" name="IA_2b_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2b_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>c) Conciliation (with extended period not to exceed another 15 days)</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2c">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IA_2c_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IA_2c_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2c_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>d) Arbitration (within 10 days from the date of the agreement to arbitrate)</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2d">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IA_2d_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IA_2d_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IA_2d_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>e) Conciliation beyond 46 days but not more than 60 days on a clearly meritorious case</td>
                <td>2</td>
                <td class="file-column" data-type="IA_2e">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IA_2e_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IA_2e_pdf_rate" min="0" max="2" class="score-input"placeholder="Ratings"></td>
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
                <td>
                <button type="button" class="btn btn-sm verify-btn" data-field="IB_1forcities_pdf_verify">
                    Verify
                </button>
                </td>
            <td><input type="number" value="" name="IB_1forcities_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td ><textarea name="IB_1forcities_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr id="municipality-row" style="display:none;">
              <td>For Municipalities:
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

              </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IB_1aformuni_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IB_1aformuni_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IB_1aformuni_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr id="municipality-row" style="display:none;">
                <td>b. Digital Record Filing</td>
                <td>1</td>
                <td class="file-column" data-type="IB_1bformuni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IB_1bformuni_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IB_1bformuni_pdf_rate" min="0" max="1" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IB_1bformuni_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Copies of Minutes of Lupon meetings with attendance sheets and notices</td>
                <td>1</td>
                <td class="file-column" data-type="IB_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IB_2_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IB_2_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IB_2_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>3. Copies of reports submitted to the Court and to the DILG on file</td>
                <td>1</td>
                <td class="file-column" data-type="IB_3">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IB_3_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IB_3_pdf_rate" min="0" max="1" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IB_3_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>4. All records are kept on file in a secured filing cabinet(s)</td>
                <td>1</td>
                <td class="file-column" data-type="IB_4">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IB_4_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IC_1_pdf_verify">
            Verify
        </button>
    </td>
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
            <td>
                <button type="button" class="btn btn-sm verify-btn" data-field="IC_2_pdf_verify">
                    Verify
                </button>
            </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="ID_1_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="ID_2_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIA_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIB_1_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IIB_1_pdf_rate" min="0" max="1" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IIB_1_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>2. Non-recurrence of cases settled</td>
                <td>1</td>
                <td class="file-column" data-type="IIB_2">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIB_2_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIC_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIA_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIB_pdf_verify">
            Verify
        </button>
    </td>
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
                <td>
                <details>
        <summary><b>1. For Cities</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>2 points</b> - IEC materials produced <br>
          <b>1 points</b> - IEC activities conducted<br>
          <b>2 points</b> - Innovative Campaign Strategy
          <br>
        </p>
      </details>
                </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIC_1forcities_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIC_1forcities2_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIC_1forcities3_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IIIC_1forcities3_pdf_rate" min="0" max="2" class="score-input"placeholder="Ratings"></td>
            <td><textarea name="IIIC_1forcities3_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="municipality-row" style="display:none;">
                <td>
                <details>
        <summary><b>2. For Municipalities</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>2 points</b> - IEC materials produced          <br>
          <b>2 points</b> - IEC activities conducted<br>
          <b>1 points</b> - Innovative Campaign Strategy
          <br>
        </p>
      </details>
                </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIC_2formuni1_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIC_2formuni2_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIIC_2formuni3_pdf_verify">
            Verify
        </button>
    </td>
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
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IIID_pdf_verify">
            Verify
        </button>
    </td>
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
                <td>
                <details>
        <summary><b>For Cities - the office or space should be exclusive for KP matters</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>5 points</b> - office or room for exclusive use during KP proceedings with tables, chairs and other furniture and necessary equipment          <br>
          <b>2 points</b> - office or room for shared use during KP proceedings with tables, chairs and other furniture and necessary equipment
          <br>
        </p>
      </details>
                </td>
                <td>5</td>
                <td class="file-column" data-type="IV_forcities">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IV_forcities_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="IV_forcities_pdf_rate" min="0" max="5" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="IV_forcities_pdf_remark" placeholder="Remarks"></textarea></td>
            </tr>
              <tr id="municipality-row" style="display:none;">
                <td>For Municipalities - KP office or space may be shared or used for other Barangay matters.</td>
                <td>5</td>
                <td class="file-column" data-type="IV_muni">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="IV_muni_pdf_verify">
            Verify
        </button>
    </td>
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
                <td>
                <details>
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
      </details>
                </td>
                <td>2</td>
                <td class="file-column" data-type="V_1">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="V_1_pdf_verify">
            Verify
        </button>
    </td>
            <td><input type="number" value="" name="V_1_pdf_rate" min="0" max="2" class="score-input" placeholder="Ratings"></td>
            <td><textarea name="V_1_pdf_remark" placeholder="Remarks"></textarea></td>
              </tr>
              <tr>
                <td>
                <details>
        <summary><b>3 From People's Organizations, NGOs or Private Sector</b></summary>
        <p><br>
          <b>Scoring Details:</b> <br><br>
          <b>1 points</b> -  received support from either the organization or individual<br>
          <b>0 points</b> - no support received from either the organization or individual
          <br>
        </p>
      </details>
                </td>
              <td>1</td>
              <td class="file-column" data-type="threepeoplesorg">
        <span class="alert alert-info">Select barangay</span> <!-- Default message if no barangay selected -->
    </td>
    <td>
        <button type="button" class="btn btn-sm verify-btn" data-field="threepeoplesorg_verify">
            Verify
        </button>
    </td>
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
  // Add this inside your existing $(document).ready(function() { ... });
$(document).on('click', '.verify-btn', function() {
    const fieldName = $(this).data('field');
    const movId = $('#mov_id').val();
    // Add verification button click handler
    // $(document).on('click', '.verify-btn', function() {
    //     const fieldName = $(this).data('field');
    //     const movId = $('#mov_id').val();
        // const barangayId = $('#barangay_id').val();
        
        if (!movId || !barangayId) {
            showModal('Please select a barangay first');
            return;
        }

        $.ajax({
            url: 'verify_mov_handler.php',
            type: 'POST',
            data: {
                field: fieldName,
                mov_id: movId,
                barangay_id: barangayId,
                action: 'verify'
            },
            success: function(response) {
                if (response.status === 'success') {
                    // Update button appearance
                    const btn = $(`.verify-btn[data-field="${fieldName}"]`);
                    btn.removeClass('btn-primary').addClass('btn-success');
                    btn.html('Verified');
                    showModal('Successfully verified ' + fieldName);
                } else {
                    showModal('Error: ' + response.message);
                }
            },
            error: function() {
                showModal('Error occurred while verifying');
            }
        });
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
