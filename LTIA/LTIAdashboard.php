<?php
session_start();
include '../connection.php';

// Redirect if user is not logged in or is not a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <title>LTIA</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body class="bg-[#E8E8E7]">
    <!-- Sidebar -->
    <?php include "../user_sidebar_header.php"; ?>
    <div class="p-4 sm:ml-44">
        <div class="rounded-lg mt-16">
            <div class="card">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center">
                            <div class="dilglogo">
                                <img src="../img/cluster.png" alt="Logo" style="max-width: 120px; max-height: 120px; margin-right: 10px;" class="align-middle">
                            </div>
                            <h1 class="text-xl font-bold ml-4">Lupong Tagapamayapa Incentives Award (LTIA) <?php echo date('Y'); ?></h1>
                        </div>
                        <div class="menu">
                            <ul class="flex space-x-4">
                                <li>
                                    <button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='form2MOVupload.php';" style="margin-left: 0;">
                                        <i class="ti ti-file-upload mr-2"></i> Upload Means of Verification
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Circle Button and Modal Trigger -->
                    <div class="flex items-center justify-center mb-4 space-x-8">
                        <button type="button" class="w-56 h-56 bg-blue-500 rounded-full flex items-center justify-center text-white text-4xl font-semibold hover:bg-blue-600 transition-transform transform hover:scale-105" onclick="location.href='form2movview.php';">
                            A+
                        </button>

                        <div class="flex flex-col text-justify w-2/3">
                            <p class="text-2xl font-bold">The Lupong Tagapamayapa Incentives Award (LTIA)</p>   
                            <p class="text-lg">
                                The Lupong Tagapamayapa Incentives Award (LTIA) was conceptualized and implemented in 1982 and has been elevated to a Presidential Award pursuant to Executive Order No. 394 s. 1997 entitled “Establishing the Lupong Tagapamayapa Incentives Award.”
                            </p>
                            <p class="text-lg">
                                This award is an avenue for granting economic and other incentives to the Lupong Tagapamayapa (LT) for their outstanding contributions to attaining the objectives of the Katarungang Pambarangay (KP).
                            </p>
                        </div>
                    </div>

                    <!-- Modal Structure -->
                    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="infoModalLabel">Award Information</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    This modal contains information about the Lupong Tagapamayapa Incentives Award (LTIA). You can customize this content as needed.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <footer class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-full">
                            <path fill="#0099ff" fill-opacity="1" d="M0,224L30,224C60,224,120,224,180,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
                        </svg>
                        <div class="absolute right-0 bottom-0 mb-4 mr-4">
                            <img src="images/ltialogo.png" alt="LTIA Logo" class="h-20" />
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Popper.js included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
