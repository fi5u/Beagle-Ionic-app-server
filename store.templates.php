<?php

include_once 'store.connect.php';

// Setup the return response
$response = array();

if (!isset($obj_data->statSharedID) && !isset($obj_data->template->statSharedID)) {
    save($obj_data->template);
} else {
    if (!isset($obj_data->template)) {
        remove($obj_data->statSharedID);
    } else {
        update($obj_data->template);
    }
}

function save($template) {
    global $mysqli;

    $query = "INSERT INTO search_templates (template, title, space, url, secure) VALUES(?, ?, ?, ?, ?)";
    $statement = $mysqli->prepare($query);
    $statement->bind_param('sssss',
       $template->template,
       $template->title,
       $template->space,
       $template->url,
       $template->secure
    );
    if ($statement->execute()) {
        $response['statSharedID'] = $statement->insert_id;
    } else {
        $response['msg'] = 'Error: ('. $mysqli->errno .') '. $mysqli->error;
    }
    $statement->close();

    echo json_encode($response);
}

function remove($id) {
    global $mysqli;

    $query = "DELETE FROM search_templates WHERE id = ?";
    $statement = $mysqli->prepare($query);
    $statement->bind_param('i', $id);

    if ($statement->execute()) {
        $response['msg'] = 'Successfully deleted id ' . $id;
    } else {
        $response['msg'] = 'Error: ('. $mysqli->errno .') '. $mysqli->error;
    }
    $statement->close();

    echo json_encode($response);
}

function update($template) {
    global $mysqli;

    $query = "UPDATE search_templates SET modified=NOW(), template=?, title=?, space=?, url=?, secure=? WHERE ID=?";
    $statement = $mysqli->prepare($query);
    $statement->bind_param('sssssi',
        $template->template,
        $template->title,
        $template->space,
        $template->url,
        $template->secure,
        $template->statSharedID
    );

    if ($statement->execute()) {
        $response['msg'] = 'Successfully updated id ' . $template->statSharedID;
    } else {
        $response['msg'] = 'Error: ('. $mysqli->errno .') '. $mysqli->error;
    }
    $statement->close();

    echo json_encode($response);
}