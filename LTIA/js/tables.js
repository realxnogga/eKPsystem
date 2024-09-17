function validateFileType(input) {
    const file = input.files[0];
    if (file) {
        const fileType = file.type;
        if (fileType !== 'application/pdf') {
            alert('Please select a PDF file.');
            input.value = ''; // Clear the input
        }
    }
}
