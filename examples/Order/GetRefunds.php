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
    $refunds = $client->getRefunds("<ORDER_ID>", $request_options);

    foreach ($refunds as $refund) {
        echo "Refund ID: " . $refund->id . "\n";
        echo "Amount: " . $refund->amount . "\n";
    }
} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    var_dump($e->getApiResponse()->getContent());
} catch (\Exception $e) {
    echo $e->getMessage();
}