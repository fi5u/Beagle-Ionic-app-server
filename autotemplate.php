<?php
ini_set('display_errors', 1);
$s = file_get_contents(getcwd() . '/autotemplate.js');
$temp_filename = tempnam(sys_get_temp_dir(), 'UPJSB');
file_put_contents($temp_filename, $s);

$url = $_GET['url'];
$random_words = get_random_words();
$rand_word1 = $random_words[0];
$rand_word2 = $random_words[1];

$command = "/usr/local/bin/phantomjs " . escapeshellarg($temp_filename) . " " . escapeshellarg($url) . " " . escapeshellarg($rand_word1) . " " . escapeshellarg($rand_word2);

$response = json_decode(shell_exec($command));
$response->terms = $random_words;

echo json_encode($response);

function get_random_words() {
    $words = ['ommiad', 'johnston', 'hemikaryon', 'militarily', 'opsonoid', 'mignonette', 'unclean', 'chirrup', 'bathetic', 'transistorizing', 'unreplevisable', 'dupion', 'unslatted', 'eugenic', 'ashplant', 'ratty', 'outate', 'mythicising', 'hindi', 'associationism', 'anuran', 'fairness', 'imprimatur', 'humpiness', 'montaigne', 'lndler', 'supercabinet', 'autumni', 'uncrushable', 'luminesced', 'brahe', 'barracouta', 'noncultivatable', 'unconvincing', 'recomputed', 'phytosociologic', 'gemination', 'catadioptric', 'thirtypenny', 'cress', 'prejunior', 'petrarch', 'premegalithic', 'hackettstown', 'trite', 'phlogosis', 'zagut', 'spilt', 'undetained', 'nonmasteries', 'azine', 'grosz', 'optime', 'nonelectrified', 'acquainted', 'preknown', 'pseudobankrupt', 'reassignation', 'illinoisan', 'catena', 'xeres', 'erosive', 'nonresponsive', 'anteriorly', 'lignitic', 'delubrum', 'epidermic', 'immedicable', 'wynnewood', 'caernarvon', 'bibliolater', 'distinctionless', 'bacterization', 'rakishness', 'cherry', 'paralexia', 'dissonance', 'verify', 'deducing', 'insanitary', 'ferryman', 'janis', 'cullis', 'gymnosperm', 'salesian', 'shorts', 'finical', 'calix', 'supertanker', 'pretempt', 'arcade', 'charitably', 'jonah', 'transmissively', 'checkrow', 'deservedly', 'manit', 'pentene', 'preoblongata', 'drinking', 'unlegible', 'tricotine', 'precollegiate', 'edmonton', 'defoliant', 'grotesqueness', 'sunbonnet', 'hencoop', 'endosarcous', 'herndon', 'circumnutatory', 'hypermoral', 'overrule', 'bikila', 'overblithe', 'anaphase', 'introspective', 'otoscopy', 'nondefinitive', 'overcentralizing', 'bevelling', 'amelioration', 'busing', 'rigorous', 'outwards', 'unchaptered', 'prechoose', 'kwangtung', 'bungle', 'cheektowaga', 'loyang', 'flawless', 'kayoed', 'hemorrhage', 'idlesse', 'journalist', 'kitchenette', 'incarcerating', 'benbow', 'heartwood', 'harvestless', 'lemuralia', 'greenlawn', 'pilose', 'progging', 'rienzo', 'troublous', 'serafin', 'myohemoglobinuria', 'weaver', 'supersensitising', 'ellipsoid', 'battledore', 'promulge', 'sovereignty', 'decompression', 'barberite', 'transformer', 'unwesternized', 'teleprompter', 'messene', 'insolubly', 'capacitor', 'makings', 'chivalrousness', 'viceregency', 'christian', 'nonhostility', 'unwillable', 'remanufactured', 'latency', 'nonministerial', 'sakkoi', 'mutiny', 'mauve', 'earlene', 'saddle', 'giblet', 'excitation', 'pathosis', 'consociating', 'cyclone', 'nonputrescent', 'cetologist', 'disunited', 'hemistich', 'postapoplectic', 'husbandage', 'pseudoascetical', 'tabularized', 'tomium', 'feodality', 'enhancement', 'carronade', 'overcommercializing', 'verlaine', 'bendy', 'mamaluke', 'cabal'];
    $random_words = array_rand($words, 2);
    return [$words[$random_words[0]], $words[$random_words[1]]];
}