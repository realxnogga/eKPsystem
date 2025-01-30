<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
	header("Location: login.php");
	exit;
}
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>LTIA Form 3</title>
	<link rel="icon" type="image/x-icon" href="../img/favicon.ico">
	<link rel="stylesheet" href="../assets/css/styles.min.css" />
	<!-- * Bootstrap v5.3.0-alpha1 (https://getbootstrap.com/) -->
	<!-- remove later -->
	<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#E8E8E7]">
	<?php include "../admin_sidebar_header.php"; ?>
	<div class="p-4 sm:ml-44 ">
		<div class="rounded-lg mt-16">
			<div class="card">
				<div class="card-body">
					<div class="menu flex items-center justify-between">
						<button class="bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-md text-white flex items-center" onclick="location.href='adminform2evaluate.php';">
							<i class="ti ti-building-community mr-3"></i> Back
						</button>
					</div>	

					List of Assessors

				</div>
			</div>
		</div>
	</div>		
</body>
</html>