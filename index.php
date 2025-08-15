<?php

use DiDom\Document;
use GuzzleHttp\Client;
//php index.php https://сайт.com
set_time_limit(0);
ini_set('memory_limit', '-1');


require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/function.php';


if (!empty($argv[1])) {
    $client = new Client(['verify' => false]);
    $document = new Document();
    $url = $argv[1];

    echo "Starting to process...\n";
    $file = get_html($url, $client);
    $document->loadHtml($file);

    $pagesTitle = $document->first('h1.main-h')->text();
    $pagesTitle = preg_replace('/[<>:"\/\\|?*]/', ' ', $pagesTitle);
    $pagesCount = get_pages_count($document);
    $productsData = [];

    for ($i = 1; $i <= $pagesCount; $i++) {
        echo "Processing page " . $i . " of " . $pagesCount . "...\n";
        sleep(rand(1, 2));

        if ($i > 1) {
            if (parse_url($url, PHP_URL_QUERY)) {
                $url = $argv[1] . '&filter/page=' . $i . "/";
            } else {
                $url = $argv[1] . 'filter/page=' . $i . "/";
            }

            $file = get_html($url, $client);
            $document->loadHtml($file);
        }
        $productsData = array_merge($productsData, get_products($document, $client, $url));
    }
    $products_cnt = count($productsData);
    file_put_contents("{$pagesTitle}.json", json_encode($productsData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "\n========================================\n";
    echo 'Completed processing ' . $products_cnt . ' products.';
   }
