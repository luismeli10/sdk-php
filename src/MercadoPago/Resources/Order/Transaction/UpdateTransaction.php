<?php

/** API version: 7d364c51-04c7-45e3-af61-f82423bcc39c */

namespace MercadoPago\Resources\Order\Transaction;

use MercadoPago\Resources\Order\Payment;

/**
 * Represents the response from updating a transaction within a MercadoPago order.
 *
 * Returned by the update transaction endpoint, this resource contains
 * the updated payment method details after modifying an existing transaction.
 *
 * @see \MercadoPago\Client\Order\OrderTransactionClient
 */
class UpdateTransaction extends Payment
{
}
