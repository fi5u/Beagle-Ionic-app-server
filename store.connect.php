<?php

$data = file_get_contents("php://input");
$obj_data = json_decode($data);

ini_set('display_errors', $obj_data->is_test ? 1 : 0);

$db = ($obj_data->is_test === true || $obj_data->is_test === 'true') ? 'test_query' : 'query';

$uri = 'mongodb://fisu:3vkOtvEW7bG6d@ds059471.mongolab.com:59471/' . $db;
$options = array("connectTimeoutMS" => 30000);

$tries = 0;
$caught = true;
while ($tries < 3 && $caught) {
    try {
        $caught = false;
        $client = new MongoClient($uri, $options);
        $collection = $client->{$db}->{$fetch_collection};
    } catch (MongoConnectionException $e) {
        $success['type'] = false;
        $success['msg'][] = 'Error connecting to MongoDB server';
        $caught = true;
    } catch ( MongoException $e ) {
        $success['type'] = false;
        $success['msg'][] = 'Mongo Error: ' . $e->getMessage();
        $caught = true;
    } catch ( Exception $e ) {
        $success['type'] = false;
        $success['msg'][] = 'Error: ' . $e->getMessage();
        $caught = true;
    }
}