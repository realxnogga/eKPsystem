<?php
session_start();
include 'connection.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['filter_month'])) {
        // Clear previous data and fetch by month
        $data = [];
        $selectedMonth = $_POST['filter_month'];
        $data = getByMonthFunc($conn, $selectedMonth);
    }

    if (isset($_POST['filter_date'])) {
        // Clear previous data and fetch by day
        $data = [];
        $selectedDate = $_POST['filter_date'];
        $data = getByDayFunc($conn, $selectedDate);

    }
    if (isset($_POST['export_excel_raw'])) {
        exportToExcelRaw($data); // Call the raw export function
    }
}
?>

<?php
function exportToExcelRaw($data)
{
    // Send headers to browser for downloading Excel file
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Attendance_Sheet.xls");

    // Start the Excel file content
    echo "<table border='1'>";
    echo "<thead>
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Address</th>
                <th>Sex</th>
                <th>Contact No.</th>
                <th>Signature</th>
            </tr>
          </thead>";

    // Populate rows with data
    echo "<tbody>";
    foreach ($data as $index => $row) {
        echo "<tr>";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sex']) . "</td>";
        echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
        echo "<td>" . htmlspecialchars($row['signature']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";

    exit; // Ensure no other output is sent
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <link rel="stylesheet" href="output.css">

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

</head>

<body class="w-screen h-screen">
    <section class="h-full w-full bg-gray-300 p-4 text-xs">
        <!-- Button section -->
        <section class="flex items-center justify-end gap-x-3 mb-4">
            <form method="POST" class="flex items-center gap-x-3">
                <input
                    class="px-2 py-2 rounded-md"
                    type="date"
                    name="filter_date"
                    value="<?php echo $selectedDate; ?>"
                    onchange="this.form.submit()">
            </form>

            <form method="POST" class="flex items-center gap-x-3">
                <input
                    class="px-2 py-2 rounded-md"
                    type="month"
                    name="filter_month"
                    value="<?php echo $selectedMonth; ?>"
                    onchange="this.form.submit()">
            </form>

            <button
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                onclick="printTable()">
                Print
            </button>

            <form method="POST">
                <button
                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
                    type="submit"
                    name="export_excel_raw">
                    Export to Excel
                </button>
            </form>


        </section>

        <section id="attendanceTable" class="p-4 bg-white h-5/6">
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

                <input type="text" value="Optimization of E-KP" class="text-center w-2/4 border-b border-gray-500">

                <p>Date: <span>
                        <input class="text-center border-b border-gray-500 w-60" type="text" value="<?php echo empty($selectedDate) ? (empty($selectedMonth) ? date('Y-m-d') : $selectedMonth) : $selectedDate; ?>" class="hidden">
                    </span></p>


                <div class="flex">
                    <p>COLLEGE: <input type="text" value="CCS" class="text-center border-b border-gray-500"></p>
                    <p>CAMPUS: <input type="text" value="LSPU Los Banos Campus" class="text-center border-b border-gray-500 w-96"></p>
                </div>

                <br>

                <b>ATTENDANCE SHEET</b>

                <!-- Attendance table -->
                <table class="table-auto w-full border-collapse border border-gray-800 font-sans text-gray-900 text-sm">
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
                    <p>
                        <?php
                        $dateTime = null;

                        if (!empty($selectedDate)) {
                            $dateTime = new DateTime($selectedDate);
                            $formattedDate = $dateTime->format('d F Y');
                        } elseif (!empty($selectedMonth)) {
                            $dateTime = new DateTime($selectedMonth);
                            $formattedDate = $dateTime->format('F Y');
                        } else {
                            $currentDate = date('Y-m-d');
                            $dateTime = new DateTime($currentDate);
                            $formattedDate = $dateTime->format('d F Y');
                        }
                        echo $formattedDate;
                        ?>
                    </p>

                </div>
            </div>
        </section>
    </section>
</body>

</html>