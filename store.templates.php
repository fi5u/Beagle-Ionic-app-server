<?php
include_once 'store.connect.php';
$document = get_document($collection, $data);
$rtnArray = array();

if (is_null($document)) {
    // Insert document
    $document = build_document($data);
    $inserted = insert_document($collection, $document);

    $rtnArray['saved'] = $inserted | false;
    $rtnArray['id'] = $document['_id']->{'$id'};

} else {
    // Update document

    // Has the template changed?
    // If the template and/or the space has changed, add to edits
    if ($document['template'] !== $data->template || $document['space'] !== $data->space) {
        $edit = array(
            'template'  => $data->template,
            'edited'    => date('Y-m-d H:i:s'),
            'reviewed'  => false
        );

        if ($document['space'] !== $data->space) {
            $edit['space'] = $data->space;
        }

        $document['edits'][] = $edit;

        // Update the document with new url
        $updated = update_document(
            $collection,
            array('_id' => new MongoId($document['_id'])),
            array('$set' => array('edits' => $document['edits']))
        );

        $rtnArray['saved'] = $updated | false;
        $rtnArray['id'] = $document['_id']->{'$id'};
    } else {
        $rtnArray['saved']      = false;
        $rtnArray['duplicate']  = true;
    }
}

echo json_encode($rtnArray);


function get_document($collection, $template) {
    if (gettype($template->statSharedID === 'NULL')) {
        // Is new
        $document = $collection->findOne(array('template' => $template->template));
    } else {
        // Is update
        $document = $collection->findOne(array('_id' => new MongoId($template->statSharedID)));
    }

    return $document;
}

function build_document($template) {
    $document = array(
        'created'   => date("Y-m-d H:i:s"),
        'template'  => $template->template,
        'title'     => $template->title,
        'space'     => $template->space,
        'secure'    => $template->secure,
        'active'    => true,
        'urls'      => array(),
        'edits'     => array()
    );

    if (isset($template->url) && !empty($template->url)) {
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