<?php  

/**
* Read Cookie
*/
$read_cookie = json_decode($_COOKIE['login'],true);
$userid = $read_cookie['value'];

$login = file_get_contents('./storage/login/'.$userid.'.json');
$login = json_decode($login,true);

require "library/FaceCoReader.php";

$fb = new FaceCoReader();

// check cookie
$check = $fb->CheckCookie($login['cookie']);
if ($check['status']) {
	// set session
	$_SESSION['login'] = $login;
}else{
	// remove cookie
	setcookie('login', false, time()-1000, '/');
}

header("location: ./");