<?php

ini_set('display_errors', 1);

$data = file_get_contents("php://input");
$obj_data = json_decode($data);

include_once 'store.connect.credentials.php';

$dsn = 'mysql:host=' . $host . ';dbname=d24;charset=utf8';
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
$pdo = new PDO($dsn, $user, $pass, $opt);
