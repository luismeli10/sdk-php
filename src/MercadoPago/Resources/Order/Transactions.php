<?php

/** API version: 7d364c51-04c7-45e3-af61-f82423bcc39c */

namespace MercadoPago\Resources\Order;

use MercadoPago\Net\MPResource;
use MercadoPago\Serialization\Mapper;

/**
 * Represents the transaction container for a MercadoPago order.
 *
 * Groups all financial operations associated with an order: payments,
 * refunds, and chargebacks. An order may contain multiple payments
 * (split payment scenarios) and their corresponding reversals.
 *
 * @see \MercadoPago\Resources\Order
 * @see \MercadoPago\Client\Order\OrderTransactionClient
 */
class Transactions extends MPResource
{
    /** Class mapper. */
    use Mapper;

    /** @var Payment[]|null Payments associated with this order. */
    public ?array $payments;

    /** @var Refund[]|null Refunds processed for this order's payments. */
    public ?array $refunds;

    /** @var Chargeback[]|null Chargebacks filed against this order's payments. */
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
