<?php

namespace MercadoPago\Client\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\MercadoPagoClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\HttpMethod;
use MercadoPago\Net\MPHttpClient;
use MercadoPago\Net\MPResponse;
use MercadoPago\Resources\Order\Transaction\UpdateTransaction;
use MercadoPago\Resources\Order\Transactions;
use MercadoPago\Serialization\Serializer;

/** Client for `/v1/orders/{order_id}/transactions`. */
final class OrderTransactionClient extends MercadoPagoClient
{
    private const URL = "/v1/orders/%s/transactions";
    private const URL_WITH_ID = "/v1/orders/%s/transactions/%s";

    public function __construct(?MPHttpClient $MPHttpClient = null)
    {
        parent::__construct($MPHttpClient ?: MercadoPagoConfig::getHttpClient());
    }

    public function create(string $order_id, array $request, ?RequestOptions $request_options = null): Transactions
    {
        $response = parent::send(sprintf(self::URL, rawurlencode($order_id)), HttpMethod::POST, json_encode($request), null, $request_options);
        $result = Serializer::deserializeFromJson(Transactions::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    public function update(string $order_id, string $transaction_id, array $request, ?RequestOptions $request_options = null): Payment
    {
        $path = sprintf(self::URL_WITH_ID, rawurlencode($order_id), rawurlencode($transaction_id));
        $response = parent::send($path, HttpMethod::PUT, json_encode($request), null, $request_options);
        $result = Serializer::deserializeFromJson(Payment::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    public function delete(string $order_id, string $transaction_id, ?RequestOptions $request_options = null): MPResponse
    {
        $path = sprintf(self::URL_WITH_ID, rawurlencode($order_id), rawurlencode($transaction_id));
        return parent::send($path, HttpMethod::DELETE, null, null, $request_options);
    }
}