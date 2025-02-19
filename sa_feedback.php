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
    $fq1 = $_POST['fq1'];
    $fq2 = $_POST['fq2'];
    $fq3 = $_POST['fq3'];
    $fq4 = $_POST['fq4'];
    $fq5 = $_POST['fq5'];

    $stmt = $conn->prepare("INSERT INTO feedback_questions (fq1, fq2, fq3, fq4, fq5) VALUES (:fq1, :fq2, :fq3, :fq4, :fq5)");
    $stmt->bindParam(':fq1', $fq1, PDO::PARAM_STR);
    $stmt->bindParam(':fq2', $fq2, PDO::PARAM_STR);
    $stmt->bindParam(':fq3', $fq3, PDO::PARAM_STR);
    $stmt->bindParam(':fq4', $fq4, PDO::PARAM_STR);
    $stmt->bindParam(':fq5', $fq5, PDO::PARAM_STR);

    if ($stmt->execute()) {
      header("Location: sa_feedback.php");
      exit();
    }
  }

  if (isset($_POST['editfq_id'])) {
    $fq_id = $_POST['editfq_id'];

    if (isset($_POST['submitEditFeedbackQuestion' . $fq_id])) {
      $fq1edit = $_POST['editfq1'];
      $fq2edit = $_POST['editfq2'];
      $fq3edit = $_POST['editfq3'];
      $fq4edit = $_POST['editfq4'];
      $fq5edit = $_POST['editfq5'];

      $stmt = $conn->prepare("UPDATE feedback_questions SET fq1 = :fq1, fq2 = :fq2, fq3 = :fq3, fq4 = :fq4, fq5 = :fq5 WHERE fq_id = :fq_id");
      $stmt->bindParam(':fq1', $fq1edit, PDO::PARAM_STR);
      $stmt->bindParam(':fq2', $fq2edit, PDO::PARAM_STR);
      $stmt->bindParam(':fq3', $fq3edit, PDO::PARAM_STR);
      $stmt->bindParam(':fq4', $fq4edit, PDO::PARAM_STR);
      $stmt->bindParam(':fq5', $fq5edit, PDO::PARAM_STR);
      $stmt->bindParam(':fq_id', $fq_id, PDO::PARAM_INT);

      if ($stmt->execute()) {
        header("Location: sa_feedback.php");
        exit();
      }
    }
  }
}

// Fetch feedback questions
$questionTemp = $conn->query("SELECT * FROM feedback_questions")->fetchAll(PDO::FETCH_ASSOC);
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

  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.23/dist/full.min.css" rel="stylesheet" type="text/css" />
</head>

<body class="bg-[#E8E8E7]">

  <?php include "sa_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <section class="p-4 bg-white rounded-xl h-fit">
        <section class="flex justify-end">
          <button onclick="my_modal_3.showModal()" type="button" class="btn btn-primary bg-blue-500">
            <span>
              <i class="ti ti-plus text-lg show-icon"></i>
              <p style="white-space: nowrap;" class="hide-icon hidden">Add complaint</p>
            </span>
          </button>
        </section>

        <section>
          <section>
            <?php foreach ($questionTemp as $row) { ?>

              <div class="w-50 flex justify-between items-center">
              <h3 class='text-lg font-bold'>Feedback Questions (<?php echo $row["fq_id"]; ?>)</h3>
              <p>creation date: <?php echo date('M Y', strtotime($row['fq_creation_date']))?></p>
              </div>
             
              <form method="POST" action="" class="flex flex-col gap-y-1 w-100 border-2 border-gray-200 rounded-lg p-2">
                <input value="<?php echo $row['fq1']; ?>" required name="editfq1" type="text" placeholder="Edit question 1" class="text-sm w-50 py-2 border rounded-md ">
                <input value="<?php echo $row['fq2']; ?>" required name="editfq2" type="text" placeholder="Edit question 2" class="text-sm w-50 py-2 border rounded-md ">
                <input value="<?php echo $row['fq3']; ?>" required name="editfq3" type="text" placeholder="Edit question 3" class="text-sm w-50 py-2 border rounded-md ">
                <input value="<?php echo $row['fq4']; ?>" required name="editfq4" type="text" placeholder="Edit question 4" class="text-sm w-50 py-2 border rounded-md ">
                <input value="<?php echo $row['fq5']; ?>" required name="editfq5" type="text" placeholder="Edit question 5" class="text-sm w-50 py-2 border rounded-md ">
                
                <input hidden value="<?php echo $row['fq_id']; ?>" required name="editfq_id" type="number">

                <button name="submitEditFeedbackQuestion<?php echo $row['fq_id']; ?>" type="submit" class="py-2 px-3 text-white rounded-md bg-blue-500 w-fit">
                  Edit
                </button>
              </form>
            <?php } ?>
          </section>
        </section>

        <dialog id="my_modal_3" class="modal">
          <div class="modal-box">
            <form method="dialog">
              <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="text-lg font-bold">Create Feedback questions for barangays.</h3>

            <form method="POST" action="" class="space-y-2 max-w-lg mx-auto my-3">
              <input required name="fq1" type="text" placeholder="Create question 1" class="text-sm w-full py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <input required name="fq2" type="text" placeholder="Create question 2" class="text-sm w-full py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <input required name="fq3" type="text" placeholder="Create question 3" class="text-sm w-full py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <input required name="fq4" type="text" placeholder="Create question 4" class="text-sm w-full py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <input required name="fq5" type="text" placeholder="Create question 5" class="text-sm w-full py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">

              <button name="submitFeedbackQuestion" type="submit" class="w-full py-3 mt-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Submit
              </button>
            </form>
          </div>
        </dialog>
      </section>
    </div>
  </div>
</body>
</html>