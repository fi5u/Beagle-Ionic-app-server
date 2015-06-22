<?php

$fetch_collection = 'autofails';
include_once 'store.connect.php';

$fail_query = $collection->findOne(array('url' => $obj_data->url));

if (!$fail_query) {
    $set_arr = array(
        'url' => $obj_data->url,
        'status' => array()
    );
    $set_arr['status'][$obj_data->status] = 1;
    $updated = $collection->insert($set_arr);
} else {
    $found = false;
    foreach ($fail_query['status'] as $key => $value) {
        if ($key === $obj_data->status) {
            $found = true;
            break;
        }
    }
    if ($found) {
        ++$fail_query['status'][$obj_data->status];
    } else {
        $fail_query['status'][$obj_data->status] = 1;
    }

    $new_data = array('$set' => $fail_query);
    $updated = $collection->update(array('url' => $obj_data->url), $new_data);
}
