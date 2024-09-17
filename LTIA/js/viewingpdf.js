
        document.getElementById('showPdfBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Your PDF Title',
                html: `
                    <div class="d-flex justify-content-start mb-3">
                        <div class="me-2">
                            <input type="number" id="modalNumberInput" class="form-control" placeholder="Number">
                        </div>
                        <div class="me-2">
                            <textarea id="modalTextInput" class="form-control" rows="1" placeholder="Your text"></textarea>
                        </div>
                        <button class="btn btn-primary" id="okButton">OK</button>
                    </div>
                    <iframe src="./path/to/your.pdf" width="100%" height="500px"></iframe>
                `,
                width: '80%',
                showCloseButton: true,
                showCancelButton: false,
                focusConfirm: false,
                confirmButtonText: 'Close',
                didOpen: () => {
                    document.getElementById('okButton').addEventListener('click', function() {
                        let numberValue = document.getElementById('modalNumberInput').value;
                        let textValue = document.getElementById('modalTextInput').value;

                        // Set the values to the main page inputs
                        document.getElementById('numberOutput').value = numberValue;
                        document.getElementById('textOutput').value = textValue;

                        // Close the modal
                        Swal.close();
                    });
                },
                willClose: () => {
                    // Get the values when the modal is closed (by the close button or manually)
                    let numberValue = document.getElementById('modalNumberInput').value;
                    let textValue = document.getElementById('modalTextInput').value;

                    // Set the values to the main page inputs
                    document.getElementById('numberOutput').value = numberValue;
                    document.getElementById('textOutput').value = textValue;
                }
            });
        });

        // Add functionality for button inside the table
        document.getElementById('showPdfBtnInTable').addEventListener('click', function() {
            Swal.fire({
                title: 'Your PDF Title',
                html: `
                    <div class="d-flex justify-content-start mb-3">
                        <div class="me-2">
                            <input type="number" id="modalNumberInputTable" class="form-control" placeholder="Number">
                        </div>
                        <div class="me-2">
                            <textarea id="modalTextInputTable" class="form-control" rows="1" placeholder="Your text"></textarea>
                        </div>
                        <button class="btn btn-primary" id="okButtonTable">OK</button>
                    </div>
                    <iframe src="./path/to/your.pdf" width="100%" height="500px"></iframe>
                `,
                width: '80%',
                showCloseButton: true,
                showCancelButton: false,
                focusConfirm: false,
                confirmButtonText: 'Close',
                didOpen: () => {
                    document.getElementById('okButtonTable').addEventListener('click', function() {
                        let numberValue = document.getElementById('modalNumberInputTable').value;
                        let textValue = document.getElementById('modalTextInputTable').value;

                        // Set the values to the main page inputs
                        document.getElementById('numberOutput').value = numberValue;
                        document.getElementById('textOutput').value = textValue;

                        // Close the modal
                        Swal.close();
                    });
                },
                willClose: () => {
                    // Get the values when the modal is closed (by the close button or manually)
                    let numberValue = document.getElementById('modalNumberInputTable').value;
                    let textValue = document.getElementById('modalTextInputTable').value;

                    // Set the values to the main page inputs
                    document.getElementById('numberOutput').value = numberValue;
                    document.getElementById('textOutput').value = textValue;
                }
            });
        });