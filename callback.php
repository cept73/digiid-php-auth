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

require_once dirname(__FILE__) . "/classes/DigiID.php";
require_once dirname(__FILE__) . "/classes/DAO.php";
require_once dirname(__FILE__) . "/classes/users.php";

$digiid = new DigiID();
$dao = new DAO();

$input = $_POST;
$post_data = json_decode(file_get_contents('php://input'), true);
// SIGNED VIA PHONE WALLET (data is send as payload)
if($post_data!==null) {
    $input = $post_data;
}

// ALL THOSE VARIABLES HAVE TO BE SANITIZED !
$signValid = $digiid->isMessageSignatureValidSafe(@$input['address'], @$input['signature'], @$input['uri']);
$nonce = $digiid->extractNonce($input['uri']);
if($signValid && $dao->checkNonce($nonce) && ($digiid->buildURI(DIGIID_SERVER_URL . 'callback.php', $nonce) === $input['uri'])) {
    $dao->update($nonce, $input['address']);

    session_start();
    $user = new token_user ($input['address']);
    $_SESSION['user'] = array (
	'address' => $input['address'],
	'info' => $user->get_info()
	);

    // SIGNED VIA PHONE WALLET (data is send as payload)
    if($post_data!==null) {
        //DO NOTHING

    } else {
        // SIGNED MANUALLY (data is stored in $_POST+$_REQUEST vs payload)
        // SHOW SOMETHING PRETTY TO THE USER

        header("location: index.php");
    }


    $data = array ('address'=>$input['address'], 'nonce'=>$nonce);
    header('Content-Type: application/json');
    echo json_encode($data);
}