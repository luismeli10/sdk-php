<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken('<ACCESS_TOKEN>');

$client = new OrderClient();
$order_id = '<ORDER_ID>';
$request = [
    'type' => 'expired',
];

try {
    $response = $client->createEvent($order_id, $request);
    echo 'Event accepted with status: ' . $response->getStatusCode() . PHP_EOL;
    // A successful event simulation returns HTTP 204 with no response body.
} catch (MPApiException $e) {
    echo 'Status code: ' . $e->getApiResponse()->getStatusCode() . PHP_EOL;
    echo 'Content: ';
    var_dump($e->getApiResponse()->getContent());
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}