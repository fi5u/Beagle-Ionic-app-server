<?php

include_once 'store.connect.pdo.php';

$query = "SELECT template, title, space, secure FROM search_templates WHERE url=?";

$statement = $pdo->prepare($query);
$statement->execute(array(get_formatted_url($obj_data->url)));

$rows = $statement->fetchAll();

$pdo = null;

$top_template = get_top_value($rows, 'template');
$top_title = get_top_value($rows, 'title');
$top_space = get_top_value($rows, 'space');
$top_secure = get_top_value($rows, 'secure');

if ($top_template !== false ||
    $top_title !== false ||
    $top_space !== false ||
    $top_secure !== false
) {
    $template_data = array(
        'template' => build_template($top_template, $top_secure),
        'title'    => $top_title,
        'space'    => $top_space
    );
} else {
    $template_data = false;
}

echo json_encode(array('data' => $template_data));

/**
 * Trim http:// or https:// and trailing slash
 * @param  string $title
 * @return string
 */
function get_formatted_url($url) {
    return preg_replace('/(http(|s):\/\/)/i', '', rtrim($url, '/'));
}

function get_top_value($arr, $key) {
    $top_value_count = [];
    for ($i = 0; $i < count($arr); $i++) {
        $top_value_key = $arr[$i][$key];
        if (!isset($top_value_count[$top_value_key])) {
            $top_value_count[$top_value_key] = 1;
        } else {
            ++$top_value_count[$top_value_key];
        }
    }

    if (count($top_value_count) === 0) {
        return false;
    }

    arsort($top_value_count);
    reset($top_value_count);
    return key($top_value_count);
}

function build_template($template, $is_secure) {
    $built_template = $is_secure === 'true' || $is_secure === true ? 'https://' . $template : 'http://' . $template;
    return $built_template;
}