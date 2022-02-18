<?php defined('BASEPATH') OR exit('no direct script access allowed');

require "library/FaceCoReader.php";

$fb = new FaceCoReader();


// check if cookie is json format
if ($fb->isJson($_POST['cookie'])) {
	// convert
	$_POST['cookie'] = $fb->ReadEditThisCookie($_POST['cookie']);
}

$login = $fb->Auth($_POST['cookie']);

// set session
if ($login['status']) {
	
	$_SESSION['login'] = $login['response'];

	// set cookie
	$cookie_expiration_time = time() + (24 * 60 * 60);  // for 1 day
	setcookie("login", json_encode(['value' => $login['response']['userid'], 'expired' => $cookie_expiration_time]), $cookie_expiration_time, "/");

	// save login
	file_put_contents('./storage/login/'.$login['response']['userid'].'.json', json_encode($login['response'],JSON_PRETTY_PRINT));

}else {
	$_SESSION['error']['message'] = "<strong>Oops</strong>, ".$login['response'];
}


header("location: ./");