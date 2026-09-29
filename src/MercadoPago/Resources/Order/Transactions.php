<?php

/** API version: 7d364c51-04c7-45e3-af61-f82423bcc39c */

namespace MercadoPago\Resources\Order;

use MercadoPago\Net\MPResource;
use MercadoPago\Serialization\Mapper;

/**
 * Represents the transaction container for a MercadoPago order.
 *
 * Groups the payments returned for an order and preserves the SDK's existing
 * refund and chargeback collections for backward compatibility. Payment
 * amounts are represented as decimal strings.
 *
 * @see \MercadoPago\Resources\Order
 * @see \MercadoPago\Client\Order\OrderTransactionClient
 */
class Transactions extends MPResource
{
    /** Class mapper. */
    use Mapper;

    /** Payments associated with this order; automatic-mode requests require at least one. Each element maps to {@see Payment}. */
    public ?array $payments;

    /** Refunds processed for this order's payments. Each element maps to {@see Refund}. */
    public ?array $refunds;

    /** Chargebacks filed against this order's payments. Each element maps to {@see Chargeback}. */
    public ?array $chargebacks;

    private $map = [
        "payments" => "MercadoPago\Resources\Order\Payment",
        "refunds" => "MercadoPago\Resources\Order\Refund",
        "chargebacks" => "MercadoPago\Resources\Order\Chargeback",
    ];

    /**
     * Method responsible for getting map of entities.
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
