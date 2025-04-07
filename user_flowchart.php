<?php
session_start();
include 'connection.php';

// Check if the user is logged in and has the correct user type
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['user_id'];

function getFlowchartImage($conn, $whatCol, $whatTable, $userID) {
    $query = "SELECT $whatCol FROM $whatTable WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flowchart</title>

    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

    <!-- flowbite component -->
    <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
    <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
    <!-- tabler icon -->
    <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">
    <!-- tabler support -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />
    <!-- tailwind cdn -->
    <link rel="stylesheet" href="output.css">

</head>


<body class="bg-gray-200">

    <?php include "user_sidebar_header.php"; ?>

    <div class="p-0 sm:p-6 sm:ml-44 ">
        <div class="mt-16">
            <div class="bg-white shadow-none rounded-0 sm:shadow-md sm:rounded-lg p-6">
                <section>
                    <h3 class="text-center font-bold text-xl sm:text-4xl">DETERMINING IF CASES FALLS UNDER THE JURISDICTION OF THE KATARUNGANG PAMBARANGAY</h3>
                    <img class="w-full h-full" src="flowchart_image/<?php echo getFlowchartImage($conn, 'flowchart_image', 'user_flowchart', $userID) ?: 'sample1_flowchart.png'; ?>" alt="Flowchart image">

                    <section class="flex items-end justify-between gap-x-4">
                        <p>To create a new flowchart, click on this <a target="_blank" class="underline text-blue-500" href="https://www.figma.com/figjam/">link.</a> to open FigJam in a new tab. <br> Once you've created and saved your flowchart, upload it here</p>

                        <a title="Upload flowchart image" href="user_crop_flowchart.php">
                            <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xl px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 mt-4">
                                <i class="ti ti-upload text-xl"></i> Upload
                            </button>
                        </a>
                    </section>
                </section>

                <br><br><br>

                <!-- ------------------------------------------------------------- -->
                <section>
                    <h3 class="text-center font-bold text-xl sm:text-4xl">KATARUNGANG PAMBARANGAY FLOW CHART</h3>
                    <img class="w-full h-full" src="flowchart_image1/<?php echo getFlowchartImage($conn, 'flowchart_image1', 'user_flowchart1', $userID) ?: 'sample2_flowchart.png'; ?>" alt="Flowchart image">

                    <section class="flex items-end justify-between gap-x-4">
                        <p>To create a new flowchart, click on this <a target="_blank" class="underline text-blue-500" href="https://www.figma.com/figjam/">link.</a> to open FigJam in a new tab. <br> Once you've created and saved your flowchart, upload it here</p>

                        <a title="Upload flowchart image" href="user_crop_flowchart1.php">
                            <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xl px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 mt-4">
                                <i class="ti ti-upload text-xl"></i> Upload
                            </button>
                        </a>
                    </section>
                </section>
                




            </div>
        </div>
    </div>

</body>

</html>