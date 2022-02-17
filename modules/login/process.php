<?php defined('BASEPATH') OR exit('no direct script access allowed');

require "library/FaceCoReader.php";

$fb = new FaceCoReader();


// check if cookie is json format
if ($fb->isJson($_POST['cookie'])) {
	// convert
	$_POST['cookie'] = $fb->ReadEditThisCookie($_POST['cookie']);
}

$login = $fb->Auth($_POST['cookie']);

if ($login['status']) {
	$_SESSION['login'] = $login['response'];
}else {
	$_SESSION['error']['message'] = "<strong>Oops</strong>, ".$login['response'];
}

header("location: ./");