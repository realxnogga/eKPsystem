<?php
session_start();
include_once("connection.php");

// Include the functions.php file from the eKPsystem folder
// Check if the user is logged in
if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
    $firstName = $_SESSION['first_name'];
    $lastName = $_SESSION['last_name'];
    
} else {
    $user = null; // Initialize $user as null if not set
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
  }
  
  
  // Check if the user is logged in
  if (!isset($_SESSION['user_id']) || $_SESSION['status'] !== 'loggedin') {
      // Return JSON error response if AJAX request, or redirect if a form submission
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a complaint.']);
          exit; // Prevent further execution
      } else {
          header("Location: login.php");
          exit; // Redirect to the login page
      }
  }
  
error_reporting(E_ALL);
ini_set('display_errors', 1);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Complaint</title>
    
</head>
<body class="bg-[#E8E8E7]">
<?php include "user_sidebar_header.php"; ?>
<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
    

      <!--  Row 1 -->
      <div class="card">
        
        <div class="card-body">
        
          <div class="d-flex align-items-center">
            <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
            <div>
                
              <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>
          <br>

          <h5 class="card-title mb-9 fw-semibold">Offline Add Complaint</h5>
          
          <b>
          <?php
$successMessage = ""; // Initialize at the beginning of your script

// Your logic to set $successMessage, e.g.:
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assume some processing here
    if ($formIsValid) {
        // Set success message
        $successMessage = "Your complaint has been successfully submitted!";
    }
}
?>




  
    <!-- Display the success message if set -->
    <b>
    <?php echo isset($successMessage) ? $successMessage : ''; 
        ?>
    </b>

    <!-- Form to add a complaint -->
    <form action="" id="complaintForm">
        <div class="row justify-content-between text-left">
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Case No.<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="CNum" name="CNum" placeholder="MMYY - Case No." required>
            </div>
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">For:<span class="text-danger">*</span></label>
                <select class="form-control" id="ForTitle" name="ForTitle" required>
                    <option value="">Select Title</option>
                    <option value="Others">Others</option>
                </select>
                <script>
    $(document).ready(function() {
      var suggestions = [
        "Tumults and other disturbances of public order; Tumltuous disturbances or interruption liable to cause disturbance (Art. 153)",
        "Unlawful use of means of publication and unlawful utterances (Art. 154)",
        "Alarms and Scandals (Art.155)",
        "Using false certificates (Art. 175)",
        "Using fictitious names and concealing true names (Art. 178)",
        "Illegal use of uniform and insignias (Art. 179)",
        "Physical injuries inflicted in a tumultuous affray (Art. 252)",
        "Giving assistance to suicide (Art. 253)",
        "Responsibility of participants in a duel (Art. 260)",
        "Less serious physical injuries [which shall incapacitate the offended party for labor for ten (10) days or more, or shall require medical assistance for the same period] (Art. 265]",
        "Slight physical injuries and maltreatment (Art. 266)",
        "Unlawful arrest (Art. 269)",
        "Inducing a minor to abandon his home (Art. 271)",
        "Abandonment of persons in danger and abandonment of one's own victim (Art. 275)",
        "Abandoning a minor (Art. 276)",
        "Abandonment of minor by a person entrusted with his custody; indifference of parents (Art. 277)",
        "Qualified trespass to dwelling (Art. 280)",
        "Other forms of trespass (Art. 281)",
        "Light threats (Art. 283)",
        "Other light threats (Art. 285)",
        "Grave coercion (Art. 286)",
        "Light coercions and unjust taxation (Art. 287)",
        "Other similar coercions (Compulsory purchase of merchandise and payment of wages by means of tokens) (Art. 288)",
        "Discovering secrets through the seizure of correspondence (Art. 290)",
        "Revealing secrets with abuse of office (if secrets are not revealed) (Art.291)",
        "Theft (Art. 309)",
        "Altering boundaries or landmarks (Art. 313)",
        "Swindling or Estafa (Art. 315)",
        "Other forms of swindling (Art. 316)",
        "Swindling a minor (Art. 317)",
        "Other deceits (Art. 318)",
        "Removal, sale or pledge of mortgaged property (Art. 319)",
        "Special cases of malicious mischief (Art. 328)",
        "Other mischief (Art. 327, in relation to Art. 329)",
        "Simple seduction (Art. 338)",
        "Acts of lasciviousness with the consent of the offended party (Art. 339)",
        "Threatening to publish and offer to prevent such publication for compensation (Art. 356)",
        "Prohibited publication of acts referred to in the course of official proceedings (Art. 357)",
        "Slander (Oral Defamation) (Art. 356)",
        "Slander by Deed (Art. 359)",
        "Incriminating Innocent Person (Art. 363)",
        "Intriguing against honor (Art. 364)",
        "Reckless imprudence and Simple negligence (Art. 365)",
        "Violation of B.P. NO. 22 or the Bouncing Checks Law",
        "Nuisance (Art. 694 of the Civil Code in the relation to Art. 695, for local ordinance with penal sanctions)",
        "Violation of P.D. No. 1612 or the Anti-Fencing Law",
        "Violation of Republic Act No. 11313 or 'The Safe Spaces Act' Gender-based sexual harassment in streets and public spaces.",
        "Others",
      ];

      // Initialize Select2
      $('#ForTitle').select2({
        placeholder: 'Select or start typing...',
        data: suggestions.map(function(item) {
          return {
            id: item,
            text: item
          };
        }),
        tags: true,
        createTag: function(params) {
          var term = $.trim(params.term);
          if (term === '') {
            return null;
          }
          return {
            id: term,
            text: term,
            newTag: true
          };
        },
        tokenSeparators: [','],
        closeOnSelect: false
      });

      // Add a click event listener to the "Other" option
      $('#ForTitle').on('select2:select', function(e) {
        var selectedValue = e.params.data.id;
        if (selectedValue === 'Other') {
          // Clear the selected value
          $(this).val(null).trigger('change');
          // Enable typing for the "Other" case
          $(this).select2('open');
        }
      });

      // Handle keyup event to update the input value with the typed text
      $('#ForTitle').on('keyup', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          var value = $(this).val();
          // Add the typed text as a tag
          if (value.trim() !== '') {
            $(this).append(new Option(value, value, true, true)).trigger('change');
          }
          // Clear the input
          $(this).val(null);
        }
      });
    });
  </script>
            </div>
        </div>

        <!-- Additional form fields -->
        <div class="row justify-content-between text-left">
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Complainants:<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="CNames" name="CNames" placeholder="Enter name of complainants" required>
            </div>
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Respondents:<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="RspndtNames" name="RspndtNames" placeholder="Enter name of respondents" required>
            </div>
        </div>

        <!-- Address and description fields -->
        <div class="row justify-content-between text-left">
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Address of Complainants:<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="CAddress" name="CAddress" placeholder="Enter address of complainants" required>
            </div>
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Address of Respondents:<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="RAddress" name="RAddress" placeholder="Enter address of respondents" required>
            </div>
        </div>

        <div class="row justify-content-between text-left">
            <div class="form-group col-12 flex-column d-flex">
                <label class="form-control-label px-3">Complaint:<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="CDesc" name="CDesc" required>
            </div>
        </div>
        <div class="row justify-content-between text-left">
            <div class="form-group col-12 flex-column d-flex">
                <label class="form-control-label px-3">Petition:<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="Petition" name="Petition" required>
            </div>
        </div>

        <!-- Date and case type fields -->
        <div class="row justify-content-between text-left">
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Made:<span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="Mdate" name="Mdate" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Received:</label>
                <input type="date" class="form-control" id="RDate" name="RDate">
            </div>
        </div>

        <div class="row justify-content-between text-left">
            <div class="form-group col-sm-6 flex-column d-flex">
                <label class="form-control-label px-3">Case Type:<span class="text-danger">*</span></label>
                <select name="CType" class="form-select" required>
                    <option value="Civil">Civil</option>
                    <option value="Criminal">Criminal</option>
                    <option value="Others">Others</option>
                </select>
            </div>
        </div><br>

        <!-- Submit button -->
        <div class="form-group col-2">
            <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-400 px-3 py-2 rounded-md text-white">Submit</button>
        </div>
    </form>
    <div id="complaintData" style="display: none;"></div>

    <!-- Offline notification -->
    <p id="offline-notification" style="display:none; color:red;">You are offline. Form data will be stored locally and submitted when online.</p>
    

<script>
    function goToDashboard() {
        // Redirect to the dashboard page
        window.location.href = 'http://localhost/eKPsystem/user_dashboard.php';
    }
</script>


    <script>
        
        const formData = {
            Mdate: '2024-10-11T14:30', // This should be dynamically set as per your requirement
            RDate: '2024-10-11'         // This too
        };
        document.getElementById('submitBtn').addEventListener('click', function() {
            // Set values from formData if needed
            document.getElementById('Mdate').value = formData.Mdate;
            document.getElementById('RDate').value = formData.RDate;
               // Log values to ensure they are set correctly
               console.log('Mdate:', document.getElementById('Mdate').value);
            console.log('RDate:', document.getElementById('RDate').value);

            // Optional: Add form submission logic here if needed
            // e.g., submit the form via AJAX or traditional POST
        });
        
    // Register the service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
            .then((registration) => {
                console.log('Service Worker registered with scope:', registration.scope);
            })
            .catch((error) => {
                console.log('Service Worker registration failed:', error);
            });
        });
    }
        // Check if the user is offline and notify them
        function updateOnlineStatus() {
            const offlineNotification = document.getElementById('offline-notification');
            const submitBtn = document.getElementById('submitBtn');
            
            if (!navigator.onLine) {
                offlineNotification.style.display = 'block';
                submitBtn.textContent = 'Submit';
            } else {
                offlineNotification.style.display = 'none';
                submitBtn.textContent = 'Submit';
            }
        }

        // Store form data in localStorage when offline
        document.getElementById('complaintForm').addEventListener('submit', function(event) {
    if (!navigator.onLine) {
        event.preventDefault(); // Prevent normal form submission

        // Retrieve existing offline data or initialize an empty array
        let existingData = localStorage.getItem('offlineComplaintData');
        let complaintList = existingData ? JSON.parse(existingData) : [];

        // Create new form data object
        let formData = {
            CNum: document.getElementById('CNum').value,
            ForTitle: document.getElementById('ForTitle').value,
            CNames: document.getElementById('CNames').value,
            RspndtNames: document.getElementById('RspndtNames').value,
            CAddress: document.getElementById('CAddress').value,
            RAddress: document.getElementById('RAddress').value,
            CDesc: document.getElementById('CDesc').value,
            Petition: document.getElementById('Petition').value,
            Mdate: document.getElementById('Mdate').value,
            RDate: document.getElementById('RDate').value,
            CType: document.querySelector('select[name="CType"]').value
        };

        // Add new complaint to the array
        complaintList.push(formData);

        // Store the updated array in localStorage
        localStorage.setItem('offlineComplaintData', JSON.stringify(complaintList));

        alert('Form data saved offline.');
    }
});



        // Listen for online and offline events to update the status dynamically
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);

        // Check connection status on load
        updateOnlineStatus();

        // Auto-fill the form with stored data when back online
        window.addEventListener('online', function() {
            
            const storedData = localStorage.getItem('offlineComplaintData');
            if (storedData) {
                const formData = JSON.parse(storedData);
                
                // Fill the form with the stored data
                document.getElementById('CNum').value = formData.CNum;
                document.getElementById('ForTitle').value = formData.ForTitle;
                document.getElementById('CNames').value = formData.CNames;
                document.getElementById('RspndtNames').value = formData.RspndtNames;
                document.getElementById('CAddress').value = formData.CAddress;
                document.getElementById('RAddress').value = formData.RAddress;
                document.getElementById('CDesc').value = formData.CDesc;
                document.getElementById('Petition').value = formData.Petition;
                document.getElementById('Mdate').value = formData.Mdate;
                document.getElementById('RDate').value = formData.RDate;
                document.querySelector('select[name="CType"]').value = formData.CType;

                // Create a FormData object to simulate a form submission
                const form = new FormData();
                for (const key in formData) {
                    form.append(key, formData[key]);
                }
                

                // Send the data to the server
                fetch('offline_add_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ForTitle: document.getElementById('ForTitle').value,
                    CNames: document.getElementById('CNames').value,
                    RspndtNames: document.getElementById('RspndtNames').value,
                    CDesc: document.getElementById('CDesc').value,
                    Petition: document.getElementById('Petition').value,
                    Mdate: document.getElementById('Mdate').value,
                    RDate: document.getElementById('RDate').value,
                    CType: document.querySelector('select[name="CType"]').value,
                    CAddress: document.getElementById('CAddress').value,
                    RAddress: document.getElementById('RAddress').value
                })
            })
                
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                })
                .catch(error => console.error('Error:', error));


            }});
                

            window.addEventListener('online', async function() {
    const storedData = localStorage.getItem('offlineComplaintData');
    
    if (storedData) {
        const complaintList = JSON.parse(storedData);

        for (const [index, complaint] of complaintList.entries()) {
            try {
                const response = await fetch('offline_add_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' // Ensure JSON is sent properly
                    },
                    body: JSON.stringify(complaint) // Convert the complaint object to a JSON string
                });
                const data = await response.json();

                if (data.success) {
                    console.log(`Complaint #${index + 1} submitted successfully!`);
                    alert(`Internet Connection is back, Complaints submitted successfully!`);
                } else {
                    console.log(`Failed to submit Complaint #${index + 1}: ${data.message}`);
                }
            } catch (error) {
                console.error(`Error submitting Complaint #${index + 1}:`, error);
            }
        }

        // Clear localStorage after all complaints are sent
        localStorage.removeItem('offlineComplaintData');
    }
});



<?php
ini_set('display_errors', 0); // Disable error display
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', '/path/to/error.log'); // Log errors to a specific file
error_reporting(E_ALL); // Report all types of errors

?>
</script></div>
          </b>
        </div>
      </div>

    </div>

  </div>
  </div>
    
</body>
</html>