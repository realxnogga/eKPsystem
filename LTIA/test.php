<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Define user and barangay ID from session
$userID = $_SESSION['user_id'];
$barangayID = $_SESSION['barangay_id'] ?? '';

// Initialize the variable to store the municipality name
$municipalityName = '';

try {
    // Check if the barangay ID is valid and exists
    if (!empty($barangayID)) {
        // Fetch the municipality name based on the barangay's municipality_id
        $query = "
            SELECT m.municipality_name 
            FROM barangays b
            INNER JOIN municipalities m ON b.municipality_id = m.id
            WHERE b.id = :barangay_id
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':barangay_id', $barangayID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $municipalityName = $result['municipality_name'];
        }
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "Database error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="css/td_hover.css">
</head>

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

        const municipalityName = <?php echo json_encode($municipalityName); ?>;
        const classification = classifyMunicipality(municipalityName);

        // Display classification type in the page
        document.getElementById("details-municipality-type").textContent = classification;
    });
</script>

<body class="bg-[#E8E8E7]">
<?php include "../user_sidebar_header.php"; ?>

<div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
        <div class="card">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                    <div class="menu">
                    <h1 class="text-xl font-bold ml-4">Lupong Tagapamayapa Incentives Award (LTIA) <?php echo date('Y'); ?>
                           <hr class="my-2">
                    <h2 class="text-lg font-semibold">
                        <span id="details-municipality-type"></span>
                        OF <?php echo strtoupper(htmlspecialchars($municipalityName)); ?>
                    </h2>
                </div>
          <div class="container mt-5">
               
</body>
</html>
