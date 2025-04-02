<?php
session_start();



$whatType = $_SESSION['user_type'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== "$whatType") {
    header("Location: login.php");
    exit;
}

function tempFunc($arg)
{
    if ($arg === 'user') {
        return 'user_setting.php';
    }
    if ($arg === 'superadmin') {
        return 'sa_setting.php';
    }
    if ($arg === 'admin') {
        return 'admin_setting.php';
    }
    if ($arg === 'assessor') {
        return 'assessor_setting.php';
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Cropper</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

    <!-- might delete later -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script src="node_modules/flowbite/dist/flowbite.min.js"></script>
    <link href="node_modules/flowbite/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css">

</head>

<body class="bg-[#4d4d4d] min-h-screen flex items-center justify-center">

    <a href="<?php echo tempFunc($whatType); ?>" class="absolute top-3 left-3 flex gap-x-2 items-center p-2 rounded-lg group">
        <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            back
        </button>
    </a>

    <section class="m-5 p-5 w-full max-w-5xl flex flex-col md:flex-row items-center gap-x-4 gap-y-4 bg-gray-300 rounded-xl bg-opacity-20">

        <section class="w-full md:w-3/4 h-full md:h-auto">
            <img id="image" class="h-full w-full object-contain" />
        </section>


        <section class="w-full md:w-1/4 flex flex-col gap-y-6">

            <section class="h-40 md:h-60 relative">
                <img id="croppedImage" class="absolute inset-0 object-contain w-full h-full" />
            </section>


            <section class="flex flex-col items-center gap-y-3">

                <label for="inputImage" class="w-full text-center text-gray-200 border border-blue-500 focus:ring-4 focus:ring-blue-300 font-medium rounded-md text-sm px-5 py-2.5 focus:outline-none flex items-center justify-center gap-x-2">
                    <i class="ti ti-photo text-lg"></i>
                    <p>Upload</p>
                </label>

                <input class="hidden" type="file" id="inputImage">

                <button id="squareButton" type="button" class="w-full text-center text-gray-200 border border-blue-500 focus:ring-4 focus:ring-blue-300 font-medium rounded-md text-sm px-5 py-2.5 focus:outline-none flex items-center justify-center gap-x-2">
                    <i class="ti ti-square text-lg"></i>
                    <p>Square (AR)</p>
                </button>

                <button id="dynamicButton" type="button" class="w-full text-center border border-blue-500 text-gray-200 focus:ring-4 focus:ring-blue-300 font-medium rounded-md text-sm px-5 py-2.5 focus:outline-none flex items-center justify-center gap-x-2">
                    <i class="ti ti-rectangle text-lg"></i>
                    <p>Dynamic (AR)</p>
                </button>

                <button id="cropButton" type="button" class="w-full text-center text-gray-200 bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-md text-sm px-5 py-2.5 focus:outline-none flex items-center justify-center gap-x-2">
                    <i class="ti ti-crop text-lg"></i>
                    <p>Crop</p>
                </button>
            </section>
        </section>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        let cropper;
        const inputImage = document.getElementById('inputImage');
        const image = document.getElementById('image');
        const cropButton = document.getElementById('cropButton');
        const squareButton = document.getElementById('squareButton');
        const dynamicButton = document.getElementById('dynamicButton');
        const croppedImage = document.getElementById('croppedImage');

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
                        viewMode: 2,
                        aspectRatio: NaN // Setting NaN to make aspect ratio dynamic
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        squareButton.addEventListener('click', function() {
            if (cropper) {
                cropper.setAspectRatio(1);
            }
        });

        dynamicButton.addEventListener('click', function() {
            if (cropper) {
                cropper.setAspectRatio(NaN);
            }
        });

        cropButton.addEventListener('click', function() {
            if (cropper) {
                const croppedCanvas = cropper.getCroppedCanvas();
                const croppedImageURL = croppedCanvas.toDataURL('image/png');
                croppedImage.src = croppedImageURL;

                fetch('crop_profile_pic_handler.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            image: croppedImageURL
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