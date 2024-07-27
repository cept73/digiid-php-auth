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
require_once __DIR__ . '/classes/users.php';
// DigiID is required for login (do not modify)
// DAO could be replaced by your CMS/FRAMEWORK database classes
require_once __DIR__ . '/classes/DigiID.php';
require_once __DIR__ . '/classes/DAO.php';

// Current stored value
$user_addr = $user_info = false;
// He is already specify QR
if (isset($_SESSION['user']['address'])) {
	// Load all we already know about user
	$user_addr = $_SESSION['user']['address'];
	if (!empty($_SESSION['user']['info'])) {
        $user_info = $_SESSION['user']['info'];
    }

	// He is logged fully
	if ($user_addr && $user_info) {
		header ('location: dashboard.php');
		exit;
	}
}

// QR not activated yet?
// 1 - Scan QR first. 2 - Wait details for registration
$step = (!isset($_SESSION['user'])) ? 1 : 2;

$digiid = new DigiID();
// generate a nonce
$nonce = $digiid->generateNonce();
// build uri with nonce, nonce is optional, but we pre-calculate it to avoid extracting it later
$digiid_uri = $digiid->buildURI(DIGIID_SERVER_URL . 'callback.php', $nonce);

// Insert nonce + IP in the database to avoid an attacker go and try several nonces
// This will only allow one nonce per IP, but it could be easily modified to allow severals per IP
// (this is deleted after an user successfully log in the system, so only will collide if two or more users try to log in at the same time)
$dao = new DAO();
$result = $dao->insert($nonce, @$_SERVER['REMOTE_ADDR']);
if ($dao->error) {
    exit;
}

if (!$result) {
	echo '<pre>';
	echo "Database fail\n";
	var_dump($dao);
    echo '</pre>';
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
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="css/main.css?060319">
<?php if (DIGIID_GOOGLE_ANALYTICS_TAG) : ?><!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?= DIGIID_GOOGLE_ANALYTICS_TAG ?>"></script>
	<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date()); gtag('config', '<?= DIGIID_GOOGLE_ANALYTICS_TAG ?>');</script>
<?php endif ?>
</head>
<body>
	
	<div class="limiter">
		<div class="container-login">
			<div class="wrap-login">
				<div id="step1" class="login-form hidden">
					<div class="bigscreen-padding hidden-xs"></div>
					<span class="login-form-title" style="padding-bottom: 20px">
						Login or Register:
					</span>
					<div class="center">
						<div><img id="qr" class="DigiQR" alt="Click on QRcode to activate compatible desktop wallet"
                                  uri="<?= $digiid_uri ?>" size="300" logo="2" r="1" /></div>
						<p class="comment">Scan it from your mobile phone. Requires DigiByte application:</p>
						<p class="applications">
							<a href="https://itunes.apple.com/us/app/coinomi-wallet/id1333588809" target="_blank"><img src="images/appstore.png" alt="iOS" height="32px" /></a>
							<a href="https://play.google.com/store/apps/details?id=com.coinomi.wallet" target="_blank"><img src="images/android.png" alt="Android" height="32px" /></a>
						</p>
					</div>
				</div>
				<div id="step2" class="login-form hidden">
					<div class="bigscreen-padding hidden-xs"></div>
					<form id="regform" action="<?= DIGIID_SERVER_URL ?>register.php" method="post">
					<span class="login-form-title" style="padding-bottom: 42px;">
						Fill the form:
					</span>
					<div class="wrap-input100">
						<input class="input100" type="text" name="fio" required="true">
						<span class="focus-input100"></span>
						<span class="label-input100">Your name</span>
					</div>
					<div class="container-login-form-btn">
						<input type="submit" class="login-form-btn main" value="Register" />
					</div>
					</form>
					<form action="<?= DIGIID_SERVER_URL ?>logout.php" method="post">
					<div class="container-login-form-btn" style="margin-top:5px">
						<input type="submit" class="login-form-btn" value="Cancel" />
					</div>
					</form>
				</div>

				<div class="login-more">
				</div>
			</div>
		</div>
	</div>
	<!-- For demo - you may remove this link -->
	<div id="source-link"><a title="Download it in open source" href="https://github.com/cept73/digiid-php-auth"><img src="images/open-source-code.png" /></a></div>
	
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script src="js/digiQR.min.js"></script>
	<script>let step=<?= $step ?>; const nonce='<?= $nonce ?>';</script>
	<script src="js/main.js"></script>
</body>
</html>
