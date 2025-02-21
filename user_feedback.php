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
}

// Fetch feedback questions
$questionTemp = $conn->query("SELECT * FROM feedback_questions ORDER BY fq_creation_date DESC")->fetchAll(PDO::FETCH_ASSOC);


function isAlreadyAnsweredFunc($conn, $brgy_id) {
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
  <link rel="stylesheet" href="assets/css/styles.min.css" />

  <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="bg-[#E8E8E7]">

  <?php include "user_sidebar_header.php"; ?>

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <section class="p-4 bg-white rounded-xl h-fit">


        <?php foreach ($questionTemp as $row) { ?>
          
          <section class="<?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'relative text-gray-300 cursor-not-allowed' : '' ?>">

          <?php

          if (in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id))) { ?>

            <h3 class="absolute inset-0 flex items-center justify-center text-[2rem] font-bold text-gray-500">You already answered to this!</h3>
              
         <?php } ?>
          
          
          <div class="w-100 flex justify-between items-center">
                <p class='text-lg font-bold'><?php echo $row["feedback_title"]; ?></p>
                <p>Created on <?php echo date('M d Y', strtotime($row['fq_creation_date'])) ?></p>

              </div>


              <form method="POST" action="" class="<?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?> mb-3 flex flex-col gap-y-3 w-100 border-2 !border-gray-400 rounded-lg p-2">

                <div class="flex justify-between">

                  <table style="table-layout: fixed;" class="w-full">

                    <tr class="border-b">
                      <th class="py-2 text-start text-xs w-1/3">Questions</th>
                      <th class="py-2 text-center text-xs">(5)Very Satisfied</th>
                      <th class="py-2 text-center text-xs">(4)Satisfied</th>
                      <th class="py-2 text-center text-xs">(3)Neutral</th>
                      <th class="py-2 text-center text-xs">(2)Dissatisfied</th>
                      <th class="py-2 text-center text-xs">(1)Very Dissatisfied</th>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq1']; ?></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="5" name="fa1" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="4" name="fa1" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="3" name="fa1" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="2" name="fa1" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="1" name="fa1" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq2']; ?></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="5" name="fa2" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="4" name="fa2" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="3" name="fa2" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="2" name="fa2" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="1" name="fa2" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq3']; ?></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="5" name="fa3" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="4" name="fa3" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="3" name="fa3" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="2" name="fa3" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="1" name="fa3" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq4']; ?></td>
                    
                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="5" name="fa4" required></td>
                     
                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="4" name="fa4" required></td>
                     
                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="3" name="fa4" required></td>
                     
                      <td class="py-1 text-center text-sm"><input class="
                     <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="2" name="fa4" required></td>
                      
                      <td class="py-1 text-center text-sm"><input class="
                       <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="1" name="fa4" required></td>
                    </tr>

                    <tr>
                      <td class="py-1 text-start text-sm"><?php echo $row['fq5']; ?></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="5" name="fa5" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="4" name="fa5" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="3" name="fa5" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="2" name="fa5" required></td>

                      <td class="py-1 text-center text-sm"><input class="
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300' : '' ?>" 
                      <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> type="radio" value="1" name="fa5" required></td>
                    </tr>

                  </table>
                </div>

                <textarea class="
                <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'border border-gray-300 placeholder-gray-300 cursor-not-allowed' : '' ?>" 
                <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?> id="comment" name="comment" rows="2" cols="50" placeholder="Write a comment/suggestion"></textarea>

                <input hidden value="<?php echo $row['fq_id']; ?>" required name="fa_id" type="number">

                <button
                  name="submitFeedbackAnswer<?php echo $row['fq_id']; ?>"
                  type="submit"
                  <?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'disabled' : '' ?>
                  class="<?php echo in_array($row['fq_id'], isAlreadyAnsweredFunc($conn, $barangay_id)) ? 'bg-gray-300 cursor-not-allowed' : '' ?> py-2 px-3 text-white rounded-md bg-blue-500 w-fit">
                  submit

                </button>
              </form>
          </section>
        <?php } ?>

      </section>
    </div>
  </div>
</body>

</html>