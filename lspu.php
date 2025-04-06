<?php
session_start();
include 'connection.php';
include 'user_set_timezone.php';

// Check if the user is logged in and has the correct user type
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

function getByMonthFunc($conn, $selectedMonth)
{
    $query = "SELECT * FROM lspu_attendance_sheet WHERE DATE_FORMAT(login_date, '%Y-%m') = :selectedMonth";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_STR); // Bind as string
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getByDayFunc($conn, $selectedDate)
{
    $query = "SELECT * FROM lspu_attendance_sheet WHERE DATE(login_date) = :selectedDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':selectedDate', $selectedDate, PDO::PARAM_STR); // Bind as string
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$data = getByDayFunc($conn, date('Y-m-d'));


// insert header data
function insertHeaderData($conn, $projectTitle, $college, $campus)
{
    $insertQuery = "INSERT INTO lspu_attendance_sheet_header (project_title, college, campus) 
                    VALUES (:projectTitle, :college, :campus)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bindParam(':projectTitle', $projectTitle);
    $stmt->bindParam(':college', $college);
    $stmt->bindParam(':campus', $campus);
    if ($stmt->execute()) {
        header('Location: lspu.php');
    }
}

$headerData = getHeaderData($conn);
// update header data
function updateHeaderData($conn, $projectTitle, $college, $campus)
{
    $updatequery = "UPDATE lspu_attendance_sheet_header SET project_title = :projectTitle, college = :college, campus = :campus";
    $stmt = $conn->prepare($updatequery);
    $stmt->bindParam(':projectTitle', $projectTitle);
    $stmt->bindParam(':college', $college);
    $stmt->bindParam(':campus', $campus);
    if ($stmt->execute()) {
        header('Location: lspu.php');
    }
}

// get header data
function getHeaderData($conn)
{
    $query = "SELECT * FROM lspu_attendance_sheet_header";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['filter_month'])) {
        // Clear previous data and fetch by month
        $data = [];
        $selectedMonth = $_POST['filter_month'];
        $d = getByMonthFunc($conn, $selectedMonth);

        $response = [
            'selectedM' => $selectedMonth,
            'd' => $d,
        ];

        // Return the result as JSON
        echo json_encode($response);
        exit;
    }

    if (isset($_POST['filter_date'])) {
        // Clear previous data and fetch by day
        $data = [];
        $selectedDate = $_POST['filter_date'];
        $d = getByDayFunc($conn, $selectedDate);

        $response = [
            'selectedD' => $selectedDate,
            'd' => $d,
        ];

        // Return the result as JSON
        echo json_encode($response);
        exit;
    }
    if (isset($_POST['project_title']) || isset($_POST['college']) || isset($_POST['campus'])) {
        $projectTitle = $_POST['project_title'];
        $college = $_POST['college'];
        $campus = $_POST['campus'];

        // count row if there already in the table
        $query = "SELECT COUNT(*) AS row_count FROM lspu_attendance_sheet_header";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ((int)$result['row_count'] == 0) {
            insertHeaderData($conn, $projectTitle, $college, $campus);
        } else {
            updateHeaderData($conn, $projectTitle, $college, $campus);
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <link rel="icon" type="image/x-icon" href="img/lspulogo.png">
    <!-- <link rel="stylesheet" href="output.css"> -->

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        function printTable() {
            const tableContent = document.getElementById('attendanceTable').outerHTML;
            const printWindow = window.open('', '_blank');

            printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Print Attendance Table</title>
                    <link rel="stylesheet" type="text/css" href="output.css">
                </head>
                <body>
                    ${tableContent}
                </body>
            </html>
        `);
            printWindow.document.close();

            // Ensure the CSS is fully loaded before calling the print function
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
    <!-- <script>
        function printTable() {
            // Extract the table content
            const tableContent = document.getElementById('attendanceTable').outerHTML;

            // Temporarily replace the body content with the table content for printing
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = tableContent;

            // Trigger the print function
            window.print();

            // Restore the original content after printing
            document.body.innerHTML = originalContent;
        }
    </script> -->



</head>

<body class="h-full w-full bg-gray-300 p-4 text-xs">
    <section class="">
        <!-- Button section -->
        <section class="flex items-center justify-end gap-x-3 mb-4">
            <form title="Filter by date" method="POST" class="flex items-center gap-x-3">
                <input
                    class="px-2 py-2 rounded-md"
                    type="date"
                    id="filter_date"
                    name="filter_date"
                    value="<?php echo $selectedDate; ?>"
                    onchange="submitDate()">
            </form>

            <form method="POST" class="flex items-center gap-x-3">
                <input
                    title="Filter by month"
                    class="px-2 py-2 rounded-md"
                    type="month"
                    id="filter_month"
                    name="filter_month"
                    value="<?php echo $selectedMonth; ?>"
                    onchange="submitMonth()">
            </form>

            <button
                title="Print Attendance Sheet"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                onclick="printTable()">
                Print
            </button>

            <button id="export-btn" title="Export to Excel" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Export to Excel</button>

            <button
                title="Update table header"
                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                type="button"
                onclick="document.getElementById('lspuForm').submit()">
                Submit
            </button>



        </section>

        <section id="attendanceTable" class="p-4 bg-white h-fit min-h-[calc(100vh-8rem)]">
            <!-- LSPU logo and header -->
            <div class="flex flex-col items-center">

                <section class="flex items-center gap-x-10 mr-24 mb-2">
                    <img src="img/lspulogo.png" class="w-14" alt="LSPU logo">
                    <div class="flex flex-col items-center">
                        <p>Republic of the Philippines</p>
                        <b>Laguna State Polytechnic University</b>
                        <p>Province of Laguna</p>
                    </div>
                </section>


                <p>EXTENSION PROGRAM/PROJECT TITLE:</p>

                <form id="lspuForm" method="POST" class="w-full flex flex-col items-center">
                    <input
                        name="project_title"
                        type="text"
                        value="<?php echo htmlspecialchars($headerData['project_title'] ?? ''); ?>"
                        class="text-center w-2/4 border-b border-gray-500 mb-1">

                    <p>Date: <span>
                            <input id="dateHeader" class="text-center border-b border-gray-500 w-60 mb-1" type="text" value="<?php echo date("Y-m-d"); ?>" readonly>
                        </span></p>


                    <div class="flex">
                        <p>COLLEGE: <input name="college" type="text" value="<?php echo htmlspecialchars($headerData['college'] ?? ''); ?>" class="text-center border-b border-gray-500"></p>
                        <p>CAMPUS: <input name="campus" type="text" value="<?php echo htmlspecialchars($headerData['campus'] ?? ''); ?>" class="text-center border-b border-gray-500 w-96"></p>
                    </div>
                </form>

                <br>

                <b>ATTENDANCE SHEET</b>

                <!-- Attendance table -->
                <table id="data-table" class="table-auto w-full border-collapse border border-gray-800 font-sans text-gray-900 text-sm">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-2 py-0 border border-gray-600">No.</th>
                            <th class="px-2 py-0 border border-gray-600">Name</th>
                            <th class="px-2 py-0 border border-gray-600">Address</th>
                            <th class="px-2 py-0 border border-gray-600">Sex</th>
                            <th class="px-2 py-0 border border-gray-600">Contact No.</th>
                            <th class="px-2 py-0 border border-gray-600">Signature</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($data as $index => $row): ?>
                            <tr class="text-center">
                                <td class="px-2 py-0 border border-gray-600"><?php echo $index + 1; ?></td>
                                <td class="px-2 py-0 border border-gray-600"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-2 py-0 border border-gray-600"><?php echo htmlspecialchars($row['address']); ?></td>
                                <td class="px-2 py-0 border border-gray-600"><?php echo htmlspecialchars($row['sex']); ?></td>
                                <td class="px-2 py-0 border border-gray-600"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                <td class="px-2 py-0 border border-gray-600"><?php echo htmlspecialchars($row['signature']); ?></td>
                            </tr>
                        <?php endforeach; ?>


                    </tbody>
                </table>

                <i>
                    <p class="text-xs text-gray-900"><span class="font-semibold">Note:</span><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;All confidential data and information that were disclosed here will be kept in strict confidentiality, and shall not be disclosed or otherwise made available to any person or interested parties, except in accordance with the procedures set by law and may be used only for the purpose of the procedures set out in the ETS Manual of Operations.</p>
                </i>

                <div class="w-full flex items-center justify-between mt-1 font-semibold">
                    <p>LSPU-ETS-SF-011</p>
                    <p>Rev. 2</p>
                    <p id="dateBottomRight">
                        <?php
                        $currentDate = new DateTime(); // Create a DateTime object for the current date
                        echo $currentDate->format("d F Y"); // Format it as "05 April 2025"
                        ?>
                    </p>

                </div>
            </div>
        </section>
    </section>

    <!-- excel code -->
    <script>
        document.getElementById("export-btn").addEventListener("click", function() {

            const workbook = XLSX.utils.book_new();

            const table = document.getElementById("data-table");
            const worksheet = XLSX.utils.table_to_sheet(table);

            const columnWidths = [];
            const tableRows = table.querySelectorAll("tr");

            tableRows.forEach(row => {
                const cells = row.querySelectorAll("th, td");
                cells.forEach((cell, index) => {
                    const cellContent = cell.innerText || "";
                    const cellLength = cellContent.length;

                    columnWidths[index] = Math.max(columnWidths[index] || 10, cellLength + 2);
                });
            });

            worksheet['!cols'] = columnWidths.map(width => ({
                wch: width
            }));

            XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");

            XLSX.writeFile(workbook, "ExportedData.xlsx");
        });
    </script>
    <!-- excel code -->

    <script>
        month

        function submitMonth() {
            const selectedMonth = document.getElementById('filter_month').value;

            $.ajax({
                url: '', // Replace with your server-side script URL
                type: 'POST',
                dataType: 'json',
                data: {
                    filter_month: selectedMonth
                },
                success: function(response) {

                    document.getElementsByName('filter_date')[0].value = null;

                    const date = new Date(response.selectedM);

                    const formattedDate = date.toLocaleDateString("en-US", {
                        month: "long",
                        year: "numeric"
                    });

                    document.getElementById('dateBottomRight').innerText = formattedDate;

                    document.getElementById('dateHeader').value = formattedDate;



                    const tableBody = document.querySelector("tbody");
                    tableBody.innerHTML = '';

                    response.d.forEach((row, index) => {

                        const newRow = `
        
          <tr class="text-center">
              <td class="px-2 py-0 border border-gray-600">${index + 1}</td>
              <td class="px-2 py-0 border border-gray-600">${row.name}</td>
              <td class="px-2 py-0 border border-gray-600">${row.address}</td>
              <td class="px-2 py-0 border border-gray-600">${row.sex}</td>
              <td class="px-2 py-0 border border-gray-600">${row.contact_number}</td>
              <td class="px-2 py-0 border border-gray-600">${row.signature}</td>
          </tr>`;
                        tableBody.innerHTML += newRow;
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
        // day
        function submitDate() {
            const selectedDate = document.getElementById('filter_date').value;

            $.ajax({
                url: '', // Replace with your server-side script URL
                type: 'POST',
                dataType: 'json',
                data: {
                    filter_date: selectedDate
                },
                success: function(response) {

                    document.getElementsByName('filter_month')[0].value = null;

                    document.getElementById('dateHeader').value = response.selectedD;

                    const date = new Date(response.selectedD);
                    const formattedDate = date.toLocaleDateString("en-GB", {
                        day: "2-digit", // Day with leading zero (e.g., "05")
                        month: "long", // Full month name (e.g., "April")
                        year: "numeric" // Four-digit year (e.g., "2025")
                    });
                    document.getElementById('dateBottomRight').innerText = formattedDate;


                    const tableBody = document.querySelector("tbody");
                    tableBody.innerHTML = '';

                    response.d.forEach((row, index) => {

                        const newRow = `
        
          <tr class="text-center">
              <td class="px-2 py-0 border border-gray-600">${index + 1}</td>
              <td class="px-2 py-0 border border-gray-600">${row.name}</td>
              <td class="px-2 py-0 border border-gray-600">${row.address}</td>
              <td class="px-2 py-0 border border-gray-600">${row.sex}</td>
              <td class="px-2 py-0 border border-gray-600">${row.contact_number}</td>
              <td class="px-2 py-0 border border-gray-600">${row.signature}</td>
          </tr>`;
                        tableBody.innerHTML += newRow;
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
    </script>

</body>

</html>