<?php
session_start();
include 'connection.php';

// Check if the user is logged in and has the correct user type
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Query to fetch user data
$query = "SELECT u.username, u.barangay_id, u.contact_number 
          FROM users u 
          JOIN user_logs l ON u.id = l.user_id WHERE MONTH(l.timestamp) = MONTH(CURDATE())";

$stmt = $conn->prepare($query);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$_SESSION['test'] = $data;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="w-screen h-screen">

    <section class="h-full w-full bg-gray-300 p-4 text-xs">
        <!-- Button section -->
        <section class="flex items-center justify-end gap-x-3 mb-4">
            <button class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-md">Print</button>
            <input class="px-4 py-2 rounded-md" type="date">
        </section>

        <section class="p-4 bg-white h-4/5">
            <!-- LSPU logo and header -->
            <div class="flex flex-col items-center">
                <p>Republic of the Philippines</p>
                <b>Laguna State Polytechnic University</b>
                <p>Province of Laguna</p>
                <p>EXTENSION PROGRAM/PROJECT TITLE:</p>

                <input type="text" class="text-center w-2/4 border-b border-gray-500">

                <p>Date: <span class="border-b border-gray-500"><?php echo date("Y-m-d"); ?></span></p>

                <div class="flex">
                    <p>COLLEGE: <input type="text" class="text-center border-b border-gray-500"></p>
                    <p>CAMPUS: <input type="text" class="text-center border-b border-gray-500"></p>
                </div>

                <br>

                <b>ATTENDANCE SHEET</b>

                <!-- Attendance table -->
                <table class="table-auto w-full border-collapse border border-gray-300 font-sans text-gray-700 text-sm">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-2 py-0 border border-gray-300">No.</th>
                            <th class="px-2 py-0 border border-gray-300">Name</th>
                            <th class="px-2 py-0 border border-gray-300">Address</th>
                            <th class="px-2 py-0 border border-gray-300">Sex</th>
                            <th class="px-2 py-0 border border-gray-300">Contact No.</th>
                            <th class="px-2 py-0 border border-gray-300">Signature</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($data as $index => $row): ?>
                            <tr class="text-center">
                                <td class="px-2 py-0 border border-gray-300"><?php echo $index + 1; ?></td>
                                <td class="px-2 py-0 border border-gray-300"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="px-2 py-0 border border-gray-300"><?php echo htmlspecialchars($row['barangay_id']); ?></td>
                                <td class="px-2 py-0 border border-gray-300"></td>
                                <td class="px-2 py-0 border border-gray-300"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                <td class="px-2 py-0 border border-gray-300"></td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

                <p class="text-xs">Note: <i>All confidential data and information that were disclosed here will be kept in strict confidentiality, and shall not be disclosed or otherwise made available to any person or interested parties, except in accordance with the procedures set by law and may be used only for the purpose of the procedures set out in the ETS Manual of Operations.</i></p>
            </div>
        </section>
    </section>

</body>

</html>