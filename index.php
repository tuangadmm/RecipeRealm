<?php

require_once __DIR__ . '/vendor/autoload.php';

$raw = $_SERVER['REQUEST_URI'];

echo '<pre>';
$path = explode('/', $raw);

var_dump($path);

echo '</pre>';
$controller = $path[1];
$action = $path[2];
$params = preg_split( "[\?]", $path[count($path)-1])[1];
$pairs = explode('&', $params);

echo $params;