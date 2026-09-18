<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken('<ACCESS_TOKEN>');

$client = new OrderClient();
$order_id = '<ORDER_ID>';
$request = [
    'transactions' => [
        [
            'id' => '<TRANSACTION_ID>',
            'amount' => '100.00',
        ],
    ],
];

$request_options = new RequestOptions();
$request_options->setCustomHeaders(['X-Idempotency-Key: <SOME_UNIQUE_VALUE>']);

try {
    $order = $client->confirm($order_id, $request, $request_options);
    echo 'Order ID: ' . $order->id . PHP_EOL;
    echo 'Status: ' . $order->status . PHP_EOL;
} catch (MPApiException $e) {
    echo 'Status code: ' . $e->getApiResponse()->getStatusCode() . PHP_EOL;
    echo 'Content: ';
    var_dump($e->getApiResponse()->getContent());
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}