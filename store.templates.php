<?php

include_once 'store.connect.php';

$document = get_document($collection, $data);

if (is_null($document)) {
    // Insert document
    $document = build_document($data);
    $inserted = insert_document($collection, $document);
    echo json_encode($document['_id']);
} else {
    // Update document
    
    // Check if url is already present
    if (!in_array($data->url, $document['urls'])) {
        // Add to array
        $document['urls'][] = $data->url;
        
        // Update the document with new url
        $updated = update_document(
            $collection,
            array('_id' => new MongoId($document['_id'])),
            array('$set' => array('urls' => $document['urls']))
        );
    }

    // Has the template changed?
    if ($document['template'] !== $data->template) {
        $edit = array(
            'template'  => $data->template,
            'timestamp' => date("Y-m-d H:i:s"),
        );
        $document['edits'][] = $edit;

        // Update the document with new url
        $updated = update_document(
            $collection,
            array('_id' => new MongoId($document['_id'])),
            array('$set' => array('edits' => $document['edits']))
        );
    }
}

function get_document($collection, $template) {
    if ($template->statSharedID !== false) {
        // Is update
        $document = $collection->findOne(array('_id' => new MongoId($template->statSharedID)));
    } else {
        // Is new
        $document = $collection->findOne(array('template' => $template->template));
    }

    return $document;
}

function build_document($template) {
    $document = array(
        'template'  => $template->template,
        'title'     => $template->title,
        'space'     => $template->space,
        'active'    => true,
        'urls'      => array(),
        'edits'     => array()
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