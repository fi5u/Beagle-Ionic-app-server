<?php

include_once 'store.connect.php';

if (is_null(get_document_exits)) {
    // Insert document
    
} else {
    // Update document
}

function get_document_exits($criteria) {
    $document = $collection->findOne(array('template' => $data->template));
    return $document;
}

