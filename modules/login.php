<?php defined('BASEPATH') OR exit('no direct script access allowed');

$is_index = true;
include "template/header.php";
?>

<?php 
if ($_POST or isset($_GET['read'])) {

	// login from previous data
	if (isset($_GET['read'])) {
		$filename = strip_tags($_GET['read']);
		$file = './storage/login/'.$filename;
		if (file_exists($file)) {
			$read = file_get_contents($file);
			$read = json_decode($read,true);
			$_POST['cookie'] = $read['cookie'];
		}
	}

	include "modules/login/process.php";
}else{	
	include "modules/login/index.php";
}
?>

<?php
include "template/footer.php";
?>