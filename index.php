<?php define('BASEPATH', true); // protect script from direct access

require "includes/helper.php";
require "includes/config.php";

switch (@$_GET['module']) {	

	case 'logout':
	include "modules/logout.php";
	break;

	case 'history':
	include "modules/history.php";
	break;

	default:
	if (!isset($_SESSION['login'])) {
		include "modules/login.php";
	}else{
		include "modules/app.php";
	}
	break;
}
?>