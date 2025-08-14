<?php

use DiDom\Document;
use GuzzleHttp\Client;

function get_html($url, Client $client)
{

    $resp = $client->get($url);
    return $resp->getBody()->getContents();
}

function get_pages_count(Document $document)
{
    $pagination = $document->find('.pager__item.j-catalog-pagination-btn');
    if (count($pagination) > 1) {
        return (int)$pagination[count($pagination) - 2]->text();
    } else {
        return 1;
    }
}
function get_products(Document $document, Client $client, $baseUrl)
{
    static $products_cnt = 1;
    $products = $document->find('.catalogCard.j-catalog-card');
    $productsData = [];
    echo (count($products) . " products found on this page.\n");
    foreach ($products as $product) {
        sleep(rand(1, 2));
        echo "Processing product " . $products_cnt . "...\n";
        $url = $product->first('.catalogCard-image ')->getAttribute('href');

        // Convert relative URL to absolute URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $parsedBase = parse_url($baseUrl);
            $baseHost = $parsedBase['scheme'] . '://' . $parsedBase['host'];
            if (isset($parsedBase['port'])) {
                $baseHost .= ':' . $parsedBase['port'];
            }
            $url = $baseHost . $url;
        }
        $productsData[$products_cnt] = get_product($document, $client, $url, $products_cnt);
        $products_cnt++;
    }
    return $productsData;
}

function get_product(Document $document, Client $client, $url, $products_cnt)
{
    global $pagesTitle;

    $file = get_html($url, $client);
    $document->loadHtml($file);

    $product['title'] = $document->first('h1.product-title')->text();
    $product['url'] = $url;

    $price = $document->first('.product-price__item')->text();
    $product['price'] = trim(preg_replace('/\s+/', ' ', $price));

    // Images
    $imgPath = $document->first('img.gallery__photo-img')->getAttribute('src');

     // Convert relative URL to absolute URL
    if (!filter_var($imgPath, FILTER_VALIDATE_URL)) {
        $parsedBase = parse_url($url);
        $baseHost = $parsedBase['scheme'] . '://' . $parsedBase['host'];
        if (isset($parsedBase['port'])) {
            $baseHost .= ':' . $parsedBase['port'];
        }
        $imgPath = $baseHost . $imgPath;
    }

    $product['img'] = $products_cnt . '.' . get_ext($imgPath);
  
    if (!is_dir("img/{$pagesTitle}")) {
        mkdir("img/{$pagesTitle}");
    }
    file_put_contents("img/{$pagesTitle}/{$product['img']}", file_get_contents($imgPath));

    return ($product);
}
function get_ext($fileName) // Extract the file extension from the URL
{
    $data = explode('.', $fileName);
    $data = explode('?', end($data));
    return ($data[0]);
}
