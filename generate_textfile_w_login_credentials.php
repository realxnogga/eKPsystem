<?php

session_start();

$email = $_SESSION['email'];
$password = $_SESSION['password']; // not hashed

$utype = $_SESSION['user_type'];
$brgy_name = $_SESSION['barangay_name'];
$munic_name = $_SESSION['municipality_name'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['generatetextfile'])) {

        $homeDrive = getenv('HOMEDRIVE');
        $homePath = getenv('HOMEPATH');
        $desktopPath = $homeDrive . $homePath . "\\Desktop\\";

        $fileName = $desktopPath . "Ekp_Login_Credential_" . date("Ymd_His") . ".txt";

        $file = fopen($fileName, "w");

        function whatTypeFunc($utype, $brgy_name, $munic_name)
        {
            if ($utype === 'user') {
                return 'For user of barangay ' . $brgy_name . '         ' . 'Created on ' . date("M d Y H:i:s");
            }
            if ($utype === 'admin') {
                return 'For admin of ' . $munic_name . '         ' . 'Created on ' . date("M d Y H:i:s");
            }
            if ($utype === 'assessor') {
                return 'For assessor of ' . $munic_name . '         ' . 'Created on ' . date("M d Y H:i:s");
            }
        }

        $StringTemp = whatTypeFunc($utype, $brgy_name, $munic_name);

        if ($file) {
            $text = "$StringTemp\n\nEmail: $email\nPassword: $password";

            fwrite($file, $text);

            fclose($file);

            switch ($utype) {
                case 'user':
                    header("Location: user_setting.php?generate_file_message=success");
                    exit();
        
                case 'admin':
                    header("Location: admin_setting.php?generate_file_message=success");
                    exit();
            }
        } else {
            switch ($utype) {
                case 'user':
                    header("Location: user_setting.php?generate_file_message=failed");
                    exit();

                case 'admin':
                    header("Location: admin_setting.php?generate_file_message=failed");
                    exit();
            }
        }
    }
}
