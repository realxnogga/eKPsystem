<?php
session_start();

// Ensure the user is a superadmin
include 'connection.php';

// Check if the user is a superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
  header("Location: login.php");
  exit;
}

$fq_id_url = isset($_GET['fq_id_url']) ? $_GET['fq_id_url'] : null;

$questionTemp = $conn->query("SELECT * FROM feedback_questions WHERE fq_id = $fq_id_url")->fetchAll(PDO::FETCH_ASSOC);

function getAFunc($conn, $whatTable, $condition)
{
  return $conn->query("SELECT * FROM $whatTable WHERE $condition")->fetchColumn(PDO::FETCH_ASSOC);
}

function getAnyFunc($conn, $whatTable, $condition)
{
  return $conn->query("SELECT * FROM $whatTable WHERE $condition")->fetchColumn(PDO::FETCH_ASSOC);
}

// ------------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['yearfilter'])) {
    $selectedYear = $_POST['yearfilter'];
    $_SESSION['fy_viewfeedback'] = $selectedYear;
    $answerTemp = fetchFeedbackAnswerFunc($conn, $selectedYear);
  }
}
$selectedYear = isset($_SESSION['fy_viewfeedback']) ? $_SESSION['fy_viewfeedback'] : date('Y');
$answerTemp = fetchFeedbackAnswerFunc($conn, $selectedYear);

function fetchFeedbackAnswerFunc($conn, $whatYear)
{
    $sql = "SELECT * FROM feedback_answers WHERE YEAR(fa_creation_date) = :year AND fa_id = :fq_id";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':year', $whatYear, PDO::PARAM_INT);
    $stmt->bindParam(':fq_id', $_GET['fq_id_url'], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// ------------------------------------------------------------------------------

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

  <style>
    tr,
    td {
      border-color: white;
    }
  </style>

</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <section id="feedbackContainer" class="p-4 bg-white rounded-xl h-fit">

        <section class="flex justify-between gap-x-4">
          <input onkeyup="searchFeedback();" type="search" id="searchFeedbackButton" class="form-control" placeholder="Search by barangay name">

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
        </section>

        <p class="text-center text-lg mt-4"><?php echo empty($answerTemp) ? 'No Response Yet!' : ''; ?></p>

        <?php foreach ($answerTemp as $row) { ?>

          <section class="feedback-item">

            <p class="font-bold text-lg"><?php echo getAnyFunc($conn, 'barangays', 'id = ' . $row['barangay_id']); ?></p>

            <table class="w-full table table-sm" style="table-layout: fixed;">
              <tr>
                <th class="bg-primary text-white">Question</th>
                <th class="bg-primary text-white">Answer</th>
                <th class="bg-primary text-white">Comment</th>
              </tr>
              <?php foreach ($questionTemp as $t) { ?>
                <tr>
                  <td><?php echo $t['fq1']; ?></td>
                  <td><?php echo $row['fa1']; ?></td>
                  <td rowspan="5"><?php echo $row['comment']; ?></td>
                </tr>
                <tr>
                  <td><?php echo $t['fq2']; ?></td>
                  <td><?php echo $row['fa2']; ?></td>
                </tr>
                <tr>
                  <td><?php echo $t['fq3']; ?></td>
                  <td><?php echo $row['fa3']; ?></td>
                </tr>
                <tr>
                  <td><?php echo $t['fq4']; ?></td>
                  <td><?php echo $row['fa4']; ?></td>
                </tr>
                <tr>
                  <td><?php echo $t['fq5']; ?></td>
                  <td><?php echo $row['fa5']; ?></td>
                </tr>
              <?php } ?>
            </table>

            <hr class="my-3 <?php echo (int)count($answerTemp) === 1 ? 'hidden' : ''; ?>">
          </section>

        <?php } ?>

      </section>
    </div>
  </div>

  <!-- ------------------------------- -->
  <script>
    function searchFeedback() {
      let input = document.getElementById('searchFeedbackButton');
      let filter = input.value.toLowerCase();
      let feedbackItems = document.getElementsByClassName('feedback-item');

      for (let i = 0; i < feedbackItems.length; i++) {
        let item = feedbackItems[i];
        let barangayName = item.getElementsByTagName('p')[0].textContent || item.getElementsByTagName('p')[0].innerText;

        if (barangayName.toLowerCase().indexOf(filter) > -1) {
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