<?php

/** API version: 7d364c51-04c7-45e3-af61-f82423bcc39c */

namespace MercadoPago\Resources\Order\Transaction;

use MercadoPago\Resources\Order\Payment;

/**
 * Represents the payment transaction returned after an order transaction update.
 *
 * It shares the complete payment response shape and mapping used by transaction
 * creation while retaining a distinct return type for the update endpoint.
 *
 * @see \MercadoPago\Client\Order\OrderTransactionClient
 */
class UpdateTransaction extends Payment
{
}
