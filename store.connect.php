<?php

ini_set('display_errors', 1);

// Connect to mongodb
$m = new MongoClient();

// Select a database
$db = $m->d24_search;

// Set the collection
$collection = $db->templates;

//$input = file_get_contents("php://input");
$input = '{"template":"www.amazon.co.uk/[?]/s?ie=UTF8&page=1&rh=i:aps,k:[?]","title":"Amazon.co.uk: Low Prices in Electronics, Books, Sports Equipment & more","space":"-","secure":"false","url":"www.amazon.co.uk","statSharedID":null}';

$data = json_decode($input);
