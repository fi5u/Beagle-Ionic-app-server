<?php

include_once 'store.connect.php';

$document = get_document($data);

if (is_null($document)) {
    // Insert document
    $document = build_document($data);
    $inserted = insert_document($collection, $document);
} else {
    // Update document
    
    // Check if url is already present
    if (!in_array($data->url, $document['urls'])) {
        // Add to array
        $document['urls'][] = $data->url;
        
        // Update the document with new url
        $updated = update_document($collection, $criteria, $new_object);
    }
}

function get_document($template) {
    $document = $collection->findOne(array('template' => $template->template));
    return $document;
}

function build_document($template) {
    $document = array(
        'template'  => $template->template,
        'title'     => $template->title,
        'space'     => $template->space,
        'active'    => true,
        'verified'  => false,
        'urls'      => array()
    );

    if (isset($template->url)) {
        $document['urls'][] = $template->url;
    }
    
    return $document;
}

function insert_document($collection, $document) {
    return $collection->insert($document);
}

function update_document($collection, $criteria, $new_object) {
    return $collection->update($criteria, $new_object);
}