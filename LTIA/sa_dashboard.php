<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}

// Fetch all municipalities from the database in alphabetical order
$query = "SELECT `id`, `municipality_name` FROM `municipalities` ORDER BY `municipality_name` ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$municipalities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$municipality_id = isset($_GET['municipality_id']) ? $_GET['municipality_id'] : 0;

$query = "
    SELECT b.barangay_name AS barangay, m.total 
    FROM barangays b
    LEFT JOIN movrate m ON b.id = m.barangay
    WHERE b.municipality_id = :municipality_id
    ORDER BY m.total DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':municipality_id', $municipality_id, PDO::PARAM_INT);
$stmt->execute();
$barangay_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LTIA</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico"> 
    <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>

<body class="bg-[#E8E8E7]">
    <!-- Sidebar -->
    <?php include "../sa_sidebar_header.php"; ?>
    <div class="p-4 sm:ml-44">
        <div class="rounded-lg mt-16">
            <div class="card">
                <div class="card-body">
                    <h1 class="text-xl font-bold">Municipalities <?php echo date('Y'); ?></h1><br>
                    <!-- Display municipalities as button boxes with images -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($municipalities as $municipality) : ?>
                        <div class="bg-white shadow-md rounded p-4 text-center">
                            <img src="images/municipalities/<?php echo $municipality['id']; ?>.jpg" 
                                alt="<?php echo htmlspecialchars($municipality['municipality_name']); ?>" 
                                class="w-24 h-24 mx-auto rounded mb-2">
                            <button onclick="showBarangays(<?php echo $municipality['id']; ?>)" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                                <?php echo htmlspecialchars($municipality['municipality_name']); ?>
                            </button>
                        </div>
                    <?php endforeach; ?>

                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="barangayModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div style="background:white; margin:5% auto; padding:20px; width:80%; max-width:600px; border-radius:8px; position:relative;">
            <button onclick="closeModal()" style="position:absolute; top:10px; right:10px; font-size:20px; background:none; border:none; cursor:pointer;">&times;</button>
            <div id="barangayModalContent"></div>
        </div>
    </div>

</body>
<script>
function showBarangays(municipalityId) {
    fetch('../fetch_barangays.php?municipality_id=' + municipalityId)
    .then(response => response.text())
    .then(data => {
        console.log(data);  // Check the response in the console
    })
    .catch(error => console.error('Error:', error));

}

// Function to close the modal
function closeModal() {
    document.getElementById('barangayModal').style.display = 'none';
}
</script>

</html>
