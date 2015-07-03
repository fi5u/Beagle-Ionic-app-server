<?php
// connect to mongodb
$m = new MongoClient();
echo 'Connection to database successfully<br>';

// select a database
$db = $m->d24_search;
echo 'Database d24_search selected<br>';

$collection = $db->templates;
echo 'Collection selected successfully<br>';

//$input = '{"template":{"template":"www.amazon.co.uk/[?]/s?ie=UTF8&page=1&rh=i:aps,k:[?]","title":"Amazon.co.uk: Low Prices in Electronics, Books, Sports Equipment & more","space":"-","secure":"false","url":"www.amazon.co.uk","statSharedID":null}}';
$input = '{"template":"www.amazon.co.uk/[?]/s?ie=UTF8&page=1&rh=i:aps,k:[?]","title":"Amazon.co.uk: Low Prices in Electronics, Books, Sports Equipment & more","space":"-","secure":"false","url":"www.amazon.co.uk","statSharedID":null}';
$data = json_decode($input);

//echo $data->title . '<br>';

// Does document already exist?
$document = $collection->findOne(array('template' => $data->template));
print_r($document);
exit;

$document = array(
    'template'  => 'abc.com/q=[?]',
    'title'     => 'Abc',
    'space'     => '%20',
    'active'    => true,
    'verified'  => true,
    'urls'      => array(
        array(
            'url'       => 'abc.com',
            'secure'    => 'both',
            'www'       => true
        ),
        array(
            'url'       => 'abc.com/search',
            'secure'    => 'both',
            'www'       => true
        )
    ),
    'edits'     => array(
        array(
            'timestamp' => date("Y-m-d H:i:s"),
            'name'      => 'title',
            'values'    => array(
                'before'    => 'Abc',
                'after'     => 'ABC'
            )
        )
    )
);

//$collection->insert($document);
echo 'Document inserted successfully';
