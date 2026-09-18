<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken('<ACCESS_TOKEN>');

$client = new OrderClient();
$order_id = '<ORDER_ID>';

$request_options = new RequestOptions();
$request_options->setCustomHeaders(['X-Idempotency-Key: <SOME_UNIQUE_VALUE>']);

try {
    $refunds = $client->getRefunds($order_id, $request_options);

    foreach ($refunds as $refund) {
        echo 'Refund ID: ' . $refund->id . PHP_EOL;
        echo 'Amount: ' . $refund->amount . PHP_EOL;
        echo 'Status: ' . $refund->status . PHP_EOL;
    }
} catch (MPApiException $e) {
    echo 'Status code: ' . $e->getApiResponse()->getStatusCode() . PHP_EOL;
    echo 'Content: ';
    var_dump($e->getApiResponse()->getContent());
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}