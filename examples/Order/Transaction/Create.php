<?php

namespace Examples\Order\Transaction;

// Step 1: Require the library from your Composer vendor folder
require_once '../../../vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderTransactionClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Step 2: Set production or sandbox access token
MercadoPagoConfig::setAccessToken("<ACCESS_TOKEN>");
// Step 2.1 (optional - default is SERVER): Set your runtime enviroment from MercadoPagoConfig::RUNTIME_ENVIROMENTS
// In case you want to test in your local machine first, set runtime enviroment to LOCAL
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

// Step 3: Initialize the API client
$client = new OrderTransactionClient();

try {
    // Step 4: Build the body for POST /v1/orders/{order_id}/transactions.
    // The response is MercadoPago\Resources\Order\Transactions and each entry in
    // payments is a MercadoPago\Resources\Order\Payment whose payment_method is a
    // MercadoPago\Resources\Order\PaymentMethod. Other transaction operations use
    // PUT and DELETE /v1/orders/{order_id}/transactions/{transaction_id}; updates map
    // to MercadoPago\Resources\Order\Transaction\UpdateTransaction. Order responses
    // can also expose MercadoPago\Resources\Order\TransactionSecurity.
    $request = [
        "payments" => [
            [
                "amount" => "100.00",
                "reference_id" => "payment-reference-001",
                "date_of_expiration" => "2027-01-15T00:00:00Z",
                "expiration_time" => "P1D",
                "payment_method" => [
                    "id" => "master",
                    "type" => "credit_card",
                    "token" => "<CARD_TOKEN>",
                    "installments" => 1,
                ],
            ],
        ],
        "transaction_security" => [
            "validation" => "<SECURITY_VALIDATION_DATA>",
        ],
    ];

    // Step 5: POST requires X-Idempotency-Key and returns HTTP 201.
    $request_options = new RequestOptions();
    $request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);

    // Step 6: Add the payment transaction to the manual-mode order.
    // Documented API errors are HTTP 400, 401, 404, and 422.
    $transaction = $client->create("<ORDER_ID>", $request, $request_options);
    $payment = $transaction->payments[0];
    echo "Payment ID: " . $payment->id;
    echo "\nAmount: " . $payment->amount;
    echo "\nPaid amount: " . $payment->paid_amount;
    echo "\nReference ID: " . $payment->reference_id;
    echo "\nStatus detail: " . $payment->status_detail;
    echo "\nExpiration date: " . $payment->date_of_expiration;
    echo "\nExpiration time: " . $payment->expiration_time;
    echo "\nPayment method ID: " . $payment->payment_method->id;
    echo "\nPayment method type: " . $payment->payment_method->type;

    // Step 7: Handle exceptions
} catch (MPApiException $e) {
    echo "\nStatus code: " . $e->getApiResponse()->getStatusCode();
    echo "\nContent: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
