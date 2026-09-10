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

## 🌟 Getting started with payment via Checkout Pro

Checkout Pro integration using the **Orders API**. Create an order with `processing_mode` set to `"manual"` to obtain a `checkout_url` for redirecting the buyer to MercadoPago's hosted payment flow.

### Step 1: Require the libraries

```php
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
```

### Step 2: Set up authentication

```php
// Getting the access token from your .env file (create your own function)
$mpAccessToken = getVariableFromEnv('mercado_pago_access_token');
// Set the token in the SDK's config
MercadoPagoConfig::setAccessToken($mpAccessToken);
// (Optional) Set the runtime environment to LOCAL for localhost testing
// Default value is SERVER
MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
```

### Step 3: Build the Checkout Pro order request

```php
// Function that returns the request payload for a Checkout Pro order
function createCheckoutProRequest(array $items, array $payer): array
{
    return [
        "type" => "online",
        "processing_mode" => "manual",
        "total_amount" => "500.00",
        "external_reference" => "order_pro_123",
        "capture_mode" => "automatic",
        "description" => "Order with multiple items",
        "expiration_time" => "P1D",
        "payer" => $payer,
        "items" => $items,
        "config" => [
            "statement_descriptor" => "MYSTORE",
            "online" => [
                "success_url" => "https://example.com/success",
                "failure_url" => "https://example.com/failure",
                "pending_url" => "https://example.com/pending",
                "auto_return" => "approved",
            ],
            "payment_method" => [
                "max_installments" => 12,
                "not_allowed_ids" => ["amex"],
                "not_allowed_types" => ["ticket"],
            ],
        ],
    ];
}
```

### Step 4: Create the order and redirect the buyer

```php
// Fill the data about the product(s) being purchased
$product1 = [
    "external_code" => "ITEM-001",
    "title" => "Product 1 Title",
    "description" => "Product 1 Description",
    "category_id" => "electronics",
    "picture_url" => "https://example.com/img1.jpg",
    "quantity" => 1,
    "unit_price" => "450.00",
    "type" => "physical",
];

$product2 = [
    "external_code" => "ITEM-002",
    "title" => "Product 2 Title",
    "description" => "Product 2 Description",
    "category_id" => "electronics",
    "picture_url" => "https://example.com/img2.jpg",
    "quantity" => 1,
    "unit_price" => "50.00",
    "type" => "physical",
];

$items = [$product1, $product2];

// Retrieve buyer information (use your own function)
$user = getSessionUser();

$payer = [
    "email" => $user->email,
    "first_name" => $user->name,
    "last_name" => $user->surname,
    "phone" => [
        "area_code" => "11",
        "number" => "999998888",
    ],
    "identification" => [
        "type" => "CPF",
        "number" => "12345678909",
    ],
    "address" => [
        "zip_code" => "01310-100",
        "street_name" => "Av. Paulista",
        "street_number" => "1000",
        "neighborhood" => "Bela Vista",
        "city" => "São Paulo",
    ],
];

$request = createCheckoutProRequest($items, $payer);

// Instantiate the Orders API client
$client = new OrderClient();

// Set X-Idempotency-Key to prevent duplicate orders on retries
$request_options = new RequestOptions();
$request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);

try {
    // Create the order — the API returns the order id and the checkout_url
    $order = $client->create($request, $request_options);

    // Save the order id for future operations (cancel, refund, get status)
    echo "Order ID: " . $order->id . "\n";
    echo "Order status: " . $order->status . "\n";

    // Redirect the buyer to the Checkout Pro payment flow
    echo "Checkout URL: " . $order->checkout_url . "\n";
    // Redirect your buyer to $order->checkout_url to complete the payment

} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

In case you need to retrieve the order by ID:

```php
    $client = new OrderClient();
    $order = $client->get("<ORDER_ID>");
    echo "Status: " . $order->status . "\n";
```

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
