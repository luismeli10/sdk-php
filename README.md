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

## 🌟 Getting started with Checkout Pro via Orders

The Orders client creates and manages Checkout Pro orders through `/v1/orders`. It maps individual responses to `MercadoPago\Resources\Order` and search responses to `MercadoPago\Resources\OrderSearch`.

### Create an order

```php
<?php
require_once 'vendor/autoload.php';

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken("<ACCESS_TOKEN>");
$client = new OrderClient();

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
                    "transaction_security" => [
                        "type" => "none",
                    ],
                ],
            ],
        ],
    ],
];

$request_options = new RequestOptions();
$request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);

try {
    $order = $client->create($request, $request_options); // POST /v1/orders
    echo "Order ID: " . $order->id . "\n";
    echo "Country: " . $order->country_code . "\n";
    echo "Client token: " . $order->client_token . "\n";
} catch (MPApiException $e) {
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    var_dump($e->getApiResponse()->getContent());
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

The payment collection is available as `transactions.payments`; each payment's security settings are mapped from `transactions.payments[].payment_method.transaction_security`.

### Retrieve and search orders

Retrieve one order with `GET /v1/orders/{id}`:

```php
$order = $client->get("<ORDER_ID>");
```

Search uses `GET /v1/orders`. The `begin_date` and `end_date` filters are required and use ISO 8601 date-time values:

```php
use MercadoPago\Net\MPSearchRequest;

$search_request = new MPSearchRequest(20, 0, [
    "begin_date" => "2024-01-01T00:00:00Z",
    "end_date" => "2024-01-31T23:59:59Z",
]);
$orders = $client->search($search_request);
```

### Manage the order lifecycle

Lifecycle operations that use `POST` require an `X-Idempotency-Key`, supplied through `RequestOptions` as shown above:

```php
$cancelled = $client->cancel("<ORDER_ID>", $request_options); // POST /v1/orders/{order_id}/cancel
$processed = $client->process("<ORDER_ID>", $request_options); // POST /v1/orders/{order_id}/process
$captured = $client->capture("<ORDER_ID>", $request_options); // POST /v1/orders/{order_id}/capture
$refunded = $client->refund("<ORDER_ID>", null, $request_options); // POST /v1/orders/{order_id}/refund
$partially_refunded = $client->refund("<ORDER_ID>", ["amount" => "25.00"], $request_options);
```

## 🌟 Getting started with payment via Checkout Pro

### Step 1: Require the libraries

```php
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
```

### Step 2: Create an authentication function

```php
protected function authenticate()
{
    // Getting the access token from .env file (create your own function)
    $mpAccessToken = getVariableFromEnv('mercado_pago_access_token');
    // Set the token the SDK's config
    MercadoPagoConfig::setAccessToken($mpAccessToken);
    // (Optional) Set the runtime enviroment to LOCAL if you want to test on localhost
    // Default value is set to SERVER
    MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
}
```

### Step 3: Create customer's preference before proceeding to Checkout Pro page

```php
// Function that will return a request object to be sent to Mercado Pago API
function createPreferenceRequest($items, $payer): array
{
    $paymentMethods = [
        "excluded_payment_methods" => [],
        "installments" => 12,
        "default_installments" => 1
    ];

    $backUrls = array(
        'success' => route('mercadopago.success'),
        'failure' => route('mercadopago.failed')
    );

    $request = [
        "items" => $items,
        "payer" => $payer,
        "payment_methods" => $paymentMethods,
        "back_urls" => $backUrls,
        "statement_descriptor" => "NAME_DISPLAYED_IN_USER_BILLING",
        "external_reference" => "1234567890",
        "expires" => false,
        "auto_return" => 'approved',
    ];

    return $request;
}
```

### Step 4: Create the preference on Mercado Pago ([DOCS](https://www.mercadopago.com.br/developers/pt/docs/sdks-library/server-side/php/preferences))

```php
public function createPaymentPreference(): ?Preference
{
    // Fill the data about the product(s) being purchased
    $product1 = array(
        "id" => "1234567890",
        "title" => "Product 1 Title",
        "description" => "Product 1 Description",
        "currency_id" => "BRL",
        "quantity" => 12,
        "unit_price" => 9.90
    );

    $product2 = array(
        "id" => "9012345678",
        "title" => "Product 2 Title",
        "description" => "Product 2 Description",
        "currency_id" => "BRL",
        "quantity" => 5,
        "unit_price" => 19.90
    );

    // Mount the array of products that will integrate the purchase amount
    $items = array($product1, $product2);

    // Retrieve information about the user (use your own function)
    $user = getSessionUser();

    $payer = array(
        "name" => $user->name,
        "surname" => $user->surname,
        "email" => $user->email,
    );

    // Create the request object to be sent to the API when the preference is created
    $request = createPreferenceRequest($item, $payer);

    // Instantiate a new Preference Client
    $client = new PreferenceClient();

    try {
        // Send the request that will create the new preference for user's checkout flow
        $preference = $client->create($request);

        // Useful props you could use from this object is 'init_point' (URL to Checkout Pro) or the 'id'
        return $preference;
    } catch (MPApiException $error) {
        // Here you might return whatever your app needs.
        // We are returning null here as an example.
        return null;
    }
}
```

In case you need to retrieve the preference by ID:

```php
    $client = new PreferenceClient();
    $client->get("123456789");
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
