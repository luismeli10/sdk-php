<?php

/** API version: 7d364c51-04c7-45e3-af61-f82423bcc39c */

namespace MercadoPago\Resources\Order;

use MercadoPago\Serialization\Mapper;

/**
 * Represents 3D Secure and other transaction security details for an order payment.
 *
 * Tracks the authentication status, liability shift outcome, and challenge URL
 * for card payments requiring strong customer authentication (SCA/3DS).
 *
 * @see \MercadoPago\Resources\Order\PaymentMethod
 * @see \MercadoPago\Resources\Order\OnlineConfig
 */
class TransactionSecurity
{
    /** Class mapper. */
    use Mapper;

    /** Unique identifier of the 3DS authentication transaction. */
    public ?string $id;

    /** Requested validation policy: "supported", "required", or "never". */
    public ?string $validation;

    /** 3DS result status: "pending", "success", or "failed". */
    public ?string $status;

    /** URL to redirect the payer to a pending 3DS challenge. */
    public ?string $redirect_url;

    /** Whether liability shifted to the issuer after 3DS authentication (e.g., "yes", "no"). */
    public ?string $liability_shift;

    /** Authentication result status (pending, success, or failed). */
    public ?string $status;

    /** URL where the buyer completes a pending 3DS challenge. */
    public ?string $redirect_url;

    /** Legacy challenge URL retained for backward compatibility. */
    public ?string $url;

    /**
     * Method responsible for getting map of entities.
     */
    public function getMap(): array
    {
        return [];
    }
}
