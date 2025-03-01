<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Cropper</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        img {
            max-width: 100%;
        }
    </style>
</head>

<body>
    <input type="file" id="inputImage">
    <br><br>
    <label for="aspectRatio">Aspect Ratio:</label>
    <input type="number" id="aspectRatio" step="0.1" value="1">
    <br><br>
    <img id="image" style="max-width: 500px;">
    <br>
    <button id="cropButton">Crop</button>
    <br><br>
    <canvas id="croppedCanvas"></canvas>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        let cropper;
        const inputImage = document.getElementById('inputImage');
        const aspectRatioInput = document.getElementById('aspectRatio');
        const image = document.getElementById('image');
        const cropButton = document.getElementById('cropButton');
        const croppedCanvas = document.getElementById('croppedCanvas');

        inputImage.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    image.src = e.target.result;
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(image, {
                        aspectRatio: parseFloat(aspectRatioInput.value) || NaN,
                        viewMode: 2,
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        aspectRatioInput.addEventListener('input', function() {
            if (cropper) {
                cropper.setAspectRatio(parseFloat(aspectRatioInput.value) || NaN);
            }
        });

        cropButton.addEventListener('click', function() {
            if (cropper) {
                const croppedImage = cropper.getCroppedCanvas().toDataURL('image/png');

                fetch('samplephp.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            image: croppedImage
                        }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.text())
                    .then(data => alert(data))
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>
</body>

</html>