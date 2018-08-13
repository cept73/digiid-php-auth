<?php
/*
Copyright 2014 Daniel Esteban

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

session_start ();

// Require users operations
require_once dirname(__FILE__) . "/classes/users.php";

// Current stored value
$user_addr = $user_info = false;
// He is already specify QR
if (isset($_SESSION['user']['address'])) 
{
	// Load all we already know about user
	$user_addr = $_SESSION['user']['address'];
	if (!empty($_SESSION['user']['info']))
		$user_info = $_SESSION['user']['info'];

	// He is logged fully
	if ($user_addr && $user_info) {
		header ('location: dashboard.php');
		exit;
	}
}

// QR not activated yet?
// 1 - Scan QR first. 2 - Wait details for registration
$step = (!isset($_SESSION['user'])) ? 1 : 2;

// DigiID is required for login (do not modify)
// DAO could be replace by your CMS/FRAMEWORK database classes
require_once dirname(__FILE__) . "/classes/DigiID.php";
require_once dirname(__FILE__) . "/classes/DAO.php";
$digiid = new DigiID();
// generate a nonce
$nonce = $digiid->generateNonce();
// build uri with nonce, nonce is optional, but we pre-calculate it to avoid extracting it later
$digiid_uri = $digiid->buildURI(SERVER_URL . 'callback.php', $nonce);

// Insert nonce + IP in the database to avoid an attacker go and try several nonces
// This will only allow one nonce per IP, but it could be easily modified to allow severals per IP
// (this is deleted after an user successfully log in the system, so only will collide if two or more users try to log in at the same time)
$dao = new DAO();
$result = $dao->insert($nonce, @$_SERVER['REMOTE_ADDR']);
if(!$result)
{
	echo "<pre>";
	echo "Database failer\n";
	var_dump($dao);
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Digi-ID demo site</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/main.css">
</head>
<body>
	
	<div class="limiter">
		<div class="container-login">
			<div class="wrap-login">
				<div id="step1" class="login-form hidden">
					<span class="login-form-title" style="padding-bottom: 20px">
						Login or Register:
					</span>
					<div class="center">
						<a href="<?= $digiid_uri ?>"><div><img id="qr" alt="Click on QRcode to activate compatible desktop wallet" border="0" src="<?= $digiid->qrCode($digiid_uri) ?>" /></div></a>
						<p class="comment">Scan it from your mobile phone. Requires DigiByte application:</p>
						<p class="applications">
							<a href="https://itunes.apple.com/us/app/digibyte/id1378061425" target="_blank"><img src="images/appstore.png" height="32px" /></a>
							<a href="https://play.google.com/store/apps/details?id=io.digibyte" target="_blank"><img src="images/android.png" height="32px" /></a>
						</p>
					</div>
				</div>
				<div id="step2" class="login-form hidden">
					<form id="regform" action="<?= SERVER_URL ?>register.php" method="post">
					<span class="login-form-title" style="padding-bottom: 42px;">
						Fill the form:
					</span>
					<div class="wrap-input100">
						<input class="input100" type="text" name="fio" required="true">
						<span class="focus-input100"></span>
						<span class="label-input100">Your name</span>
					</div>
					<div class="container-login-form-btn">
						<input type="submit" class="login-form-btn" value="Register" />
					</div>
					</form>
					<form action="<?= SERVER_URL ?>logout.php" method="post">
					<div class="container-login-form-btn" style="margin-top:5px">
						<input type="submit" class="login-form-btn" value="Cancel" />
					</div>
					</form>
				</div>

				<div class="login-more" style="background-image: url(images/bg-01.jpg);">
				</div>
			</div>
		</div>
	</div>
	
	<script src="http://code.jquery.com/jquery-3.3.1.min.js"></script>
	<script>var step=<?= $step ?>; var nonce='<?= $nonce ?>';</script>
	<script src="js/main.js"></script>
</body>
</html>