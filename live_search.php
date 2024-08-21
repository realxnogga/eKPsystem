<?php
session_start();
include 'connection.php';
include 'functions.php'; // Include functions if needed


// Define the getOrdinalSuffix function
function getOrdinalSuffix($number) {
    if ($number % 100 >= 11 && $number % 100 <= 13) {
        return 'th';
    }
    switch ($number % 10) {
        case 1: return 'st';
        case 2: return 'nd';
        case 3: return 'rd';
        default: return 'th';
    }
}



// Retrieve the search input from the AJAX request
$searchInput = isset($_GET['search']) ? $_GET['search'] : '';

// Assume $conn is the database connection, make sure it's defined in your main file
if (isset($conn)) {
    // Retrieve user-specific complaints
    $userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; // Initialize $userID

    // Check if the search input is empty
    if (empty($searchInput)) {
        // If empty, fetch all complaints
        $query = "SELECT * FROM complaints WHERE UserID = '$userID' AND IsArchived = 0 ORDER BY MDate DESC";
    } else {
        // If not empty, fetch complaints based on search input
        $query = "SELECT * FROM complaints WHERE UserID = '$userID' AND IsArchived = 0 AND (CNum LIKE '%$searchInput%' OR ForTitle LIKE '%$searchInput%' OR CNames LIKE '%$searchInput%' OR RspndtNames LIKE '%$searchInput%') ORDER BY MDate DESC";
    }




    
    $result = $conn->query($query);

    // Render the filtered data within HTML table tags
    echo '<table class="table">';
    echo '<thead class="thead">';
    echo '<tr>';
    echo '<th class="case-number">No.</th>';
    echo '<th class="title-column">Title</th>';
    echo '<th class="complainants-column">Complainants</th>';
    echo '<th class="respondents-column">Respondents</th>';
    echo '<th class="date-column">Date</th>';
    echo '<th class="status-column">Status</th>';
    echo '<th class="hearing-column">Hearing</th>';
    echo '<th class="actions-column">Actions</th>';
    // Add other headers as needed
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        // Calculate the elapsed days since the complaint was added
        $dateAdded = strtotime($row['Mdate']);
        $currentDate = strtotime(date('Y-m-d'));
        $elapsedDays = ($currentDate - $dateAdded) / (60 * 60 * 24);

        // Check if the complaint is settled
        $isSettled = $row['CMethod'] === 'Settled';

        // Determine the background color based on elapsed days and settlement status
        if ($elapsedDays <= 5 && !$isSettled) {
            $backgroundColor = '#dcfadf'; // Light green
        } elseif ($elapsedDays > 5 && $elapsedDays <= 7 && !$isSettled) {
            $backgroundColor = '#FFE181'; // Light yellow
        } elseif ($elapsedDays > 7 && $elapsedDays <= 15 && !$isSettled) {
            $backgroundColor = '#F88D96'; // Light red
        } else {
            $backgroundColor = ''; // No background color
        }

        echo '<tr style="background-color: ' . $backgroundColor . '">';
        echo '<td class="case-number">' . str_pad($row['CNum'], 11, '0', STR_PAD_LEFT) . '</td>';
        echo '<td class="title-column" style="white-space: pre-line;">' . $row['ForTitle'] . '</td>';
        echo '<td class="complainants-column" style="white-space: nowrap;">' . $row['CNames'] . '</td>';
        echo '<td class="respondents-column" style="white-space: nowrap;">' . $row['RspndtNames'] . '</td>';
        echo '<td class="date-column">' . date('Y-m-d', strtotime($row['Mdate'])) . '</td>';
        echo '<td class="status-column" style="white-space: nowrap;">' . $row['CMethod'] . '</td>';

        // Fetch hearing data based on your requirements
        $complaintId = $row['id'];
        $caseProgressQuery = "SELECT current_hearing FROM case_progress WHERE complaint_id = $complaintId";
        $caseProgressResult = $conn->query($caseProgressQuery);
        $caseProgressRow = $caseProgressResult->fetch(PDO::FETCH_ASSOC);

        echo '<td class="hearing-column" style="white-space: nowrap;">';
        if ($caseProgressRow) {
            $currentHearing = $caseProgressRow['current_hearing'];
            if ($currentHearing === '0') {
                echo 'Not Set';
            } else {
                $ordinalHearing = str_replace('th', getOrdinalSuffix((int)$currentHearing), $currentHearing);
                echo $ordinalHearing . ' Hearing';
            }
        } else {
            echo 'Not Set';
        }
        echo '</td>';

        echo '<td class="actions-column">';
        echo '<a href="user_edit_complaint.php?id=' . $row['id'] . '" class="btn btn-sm btn-secondary" title="Edit" data-placement="top">Edit</a>';
        echo '<a href="archive_complaint.php?id=' . $row['id'] . '" class="btn btn-sm btn-danger" title="Archive" data-placement="top">Archive</a>';
        echo '<a href="user_manage_case.php?id=' . $row['id'] . '" class="btn btn-sm btn-warning" title="Manage" data-placement="top">Manage</a>';
        echo '<a href="user_uploadfile_complaint.php?id=' . $row['id'] . '" class="btn btn-sm btn-primary" title="Upload" data-placement="top">Upload</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo 'Database connection not available.';
}
?>
