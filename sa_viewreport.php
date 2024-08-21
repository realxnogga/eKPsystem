<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://rawgit.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>

    <script>
        function generatePDF() {
            var element = document.querySelector('table');
            var additionalContent = `
           
        `;

            // Create a new window with the combined content
            var newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>PDF</title></head><body>' + element.outerHTML + additionalContent + '</body></html>');
            newWindow.document.close();

            // Use html2pdf library to generate PDF
            html2pdf(newWindow.document.body, {
                margin: 10,
                filename: 'table_' + getFormattedDate() + '.pdf',
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
            });

            // Close the new window
            newWindow.close();
        }

        // Function to download table as Excel
        function downloadExcel() {
            var element = document.querySelector('table');

            // Use xlsx library to generate Excel file
            var wb = XLSX.utils.table_to_book(element);
            var wbout = XLSX.write(wb, { bookType: 'xlsx', bookSST: true, type: 'binary' });

            // Convert string to ArrayBuffer
            function s2ab(s) {
                var buf = new ArrayBuffer(s.length);
                var view = new Uint8Array(buf);
                for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
                return buf;
            }

            // Create Blob and trigger download
            var blob = new Blob([s2ab(wbout)], { type: 'application/octet-stream' });
            saveAs(blob, 'table_' + getFormattedDate() + '.xlsx');
        }

        // Function to adjust table styles for PDF generation
        function adjustTableStyles(table) {
            // Store original styles
            table.setAttribute('data-original-style', table.getAttribute('style') || '');

            // Set new styles for PDF generation
            table.style.fontSize = '6pt'; // Adjust font size
            table.style.width = '100%'; // Adjust table width
            // Add more style adjustments as needed
        }

        // Function to restore original table styles
        function restoreTableStyles(table) {
            // Restore original styles
            var originalStyle = table.getAttribute('data-original-style');
            table.setAttribute('style', originalStyle);
        }

        // Function to get the current date and time in a formatted string
        function getFormattedDate() {
            var now = new Date();
            var year = now.getFullYear();
            var month = ('0' + (now.getMonth() + 1)).slice(-2);
            var day = ('0' + now.getDate()).slice(-2);

            return year + month + day + '_';
        }
    </script>

    <style>
        h1 {
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 14pt;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 5px;
            text-align: center;
            white-space: nowrap; /* Prevent text wrapping */
        }

        table {
            width: 100%;
            max-width: 100%; /* Set max-width to 100% */
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        @media print {
            /* ... existing styles ... */

            table {
                width: 50%;
                font-size: 4pt; /* Adjust font size for printing */
            }

            th, td {
                padding: 2px; /* Adjust padding for printing */
            }

            td[colspan="4"] {
                white-space: normal; /* Allow text wrapping for specific cells */
            }

            @page {
                size: A4 landscape;
            }
        }
    </style>
</head>

<body>
    <b>
        <table>

            <h1> CONSOLIDATED KATARUNGANG PAMBARANGAY COMPLIANCE REPORT ON THE ACTION TAKEN BY THE LUPONG TAGAPAMAYAPA CY 2023																		
            </h1>

            <tr>
                <td rowspan="3"> PROVINCE <br> CITY </td>
                <td rowspan="4" colspan="1"> C/M </td>
                <td rowspan="2" colspan="4"> NATURE OF DISPUTES (2) </td>
            </tr>
            <tr>
                <td colspan="4">SETTLED CASES (3)</td>
                <td colspan="8">UNSETTLED CASES (4)</td>
            </tr>
            <tr>
                <td colspan="1">CRIMIN <br> AL </td>
                <td colspan="1"> CIVIL </td>
                <td colspan="1">OTHERS </td>
                <td colspan="1">TOTAL </td>
                <td colspan="1">MEDIA <br> TION </td>
                <td colspan="1">CONCI<br>LIATION </td>
                <td colspan="1">ARBIT<br>RATION </td>
                <td colspan="1">TOTAL </td>
                <td colspan="1">REPU<br>DIATED </td>
                <td colspan="1">WITH<br>DRAWN </td>
                <td colspan="1">PEND<br>ING </td>
                <td colspan="1">DIS<br>MISSED </td>
                <td colspan="1">CERTIFIED <br> TO FILE <br> ACTION IN <br> COURT </td>
                <td colspan="1"> REFER <br> ED TO <br> CONCER <br> NED <br> AGENCY </td>
                <td colspan="1">TOTAL </td>
                <td colspan="1" rowspan="2">ESTIMA <br> TED <br> GOVT. <br> SAVINGS <br> (5) </td>
            </tr>

            <tr>
                <td colspan="1"> (1) </td>
                <td colspan="1"> (2a) </td>
                <td colspan="1"> (2b) </td>
                <td colspan="1"> (2c) </td>
                <td colspan="1"> (2d) </td>
                <td colspan="1"> (3a) </td>
                <td colspan="1"> (3b) </td>
                <td colspan="1"> (3c) </td>
                <td colspan="1"> (3d) </td>
                <td colspan="1"> (4a)</td>
                <td colspan="1"> (4b) </td>
                <td colspan="1"> (4c) </td>
                <td colspan="1"> (4d) </td>
                <td colspan="1"> (4e) </td>
                <td colspan="1"> (4f) </td>
                <td colspan="1"> (4g) </td>
            </tr>
            <?php $municipalities = array(
                "ALAMINOS",
                "BAY",
                "CALAUAN",
                "LOS BAÑOS",
                "CABUYAO CITY",
                "CITY OF BIÑAN",
                "CITY OF CALAMBA",
                "SAN PABLO CITY",
                "CITY OF SAN PEDRO",
                "CITY OF SANTA ROSA"
            );

            for ($i = 0; $i < 10; $i++) :
            ?>
                <tr>
                    <td colspan="1"> </td>
                    <td colspan="1"> <?php echo $municipalities[$i]; ?></td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                    <td colspan="1"> </td>
                </tr>
            <?php endfor; ?>
        </table>

        <button onclick="generatePDF()">Generate PDF</button>
        <button onclick="downloadExcel()">Download Excel</button>
    </body>

    </html>
