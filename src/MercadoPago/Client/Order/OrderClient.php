<?php

namespace MercadoPago\Client\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\MercadoPagoClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\HttpMethod;
use MercadoPago\Net\MPHttpClient;
use MercadoPago\Net\MPSearchRequest;
use MercadoPago\Resources\Order;
use MercadoPago\Resources\OrderSearch;
use MercadoPago\Serialization\Serializer;

/** Client for the Orders API (`/v1/orders`). */
final class OrderClient extends MercadoPagoClient
{
    private const URL = "/v1/orders";
    private const URL_WITH_ID = "/v1/orders/%s";
    private const URL_CANCEL = "/v1/orders/%s/cancel";
    private const URL_PROCESS = "/v1/orders/%s/process";
    private const URL_CAPTURE = "/v1/orders/%s/capture";
    private const URL_REFUND = "/v1/orders/%s/refund";

    public function __construct(?MPHttpClient $MPHttpClient = null)
    {
        parent::__construct($MPHttpClient ?: MercadoPagoConfig::getHttpClient());
    }

    public function create(array $request, ?RequestOptions $request_options = null): Order
    {
        return $this->sendOrder(self::URL, HttpMethod::POST, $request, null, $request_options);
    }

    public function search(MPSearchRequest $request, ?RequestOptions $request_options = null): OrderSearch
    {
        $response = parent::send(self::URL, HttpMethod::GET, null, $request->getParameters(), $request_options);
        $result = Serializer::deserializeFromJson(OrderSearch::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    public function get(string $id, ?RequestOptions $request_options = null): Order
    {
        return $this->sendOrder(sprintf(self::URL_WITH_ID, rawurlencode($id)), HttpMethod::GET, null, null, $request_options);
    }

    public function cancel(string $order_id, ?RequestOptions $request_options = null): Order
    {
        return $this->sendOrder(sprintf(self::URL_CANCEL, rawurlencode($order_id)), HttpMethod::POST, null, null, $request_options);
    }

    public function process(string $order_id, ?RequestOptions $request_options = null): Order
    {
        return $this->sendOrder(sprintf(self::URL_PROCESS, rawurlencode($order_id)), HttpMethod::POST, null, null, $request_options);
    }

    public function capture(string $order_id, ?RequestOptions $request_options = null): Order
    {
        return $this->sendOrder(sprintf(self::URL_CAPTURE, rawurlencode($order_id)), HttpMethod::POST, null, null, $request_options);
    }

    public function refund(string $order_id, ?array $request = null, ?RequestOptions $request_options = null): Order
    {
        return $this->sendOrder(sprintf(self::URL_REFUND, rawurlencode($order_id)), HttpMethod::POST, $request, null, $request_options);
    }

    /** @param array<string,mixed>|null $request @param array<string,mixed>|null $query */
    private function sendOrder(string $path, string $method, ?array $request, ?array $query, ?RequestOptions $options): Order
    {
        $payload = $request === null ? null : json_encode($request);
        $response = parent::send($path, $method, $payload, $query, $options);
        $result = Serializer::deserializeFromJson(Order::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }
}