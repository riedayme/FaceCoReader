<?php 

$webconfig = [
'title' => 'FaceCoReader',
'description' => 'Extract Comment from Facebook Post with cookie'
];

$appinfo = [
'name' => 'FaceCoReader',
'version' => 'v1.0',
'creator' => 'FaanTeyki',
'contact' => 'https://fb.com/faanteyki'
];

// start session
session_start();

// set timezone
date_default_timezone_set('Asia/Jakarta');