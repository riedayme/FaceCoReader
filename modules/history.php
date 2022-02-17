<?php defined('BASEPATH') OR exit('no direct script access allowed');
include "template/header.php";
?>

<?php 
if (@$_GET['read']) {
	include "modules/history/read.php";
}else{
	include "modules/history/index.php";
}
?>

<?php
include "template/footer.php";
?>