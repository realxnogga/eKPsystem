
<?php

include 'connection.php';

// --- get some data from complaints table based on specific condition then insert them into notification table only if that data is new
$user_id = $_SESSION['user_id'] ?? '';

$query = "SELECT * FROM complaints 
          WHERE UserID = :user_id 
          AND CStatus = 'Settled' 
          AND (
              (CMethod = 'Mediation' )
              OR (CMethod = 'Conciliation' )
          )
          AND isArchived = 0
          AND YEAR(Mdate) = YEAR(NOW())";


$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);



// Step 1: Execute the initial statement to get data for comparison
if ($stmt->execute()) {
    // Fetch results into an associative array
    $stmt_temp = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 2: Fetch existing notifications to compare against
    $existingNotifications = $conn->query("SELECT userID, case_number FROM notification")->fetchAll(PDO::FETCH_ASSOC);

    // Convert existing notifications into a key for easy comparison
    $existingKeys = [];
    foreach ($existingNotifications as $existing) {
        $existingKeys[] = $existing['userID'] . '-' . $existing['case_number'];
    }

    $sql = "INSERT INTO notification (userID, case_number, message) VALUES ";
    $valueStrings = [];
    $params = [];

    // Step 3: Check for new values and prepare the insert statement
    foreach ($stmt_temp as $row) {
        $userID = $row['UserID'];  // Access using associative array keys
        $cNum = $row['CNum'];

        if ($row['CMethod'] === 'Mediation') {
            $note = "The case #{$cNum} has lapse 15 days for mediation";
        } else if ($row['CMethod'] === 'Conciliation') {
            $note = "The case #{$cNum} has lapse 30 days for conciliation";
        }


        // Create a unique key to check for existence
        $newKey = $userID . '-' . $cNum;

        // If the new key does not exist in existing keys, prepare to insert
        if (!in_array($newKey, $existingKeys)) {
            // Add placeholders for each row of data
            $valueStrings[] = "(?, ?, ?)";
            $params[] = $userID;      // Add values in the same order
            $params[] = $cNum;
            $params[] = $note;
        }
    }

    // Proceed with the insertion only if there are new values
    if (!empty($valueStrings)) {
        $sql .= implode(",", $valueStrings);

        // Prepare and execute the statement with parameterized queries
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    }

}
// -------------------------------------------

// count notification
$count_notif_query = "SELECT 
SUM(CASE WHEN seen = 0 THEN 1 ELSE 0 END) AS count_notif

FROM notification 
WHERE UserID = :user_id";

$stmt_notif_count = $conn->prepare($count_notif_query);
$stmt_notif_count->bindParam(':user_id', $user_id);
$stmt_notif_count->execute();
$stmt_notif_count_temp = $stmt_notif_count->fetch(PDO::FETCH_ASSOC);

$notifCount = $stmt_notif_count_temp['count_notif'] ?? 0;


?>