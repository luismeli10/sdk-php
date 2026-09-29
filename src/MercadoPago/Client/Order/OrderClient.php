<?php

namespace MercadoPago\Client\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\MercadoPagoClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\HttpMethod;
use MercadoPago\Net\MPHttpClient;
use MercadoPago\Net\MPSearchRequest;
use MercadoPago\Resources\Order;
use MercadoPago\Resources\OrderSearch;
use MercadoPago\Serialization\Serializer;

/**
 * Client for the MercadoPago Orders API.
 */
final class OrderClient extends MercadoPagoClient
{
    private const ORDERS_PATH = "/v1/orders";

    public function __construct(?MPHttpClient $http_client = null)
    {
        parent::__construct($http_client ?? MercadoPagoConfig::getHttpClient());
    }

    /**
     * Creates an order.
     *
     * @throws MPApiException
     * @throws \Exception
     */
    public function create(array $request, ?RequestOptions $request_options = null): Order
    {
        $response = $this->send(
            self::ORDERS_PATH,
            HttpMethod::POST,
            json_encode($request),
            null,
            $request_options
        );

        $result = Serializer::deserializeFromJson(Order::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    /**
     * Gets an order by ID.
     *
     * @throws MPApiException
     * @throws \Exception
     */
    public function get(string $id, ?RequestOptions $request_options = null): Order
    {
        $response = $this->send(
            self::ORDERS_PATH . "/" . $id,
            HttpMethod::GET,
            null,
            null,
            $request_options
        );

        $result = Serializer::deserializeFromJson(Order::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    /**
     * Searches orders using pagination and API filters such as begin_date and end_date.
     *
     * @throws MPApiException
     * @throws \Exception
     */
    public function search(MPSearchRequest $search_request, ?RequestOptions $request_options = null): OrderSearch
    {
        $response = $this->send(
            self::ORDERS_PATH,
            HttpMethod::GET,
            null,
            $search_request->getParameters(),
            $request_options
        );

        $result = Serializer::deserializeFromJson(OrderSearch::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    /** @throws MPApiException|\Exception */
    public function cancel(string $order_id, ?RequestOptions $request_options = null): Order
    {
        return $this->executeAction($order_id, "cancel", $request_options);
    }

    /** @throws MPApiException|\Exception */
    public function process(string $order_id, ?RequestOptions $request_options = null): Order
    {
        return $this->executeAction($order_id, "process", $request_options);
    }

    /** @throws MPApiException|\Exception */
    public function capture(string $order_id, ?RequestOptions $request_options = null): Order
    {
        return $this->executeAction($order_id, "capture", $request_options);
    }

    /**
     * Refunds an order. Pass null for a full refund or a request body for a partial refund.
     *
     * @throws MPApiException
     * @throws \Exception
     */
    public function refund(string $order_id, ?array $request = null, ?RequestOptions $request_options = null): Order
    {
        return $this->executeAction($order_id, "refund", $request_options, $request);
    }

    /**
     * Updates a transaction associated with an order.
     *
     * @throws MPApiException
     * @throws \Exception
     */
    public function updateTransaction(
        string $order_id,
        string $transaction_id,
        array $request,
        ?RequestOptions $request_options = null
    ): Order
    {
        $response = $this->send(
            self::ORDERS_PATH . "/" . $order_id . "/transactions/" . $transaction_id,
            HttpMethod::PUT,
            json_encode($request),
            request_options: $request_options
        );

        $result = Serializer::deserializeFromJson(Order::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    private function executeAction(
        string $order_id,
        string $action,
        ?RequestOptions $request_options,
        ?array $request = null
    ): Order
    {
        $response = $this->send(
            self::ORDERS_PATH . "/" . $order_id . "/" . $action,
            HttpMethod::POST,
            $request === null ? null : json_encode($request),
            request_options: $request_options
        );

        $result = Serializer::deserializeFromJson(Order::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }
}