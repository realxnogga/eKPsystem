<?php
session_start();

// Ensure the user is a superadmin
include 'connection.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['submitFeedbackQuestion'])) {
    $feedbackTitle = $_POST['feedbackTitle'];
    $fq1 = $_POST['fq1'];
    $fq2 = $_POST['fq2'];
    $fq3 = $_POST['fq3'];
    $fq4 = $_POST['fq4'];
    $fq5 = $_POST['fq5'];

    $stmt = $conn->prepare("INSERT INTO feedback_questions (feedback_title, fq1, fq2, fq3, fq4, fq5) VALUES (:feedbackTitle, :fq1, :fq2, :fq3, :fq4, :fq5)");
    $stmt->bindParam(':feedbackTitle', $feedbackTitle, PDO::PARAM_STR);
    $stmt->bindParam(':fq1', $fq1, PDO::PARAM_STR);
    $stmt->bindParam(':fq2', $fq2, PDO::PARAM_STR);
    $stmt->bindParam(':fq3', $fq3, PDO::PARAM_STR);
    $stmt->bindParam(':fq4', $fq4, PDO::PARAM_STR);
    $stmt->bindParam(':fq5', $fq5, PDO::PARAM_STR);

    if ($stmt->execute()) {
      header("Location: sa_feedback.php?feedback_inserted_message=success");
      exit();
    } else {
      header("Location: sa_feedback.php?feedback_inserted_message=failed");
      exit();
    }
  }

  if (isset($_POST['editfq_id'])) {
    $id = $_POST['editfq_id'];

    if (isset($_POST['submitDeleteFeedbackQuestion' . $id])) {
      // Delete from feedback_questions table
      $stmt1 = $conn->prepare("DELETE FROM feedback_questions WHERE fq_id = :fq_id");
      $stmt1->bindParam(':fq_id', $id, PDO::PARAM_INT);

      // Delete from another_table
      $stmt2 = $conn->prepare("DELETE FROM feedback_answers WHERE fa_id = :fa_id");
      $stmt2->bindParam(':fa_id', $id, PDO::PARAM_INT);

      // Execute both statements
      $success1 = $stmt1->execute();
      $success2 = $stmt2->execute();

      if ($success1 && $success2) {
        // Redirect after successful deletion
        header("Location: sa_feedback.php");
        exit();
      }
    }
  }



  // -----------------------------------------------------------------------------------
  if (isset($_POST['yearfilter'])) {
    $selectedYear = $_POST['yearfilter'];
    $_SESSION['fy_feedback'] = $selectedYear;
    $questionTemp = fetchFeedbackQuestionFunc($conn, $selectedYear);
  }
}

$selectedYear = isset($_SESSION['fy_feedback']) ? $_SESSION['fy_feedback'] : date('Y');
$questionTemp = fetchFeedbackQuestionFunc($conn, $selectedYear);

// Fetch feedback questions
function fetchFeedbackQuestionFunc($conn, $whatYear)
{
  $sql = "SELECT * FROM feedback_questions WHERE YEAR(fq_creation_date) = :year ORDER BY fq_creation_date DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':year', $whatYear, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// -----------------------------------------------------------------------------------


function countResponseFunc($conn, $whatTable, $condition = null)
{
  $sql = "SELECT COUNT(*) FROM $whatTable";
  if ($condition !== null) {
    $sql .= " WHERE $condition";
  }
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->fetchColumn();
}

function getFeedbackDataFunc($conn, $whatCol, $whatTable, $id)
{
  $stmt = $conn->prepare("SELECT AVG($whatCol) FROM $whatTable WHERE fa_id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $temp = $stmt->fetchColumn();
  return number_format($temp, 1) === '0.0' ? '' : number_format($temp, 1);
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="assets/css/styles.min.css" />

  <script src="https://cdn.tailwindcss.com"></script>

  <link rel="stylesheet" href="hide_show_icon.css">

</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <section class="p-4 bg-white rounded-xl h-fit">

        <section class="flex justify-between gap-x-4">

          <input onkeyup="searchFeedback();" type="search" id="searchFeedbackButton" class="form-control" placeholder="Search by feedback title">

          <form method="POST" action="">
            <select id="yearfilter" name="yearfilter" onchange="this.form.submit()">
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

          <button onclick="showmodalFunc();" id="showModalBtn" type="button" class="btn btn-primary bg-blue-500">
            <span>
              <i class="ti ti-plus text-lg show-icon"></i>
              <p style="white-space: nowrap;" class="hide-icon hidden">Add feedback</p>
            </span>
          </button>
        </section>

        <div id="feedbackModal" class="hidden h-screen w-screen absolute inset-0 z-50 flex justify-center items-center bg-black bg-opacity-75">

          <div class="p-4 md:p-5 space-y-4 bg-white w-fit rounded-xl">
            <h3 class="text-lg font-bold">Create feedback questions for barangays.</h3>

            <form method="POST" action="" class="space-y-2 max-w-lg mx-auto my-3">
              <input required name="feedbackTitle" type="text" placeholder="Enter title" class="text-sm w-full py-2 border border-gray-300 rounded-md border !border-gray-300">
              <input required name="fq1" type="text" placeholder="Create question 1" class="text-sm w-full py-2 border border-gray-300 rounded-md border !border-gray-300">
              <input required name="fq2" type="text" placeholder="Create question 2" class="text-sm w-full py-2 border border-gray-300 rounded-md border !border-gray-300">
              <input required name="fq3" type="text" placeholder="Create question 3" class="text-sm w-full py-2 border border-gray-300 rounded-md border !border-gray-300">
              <input required name="fq4" type="text" placeholder="Create question 4" class="text-sm w-full py-2 border border-gray-300 rounded-md border !border-gray-300">
              <input required name="fq5" type="text" placeholder="Create question 5" class="text-sm w-full py-2 border border-gray-300 rounded-md border !border-gray-300">


              <button name="submitFeedbackQuestion" type="submit" class=" py-2 px-3 mt-4 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600">
                Submit
              </button>

              <button onclick="hidemodalFunc();" type="button" class="py-2 px-3 mt-4 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600" data-modal-hide="default-modal">
                <span>Cancel</span>
              </button>

            </form>
          </div>
        </div>

        <?php
        if (isset($_GET['feedback_inserted_message'])) {
          if ($_GET['feedback_inserted_message'] === 'success') {
            echo '<div id="alertMessage" class="alert alert-success my-3" role="alert">Feedback questions inserted successfully.</div>';
          }
          if ($_GET['feedback_inserted_message'] === 'failed') {
            echo '<div id="alertMessage" class="alert alert-danger my-3" role="alert">Failed to insert feedback questions.</div>';
          }
        }
        ?>

        <section id="feedbackContainer">

          <p class="text-center text-lg mt-4">
            <?php echo empty($questionTemp) ? 'No data for this year' : ''; ?>
          </p>

          <?php foreach ($questionTemp as $row) { ?>

            <section class="feedback-item">

              <div class="w-50 flex justify-between items-center mt-4">
                <h3 class='text-lg font-bold'><?php echo $row["feedback_title"]; ?></h3>
                <p>Created on <?php echo date('M d Y', strtotime($row['fq_creation_date'])) ?></p>
              </div>

              <form method="POST" action="" class="flex flex-col gap-y-1 w-100 border-2 border-gray-200 rounded-lg p-2">

                <div class="flex justify-between items-center">
                  <input value="<?php echo $row['fq1']; ?>" required name="editfq1" type="text" placeholder="Edit question 1" class="text-sm w-50 py-2 border !border-gray-300 rounded-md ">
                  <p><?php echo getFeedbackDataFunc($conn, "fa1", "feedback_answers", $row['fq_id']); ?></p>
                </div>

                <div class="flex justify-between items-center">
                  <input value="<?php echo $row['fq2']; ?>" required name="editfq2" type="text" placeholder="Edit question 2" class="text-sm w-50 py-2 border !border-gray-300 rounded-md ">
                  <p><?php echo getFeedbackDataFunc($conn, "fa2", "feedback_answers", $row['fq_id']); ?></p>
                </div>

                <div class="flex justify-between items-center">
                  <input value="<?php echo $row['fq3']; ?>" required name="editfq3" type="text" placeholder="Edit question 3" class="text-sm w-50 py-2 border !border-gray-300 rounded-md ">
                  <p><?php echo getFeedbackDataFunc($conn, "fa3", "feedback_answers", $row['fq_id']); ?></p>
                </div>

                <div class="flex justify-between items-center">
                  <input value="<?php echo $row['fq4']; ?>" required name="editfq4" type="text" placeholder="Edit question 4" class="text-sm w-50 py-2 border !border-gray-300 rounded-md ">
                  <p><?php echo getFeedbackDataFunc($conn, "fa4", "feedback_answers", $row['fq_id']); ?></p>
                </div>

                <div class="flex justify-between items-center">
                  <input value="<?php echo $row['fq5']; ?>" required name="editfq5" type="text" placeholder="Edit question 5" class="text-sm w-50 py-2 border !border-gray-300 rounded-md ">
                  <p><?php echo getFeedbackDataFunc($conn, "fa5", "feedback_answers", $row['fq_id']); ?></p>
                </div>

                <input hidden value="<?php echo $row['fq_id']; ?>" required name="editfq_id" type="number">

                <section class="flex justify-between items-end">
                  <button name="submitDeleteFeedbackQuestion<?php echo $row['fq_id']; ?>" type="submit" class="py-2 px-3 text-white rounded-md bg-red-500 w-fit">
                    Delete
                  </button>

                  <a title="View more" href="sa_feedback_view.php?fq_id_url=<?php echo $row['fq_id']; ?>">
                    <p><?php echo countResponseFunc($conn, "feedback_answers", 'fa_id = ' . $row['fq_id'] . ''); ?> / <?php echo countResponseFunc($conn, "barangays"); ?> <?php echo countResponseFunc($conn, "feedback_answers", 'fa_id = ' . $row['fq_id'] . '') > 1 ? "responses" : "response"; ?></p>
                  </a>

                  <!-- <div id="tooltip-light<?php echo $row['fq_id']; ?>" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip">
                    view more
                    <div class="tooltip-arrow" data-popper-arrow></div>
                  </div> -->

                </section>

              </form>

            </section>
          <?php } ?>
        </section>

      </section>
    </div>
  </div>

  <script>
    function showmodalFunc() {
      document.getElementById('feedbackModal').classList.remove('hidden');
      document.getElementsByTagName('body')[0].classList.add('overflow-hidden');
      document.getElementsByTagName('body')[0].classList.remove('overflow-scroll');
    }

    function hidemodalFunc() {
      document.getElementById('feedbackModal').classList.add('hidden');
      document.getElementsByTagName('body')[0].classList.remove('overflow-hidden');
      document.getElementsByTagName('body')[0].classList.add('overflow-scroll');
    }
  </script>

  <!-- ------------------------------- -->
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

  <script src="hide_toast.js"></script>
</body>

</html>