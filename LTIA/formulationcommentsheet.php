<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user' || !isset($_SESSION['barangay_id'])) {
    header("Location: login.php");
    exit;
}
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y'); // Default to the current year

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LTIA Form 3</title>
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
            .headerwiwit div h1 {
                font-size: medium;
            }
            .headerwiwit div img {
                height: 5rem;
                width: 5rem;
            }
            .docscode table {
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
        }
    </style>
</head>

<body class="bg-[#E8E8E7]">
<?php include "../user_sidebar_header.php"; ?>
<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
        <div class="card">
            <div class="card-body">
                <div class="menu flex items-center justify-between">
                    <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='ltia_dashboard.php';">
                        <i class="ti ti-chevron-compact-left mr-2"></i> Back
                    </button>
                    <div class="flex items-center space-x-4">
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
                    <div class="headerwiwit flex items-center justify-between gap-x-5">
                        <div class="dilglogo flex justify-center">
                            <img src="../img/dilg.png" alt="DILG Logo" style="max-width: 120px; max-height: 120px;" class="mx-auto">
                        </div>
                        <div class="text-left flex-1">
                            <strong>DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</strong>
                            <h1 class="text-xl font-bold">POLICY FORMULATION COMMENT SHEET</h1>
                        </div>
                        <div class="docscode flex justify-end text-xs">
                            <table class="table-auto border border-black w-auto text-xs">
                                <tr>
                                    <td colspan="3" class="bg-black text-white p-1"><b>Document Code</b></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="p-1"><h1 class="text-sm">FM-OP-DILG-CO-41-03</h1></td>
                                </tr>
                                <tr class="bg-[#f1d8f0]">
                                    <td class="p-1 text-center border border-black">Rev. No.</td>
                                    <td class="text-center border border-black">Eff. Date</td>
                                    <td class="text-center border border-black">Page</td>
                                </tr>
                                <tr>
                                    <td class="text-center border border-black"><input type="number" class="underlinetable-input w-full"></td>
                                    <td class="text-center border border-black"><input type="date" class="underlinetable-input w-full"></td>
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
                        <strong>NAME OF POLICY REVIEWED:</strong> <input type="text" class="underline-input">
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
                            <tr>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: center; justify-content: center;" contenteditable="true" onfocus="prependDot(this)"></textarea>                                
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea></td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: center; justify-content: center;" contenteditable="true" onfocus="prependDot(this)"></textarea>                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: center; justify-content: center;" contenteditable="true" onfocus="prependDot(this)"></textarea>                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: center; justify-content: center;" contenteditable="true" onfocus="prependDot(this)"></textarea>                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: center; justify-content: center;" contenteditable="true" onfocus="prependDot(this)"></textarea>                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: center; justify-content: center;" contenteditable="true" onfocus="prependDot(this)"></textarea>                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 14px; white-space: pre-line; padding: 0; margin: 0; font-weight: bold; display: flex; align-items: center; justify-content: center;" contenteditable="true" onfocus="prependDot(this)"></textarea>                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                                <td class="px-2 py-1 text-center">
                                <textarea class="auto-adjust" style="text-decoration: none; width: 100%; border: none; overflow-y: hidden; resize: none; font-size: 12px; white-space: pre-line; padding: 0; margin: 0;" contenteditable="true"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <script>
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
                        textarea.value = '•     ';
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
                    </script>
                    <br>
                    PREPARED BY:
                    <br>
                    <input type="text" class="underline-input" placeholder="Name of Policy Reviewed"><br>
                    <p>[Name and Position]</p>
                    <p>[Name of Office]</p>
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