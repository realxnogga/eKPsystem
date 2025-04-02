<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
  header("Location: login.php");
  exit;
}

$userID = $_SESSION['user_id'] ?? null; // Get user ID from session
$barangayID = $_SESSION['barangay_id'] ?? null; // Get barangay ID from session

// Handle file upload logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_FILES["file"]) && !empty($_FILES["file"]["name"])) {
    // Specify upload directory
    $uploadDirectory = "uploads/lupon/";

    // Get the file name and target path
    $fileName = basename($_FILES["file"]["name"]);
    $targetFilePath = $uploadDirectory . $fileName;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
      // Insert file information into the database
      $sql = "INSERT INTO user_files (user_id, barangay_id, file_name, file_path) 
                    VALUES (:user_id, :barangay_id, :file_name, :file_path)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':user_id', $userID);
      $stmt->bindParam(':barangay_id', $barangayID);
      $stmt->bindParam(':file_name', $fileName);
      $stmt->bindParam(':file_path', $targetFilePath);
      $stmt->execute();
      $uploadMessage = "The file " . htmlspecialchars($fileName) . " has been uploaded and stored successfully.";
    } else {
      $uploadMessage = "Sorry, there was an error uploading your file.";
    }
  } else {
    $uploadMessage = "Please select a file to upload.";
  }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_FILES["file"]) && !empty($_FILES["file"]["name"])) {
    // Handle file upload logic
  } elseif (isset($_POST['delete_file'])) {
    $fileID = $_POST['file_id'] ?? null;
    if ($fileID) {
      // Fetch file information from the database
      $sql = "SELECT file_path FROM user_files WHERE id = :file_id AND user_id = :user_id AND barangay_id = :barangay_id";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':file_id', $fileID);
      $stmt->bindParam(':user_id', $userID);
      $stmt->bindParam(':barangay_id', $barangayID);
      $stmt->execute();
      $file = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($file) {
        // Delete the file from the server
        unlink($file['file_path']);

        // Delete the file record from the database
        $sql = "DELETE FROM user_files WHERE id = :file_id AND user_id = :user_id AND barangay_id = :barangay_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':file_id', $fileID);
        $stmt->bindParam(':user_id', $userID);
        $stmt->bindParam(':barangay_id', $barangayID);
        $stmt->execute();

        $deleteMessage = "File deleted successfully.";
      } else {
        $deleteMessage = "File not found or you do not have permission to delete it.";
      }
    } else {
      $deleteMessage = "Invalid file ID.";
    }
  }
}
// Retrieve uploaded files from the database
$sql = "SELECT * FROM user_files WHERE user_id = :user_id AND barangay_id = :barangay_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $userID);
$stmt->bindParam(':barangay_id', $barangayID);
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upload File for Lupon</title>
  
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
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

  <div class="p-4 sm:ml-44">
    <div class="rounded-lg mt-16">

      <div class="flex flex-col lg:flex-row">
        <div class="lg:w-2/3">
          <div class="bg-white shadow-md rounded-lg p-6">
            <h5 class="text-lg font-semibold text-black mb-4">Uploaded Files</h5>
            <?php if (isset($deleteMessage)) echo '<p class="text-red-500">' . $deleteMessage . '</p>'; ?>
            <ul class="space-y-2">
              <?php foreach ($files as $file): ?>
                <li class="flex items-center justify-between">
                  <a href="<?php echo $file['file_path']; ?>" target="_blank" class="text-blue-500 hover:underline"><?php echo $file['file_name']; ?></a>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="inline">
                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                    <button type="submit" class="bg-red-500 px-3 py-2 hover:bg-red-400 border rounded-md text-white" name="delete_file" onclick="return confirm('Are you sure you want to delete the file?')">Delete</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <br>
          <div class="bg-white shadow-md rounded-lg p-6">
            <h5 class="text-lg font-semibold text-black mb-4">Upload File for Lupon</h5>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
              <div class="flex flex-row space-x-2">
                <input type="file" class="w-full bg-white rounded-md text-black border border-gray-300" name="file" id="file">
                <input type="submit" class="w-fit bg-blue-500 px-4 py-2 hover:bg-blue-400 border rounded-md text-white cursor-pointer " value="Upload" name="submit">
              </div>
            </form>       
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>