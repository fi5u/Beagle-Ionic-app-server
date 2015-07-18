<?php

// Connect to mongodb
$m = new MongoClient();

// Select a database
$db = $m->d24_search;

// Set the collection
$collection = $db->templates;

$input = file_get_contents('php://input');
//$input = '{"template":"www.houseoffraser.co.uk/on/demandware.store/Sites-hof-Site/default/Search-Show?q=[?]","title":"House of Fraser™ | Clothes, Fashion, Beauty, Home & Electronics","space":"%20","secure":"false","url":"www.houseoffraser.co.uk","statSharedID":null}';

$data = json_decode($input);
