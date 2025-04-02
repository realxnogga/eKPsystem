<?php
session_start();

// Store message in a temporary variable if it exists
$temp_message = $_SESSION['message'] ?? null;
// Clear the session message immediately
unset($_SESSION['message']);

include '../connection.php';

if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'user') || !isset($_SESSION['barangay_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables to store form data
$formsheet = [];

// Fetch existing record if available
if (isset($_SESSION['user_id'])) {
    $fetch_query = "SELECT * FROM movcommentsheet WHERE user_id = :user_id ORDER BY date DESC LIMIT 1";
    $fetch_stmt = $conn->prepare($fetch_query);
    $fetch_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    
    if ($fetch_stmt->execute()) {
        $formsheet = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Fetch all rev_numbers for this user
$rev_numbers_query = "SELECT DISTINCT rev_number FROM movcommentsheet WHERE user_id = :user_id ORDER BY date DESC";
$rev_stmt = $conn->prepare($rev_numbers_query);
$rev_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$rev_stmt->execute();
$rev_numbers = $rev_stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle rev_number selection
$selectedRev = $_POST['rev_number_select'] ?? null;
if ($selectedRev) {
    // Fetch the specific record
    $fetch_query = "SELECT * FROM movcommentsheet WHERE user_id = :user_id AND rev_number = :rev_number ORDER BY date DESC LIMIT 1";
    $fetch_stmt = $conn->prepare($fetch_query);
    $fetch_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $fetch_stmt->bindParam(':rev_number', $selectedRev);
    $fetch_stmt->execute();
    $formsheet = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $rev_number = $_POST['rev_number'] ?? null;
    $effect_date = $_POST['effect_date'] ?? null;
    $name_policy = $_POST['name_policy'] ?? null;
    $prepare_by = $_POST['prepare_by'] ?? null;
    $prov1 = $_POST['prov1'] ?? null;
    $prov2 = $_POST['prov2'] ?? null;
    $prov3 = $_POST['prov3'] ?? null;
    $prov4 = $_POST['prov4'] ?? null;
    $prov5 = $_POST['prov5'] ?? null;
    $feed1 = $_POST['feed1'] ?? null;
    $feed2 = $_POST['feed2'] ?? null;
    $feed3 = $_POST['feed3'] ?? null;
    $feed4 = $_POST['feed4'] ?? null;
    $feed5 = $_POST['feed5'] ?? null;
    $basis1 = $_POST['basis1'] ?? null;
    $basis2 = $_POST['basis2'] ?? null;
    $basis3 = $_POST['basis3'] ?? null;
    $basis4 = $_POST['basis4'] ?? null;
    $basis5 = $_POST['basis5'] ?? null;

    // Prevent empty form submission
    if (!empty($rev_number) && !empty($effect_date) && !empty($name_policy) && !empty($prepare_by)) {
        try {
            // Insert new record
            $insert_query = "INSERT INTO movcommentsheet 
                (user_id, rev_number, effect_date, name_policy, prepare_by, 
                 prov1, prov2, prov3, prov4, prov5, 
                 feed1, feed2, feed3, feed4, feed5, 
                 basis1, basis2, basis3, basis4, basis5) 
                VALUES 
                (:user_id, :rev_number, :effect_date, :name_policy, :prepare_by,
                 :prov1, :prov2, :prov3, :prov4, :prov5,
                 :feed1, :feed2, :feed3, :feed4, :feed5,
                 :basis1, :basis2, :basis3, :basis4, :basis5)";
            
            $stmt = $conn->prepare($insert_query);
            
            // Bind all parameters
            $params = [
                ':user_id' => $user_id,
                ':rev_number' => $rev_number,
                ':effect_date' => $effect_date,
                ':name_policy' => $name_policy,
                ':prepare_by' => $prepare_by,
                ':prov1' => $prov1,
                ':prov2' => $prov2,
                ':prov3' => $prov3,
                ':prov4' => $prov4,
                ':prov5' => $prov5,
                ':feed1' => $feed1,
                ':feed2' => $feed2,
                ':feed3' => $feed3,
                ':feed4' => $feed4,
                ':feed5' => $feed5,
                ':basis1' => $basis1,
                ':basis2' => $basis2,
                ':basis3' => $basis3,
                ':basis4' => $basis4,
                ':basis5' => $basis5
            ];

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                $_SESSION['message'] = "Policy Formulation Comment Sheet saved successfully.";
                
                // Fetch the latest record
                $fetch_query = "SELECT * FROM movcommentsheet WHERE user_id = :user_id ORDER BY date DESC LIMIT 1";
                $fetch_stmt = $conn->prepare($fetch_query);
                $fetch_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $fetch_stmt->execute();
                $formsheet = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $_SESSION['message'] = "Error saving record.";
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['message'] = "Please fill in all required fields.";
    }

    // Redirect with a unique URL parameter to prevent resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Near the top of your file, update the form handling logic
// Handle rev_number selection
if (isset($_POST['rev_number_select']) && !empty($_POST['rev_number_select'])) {
    $selectedRev = $_POST['rev_number_select'];
    // Fetch the specific record
    $fetch_query = "SELECT * FROM movcommentsheet WHERE user_id = :user_id AND rev_number = :rev_number ORDER BY date DESC LIMIT 1";
    $fetch_stmt = $conn->prepare($fetch_query);
    $fetch_stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $fetch_stmt->bindParam(':rev_number', $selectedRev);
    $fetch_stmt->execute();
    $formsheet = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // If no revision is selected or it's a new form, initialize empty formsheet
    $formsheet = [];
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Policy Formulation Comment Sheet</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.min.css" />

    <style>
        .spacingtabs {
            padding-left: 2em;
        }

        @media print {
            @page {
                size: auto;
            }
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
                align-items: center;
                justify-content: space-between;
                gap: 5px;
                page-break-before: always;
            }
            .headerwiwit div {
                margin-top: -2rem;

                padding: none;
            }
            .headerwiwit div h1 {
                font-size: medium;
            }
            .headerwiwit div img {
                height: 5rem;
                width: 5rem;
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
                border: 1px solid black;
                padding: 0;
                margin: 0;
            }
            .print-content th,
            .print-content td {
                padding: 0;
                margin: 0;
                font-size: 12px;
                line-height: 1;
                border: 1px solid black;
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
                line-height: 1;
            }
            .print-content .spacingtabs {
                display: inline-block;
                width: 6em;
                text-align: center;
            }
            .print-content p {
                word-wrap: break-word;
            }
            .print-content strong {
                color: black;
            }
            .underline-inputPFB {
                border-bottom: none !important;
            }
        }

        @media (max-width: 768px) {
            .headerwiwit {
                flex-direction: column;
                align-items: left;
                margin-top: -1rem;
            }
            .headerwiwit .dilglogo {
                margin-bottom: 10px;
            }
            .headerwiwit .text-left {
                text-align: left;
            }
            .headerwiwit strong {
                font-size: 15px;
            }
            .underline-inputPFB {
                border-bottom: none !important;
            }
        }

        .underline-inputPFB,
        .underlinetable-input,
        .underline-input {
            text-align: center;
            border: none;
            outline: none;
            background-color: transparent;
            font-size: 10px;
            padding: 5px 0;
        }

        .underline-inputPFB,
        .underlinetable-input {
            width: 100%;
            border-bottom: 1px solid #5A5A5A;
        }

        .underline-input {
            width: 25%;
            border-bottom: 1px solid #5A5A5A;
        }

        .underline-input:focus {
            border-bottom-color: #007bff;
        }

        .custom-hr {
            border-top: 3px dashed black;
        }

        .docscode table {
            font-size: 10px;
            padding: 0;
            margin: 0;
        }

        .docscode table h1,
        .docscode table input {
            font-size: 10px;
        }
        .auto-adjust {
            margin: 0 !important;
            padding: 0 !important;
        }

        @media print {
            @page {
                size: auto;
            }
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
                align-items: center;
                justify-content: space-between;
                gap: 5px;
                page-break-before: always;
            }
            .headerwiwit div {
                padding: none;
            }
            .headerwiwit div strong {
                font-size: 1rem;
            }
            .headerwiwit div h1 {
                font-size: 1.5rem;
            }
            .headerwiwit div p {
                color: black;
                font-size: 1rem;
            }
            .headerwiwit div img {
                height: 5rem;
                width: 5rem;
            }
            .docscode table {
                color: black;
                width: 100%;
                table-layout: fixed;
                border-collapse: collapse;
            }
            .docscode table th, .docscode table td {
                word-wrap: break-word;
            }
            .docscode .bg-black {
                background-color: #000 !important;
                color: white !important;
            }
            .docscode .bg-\[\#f1d8f0\] {
                background-color: #f1d8f0 !important;
            }
            .docscode table tr, td {
                background-color: inherit !important;
                padding: 0;
                margin: 0;
            }
            .docscode table {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .docscode .bg-black {
                background-color: #000 !important;
                color: white !important;
            }
            .docscode .bg-\[\#f1d8f0\] {
                background-color: #f1d8f0 !important;
            }
            .docscode table tr, td {
                background-color: inherit !important;
                padding: 0;
                margin: 0;
            }
        }

        #messageModal {
            display: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        #messageModal {
            animation: fadeIn 0.3s ease-in-out;
        }

        @media print {
            #messageModal {
                display: none !important;
            }
        }

        /* Remove the previous button styles and add these new ones */
        .btn {
            transition: transform 0.2s ease;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        /* Back button */
        .bg-gray-800 {
            background-color: #1f2937 !important;
        }
        .bg-gray-800:hover {
            background-color: #111827 !important;
        }

        /* New Form button */
        .bg-green-500 {
            background-color: #22c55e !important;
        }
        .bg-green-500:hover {
            background-color: #16a34a !important;
        }

        /* Print and Save buttons */
        .bg-blue-500 {
            background-color: #3b82f6 !important;
        }
        .bg-blue-500:hover {
            background-color: #2563eb !important;
        }

        /* Override any Tailwind hover opacity classes */
        [class*="hover:bg-"]:hover {
            opacity: 1 !important;
        }

        /* Override any transition properties that might cause fading */
        .transition-colors {
            transition: none !important;
        }
    </style>
</head>

<body class="bg-[#E8E8E7]">
<?php if ($temp_message): ?>
<div id="messageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full mx-4">
        <div class="text-center">
            <h3 class="text-lg font-semibold mb-2">Notification</h3>
            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($temp_message); ?></p>
            <button onclick="closeModal()" class="bg-blue-500 text-white px-4 py-2 rounded">Close</button>
        </div>
    </div>
</div>
<?php endif; ?>
<?php include "../user_sidebar_header.php"; ?>
<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
        <div class="card">
            <div class="card-body">
                <div class="menu flex items-center justify-between">
                    <button class="btn bg-gray-800 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_dashboard.php';">
                        <i class="ti ti-chevron-compact-left mr-2"></i> Back
                    </button>
                    <div class="flex items-center space-x-4">
                        <form method="post" action="" class="flex items-center space-x-2">
                            <select name="rev_number_select" class="form-select" onchange="this.form.submit()">
                                <option value="">Select Rev. Number</option>
                                <?php foreach ($rev_numbers as $rev): ?>
                                    <option value="<?php echo htmlspecialchars($rev); ?>" 
                                        <?php if (isset($selectedRev) && $selectedRev == $rev) echo 'selected'; ?>>
                                        Rev. No. <?php echo htmlspecialchars($rev); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" onclick="clearForm()" class="btn bg-green-500 text-white px-3 py-2 rounded-md whitespace-nowrap">
                                <i class="ti ti-plus mr-2"></i>New Form
                            </button>
                            <button type="button" onclick="printSecondCard()" class="btn bg-blue-500 text-white px-3 py-2 rounded-md">
                                Print
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="print-content">
            <div class="card mt-4">
                <div class="card-body">
                    <div class="headerwiwit flex items-center justify-between gap-x-5">
                        <div class="dilglogo flex justify-center">
                            <img src="../img/dilg.png" alt="DILG Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
                        </div>
                        <div class="text-left flex-1">
                            <strong>DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</strong>
                            <h1 class="text-xl font-bold">POLICY FORMULATION COMMENT SHEET</h1>
                        </div>
                        <form method="post" action="" enctype="multipart/form-data">
                        <div class="docscode flex justify-end text-xs">
                            <table class="table-auto border border-black w-auto text-xs">
                                <tr>
                                    <td colspan="3" class="bg-black text-white p-1"><b>Document Code</b></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-1"><p class="text-sm">FM-OP-DILG-CO-41-03</td>
                                </tr>
                                <tr class="bg-[#f1d8f0]">
                                    <h6>
                                    <td class="p-1 text-center border border-black">Rev. No.</td>
                                    <td class="p-1 text-center border border-black">Eff. Date</td>
                                    <td class="p-1 text-center border border-black">Page</td>
                                    </h6>
                                </tr>
                                <tr>
                                    <td class="text-center border border-black">
                                    <input type="text" name="rev_number" class="underlinetable-input w-full" value="<?php echo htmlspecialchars($formsheet['rev_number'] ?? ''); ?>" required>
                                    </td>
                                    <td class="text-center border border-black">
                                    <input type="date" name="effect_date" class="underlinetable-input w-full" value="<?php echo htmlspecialchars($formsheet['effect_date'] ?? ''); ?>" required>
                                    </td>
                                    <td class="text-center border border-black" style="padding: 10px;">1 of 1</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4">
                        Please accomplish the comment sheet by indicating the <b>(a)</b> policy provision you wish to comment on, <b>(b)</b> your corresponding feedback (e.g. queries, recommendations, comments, etc.), and <b>(c)</b> the rationale behind the feedback you provided (e.g. legal basis, studies, relevant experiences, etc.). An example is provided below for your guidance. Thank you.
                    </div>

                    <div class="overflow-x-auto mt-4 print:hidden">
                        <b>EXAMPLE:</b><br>
                        <table class="table table-bordered w-full border border-black">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-center">PROVISIONS</th>
                                    <th class="px-4 py-2 text-center">FEEDBACK</th>
                                    <th class="px-4 py-2 text-center">BASIS FOR FEEDBACK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Par. (b) of Sec.</td>
                                    <td>Why is it phrased as...? If possible, we suggest that... such that this provision becomes consistent with...</td>
                                    <td>Sec. 10 of R.A... provides that... hence, the... stated in Par. (b) of Sec. 13 of your proposed policy may be in conflict with the...</td>
                                </tr>
                            </tbody>
                        </table>
                        <hr class="custom-hr">
                    </div>

                    <h3>
                        <strong>NAME OF POLICY REVIEWED:</strong> 
                        <input type="text" name="name_policy" class="underline-input" value="<?php echo htmlspecialchars($formsheet['name_policy'] ?? ''); ?>" required>
                    </h3><br>
                    <table class="table table-bordered">
    <thead>
        <tr>
            <th class="px-2 py-1 text-center"><h2>PROVISIONS</h2></th>
            <th class="px-2 py-1 text-center"><h2>FEEDBACK</h2></th>
            <th class="px-2 py-1 text-center"><h2>BASIS FOR FEEDBACK</h2></th>
        </tr>
    </thead>
    <tbody>
        <!-- Row 1 -->
        <tr>
            <td class="px-2 py-1 text-left">
                <textarea name="prov1" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: left; justify-content: left;" contenteditable="true" onfocus="prependDot(this)">
                    <?php echo htmlspecialchars($formsheet['prov1'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="feed1" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['feed1'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="basis1" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['basis1'] ?? ''); ?>
                </textarea>
            </td>
        </tr>
        <!-- Row 2 -->
        <tr>
            <td class="px-2 py-1 text-left">
                <textarea name="prov2" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: left; justify-content: left;" contenteditable="true" onfocus="prependDot(this)">
                    <?php echo htmlspecialchars($formsheet['prov2'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="feed2" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['feed2'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="basis2" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['basis2'] ?? ''); ?>
                </textarea>
            </td>
        </tr>
        <!-- Row 3 -->
        <tr>
            <td class="px-2 py-1 text-left">
                <textarea name="prov3" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: left; justify-content: left;" contenteditable="true" onfocus="prependDot(this)">
                    <?php echo htmlspecialchars($formsheet['prov3'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="feed3" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['feed3'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="basis3" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['basis3'] ?? ''); ?>
                </textarea>
            </td>
        </tr>
        <!-- Row 4 -->
        <tr>
            <td class="px-2 py-1 text-left">
                <textarea name="prov4" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: left; justify-content: left;" contenteditable="true" onfocus="prependDot(this)">
                    <?php echo htmlspecialchars($formsheet['prov4'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="feed4" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['feed4'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="basis4" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['basis4'] ?? ''); ?>
                </textarea>
            </td>
        </tr>
        <!-- Row 5 -->
        <tr>
            <td class="px-2 py-1 text-left">
                <textarea name="prov5" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: left; justify-content: left;" contenteditable="true" onfocus="prependDot(this)">
                    <?php echo htmlspecialchars($formsheet['prov5'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="feed5" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['feed5'] ?? ''); ?>
                </textarea>
            </td>
            <td class="px-2 py-1 text-left">
                <textarea name="basis5" class="auto-adjust" rows="1" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0; align-items: left; justify-content: left;" contenteditable="true">
                    <?php echo htmlspecialchars($formsheet['basis5'] ?? ''); ?>
                </textarea>
            </td>
        </tr>
    </tbody>
</table>
                    <script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            <?php if (isset($_SESSION['message'])): ?>
                                var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
                                messageModal.show();
                            <?php endif; ?>
                        });
                        function adjustTextareaHeight(textarea) {
                            textarea.style.height = 'auto';
                            textarea.style.height = (textarea.scrollHeight) + 'px';
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            var textareas = document.querySelectorAll('.auto-adjust');
                            textareas.forEach(function(textarea) {
                                adjustTextareaHeight(textarea);
                                textarea.addEventListener('input', function() {
                                    adjustTextareaHeight(textarea);
                                });
                            });
                        });

                        function printSecondCard() {
                            var textareas = document.querySelectorAll('.auto-adjust');
                            textareas.forEach(function(textarea) {
                                adjustTextareaHeight(textarea);
                            });
                            window.print();
                        }
                        function prependDot(textarea) {
                    if (textarea.value.trim() === '') {
                        textarea.value = '  •     ';
                    }
                }

            function adjustTextareaHeight(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
            }

            document.addEventListener('DOMContentLoaded', function() {
                var textareas = document.querySelectorAll('.auto-adjust');
                textareas.forEach(function(textarea) {
                    adjustTextareaHeight(textarea);
                    textarea.addEventListener('input', function() {
                        adjustTextareaHeight(textarea);
                    });
                });
            });

            function printSecondCard() {
                var textareas = document.querySelectorAll('.auto-adjust');
                textareas.forEach(function(textarea) {
                    adjustTextareaHeight(textarea);
                });
                window.print();
            }

            // Function to close the modal
            function closeModal() {
                const modal = document.getElementById('messageModal');
                if (modal) {
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 300);
                }
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('messageModal');
                if (event.target == modal) {
                    closeModal();
                }
            }

            // Show modal automatically if message exists
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('messageModal');
                if (modal) {
                    modal.style.display = 'flex';
                    
                    // Auto-hide modal after 3 seconds
                    setTimeout(function() {
                        closeModal();
                    }, 3000);
                }
            });

            function clearForm() {
                // Clear the revision selection
                const revSelect = document.querySelector('select[name="rev_number_select"]');
                if (revSelect) {
                    revSelect.value = '';
                    revSelect.form.submit(); // Submit the form to clear the data
                }
                
                // Clear all form inputs
                const inputs = document.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea');
                inputs.forEach(input => {
                    input.value = '';
                    if (input.classList.contains('auto-adjust')) {
                        adjustTextareaHeight(input);
                    }
                });

                // Set focus to the first input
                const firstInput = document.querySelector('input[name="rev_number"]');
                if (firstInput) firstInput.focus();
            }
                    </script>
                    <br><br>
                    PREPARED BY:
                    <br>
                    <input type="text" name="prepare_by" class="underline-input" style="text-align: left;" placeholder="Name of Policy Reviewed" value="<?php echo htmlspecialchars($formsheet['prepare_by'] ?? ''); ?>" required>                    
                    <p>[Name and Position]</p>
                    <p>[Name of Office]</p>
                    <div class="text-right print:hidden">
                <button type="submit" name="submit" class="btn bg-blue-500 text-white px-4 py-2 rounded-md">
                    Save
                </button>
                <br>
                </div>
                </form>
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
</html>