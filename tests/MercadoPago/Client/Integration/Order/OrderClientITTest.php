<?php

namespace MercadoPago\Tests\Client\Integration\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use PHPUnit\Framework\TestCase;
use MercadoPago\Client\CardToken\CardTokenClient;

/**
 * OrderClient integration tests.
 */
final class OrderClientITTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        MercadoPagoConfig::setAccessToken(getenv("ACCESS_TOKEN"));
    }

    public function testCreateSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequest();
            $request_options = new RequestOptions();
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createRequest(): array
    {
        $request = [
            "type" => "online",
            "total_amount" => "1000.00",
            "external_reference" => "ext_ref_1234",
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "1000.00",
                        "payment_method" => [
                            "id" => "pix",
                            "type" => "bank_transfer"
                        ],
                    ],
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
        return $request;
    }

    public function testCaptureSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestCapture();
            $request_options = new RequestOptions();
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            $order_capture = $client->capture($order->id, $request_options);
            $this->assertSame($order_capture->status, "processed");
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createRequestCapture(): array
    {
        $client_token = new CardTokenClient();
        $card_token = $client_token->create($this->createCardTokenRequest());
        $this->assertNotNull($card_token->id);

        $request = [
            "type" => "online",
            "processing_mode" => "automatic",
            "total_amount" => "200.00",
            "external_reference" => "ext_ref_1234",
            "capture_mode" => "manual",
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "200.00",
                        "payment_method" => [
                            "id" => "master",
                            "type" => "credit_card",
                            "token" => $card_token->id,
                            "installments" => 1,
                        ]
                    ]
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
        return $request;
    }

    public function testGetOrderSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequest();
            $request_options = new RequestOptions();
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            $order_get = $client->get($order->id, $request_options);
            $this->assertNotNull($order_get->id);
            $this->assertSame($order->id, $order_get->id);
            $this->assertSame($order->total_amount, $order_get->total_amount);
            $this->assertSame($order->status, $order_get->status);
            $this->assertSame($order->status_detail, $order_get->status_detail);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    public function testCancelOrderSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestCapture();
            $request_options = new RequestOptions();
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            sleep(3); // sleep to avoid error 422 when create and cancel occurrs just in time

            $order_cancelled = $client->cancel($order->id, $request_options);
            $this->assertNotNull($order_cancelled->id);
            $this->assertSame("canceled", $order_cancelled->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    public function testProcessSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createOrderProcess();
            $request_options = new RequestOptions();
            $order = $client->create($request, $request_options);
            $this->assertNotNull($order->id);

            $order_processed = $client->process($order->id, $request_options);
            $this->assertSame("processed", $order_processed->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createOrderProcess(): array
    {
        $client_token = new CardTokenClient();
        $card_token = $client_token->create($this->createCardTokenRequest());
        $this->assertNotNull($card_token->id);

        return [
            "type" => "online",
            "processing_mode" => "manual",
            "total_amount" => "200.00",
            "external_reference" => "ext_ref_1234",
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "200.00",
                        "payment_method" => [
                            "id" => "master",
                            "type" => "credit_card",
                            "token" => $card_token->id,
                            "installments" => 1,
                        ]
                    ]
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
    }

    public function testRefundTotalSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestOrderCreditCard();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Sandbox: true"]);

            $order = $client->create($request, $request_options);
            sleep(5);
            $refunded_order = $client->refund($order->id, null, $request_options);

            $this->assertSame($order->id, $refunded_order->id);
            $this->assertSame("refunded", $refunded_order->status);
            $this->assertSame("refunded", $refunded_order->status_detail);
            $this->assertSame("100.00", $refunded_order->transactions->refunds[0]->amount);
            $this->assertSame("processed", $refunded_order->transactions->refunds[0]->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    public function testRefundPartialSuccess(): void
    {
        try {
            $client = new OrderClient();
            $request = $this->createRequestOrderCreditCard();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Sandbox: true"]);

            $order = $client->create($request, $request_options);
            $refund_request = [
                "transactions" => [
                    [
                        "id" => $order->transactions->payments[0]->id,
                        "amount" => "25.00",
                    ],
                ],
            ];
            sleep(5);
            $refunded_order = $client->refund($order->id, $refund_request, $request_options);

            $this->assertSame($order->id, $refunded_order->id);
            $this->assertSame("processed", $refunded_order->status);
            $this->assertSame("partially_refunded", $refunded_order->status_detail);
            $this->assertSame("25.00", $refunded_order->transactions->refunds[0]->amount);
            $this->assertSame("processed", $refunded_order->transactions->refunds[0]->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $statusCode = $apiResponse->getStatusCode();
            $responseBody = json_encode($apiResponse->getContent());
            $this->fail("API Exception: " . $statusCode . " - " . $responseBody);
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createRequestOrderCreditCard(): array
    {
        $client_token = new CardTokenClient();
        $card_token = $client_token->create($this->createCardTokenRequest());
        $this->assertNotNull($card_token->id);

        return [
            "type" => "online",
            "processing_mode" => "automatic",
            "total_amount" => "100.00",
            "external_reference" => "ext_ref_1234",
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "100.00",
                        "payment_method" => [
                            "id" => "master",
                            "type" => "credit_card",
                            "token" => $card_token->id,
                            "installments" => 1,
                        ]
                    ]
                ]
            ],
            "payer" => [
                "email" => "test_1731350184@testuser.com",
            ]
        ];
    }

    public function testConfirmSuccess(): void
    {
        $order_id = getenv("CONFIRM_ORDER_ID");
        if (!$order_id) {
            $this->markTestSkipped("Set CONFIRM_ORDER_ID to an in-store QR order that is ready to be confirmed.");
        }

        try {
            $client = new OrderClient();
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Idempotency-Key: " . uniqid("confirm-", true)]);
            $order = $client->confirm($order_id, $this->createConfirmRequest(), $request_options);

            $this->assertSame($order_id, $order->id);
            $this->assertNotNull($order->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $this->fail("API Exception: " . $apiResponse->getStatusCode() . " - " . json_encode($apiResponse->getContent()));
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createConfirmRequest(): array
    {
        $request = getenv("CONFIRM_ORDER_REQUEST");
        if (!$request) {
            $this->markTestSkipped("Set CONFIRM_ORDER_REQUEST to the JSON confirmation payload for CONFIRM_ORDER_ID.");
        }

        $decoded_request = json_decode($request, true);
        $this->assertIsArray($decoded_request, "CONFIRM_ORDER_REQUEST must contain a JSON object.");
        return $decoded_request;
    }

    public function testSimulateEventSuccess(): void
    {
        $order_id = getenv("SIMULATE_EVENT_ORDER_ID");
        if (!$order_id) {
            $this->markTestSkipped("Set SIMULATE_EVENT_ORDER_ID to an in-store order that accepts simulated events.");
        }

        try {
            $client = new OrderClient();
            $response = $client->simulateEvent($order_id, $this->createSimulateEventRequest());

            $this->assertSame(204, $response->getStatusCode());
            $this->assertEmpty($response->getContent());
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $this->fail("API Exception: " . $apiResponse->getStatusCode() . " - " . json_encode($apiResponse->getContent()));
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createSimulateEventRequest(): array
    {
        $request = getenv("SIMULATE_EVENT_REQUEST");
        if (!$request) {
            $this->markTestSkipped("Set SIMULATE_EVENT_REQUEST to the JSON event payload for SIMULATE_EVENT_ORDER_ID.");
        }

        $decoded_request = json_decode($request, true);
        $this->assertIsArray($decoded_request, "SIMULATE_EVENT_REQUEST must contain a JSON object.");
        return $decoded_request;
    }

    public function testGetRefundsSuccess(): void
    {
        $order_id = getenv("REFUNDED_ORDER_ID");
        if (!$order_id) {
            $this->markTestSkipped("Set REFUNDED_ORDER_ID to an order with at least one refund.");
        }

        try {
            $client = new OrderClient();
            $refunds = $client->getRefunds($order_id, new RequestOptions());

            $this->assertNotEmpty($refunds);
            $this->assertInstanceOf(\MercadoPago\Resources\Order\Refund::class, $refunds[0]);
            $this->assertNotNull($refunds[0]->id);
            $this->assertNotNull($refunds[0]->status);
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $this->fail("API Exception: " . $apiResponse->getStatusCode() . " - " . json_encode($apiResponse->getContent()));
        } catch (\Exception $e) {
            $this->fail("Exception: " . $e->getMessage());
        }
    }

    private function createCardTokenRequest(): array
    {
        return [
            "card_number" => "5031433215406351",
            "expiration_year" => "2099",
            "expiration_month" => "12",
            "security_code" => "123",
            "cardholder" => [
                "name" => "APRO",
                "identification" => [
                    "type" => "CPF",
                    "number" => "19119119100",
                ],
            ]
        ];
    }
}