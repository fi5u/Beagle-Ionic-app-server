<?php

ini_set('display_errors', 1);

$data = file_get_contents("php://input");
$obj_data = json_decode($data);

include_once 'store.connect.credentials.php';

$mysqli = new mysqli($host, $user, $pass, 'd24');

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');