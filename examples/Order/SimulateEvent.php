<?php

namespace Examples\Order;

require_once '../../vendor/autoload.php';

use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken("<ACCESS_TOKEN>");
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

$client = new OrderClient();

try {
    $response = $client->simulateEvent("<ORDER_ID>", [
        "type" => "<EVENT_TYPE>",
    ]);

    echo "Event accepted with status: " . $response->getStatusCode() . "\n";
} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    var_dump($e->getApiResponse()->getContent());
} catch (\Exception $e) {
    echo $e->getMessage();
}