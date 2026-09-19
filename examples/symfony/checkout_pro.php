<?php

namespace App\Controller;

use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\MercadoPagoConfig;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class CheckoutProController
{
    #[Route('/checkout/pro', name: 'checkout_pro', methods: ['POST'])]
    public function checkout(
        #[Autowire('%env(MERCADO_PAGO_ACCESS_TOKEN)%')]
        string $accessToken,
    ): RedirectResponse {
        MercadoPagoConfig::setAccessToken($accessToken);

        $client = new OrderClient();

        // Orders with processing_mode "manual" return a checkout_url for Checkout Pro.
        $request = [
            "type" => "online",
            "processing_mode" => "manual",
            "total_amount" => "500.00",
            "external_reference" => "ext_ref_checkout_pro_001",
            "capture_mode" => "automatic",
            "marketplace_fee" => "5.00",
            "description" => "Travel package SAO-RIO with insurance",
            "expiration_time" => "P1D",
            "payer" => [
                "email" => "buyer@testuser.com",
                "first_name" => "John",
                "last_name" => "Smith",
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
            ],
            "shipment" => [
                "mode" => "custom",
                "local_pickup" => false,
                "cost" => "15.00",
                "free_shipping" => false,
                "free_methods" => [
                    ["id" => 73328],
                ],
                "address" => [
                    "zip_code" => "01310-100",
                    "street_name" => "Av. Paulista",
                    "street_number" => "1000",
                    "floor" => "3",
                    "apartment" => "B",
                    "neighborhood" => "Bela Vista",
                    "city" => "São Paulo",
                ],
            ],
            "config" => [
                "statement_descriptor" => "MYSTORE",
                "default_payment_due_date" => "P1D",
                "online" => [
                    "available_from" => "2026-01-01T00:00:00Z",
                    "allowed_user_type" => "account_only",
                    "success_url" => "https://example.com/success",
                    "failure_url" => "https://example.com/failure",
                    "pending_url" => "https://example.com/pending",
                    "auto_return" => "approved",
                    "tracks" => [
                        [
                            "type" => "google_ad",
                            "values" => [
                                "conversion_id" => "21312312312123",
                                "conversion_label" => "TEST",
                            ],
                        ],
                        [
                            "type" => "facebook_ad",
                            "values" => [
                                "pixel_id" => "21312312312123",
                            ],
                        ],
                    ],
                ],
                "payment_method" => [
                    "max_installments" => 12,
                    "not_allowed_ids" => ["amex"],
                    "not_allowed_types" => ["ticket"],
                    "installments" => [
                        "interest_free" => [
                            "type" => "range",
                            "values" => [2, 6],
                        ],
                    ],
                ],
            ],
            "items" => [
                [
                    "external_code" => "ITEM-001",
                    "title" => "Flight SAO-RIO",
                    "description" => "Round trip, economy class",
                    "category_id" => "travels",
                    "picture_url" => "https://example.com/img.jpg",
                    "quantity" => 1,
                    "unit_price" => "450.00",
                    "type" => "travel",
                    "event_date" => "2027-01-15T00:00:00.000-03:00",
                ],
                [
                    "external_code" => "ITEM-002",
                    "title" => "Travel insurance",
                    "description" => "Basic coverage during trip",
                    "category_id" => "travels",
                    "picture_url" => "https://example.com/insurance.jpg",
                    "quantity" => 1,
                    "unit_price" => "50.00",
                    "type" => "travel",
                    "event_date" => "2027-01-15T00:00:00.000-03:00",
                ],
            ],
        ];

        $request_options = new RequestOptions();
        $request_options->setCustomHeaders([
            'X-Idempotency-Key' => bin2hex(random_bytes(16)),
        ]);

        // Create the Checkout Pro order through OrderClient::create().
        $order = $client->create($request, $request_options);

        return new RedirectResponse($order->checkout_url);
    }
}