<?php
$data = file_get_contents("php://input");
$obj_data = json_decode($data);

include_once 'store.connect.credentials.php';

ini_set('display_errors', $obj_data->is_test ? 1 : 0);

$mysqli = new mysqli($host, $user, $pass, 'd24');