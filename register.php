<?php
/*
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

require_once dirname(__FILE__) . "/config.php";
require_once dirname(__FILE__) . "/classes/users.php";

// Address must be defined for register
if (isset($_SESSION['user']['address']))
{
	// Save it
	$user = new token_user($_SESSION['user']['address']);
	$user->insert ($_POST);
	//$dao = new DAO();
	//$dao->remove($nonce);
	$_SESSION['user']['info'] = $user->get_info ();
}

header ('location: ' . DIGIID_SERVER_URL);
