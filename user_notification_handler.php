
<?php

$user_id = $_SESSION['user_id'];

function getAllNotificationData($conn, $user_id)
{
    $query = "SELECT * FROM complaints 
          WHERE UserID = :user_id 
          AND CStatus = 'Settled' 
          AND (          /* true if current date is greater than mdate + 15 days */       
              (CMethod = 'Mediation' AND NOW() > DATE_ADD(Mdate, INTERVAL 15 DAY))
              OR 
              (CMethod = 'Conciliation' AND NOW() > DATE_ADD(Mdate, INTERVAL 30 DAY))
          )
          AND isArchived = 0
          AND YEAR(Mdate) = YEAR(NOW())
          ORDER BY Mdate DESC";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$notifData = getAllNotificationData($conn, $user_id);


// for count notification
$count_notif_query = "SELECT 
SUM(CASE WHEN seen = 0 
  AND CStatus = 'Settled' 
  AND (         
        (CMethod = 'Mediation' AND NOW() > DATE_ADD(Mdate, INTERVAL 15 DAY))
        OR 
        (CMethod = 'Conciliation' AND NOW() > DATE_ADD(Mdate, INTERVAL 30 DAY))
    )
  AND isArchived = 0
  AND seen = 0 /*  add seen = 0 to count only not seen*/
  AND YEAR(Mdate) = YEAR(NOW())

THEN 1 ELSE 0 END) AS count_notif

FROM complaints WHERE UserID = :user_id";

$stmt_notif_count = $conn->prepare($count_notif_query);
$stmt_notif_count->bindParam(':user_id', $user_id);
$stmt_notif_count->execute();
$stmt_notif_count_temp = $stmt_notif_count->fetch(PDO::FETCH_ASSOC);

$notifCount = $stmt_notif_count_temp['count_notif'] ?? 0;


?>