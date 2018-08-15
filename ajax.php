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
set_time_limit (5);

// Result by default
$result = array ('address' => false);

// If required param is not passed
if (!isset($_POST['nonce'])) {
    echo json_encode($result);
    exit;
}

require_once dirname(__FILE__) . "/classes/DAO.php";
require_once dirname(__FILE__) . "/classes/users.php";
$dao = new DAO();

// Check if this nonce is logged or not
$address = $dao->address($_POST['nonce'], @$_SERVER['REMOTE_ADDR']);
// Logged
if ($address !== false) {
    // Get info about user from db and store into session
    $user = new token_user($address);
    session_start();
    $result = $_SESSION['user'] = array (
	'address' => $address, 
	'info' => $user->get_info()
	);
}

//return address/false to tell the VIEW it could log in now or not
echo json_encode($result);
