
<?php

$user_id = $_SESSION['user_id'];

// Function to always get notification data from db whenever the page is displayed
function getAllNotificationData($conn, $user_id) {
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
$stmt->execute();
return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$notifData = getAllNotificationData($conn, $user_id);

// count notification
$count_notif_query = "SELECT 
SUM(CASE WHEN seen = 0 THEN 1 ELSE 0 END) AS count_notif

FROM complaints WHERE UserID = :user_id";

$stmt_notif_count = $conn->prepare($count_notif_query);
$stmt_notif_count->bindParam(':user_id', $user_id);
$stmt_notif_count->execute();
$stmt_notif_count_temp = $stmt_notif_count->fetch(PDO::FETCH_ASSOC);

$notifCount = $stmt_notif_count_temp['count_notif'] ?? 0;


?>