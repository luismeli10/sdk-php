<?php

namespace MercadoPago\Tests\Client\Unit\Order;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderTransactionClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPDefaultHttpClient;
use MercadoPago\Net\MPHttpClient;
use MercadoPago\Net\MPResponse;
use MercadoPago\Resources\Order\Payment;
use MercadoPago\Resources\Order\PaymentMethod;
use MercadoPago\Resources\Order\Transaction\UpdateTransaction;
use MercadoPago\Resources\Order\Transactions;
use MercadoPago\Tests\Client\Unit\Base\BaseClient;

/**
 * OrderTransactionClient unit tests.
 */
final class OrderTransactionClientUnitTest extends BaseClient
{
    private $http_client_mock;
    private $client;

    protected function setUp(): void
    {
        /** @var MPHttpClient|\PHPUnit\Framework\MockObject\MockObject $http_client_mock */
        $this->http_client_mock = $this->createMock(MPHttpClient::class);

        $this->client = new OrderTransactionClient($this->http_client_mock);
    }

    public function testCreateSuccess(): void
    {
        $order_id = "order/id";
        $request_body = $this->createRequest();
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: create-key"]);
        $expected_response = new MPResponse(201, [
            "payments" => [[
                "id" => "pay_01JD26HQ96FFHBD2CHDW984TZM",
                "amount" => "100.00",
                "payment_method" => [
                    "id" => "master",
                    "type" => "credit_card",
                    "installments" => 3,
                ],
            ]],
        ]);

        $this->http_client_mock->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($request) use ($request_body): bool {
                $this->assertSame('/v1/orders/order%2Fid/transactions', $request->getUri());
                $this->assertSame('POST', $request->getMethod());
                $this->assertContains('X-Idempotency-Key: create-key', $request->getHeaders());
                $this->assertSame($request_body, json_decode($request->getPayload(), true));
                $this->assertArrayHasKey('payments', $request_body);
                $this->assertArrayNotHasKey('payment_method', $request_body);
                return true;
            }))
            ->willReturn($expected_response);

        $transaction = $this->client->create($order_id, $request_body, $request_options);

        $this->assertInstanceOf(Transactions::class, $transaction);
        $this->assertSame(201, $transaction->getResponse()->getStatusCode());
        $this->assertInstanceOf(Payment::class, $transaction->payments[0]);
        $this->assertSame("pay_01JD26HQ96FFHBD2CHDW984TZM", $transaction->payments[0]->id);
        $this->assertSame("100.00", $transaction->payments[0]->amount);
        $this->assertInstanceOf(PaymentMethod::class, $transaction->payments[0]->payment_method);
        $this->assertSame("master", $transaction->payments[0]->payment_method->id);
        $this->assertSame("credit_card", $transaction->payments[0]->payment_method->type);
        $this->assertSame(3, $transaction->payments[0]->payment_method->installments);
    }

    private function createRequest(): array
    {
        return [
            "payments" => [
                [
                    "amount" => "100.00",
                    "payment_method" => [
                        "id" => "master",
                        "type" => "credit_card",
                        "token" => "{{card_token}}",
                        "installments" => 3,
                    ],
                ],
            ],
        ];
    }

    public function testUpdateSuccess(): void
    {
        $order_id = "order/id";
        $transaction_id = "payment/id";
        $request_body = [
            "payment_method" => [
                "type" => "credit_card",
                "installments" => 1,
            ],
        ];
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: update-key"]);
        $expected_response = new MPResponse(200, [
            "id" => "payment/id",
            "payment_method" => [
                "id" => "master",
                "type" => "credit_card",
                "installments" => 1,
            ],
        ]);

        $this->http_client_mock->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($request) use ($request_body): bool {
                $this->assertSame('/v1/orders/order%2Fid/transactions/payment%2Fid', $request->getUri());
                $this->assertSame('PUT', $request->getMethod());
                $this->assertContains('X-Idempotency-Key: update-key', $request->getHeaders());
                $this->assertSame($request_body, json_decode($request->getPayload(), true));
                return true;
            }))
            ->willReturn($expected_response);

        $transaction = $this->client->update($order_id, $transaction_id, $request_body, $request_options);

        $this->assertInstanceOf(UpdateTransaction::class, $transaction);
        $this->assertSame(200, $transaction->getResponse()->getStatusCode());
        $this->assertSame("master", $transaction->payment_method->id);
        $this->assertSame("credit_card", $transaction->payment_method->type);
        $this->assertSame(1, $transaction->payment_method->installments);
    }

    public function testDeleteSuccessWithoutRequestOptionsOrIdempotencyHeader(): void
    {
        $order_id = "1234321";
        $transaction_id = "pay_3456789";
        $expected_response = new MPResponse(204, []);

        $this->http_client_mock->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($request) use ($order_id, $transaction_id): bool {
                $this->assertSame(
                    "/v1/orders/{$order_id}/transactions/{$transaction_id}",
                    $request->getUri()
                );
                $this->assertSame('DELETE', $request->getMethod());
                $this->assertNotContains(
                    'X-Idempotency-Key',
                    array_map(static fn (string $header): string => explode(':', $header, 2)[0], $request->getHeaders())
                );
                return true;
            }))
            ->willReturn($expected_response);

        $response = $this->client->delete($order_id, $transaction_id);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testDeleteErrorNotFound()
    {
        $order_id = "1234321";
        $transaction_id = "pay_3456789";
        $expectedResponse = new MPResponse(404, ['Order not found.']);

        $this->http_client_mock->method('send')->willReturn($expectedResponse);
        $response = $this->client->delete($order_id, $transaction_id);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
