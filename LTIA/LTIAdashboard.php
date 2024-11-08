<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user' || !isset($_SESSION['barangay_id'])) {
    header("Location: login.php");
    exit;
}

function getPerformanceRating($total) {
    if ($total >= 100) {
        return "Outstanding";
    } elseif ($total >= 90) {
        return "Very Satisfactory";
    } elseif ($total >= 80) {
        return "Fair";
    } elseif ($total >= 70) {
        return "Poor";
    } else {
        return "Very Poor";
    }
}

$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y'); // Default to the current year

try {
    $query = "
        SELECT 
            `total`, `daterate`
        FROM 
            `movrate`
        WHERE 
            EXTRACT(YEAR FROM `daterate`) = :year 
            AND `barangay` = :barangay
    ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->bindParam(':barangay', $_SESSION['barangay_id'], PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $total = $row['total'];
        $performance = getPerformanceRating($total);
    } else {
        $total = "N/A";
        $performance = "No Rating yet";
    }

    // Fetch distinct years for dropdown, including the current year if not in data
    $yearQuery = "SELECT DISTINCT EXTRACT(YEAR FROM `daterate`) AS year FROM `movrate` ORDER BY year DESC";
    $yearResult = $conn->query($yearQuery);
    $years = $yearResult->fetchAll(PDO::FETCH_COLUMN);

    // Add the current year to the list if it’s not already there
    if (!in_array(date('Y'), $years)) {
        array_unshift($years, date('Y'));
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $total = "Error";
    $performance = "Error fetching data";
}
$query = "
    SELECT 
        `total`, `daterate`
    FROM 
        `movrate`
    WHERE 
        EXTRACT(YEAR FROM `daterate`) = :year 
        AND `barangay` = :barangay
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);
$stmt->bindParam(':barangay', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();

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

                            <h1 class="text-xl font-bold flex items-center ml-4">
                                <span>Lupong Tagapamayapa Incentives Award (LTIA)</span>
                                <form method="GET" action="" class="ml-4">
                                    <select name="year" onchange="this.form.submit()">
                                        <?php foreach ($years as $availableYear): ?>
                                            <option value="<?php echo $availableYear; ?>" <?php if ($availableYear == $year) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($availableYear); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                            </h1>
             
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
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        
                    <!-- Circle Button with Conditional Text -->
                                        
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <button type="button" class="btn btn-circle bg-primary text-white d-flex flex-column justify-content-center align-items-center shadow-lg ms-5" onclick="location.href='form2movview.php';" style="width: 150px; height: 150px; font-size: 1.5rem;">
                            <span class="fw-bold fs-2"><?php echo htmlspecialchars($total); ?></span> 
                            <span class="fs-6"><?php echo htmlspecialchars($performance); ?></span>
                        </button>
                    </div>

                    <!-- Text Section -->
                    <div class="flex flex-col text-justify w-75 me-5">
                        <p class="h4 fw-bold text-secondary">The Lupong Tagapamayapa Incentives Award (LTIA)</p>   
                        <p class="text-muted">
                            The Lupong Tagapamayapa Incentives Award (LTIA) was conceptualized and implemented in 1982 and has been elevated to a Presidential Award pursuant to Executive Order No. 394 s. 1997 entitled “Establishing the Lupong Tagapamayapa Incentives Award.”
                        </p>
                        <p class="text-muted">
                            This award is an avenue for granting economic and other incentives to the Lupong Tagapamayapa (LT) for their outstanding contributions to attaining the objectives of the Katarungang Pambarangay (KP).
                        </p>
                    </div>
                </div>
                <footer class="position-relative">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-100">
                    <path fill="#0099ff" fill-opacity="1" d="M0,224L30,224C60,224,120,224,180,208C240,192,300,160,360,149.3C420,139,480,149,540,160C600,171,660,181,720,154.7C780,128,840,64,900,58.7C960,53,1020,107,1080,117.3C1140,128,1200,96,1260,69.3C1320,43,1380,21,1410,10.7L1440,0L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
                </svg>
                <div class="position-absolute bottom-0 end-0 mb-3 me-3 d-flex justify-content-center">
                    <img src="images/ltialogo.png" alt="LTIA Logo" class="img-fluid" style="max-height: 80px; width: auto;" />
                </div>
                </footer>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
