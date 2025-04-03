


<?php
include "connection.php";

$userID = $_SESSION['user_id'];

// to determine if modal must show or not
$stmt = $conn->prepare("SELECT * FROM security WHERE user_id = :userID");
$stmt->bindParam(':userID', $userID);
$stmt->execute();

$isModalHidden = ($stmt->rowCount() > 0) ? "hidden" : "flex";

// ----------------------


// Fetch the user's security questions from the database
$stmt = $conn->prepare("SELECT question1, question2, question3 FROM security WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$securityQuestions = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if security questions exist for the user
if ($securityQuestions) {
  $question1 = $securityQuestions['question1'];
  $question2 = $securityQuestions['question2'];
  $question3 = $securityQuestions['question3'];
} else {
  // Set empty values if no questions are found
  $question1 = '';
  $question2 = '';
  $question3 = '';
}

?>

<!-- filepath: c:\xampp\htdocs\eKPsystem\user_security_question_modal.php -->
<div id="static-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="<?php echo $isModalHidden ?> fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
  <div class="relative w-full max-w-md max-h-full p-4">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-lg">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-300 rounded-t">
        <h3 class="text-lg font-semibold text-gray-900">
          Add Security Questions
        </h3>
      </div>
      <!-- Modal body -->
      <div class="p-4 space-y-4">
        <form id="securityForm" method="post" action="security_handler.php">
          <div class="space-y-4">
            <?php if (!empty($message)) { ?>
              <p class="text-green-500 text-sm"><?php echo $message; ?></p>
            <?php } ?>
            <?php if (!empty($error)) { ?>
              <p class="text-red-500 text-sm"><?php echo $error; ?></p>
            <?php } ?>

            <!-- Security Question 1 -->
            <div class="space-y-2">
              <label for="question1" class="block text-sm font-medium text-gray-700">Security Question 1:</label>
              <select class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" id="question1" name="question1" required>
                <option value="" <?php echo ($question1 == '') ? 'selected' : ''; ?>>Select a Question</option>
                <option value="1" <?php echo ($question1 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                <option value="2" <?php echo ($question1 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                <option value="3" <?php echo ($question1 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                <option value="4" <?php echo ($question1 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
              </select>
              <label for="answer1" class="block text-sm font-medium text-gray-700">Answer:</label>
              <input type="password" class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" id="answer1" name="answer1" required>
            </div>

            <!-- Security Question 2 -->
            <div class="space-y-2">
              <label for="question2" class="block text-sm font-medium text-gray-700">Security Question 2:</label>
              <select class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" id="question2" name="question2" required>
                <option value="" <?php echo ($question2 == '') ? 'selected' : ''; ?>>Select a Question</option>
                <option value="1" <?php echo ($question2 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                <option value="2" <?php echo ($question2 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                <option value="3" <?php echo ($question2 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                <option value="4" <?php echo ($question2 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
              </select>
              <label for="answer2" class="block text-sm font-medium text-gray-700">Answer:</label>
              <input type="password" class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" id="answer2" name="answer2" required>
            </div>

            <!-- Security Question 3 -->
            <div class="space-y-2">
              <label for="question3" class="block text-sm font-medium text-gray-700">Security Question 3:</label>
              <select class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" id="question3" name="question3" required>
                <option value="" <?php echo ($question3 == '') ? 'selected' : ''; ?>>Select a Question</option>
                <option value="1" <?php echo ($question3 == 1) ? 'selected' : ''; ?>>What is the name of your pet?</option>
                <option value="2" <?php echo ($question3 == 2) ? 'selected' : ''; ?>>What is your mother's maiden name?</option>
                <option value="3" <?php echo ($question3 == 3) ? 'selected' : ''; ?>>What city were you born in?</option>
                <option value="4" <?php echo ($question3 == 4) ? 'selected' : ''; ?>>What is your favorite book?</option>
              </select>
              <label for="answer3" class="block text-sm font-medium text-gray-700">Answer:</label>
              <input type="password" class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" id="answer3" name="answer3" required>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <button type="submit" class="w-full px-3 py-2 text-white bg-blue-500 rounded-md hover:bg-blue-400 focus:ring-2 focus:ring-blue-300 focus:outline-none text-sm" name="security_settings">Save Security Settings</button>
              <input type="hidden" name="active_tab" value="security">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>