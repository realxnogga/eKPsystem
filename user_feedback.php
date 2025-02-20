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
    }
  }
}


// Fetch feedback questions
$questionTemp = $conn->query("SELECT * FROM feedback_questions ORDER BY fq_creation_date DESC")->fetchAll(PDO::FETCH_ASSOC);
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

</head>


<body class="bg-[#E8E8E7]">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <section class="p-4 bg-white rounded-xl h-fit">

        <section>
          <section>
            <?php foreach ($questionTemp as $row) { ?>

              <div class="w-100 flex justify-between items-center">
                <h3 class='text-lg font-bold'><?php echo $row["feedback_title"]; ?></h3>
                <p>Created on <?php echo date('M d Y', strtotime($row['fq_creation_date'])) ?></p>

              </div>


              <form method="POST" action="" class="mb-3 flex flex-col gap-y-3 w-100 border-2 !border-gray-300 rounded-lg p-2">

                <div class="flex justify-between">

                  <table style="table-layout: fixed;" class="w-full">

                    <tr class="border-b">
                      <th class="py-2 text-start text-xs w-50">Questions</th>
                      <th class="py-2 text-center text-xs">Very Satisfied</th>
                      <th class="py-2 text-center text-xs">Satisfied</th>
                      <th class="py-2 text-center text-xs">Neutral</th>
                      <th class="py-2 text-center text-xs">Dissatisfied</th>
                      <th class="py-2 text-center text-xs">Very Dissatisfied</th>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq1']; ?></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="5" name="fa1" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="4" name="fa1" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="3" name="fa1" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="2" name="fa1" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="1" name="fa1" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq2']; ?></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="5" name="fa2" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="4" name="fa2" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="3" name="fa2" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="2" name="fa2" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="1" name="fa2" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq3']; ?></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="5" name="fa3" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="4" name="fa3" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="3" name="fa3" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="2" name="fa3" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="1" name="fa3" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq4']; ?></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="5" name="fa4" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="4" name="fa4" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="3" name="fa4" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="2" name="fa4" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="1" name="fa4" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq5']; ?></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="5" name="fa5" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="4" name="fa5" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="3" name="fa5" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="2" name="fa5" required></td>
                      <td class="py-1 text-center text-sm"><input type="radio" value="1" name="fa5" required></td>
                    </tr>

                  </table>
                </div>

                <textarea id="comment" name="comment" rows="2" cols="50" placeholder="Write a comment/suggestion"></textarea>

                <input hidden value="<?php echo $row['fq_id']; ?>" required name="fa_id" type="number">

                <button name="submitFeedbackAnswer<?php echo $row['fq_id']; ?>" type="submit" class="py-2 px-3 text-white rounded-md bg-blue-500 w-fit">
                  submit
                </button>
              </form>
            <?php } ?>
          </section>
        </section>
      </section>
    </div>
  </div>
</body>

</html>