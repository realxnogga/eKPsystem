<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'];

include 'lupon_handler.php';
if (!isset($_SESSION['language'])) {
  $_SESSION['language'] = 'english'; // Set default language to English
}

$folderName = ($_SESSION['language'] === 'english') ? 'forms_english' : 'forms_tagalog';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  if (isset($data['formID'])) {
    $formID = $data['formID'];
    $userID = $_SESSION['user_id'];

    $_SESSION['test'] = $userID;

    // Perform deletion logic, e.g., database query
    $query = "DELETE FROM luponforms WHERE id = :id AND user_id = :user_id";
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bindParam(':id', $formID, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);

    if ($stmt->execute()) {
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false]);
    }
  } else {
    echo json_encode(['success' => false]);
  }
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lupon</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <style>
    .truncate-text {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      display: block;
    }

    .truncate-text:hover {
      white-space: normal;
      overflow: visible;
      z-index: 1;
    }
  </style>
</head>

<body class="bg-[#E8E8E7]">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
      <div class="card overflow-hidden">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <img src="img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
            <div>
              <h5 class="card-title mb-2 fw-semibold">Department of the Interior and Local Government</h5>
            </div>
          </div>
          <br>
          <h5 class="card-title mb-9 fw-semibold"><?php echo ucfirst($_SESSION['language']); ?> Forms</h5>

          <!-- Language Selection Buttons -->
          <button type="button" onclick="setLanguage('english')" class="btn <?php echo ($_SESSION['language'] === 'english') ? 'bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white' : 'bg-gray-300 hover:bg-gray-200 px-3 py-2 rounded-md text-black'; ?> m-1">English</button>
          <button type="button" onclick="setLanguage('tagalog')" class="btn <?php echo ($_SESSION['language'] === 'tagalog') ? 'bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white' : 'bg-gray-300 hover:bg-gray-200 px-3 py-2 rounded-md text-black'; ?> m-1">Tagalog</button>

          <script>
            // Function to set language via AJAX
            function setLanguage(language) {
              // Send AJAX request to update language session variable
              $.ajax({
                url: 'update_language.php',
                method: 'POST',
                data: {
                  language: language
                },
                success: function(response) {
                  // Reload the page to reflect language changes
                  location.reload();
                },
                error: function(xhr, status, error) {
                  // Handle error
                  console.error(error);
                }
              });
            }
          </script>
          <br>
        </div>
        <a href="user_lupon.php" class="btn btn-dark">Back to Lupon</a>
      </div>

      <!-- Single input for selecting the year -->
      <input type="search" id="searchBar" class="form-control" placeholder="Search by Year">
      <br>

      <!-- Display KP Columns -->
      <div class="row">
        <?php
        // Get the current user's session formUsed and user_id
        $userID = $_SESSION['user_id'];

        // Query to fetch distinct formUsed values and form IDs for the current user from the luponforms table
        $query = "SELECT id, formUsed, made_date FROM luponforms WHERE user_id = :userID ORDER BY made_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize array to store forms for each KP column
        $kpForms = array_fill(1, 6, array());
        // Initialize array to store counters for each year and KP column
        $counters = array();

        // Organize forms into respective KP columns
        foreach ($results as $row) {
          $formID = $row['id'];
          $formUsed = $row['formUsed'];
          $madeDate = $row['made_date'];
          $year = date('Y', strtotime($madeDate));

          // Initialize counter for the current year and KP column if not set
          if (!isset($counters[$year][$formUsed])) {
            $counters[$year][$formUsed] = 1;
          } else {
            // Increment counter for the current year and KP column
            $counters[$year][$formUsed]++;
          }

          // Store form in respective KP column
          $kpForms[$formUsed][] = array('formID' => $formID, 'madeDate' => $madeDate, 'counter' => $counters[$year][$formUsed]);
        }

        // Display forms in KP columns
        for ($i = 1; $i <= 6; $i++) {
          echo '<div class="col-md-2 mb-3" id="kpColumn_' . $i . '">';
          echo '<h6>KP ' . $i . '</h6>';

          // Display buttons in the column
          foreach ($kpForms[$i] as $form) {
            $formID = $form['formID'];
            $formattedDate = date('Y', strtotime($form['madeDate']));
            $counter = $form['counter'];
            $buttonText = 'KP Form ' . $i . ' (' . $formattedDate . ')';

            // Append counter if it's greater than 1
            if ($counter > 1) {
              $buttonText .= ' (' . $counter . ')';
            }

            $buttonID = 'formButton_' . $formID;

            echo '<div id="toHide" class="h-fit w-full flex item-center justify-between bg-white my-2 rounded-lg">';
            echo '<button class="truncate-text open-form btn btn-success w-full" id="' . $buttonID . '" data-form-id="' . $formID . '" data-form-used="' . $i . '"> ' . $buttonText . ' </button>';

            echo '<button class="delete-form w-fit" data-form-id="' . $formID . '"><i class="ti ti-trash text-red-500 p-2"></i></button>';
            echo '</div>';
          }

          echo '</div>';
        }
        ?>
      </div>

      <script>
        // JavaScript to handle button clicks and redirection with formUsed and formID
        var buttons = document.querySelectorAll('.open-form');
        buttons.forEach(function(button) {
          button.addEventListener('click', function() {
            var formID = this.getAttribute('data-form-id'); // Get the formID
            var formUsed = this.getAttribute('data-form-used');
            var folderName = '<?php echo $folderName; ?>'; // PHP variable for folder name

            // Redirect to the appropriate form page with formID added to the URL
            window.location.href = folderName + '/kp_form' + formUsed + '.php?formID=' + formID;
          });
        });

        // Search functionality to filter elements by year
        document.getElementById("searchBar").addEventListener("input", function() {
          const searchValue = this.value.toLowerCase();
          const elements = document.querySelectorAll("#toHide");

          elements.forEach((element) => {
            // Extract the year from the text (assuming it's in a specific format, e.g., "(2023)")
            const yearMatch = element.textContent.match(/\((\d{4})\)/);
            const year = yearMatch ? yearMatch[1] : ""; // Extract year or empty string if no match

            if (year.includes(searchValue)) {
              element.style.display = "flex"; // Show matched element
            } else {
              element.style.display = "none"; // Hide unmatched element
            }
          });
        });
      </script>

      <script>
        document.querySelectorAll('.delete-form').forEach(button => {
          button.addEventListener('click', function() {
            const formId = this.getAttribute('data-form-id');

            // Optional: Confirm before deletion
            if (!confirm('Are you sure you want to delete this form?')) {
              return;
            }

            // Send a request to the server to delete the form
            fetch('user_used_forms.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                  formID: formId
                }),
              })
              .then(response => {
                if (!response.ok) {
                  throw new Error('Failed to delete the form');
                }
                return response.json();
              })
              .then(data => {
                // Remove the corresponding form's HTML element from the DOM
                if (data.success) {
                  this.closest('#toHide').remove();
               
                } else {
                  alert('Failed to delete the form. Please try again.');
                }
              })
              .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the form.');
              });
          });
        });
      </script>

    </div>
  </div>

</body>

</html>