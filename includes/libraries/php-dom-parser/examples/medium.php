<?php
require_once __DIR__ . '/../vendor/autoload.php';

$article = file_get_contents('https://medium.engineering/the-stack-that-helped-medium-drive-2-6-millennia-of-reading-time-e56801f7c492');

$parser = new \Algolia\DOMParser();
$parser->setRootSelector('main');

$records = $parser->parse($article);

$json = json_encode($records, JSON_PRETTY_PRINT);

echo $json;

