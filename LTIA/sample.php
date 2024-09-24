<?php
session_start();
include '../connection.php';

// Fetch uploaded files from the database
$sql = "SELECT `IA_1a_pdf_File`, `IA_1b_pdf_File`, `IA_2_pdf_File`, `IA_2a_pdf_File`, `IA_2b_pdf_File`, `IA_2c_pdf_File`, 
`IA_2d_pdf_File`, `IA_2e_pdf_File`, `IB_1forcities_pdf_File`, `IB_1aformuni_pdf_File`, `IB_1bformuni_pdf_File`, 
`IB_2_pdf_File`, `IB_3_pdf_File`, `IB_4_pdf_File`, `IC_1_pdf_File`, `IC_2_pdf_File`, `ID_1_pdf_File`, `ID_2_pdf_File`, 
`IIA_pdf_File`, `IIB_1_pdf_File`, `IIB_2_pdf_File`, `IIC_pdf_File`, `IIIA_pdf_File`, `IIIB_pdf_File`, 
`IIIC_1forcities_pdf_File`, `IIIC_1forcities2_pdf_File`, `IIIC_1forcities3_pdf_File`, `IIIC_2formuni1_pdf_File`, 
`IIIC_2formuni2_pdf_File`, `IIIC_2formuni3_pdf_File`, `IIID_pdf_File`, `IV_forcities_pdf_File`, `IV_muni_pdf_File`, 
`V_1_pdf_File`, `threepeoplesorg` FROM mov WHERE user_id = :user_id AND barangay_id = :barangay_id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindParam(':barangay_id', $_SESSION['barangay_id'], PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded PDFs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Uploaded PDF Files</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($row as $fileField => $filePath) {
                    if ($filePath) {
                        echo "
                                <td></td>
                                <td>
                                    <button class='btn btn-primary view-pdf' data-file='movfolder/{$filePath}'>View</button>
                                </td>
                              ";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap Modal -->
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

    <script>
        $(document).ready(function() {
            // Event listener for "View" button
            $('.view-pdf').on('click', function() {
                var filePath = $(this).data('file');
                $('#pdfViewer').attr('src', filePath);
                $('#pdfModal').modal('show');
            });
        });
    </script>
</body>
</html>
