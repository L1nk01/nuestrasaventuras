<?php
$database = [
  'host' => 'localhost',
  'dbname' => 'nuestrasaventuras',
  'username' => 'root',
  'password' => '',
  'charset' => 'utf8'
];

$folderPath = dirname($_SERVER['SCRIPT_NAME']);
$urlPath = $_SERVER['REQUEST_URI'];
$url = substr($urlPath, strlen($folderPath));

define('URL', $url);