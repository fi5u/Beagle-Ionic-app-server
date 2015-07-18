<?php

include_once 'store.connect.php';
$document = $collection->findOne(array('urls' => get_formatted_url($data->url)));

if (!is_null($document)) {
    $template_data = array(
        'template' => $document['secure'] === 'true' ? 'https://' . $document['template'] : 'http://' . $document['template'],
        'title'    => $document['title'],
        'space'    => $document['space']
    );
    echo json_encode($template_data);
}

/**
 * Trim http:// or https:// and trailing slash
 * @param  string $title
 * @return string
 */
function get_formatted_url($url) {
    return preg_replace('/(http(|s):\/\/)/i', '', rtrim($url, '/'));
}