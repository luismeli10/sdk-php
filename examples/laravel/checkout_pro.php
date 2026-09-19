<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\MercadoPagoConfig;

final class CheckoutProController extends Controller
{
    public function create(): RedirectResponse
    {
        MercadoPagoConfig::setAccessToken((string) config('services.mercadopago.access_token'));

        $client = new OrderClient();

        // OrderClient::create returns checkout_url for Checkout Pro orders using manual processing.
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
                    "success_url" => route('checkout.success'),
                    "failure_url" => route('checkout.failure'),
                    "pending_url" => route('checkout.pending'),
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
            'X-Idempotency-Key' => (string) Str::uuid(),
        ]);

        $order = $client->create($request, $request_options);

        abort_if(empty($order->checkout_url), 502, 'Mercado Pago did not return a checkout URL.');

        return redirect()->away($order->checkout_url);
    }
}