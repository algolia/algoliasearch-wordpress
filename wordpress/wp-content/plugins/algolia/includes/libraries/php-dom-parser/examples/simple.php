<?php

require_once __DIR__ . '/../vendor/autoload.php';

$article = file_get_contents('https://blog.algolia.com/how-we-re-invented-our-office-space-in-paris/');

$parser = new \Algolia\DOMParser();
$parser->setExcludeSelectors(array(
    'pre',
    '.entry-meta',
    'div.rp4wp-related-posts'
));
$parser->setRootSelector('article.post');

$records = $parser->parse($article);

$json = json_encode($records, JSON_PRETTY_PRINT);

echo $json;
