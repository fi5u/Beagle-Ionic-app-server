<?php

// Connect to mongodb
$m = new MongoClient();

// Select a database
$db = $m->d24_beagle;

// Set the collection
$collection = $db->tracking;

$input = file_get_contents('php://input');
//$input = '[{"type": "event", "user": "sdj2j32mfls2mldm", "event": "auto url search", "value": "http://www.google.com/", "timestamp": "2016-05-02T19:19:52.731Z"}]';

$data = json_decode($input);
