<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Step 1: Get the municipality ID for the logged-in user
    $query = "SELECT municipality_id FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_row && isset($user_row['municipality_id'])) {
        $municipality_id = $user_row['municipality_id'];

        // Step 2: Fetch municipality name
        $query = "SELECT municipality_name FROM municipalities WHERE id = :municipality_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();

        $municipality_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $municipality_name = $municipality_row ? strtoupper($municipality_row['municipality_name']) : 'No municipality found';

        // Step 3: Fetch available years from movrate table for dropdown
        $query = "SELECT DISTINCT year FROM movrate ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get selected year from request or default to the latest year
        $selectedYear = $_GET['year'] ?? $years[0];

        // Step 4: Fetch barangays and their average ratings from movrate for the selected year
        $query = "
            SELECT b.barangay_name AS barangay, AVG(m.total) AS average_total
            FROM barangays b
            LEFT JOIN movrate m ON b.id = m.barangay
            WHERE b.municipality_id = :municipality_id AND m.year = :year
            GROUP BY b.id
            ORDER BY average_total DESC";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
        $stmt->execute();
        $barangay_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 5: Fetch verified users including admin
        $query = "
            SELECT first_name, last_name, user_type, assessor_type
            FROM users
            WHERE municipality_id = :municipality_id AND verified = 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        $verified_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 6: Fetch admin details
        $query = "
            SELECT first_name, last_name, assessor_type
            FROM users
            WHERE municipality_id = :municipality_id AND user_type = 'admin' AND verified = 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $municipality_name = 'No municipality ID found for this user';
        $barangay_ratings = []; // Empty if no data found
        $verified_users = []; // Empty if no data found
        $admin = null; // No admin found
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

function getAdjectivalRating($total)
{
    if ($total >= 100) {
        return "Outstanding";
    } elseif ($total >= 90 && $total <= 99) {
        return "Very Satisfactory";
    } elseif ($total >= 80 && $total <= 89) {
        return "Fair";
    } elseif ($total >= 70 && $total <= 79) {
        return "Poor";
    } else {
        return "Very Poor";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LTIA Form 3</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-content, .print-content * {
                visibility: visible;
            }
            .print-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: auto;
                font-size: 12px;
                margin: 0;
                box-sizing: border-box;
            }
            .headerwiwit {
                height: fit-content;
                display: flex;
                flex-direction: row;
                background-color: #000035;
            }
            .headerwiwit div {
                padding: none;
            }
            .headerwiwit div h1 {
                font-size: medium;
            }
            .headerwiwit div img {
                height: 4rem;
                width: 4rem;
            }
            .print-content .card {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
                box-sizing: border-box;
                box-shadow: none;
            }
            .print-content table {
                width: 100%;
                border-collapse: collapse;
            }
            .print-content th,
            .print-content td {
                padding: 2px; /* Reduce padding */
                font-size: 12px; /* Adjust font-size */
                line-height: 1; /* Adjust line-height */
            }
            .btn,
            .btn-save,
            .text-right {
                display: none;
            }
            .print-content h1 {
                font-size: 14pt;
            }
            .print-content p,
            .print-content b {
                font-size: 12px;
                line-height: 1; /* Adjust line-height */
            }
            .print-content .spacingtabs {
                display: inline-block;
                width: 6em;
                text-align: center;
            }
            .print-content p {
                word-wrap: break-word;
            }
        }
        @media print {
            .underline-input {
                border-bottom: 1px solid #000;
                width: auto;
                min-width: 200px;
                background-color: transparent;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
            }
            .underline-input option {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-[#E8E8E7]">
    <?php include "../admin_sidebar_header.php"; ?>
    <div class="p-4 sm:ml-44 ">
        <div class="rounded-lg mt-16">
            <div class="card">
                <div class="card-body">
                    <div class="menu flex items-center justify-between">
                        <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_admin_overallsummary.php';">
                            <i class="ti ti-building-community mr-2"></i> Back
                        </button>
                        <div class="flex items-center space-x-4">
                            <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='adminform3.php';">
                                <i class="ti ti-license mr-2"></i> Form 3
                            </button>
                            <form method="get" action="">
                                <select name="year" onchange="this.form.submit()" class="form-select">
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>" <?php if ($year == $selectedYear) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($year); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <button onclick="printSecondCard()" class="btn btn-primary">Print</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="print-content">
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="headerwiwit flex items-center justify-center gap-x-5">
                            <div class="dilglogo flex justify-center">
                                <img src="../img/dilg.png" alt="DILG Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
                            </div>
                            <div class="text-center">
                                <h1 class="text-xl font-bold">
                                    CY Lupong Tagapamayapa Incentives Award (LTIA) <br>
                                    LTIA FORM 2 (C/M) - COMPARATIVE EVALUATION FORM
                                </h1>
                            </div>
                            <div class="ltialogo flex justify-center">
                                <img src="images/ltialogo.png" alt="LTIA Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
                            </div>
                        </div>
                        <div class="border border-gray-800 rounded-md p-4 mt-4">
                            <b>A. IDENTIFYING INFORMATION</b><br>
                            <table>
                                <tr>
                                    <td style="padding-left: 5em;">
                                        Lupong Tagapamayapa
                                    </td>
                                    <td style="padding-left: 1em;">
                                        <select name="verified_barangays" class="underline-input" style="width: auto; min-width: 200px;">
                                            <?php foreach ($barangay_ratings as $barangay): ?>
                                                <option value="<?php echo htmlspecialchars($barangay['barangay']); ?>">
                                                    <?php echo htmlspecialchars($barangay['barangay']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5em;">
                                        City/Municipality
                                    </td>
                                    <td style="padding-left: 1em;">
                                        <span id="details-municipality-type" style="display: inline-block; white-space: nowrap; width: auto; text-transform: uppercase; text-decoration: underline;"></span> 
                                        <u>OF <?php echo strtoupper(htmlspecialchars($municipality_name)); ?></u>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5em;">
                                        <p>
                                            Region 
                                        </p>
                                    </td>
                                    <td style="padding-left: 1em;">
                                        <u>IV-A CALABARZON</u>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 5em;">
                                        Category
                                    </td>
                                    <td style="padding-left: 1em;">
                                        <span style="display: inline-block; white-space: nowrap; width: auto; text-transform: uppercase; text-decoration: underline;"></span> 
                                        <span id="municipality-category" style="text-transform: uppercase; text-decoration: underline;"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const cities = ["Calamba", "Biñan", "San Pedro", "Sta Rosa", "Cabuyao", "San Pablo"];
                                const municipalities = ["Bay", "Alaminos", "Calauan", "Los Baños"];

                                function normalizeName(name) {
                                    return name.toLowerCase().replace(/\s+/g, "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                                }

                                function classifyMunicipality(municipalityName) {
                                    const normalized = normalizeName(municipalityName);
                                    const normalizedCities = cities.map(normalizeName);
                                    const normalizedMunicipalities = municipalities.map(normalizeName);

                                    if (normalizedCities.includes(normalized)) {
                                        return "City";
                                    } else if (normalizedMunicipalities.includes(normalized)) {
                                        return "Municipality";
                                    } else {
                                        return "Unknown";
                                    }
                                }

                                const municipalityName = <?php echo json_encode($municipality_name); ?>;
                                const classification = classifyMunicipality(municipalityName);

                                document.getElementById("details-municipality-type").textContent = classification;
                                document.getElementById("municipality-category").textContent = classification.toUpperCase();

                                // Update admin title based on classification
                                const adminTitle = document.getElementById("admin-title");
                                if (classification === "Municipality") {
                                    adminTitle.textContent = "MLGOO:";
                                } else if (classification === "City") {
                                    adminTitle.textContent = "CLGOO:";
                                }
                            });
                        </script>
                        <br>
                        <b>B. COMPARATIVE EVALUATION RESULTS</b><br>
                        <div class="overflow-x-auto mt-4">
                            <table class="table table-bordered w-full border border-gray-800">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">CRITERIA</th>
                                        <th class="px-4 py-2 text-left">ASSIGNED POINT SCORE</th>
                                        <th class="px-4 py-2 text-left">PERFORMANCE RATING<th>EVALUATOR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $num = 1;
                                    $rank = 1;
                                    foreach ($barangay_ratings as $row): ?>
                                        <tr>
                                            <td class="px-4 py-2"><?php echo $num++; ?>. <span class="spacingtabs"><?php echo htmlspecialchars($row['barangay']); ?></span></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars(round($row['average_total'], 2)); ?></td>
                                            <td class="px-4 py-2"><?php echo getAdjectivalRating($row['average_total']); ?></td>
                                            <td class="px-4 py-2"><?php echo $rank++; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <b>C. WE CERTIFY TO THE CORRECTNESS OF THE ABOVE INFORMATION</b><br><br>
                        <div class="certification-section text-center">
                            <?php if (!empty($admin)): ?>
                                <div class="pb-2 mb-4">
                                    <input type="text" name=" " class="underline-input" placeholder="Enter Name" value="<?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?>"><br>
                                    <p><?php echo $admin['user_type'] === 'CLGOO' ? 'CLGOO' : 'MLGOO'; ?> - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee</p>
                                </div>
                            <?php else: ?>
                                <p class="text-red-500 font-semibold">No admin found for this municipality.</p>
                            <?php endif; ?>
                            <?php foreach ($verified_users as $user): ?>
                                <?php if ($user['user_type'] === 'admin' || $user['user_type'] === 'assessor'): ?>
                                    <input type="text" name=" " class="underline-input" placeholder="Enter Name" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><br>
                                    <?php echo $user['user_type'] === 'admin' ? 'Admin' : 'Member'; ?> - <?php echo htmlspecialchars($municipality_name); ?> City Awards Committee <br><br>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <br><br>
                        <b>D. DATE ACCOMPLISHED</b><br>
                        <input type="date" name="date_accomplished" class="underline-input" placeholder="Enter Date" value=""><br>
                        <br><br>
                        <div class="text-right mt-4">
                            <form method="post" action="" enctype="multipart/form-data">
                                <!-- <input type="submit" value="Save" style="background-color: #000035;" class="btn-save">-->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function printSecondCard() {
                    window.print();
                }
            </script>
        </div>
    </div>
</body>
<style>
    .spacingtabs {
        padding-left: 2em;
    }
    .underline-input {
        text-align: center;
        border: none;
        border-bottom: 1px solid #5A5A5A;
        outline: none;
        background-color: transparent;
        width: 25%;
        font-size: 16px;
        padding: 5px 0;
    }
    .underline-input:focus {
        border-bottom-color: #007bff;
    }
</style>
</html>