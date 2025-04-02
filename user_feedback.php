<?php
session_start();

// Ensure the user is a superadmin
include 'connection.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$barangay_id = $_SESSION['barangay_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['fa_id'])) {

    $fa_id = $_POST['fa_id'];

    if (isset($_POST['submitFeedbackAnswer' . $fa_id])) {

      $fa1 = $_POST['fa1'];
      $fa2 = $_POST['fa2'];
      $fa3 = $_POST['fa3'];
      $fa4 = $_POST['fa4'];
      $fa5 = $_POST['fa5'];
      $comment = $_POST['comment'];

      // Check if the combination of fa_id and barangay_id already exists
      $checkStmt = $conn->prepare("SELECT COUNT(*) FROM feedback_answers WHERE fa_id = :fa_id AND barangay_id = :barangay_id");
      $checkStmt->bindParam(':fa_id', $fa_id, PDO::PARAM_STR);
      $checkStmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_STR);
      $checkStmt->execute();
      $count = $checkStmt->fetchColumn();

      if ($count == 0) {
        $stmt = $conn->prepare("INSERT INTO feedback_answers (fa_id, barangay_id, fa1, fa2, fa3, fa4, fa5, comment) VALUES (:fa_id, :barangay_id, :fa1, :fa2, :fa3, :fa4, :fa5, :comment)");

        $stmt->bindParam(':fa_id', $fa_id, PDO::PARAM_STR);
        $stmt->bindParam(':barangay_id', $barangay_id, PDO::PARAM_STR);
        $stmt->bindParam(':fa1', $fa1, PDO::PARAM_STR);
        $stmt->bindParam(':fa2', $fa2, PDO::PARAM_STR);
        $stmt->bindParam(':fa3', $fa3, PDO::PARAM_STR);
        $stmt->bindParam(':fa4', $fa4, PDO::PARAM_STR);
        $stmt->bindParam(':fa5', $fa5, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

        if ($stmt->execute()) {
          header("Location: user_feedback.php");
          exit();
        }
      } else {
        // Handle the case when the combination of fa1 and fa2 already exists
        echo "The combination of fa1 and fa2 already exists in the database.";
      }
    }
  }

  // -----------------------------------------------------------------------------------
  if (isset($_POST['yearfilter'])) {
    $selectedYear = $_POST['yearfilter'];
    $_SESSION['fy_questionfeedback'] = $selectedYear;
    $questionTemp = fetchFeedbackQuestionFunc($conn, $selectedYear);
  }
}

$selectedYear = isset($_SESSION['fy_questionfeedback']) ? $_SESSION['fy_questionfeedback'] : date('Y');
$questionTemp = fetchFeedbackQuestionFunc($conn, $selectedYear);

function fetchFeedbackQuestionFunc($conn, $whatYear)
{
  $sql = "SELECT * FROM feedback_questions WHERE YEAR(fq_creation_date) = :year ORDER BY fq_creation_date DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':year', $whatYear, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// ------------------------------------------------------------------------------------


function isAlreadyAnsweredFunc($conn, $brgy_id)
{
  $stmt = $conn->prepare("SELECT fa_id FROM feedback_answers WHERE barangay_id = :brgy_id");
  $stmt->bindParam(':brgy_id', $brgy_id, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_NUM);

  // Extract the fa_id column into a single-dimensional array
  return array_column($result, 0);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!-- flowbite component -->
  <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
  <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
  <!-- tabler icon -->
  <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
  <!-- tabler support -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

  <script src="https://cdn.tailwindcss.com"></script>
  
</head>

<body class="sm:bg-gray-200 bg-white">

  <?php include "user_sidebar_header.php"; ?>
    <!-- tailwind cdn -->
    

  <div class="p-0 sm:p-6 sm:ml-44 text-gray-700">
    <div class="rounded-lg mt-16">

      <section class="p-6 bg-white rounded-lg shadow-none sm:shadow-md">

        <!-- Search and Filter Section -->
        <section class="flex flex-col sm:flex-row justify-between gap-4 mb-4">
          <input onkeyup="searchFeedback();" type="search" id="searchFeedbackButton" class="form-control border border-gray-300 rounded-md p-2 w-full sm:w-1/2" placeholder="Search by feedback title">

          <form method="POST" action="" class="w-full sm:w-auto">
            <select id="yearfilter" name="yearfilter" onchange="this.form.submit()" class="border border-gray-300 rounded-md p-2 w-full sm:w-auto">
              <?php
              $currentYear = date('Y');
              $startYear = $currentYear - 5; // Start 5 years before the current year

              // Loop to generate options from startYear to endYear
              for ($year = $startYear; $year <= $currentYear; $year++) {
                echo "<option value='$year'" . ($year == $selectedYear ? " selected" : "") . ">$year</option>";
              }
              ?>
            </select>
          </form>
        </section>

        <!-- Feedback Items -->
        <p class="text-center text-lg mt-4">
          <?php echo empty($questionTemp) ? 'No data available' : ''; ?>
        </p>

        <?php foreach ($questionTemp as $row) { ?>
          <section class="feedback-item <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'relative text-gray-300 cursor-not-allowed' : '' ?> border border-gray-300 rounded-lg p-4 mb-4 bg-gray-50 shadow-sm">

            <?php if (in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id))) { ?>
              <h3 class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-gray-500">You already answered this!</h3>
            <?php } ?>

            <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
              <p class="text-lg font-bold"><?php echo $row["feedback_title"]; ?></p>
              <p class="text-sm text-gray-500">Created on <?php echo date('M d Y', strtotime($row['fq_creation_date'])) ?></p>
            </div>

            <form method="POST" action="" class="flex flex-col gap-4">
              <div class="overflow-x-auto">
                <table class="table-auto w-full border-collapse border border-gray-300">
                  <thead>
                    <tr class="bg-gray-200">
                      <th class="py-2 px-4 text-left text-xs">Questions</th>
                      <th class="py-2 px-4 text-center text-xs">(5) Very Satisfied</th>
                      <th class="py-2 px-4 text-center text-xs">(4) Satisfied</th>
                      <th class="py-2 px-4 text-center text-xs">(3) Neutral</th>
                      <th class="py-2 px-4 text-center text-xs">(2) Dissatisfied</th>
                      <th class="py-2 px-4 text-center text-xs">(1) Very Dissatisfied</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                      <tr>
                        <td class="py-2 px-4 text-sm"><?php echo $row["fq$i"]; ?></td>
                        <?php for ($j = 5; $j >= 1; $j--) { ?>
                          <td class="py-2 px-4 text-center">
                            <input class="<?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                                   <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> 
                                   type="radio" value="<?php echo $j; ?>" name="fa<?php echo $i; ?>" required>
                          </td>
                        <?php } ?>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>

              <textarea class="border border-gray-300 rounded-md p-2 w-full <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'placeholder-gray-300 cursor-not-allowed' : '' ?>" 
                        <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> 
                        id="comment" name="comment" rows="2" placeholder="Write a comment/suggestion"></textarea>

              <input hidden value="<?php echo $row['fq_id']; ?>" required name="fa_id" type="number">

              <button name="submitFeedbackAnswer<?php echo $row['fq_id']; ?>" type="submit" 
                      class="py-2 px-4 text-white rounded-md bg-blue-500 hover:bg-blue-600 w-fit <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'bg-gray-300 cursor-not-allowed' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?>>
                Submit
              </button>
            </form>
          </section>
        <?php } ?>

      </section>
    </div>
  </div>

  <script>
    function searchFeedback() {
      let input = document.getElementById('searchFeedbackButton');
      let filter = input.value.toLowerCase();
      let feedbackItems = document.getElementsByClassName('feedback-item');

      for (let i = 0; i < feedbackItems.length; i++) {
        let item = feedbackItems[i];
        let textContent = item.textContent || item.innerText;

        if (textContent.toLowerCase().indexOf(filter) > -1) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      }
    }
  </script>



</body>

</html>