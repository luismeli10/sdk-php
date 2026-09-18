<?php

namespace Examples\Order;

require_once '../../vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken("<ACCESS_TOKEN>");
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

$client = new OrderClient();
$request_options = new RequestOptions();
$request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);

try {
    $order = $client->confirm("<ORDER_ID>", [
        "transactions" => [
            ["id" => "<TRANSACTION_ID>", "amount" => "100.00"],
        ],
    ], $request_options);

    echo "Order ID: " . $order->id . "\n";
    echo "Status: " . $order->status . "\n";
} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    var_dump($e->getApiResponse()->getContent());
} catch (\Exception $e) {
    echo $e->getMessage();
}