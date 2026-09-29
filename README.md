![image](https://github.com/mercadopago/sdk-php/assets/86324641/46001c9c-b28a-44cb-9fc3-211712be5022)

# Mercado Pago SDK for PHP

[![Latest Stable Version](https://poser.pugx.org/mercadopago/dx-php/v/stable)](https://packagist.org/packages/mercadopago/dx-php) [![Total Downloads](https://poser.pugx.org/mercadopago/dx-php/downloads)](https://packagist.org/packages/mercadopago/dx-php) [![License](https://poser.pugx.org/mercadopago/dx-php/license)](https://packagist.org/packages/mercadopago/dx-php)

This library provides developers with a simple set of bindings to help you integrate Mercado Pago API to a website and start receiving payments.

## 💡 Requirements

The SDK Supports PHP version 8.2 or higher.

## 💻 Installation

If you already use another version of MercadoPago PHP SDK, take a look at our [migration guide](MIGRATION_GUIDE.md) from version 2 to version 3.

First time using Mercado Pago? Create your [Mercado Pago account](https://www.mercadopago.com), if you don’t have one already.

1. Download [Composer](https://getcomposer.org/doc/00-intro.md) if not already installed.

2. Install PHP SDK for MercadoPago running in command line:

```
composer require "mercadopago/dx-php:3.16.0"
```

> You can also run _composer require "mercadopago/dx-php:2.6.2"_ for PHP7.1 or _composer require "mercadopago/dx-php:1.12.6"_ for PHP5.6.

3. Copy the access_token in the [credentials](https://www.mercadopago.com/developers/en/docs/your-integrations/credentials) section of the page and replace YOUR_ACCESS_TOKEN with it.

That's it! Mercado Pago SDK has been successfully installed.

## Useful links

- [SDK Docs](https://www.mercadopago.com.br/developers/pt/docs/sdks-library/server-side)
- [REST API (consumed by the SDK)](https://www.mercadopago.com.br/developers/en/reference)
- [CHANGELOG](./CHANGELOG.md)

Here you can check eg. data structures for each parameter used by the SDK for each class.

## 🌟 Getting Started with payment via your own website forms

Simple usage looks like:

```php
<?php
    // Step 1: Require the library from your Composer vendor folder
    require_once 'vendor/autoload.php';

    use MercadoPago\Client\Common\RequestOptions;
    use MercadoPago\Client\Order\OrderClient;
    use MercadoPago\Exceptions\MPApiException;
    use MercadoPago\MercadoPagoConfig;

    // Step 2: Set production or sandbox access token
    MercadoPagoConfig::setAccessToken("<ACCESS_TOKEN>");
    // Step 2.1 (optional - default is SERVER): Set your runtime enviroment from MercadoPagoConfig::RUNTIME_ENVIROMENTS
    // In case you want to test in your local machine first, set runtime enviroment to LOCAL
    MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);

    // Step 3: Initialize the API client
    $client = new OrderClient();

    try {

        // Step 4: Create the request array
        $request = [
            "type" => "online",
            "processing_mode" => "automatic",
            "total_amount" => "1000.00",
            "external_reference" => "ext_ref_1234",
            "capture_mode" => "automatic_async",
            "payer" => [
                "email" => "<PAYER_EMAIL>",
            ],
            "transactions" => [
                "payments" => [
                    [
                        "amount" => "1000.00",
                        "payment_method" => [
                            "id" => "master",
                            "type" => "credit_card",
                            "token" => "<CARD_TOKEN>",
                            "installments" => 1,
                            "statement_descriptor" => "Store name",
                        ]
                    ]
                ]
            ]
        ];

        // Step 5: Create the request options, setting X-Idempotency-Key
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);

        // Step 6: Make the request
        $order = $client->create($request, $request_options);
        echo "Order ID:" . $order->id;

    // Step 7: Handle exceptions
    } catch (MPApiException $e) {
        echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
        echo "Content: ";
        var_dump($e->getApiResponse()->getContent());
        echo "\n";
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
```

### Step 1: Require the library from your Composer vendor folder

```php
require_once 'vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
```

### Step 2: Set production or sandbox access token

```php
MercadoPagoConfig::setAccessToken("<ACCESS_TOKEN>");
```

You can also set another properties as quantity of retries, tracking headers, timeouts and a custom http client.

### Step 3: Initialize the API client

```php
$client = new OrderClient();
```

### Step 4: Create the request array

```php
$request = [
    "type" => "online",
    "processing_mode" => "automatic",
    "total_amount" => "1000.00",
    "external_reference" => "ext_ref_1234",
    "capture_mode" => "automatic_async",
    "payer" => [
        "email" => "<PAYER_EMAIL>",
    ],
    "transactions" => [
        "payments" => [
            [
                "amount" => "1000.00",
                "payment_method" => [
                    "id" => "master",
                    "type" => "credit_card",
                    "token" => "<CARD_TOKEN>",
                    "installments" => 1,
                    "statement_descriptor" => "Store name",
                ]
            ]
        ]
    ]
];
```

### Step 5: Create the request options, setting X-Idempotency-Key

```php
$request_options = new RequestOptions();
$request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);
```

### Step 6: Make the request

```php
$order = $client->create($request, $request_options);
```

### Step 7: Handle exceptions

```php
try{
    // Do your stuff here
} catch (MPApiException $e) {
    // Handle API exceptions
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (\Exception $e) {
    // Handle all other exceptions
    echo $e->getMessage();
}
```

## 🌟 Getting started with Checkout Pro via Orders

The SDK exposes the bounded Orders API through `MercadoPago\Client\Order\OrderClient` and `MercadoPago\Client\Order\OrderTransactionClient`. Create the order with its transaction data; no separate transaction-creation step is required before processing.

### Create an order

The snippet uses the `RequestOptions`, `OrderClient`, and `MPApiException` imports and access-token configuration established above.

```php
$order_client = new OrderClient();
$request = [
    'type' => 'online',
    'processing_mode' => 'automatic',
    'capture_mode' => 'automatic_async',
    'external_reference' => 'checkout-pro-order-001',
    'total_amount' => '500.00',
    'payer' => [
        'email' => '<PAYER_EMAIL>',
    ],
    'transactions' => [
        'payments' => [
            [
                'amount' => '500.00',
                'payment_method' => [
                    'id' => 'master',
                    'type' => 'credit_card',
                    'token' => '<CARD_TOKEN>',
                    'installments' => 1,
                ],
            ],
        ],
    ],
];

$options = new RequestOptions();
$options->setCustomHeaders([
    'X-Idempotency-Key: <SOME_UNIQUE_VALUE>',
]);

try {
    $order = $order_client->create($request, $options);
    echo 'Order ID: ' . $order->id;
} catch (MPApiException $e) {
    echo 'Status code: ' . $e->getApiResponse()->getStatusCode() . "\n";
    var_dump($e->getApiResponse()->getContent());
}
```

`OrderClient` maps successful order responses to `MercadoPago\Resources\Order` and search responses to `MercadoPago\Resources\OrderSearch`. Nested transaction data is represented by `MercadoPago\Resources\Order\Transactions`, `MercadoPago\Resources\Order\Payment`, and `MercadoPago\Resources\Order\PaymentMethod`.

The supported order routes are `/v1/orders`, `/v1/orders/{id}`, `/v1/orders/{order_id}/cancel`, `/v1/orders/{order_id}/process`, `/v1/orders/{order_id}/capture`, and `/v1/orders/{order_id}/refund`. `OrderTransactionClient` operates on `/v1/orders/{order_id}/transactions` and `/v1/orders/{order_id}/transactions/{transaction_id}`. Supply `X-Idempotency-Key` through `RequestOptions` only for operations that declare it: order create, cancel, process, capture, and refund, plus transaction create and update. The transaction delete operation does not declare this header. A full refund can omit the request body, while a partial refund provides the amount in the request body.

## 📚 Documentation

See our documentation for more details.

- Mercado Pago reference API. [Portuguese](https://www.mercadopago.com/developers/pt/reference) / [English](https://www.mercadopago.com/developers/en/reference) / [Spanish](https://www.mercadopago.com/developers/es/reference)

## 🤝 Contributing

All contributions are welcome, ranging from people wanting to triage issues, others wanting to write documentation, to people wanting to contribute code.

Please read and follow our [contribution guidelines](CONTRIBUTING.md). Contributions not following these guidelines will
be disregarded. The guidelines are in place to make all of our lives easier and make contribution a consistent process for everyone.

### Patches to version 2.x.x

Since the release of version 3.0.0, version 2 is deprecated and will not be receiving new features, only bug fixes. If you need to submit PRs for that version, please do so by using [master-v2](https://github.com/mercadopago/sdk-php/tree/master-v2) as your base branch.

## ❤️ Support

If you require technical support, please contact our support team at our developers site: [English](https://www.mercadopago.com/developers/en/support/center/contact) / [Portuguese](https://www.mercadopago.com/developers/pt/support/center/contact) / [Spanish](https://www.mercadopago.com/developers/es/support/center/contact)

## 🏻 License

```
MIT license. Copyright (c) 2023 - Mercado Pago / Mercado Libre
For more information, see the LICENSE file.
```
