<?php defined('BASEPATH') OR exit('no direct script access allowed');


// remove session
unset($_SESSION['login']);

// remove cookie
setcookie('login', false, time()-1000, '/');

header("location: ./");