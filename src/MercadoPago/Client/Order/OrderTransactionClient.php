<?php

namespace MercadoPago\Client\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\MercadoPagoClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\HttpMethod;
use MercadoPago\Net\MPHttpClient;
use MercadoPago\Net\MPResponse;
use MercadoPago\Resources\Order\Payment;
use MercadoPago\Resources\Order\PaymentMethod;
use MercadoPago\Resources\Order\Transaction\UpdateTransaction;
use MercadoPago\Resources\Order\Transactions;
use MercadoPago\Serialization\Serializer;

/**
 * Client for the manual Order Transactions API.
 *
 * Adds payments through `/v1/orders/{order_id}/transactions` and updates or
 * deletes one through `/v1/orders/{order_id}/transactions/{transaction_id}`.
 */
final class OrderTransactionClient extends MercadoPagoClient
{
    private const URL = "/v1/orders/%s/transactions";
    private const URL_WITH_ID = self::URL . "/%s";

    /** @param MPHttpClient|null $MPHttpClient Custom HTTP client. Defaults to the SDK global client. */
    public function __construct(?MPHttpClient $MPHttpClient = null)
    {
        parent::__construct($MPHttpClient ?: MercadoPagoConfig::getHttpClient());
    }

    /**
     * Adds payment transactions to a manual-mode order.
     *
     * POST /v1/orders/{order_id}/transactions requires X-Idempotency-Key and
     * a request body containing a payments array of Order Payment resources.
     *
     * @param string $order_id Order ID.
     * @param array{payments: array<int, Payment|array<string,mixed>>} $request Payment transactions to add.
     * @param RequestOptions|null $request_options Per-request configuration overrides, including X-Idempotency-Key.
     * @return Transactions The 201 response containing mapped payments.
     * @throws \MercadoPago\Exceptions\MPApiException When the API returns a non-2xx status code.
     * @throws \Exception On transport-level errors.
     */
    public function create(string $order_id, array $request, ?RequestOptions $request_options = null): Transactions
    {
        $path = sprintf(self::URL, rawurlencode($order_id));
        $response = parent::send($path, HttpMethod::POST, json_encode($request), null, $request_options);
        $result = Serializer::deserializeFromJson(Transactions::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    /**
     * Updates the payment method of a pending manual-order transaction.
     *
     * PUT /v1/orders/{order_id}/transactions/{transaction_id} requires
     * X-Idempotency-Key and a payment_method object.
     *
     * @param string $order_id Order ID.
     * @param string $transaction_id Transaction ID to update.
     * @param array{payment_method: PaymentMethod|array<string,mixed>} $request Payment method update.
     * @param RequestOptions|null $request_options Per-request configuration overrides, including X-Idempotency-Key.
     * @return UpdateTransaction The mapped 200 Order transaction payment.
     * @throws \MercadoPago\Exceptions\MPApiException When the API returns a non-2xx status code.
     * @throws \Exception On transport-level errors.
     */
    public function update(string $order_id, string $transaction_id, array $request, ?RequestOptions $request_options = null): UpdateTransaction
    {
        $path = sprintf(self::URL_WITH_ID, rawurlencode($order_id), rawurlencode($transaction_id));
        $response = parent::send($path, HttpMethod::PUT, json_encode($request), null, $request_options);
        $result = Serializer::deserializeFromJson(UpdateTransaction::class, $response->getContent());
        $result->setResponse($response);
        return $result;
    }

    /**
     * Deletes a transaction from a manual-mode order.
     *
     * DELETE /v1/orders/{order_id}/transactions/{transaction_id} returns 204
     * and does not require X-Idempotency-Key.
     *
     * @param string $order_id Order ID.
     * @param string $transaction_id Transaction ID to delete.
     * @param RequestOptions|null $request_options Per-request configuration overrides.
     * @return MPResponse Raw API response (typically empty body with 204 status).
     * @throws \MercadoPago\Exceptions\MPApiException When the API returns a non-2xx status code.
     * @throws \Exception On transport-level errors.
     */
    public function delete(string $order_id, string $transaction_id, ?RequestOptions $request_options = null): MPResponse
    {
        $path = sprintf(self::URL_WITH_ID, rawurlencode($order_id), rawurlencode($transaction_id));
        return parent::send($path, HttpMethod::DELETE, null, null, $request_options);
    }
}
