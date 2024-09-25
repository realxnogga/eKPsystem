<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Fetch uploaded files from the database
$sql = "SELECT `IA_1a_pdf_File`, `IA_1b_pdf_File` FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: []; // Initialize $row as an empty array if no records found

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads
    $upload_dir = 'movfolder/';
    foreach ($_FILES as $key => $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $file_name = time() . '_' . basename($file['name']);
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Validate column name
                $allowed_columns = ['IA_1a_pdf_File', 'IA_1b_pdf_File']; // Add more columns as needed
                if (in_array($key, $allowed_columns)) {
                    // Update the database with the uploaded file path
                    $sql = "UPDATE mov SET $key = :file_path WHERE user_id = :user_id AND barangay_id = :barangay_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':file_path', $file_name, PDO::PARAM_STR);
                    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);

                    if (!$stmt->execute()) {
                        error_log(print_r($stmt->errorInfo(), true)); // Log error
                    }
                }
            }
        }
    }
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTIA</title>

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body class="bg-[#E8E8E7]">
  <?php include "../user_sidebar_header.php"; ?>
  <div class="p-4 sm:ml-44 ">
    <div class="rounded-lg mt-16">
    <div class="card">
                <div class="card-body">
  <div class="container mt-5">
    <h2 class="text-left text-2xl font-semibold">FORM 1</h2>
    <form method="post" enctype="multipart/form-data">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>CRITERIA</th>
            <th>Means Of Verification</th>
            <th>File</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><b>1. a) Proper Recording of every dispute/complaint</b></td>
            <td><input type="file" id="IA_1a_pdf_File" name="IA_1a_pdf_File" accept=".pdf" /></td>
            <td>
              <?php if (!empty($row['IA_1a_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1a_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>this is remark</td>
          </tr>
          <tr>
            <td>b) Sending of Notices and Summons</td>
            <td><input type="file" id="IA_1b_pdf_File" name="IA_1b_pdf_File" accept=".pdf" /></td>
            <td>
              <?php if (!empty($row['IA_1b_pdf_File'])) : ?>
                <button type="button" class="btn btn-primary view-pdf" data-file="movfolder/<?php echo $row['IA_1b_pdf_File']; ?>">View</button>
              <?php else : ?>
                <span>No file uploaded</span>
              <?php endif; ?>
            </td>
            <td>this is remark</td>
          </tr>
        </tbody>
      </table>
      <input type="submit" value="Update" class="btn btn-dark mt-3" />
    </form>

    <!-- Modal for PDF Viewer -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="pdfModalLabel">PDF Viewer</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <iframe id="pdfViewer" src="" width="100%" height="500px"></iframe>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
        $('.view-pdf').click(function() {
            var pdfFile = $(this).data('file'); // Get the PDF file path from data attribute
            $('#pdfViewer').attr('src', pdfFile); // Set the file path in the iframe
            $('#pdfModal').modal('show'); // Show the modal
        });
    });
  </script>

</body>
</html>
