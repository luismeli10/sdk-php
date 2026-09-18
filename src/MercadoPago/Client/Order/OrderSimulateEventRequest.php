<?php

namespace MercadoPago\Client\Order;

/**
 * Request payload for simulating an event on an order.
 *
 * Allows changing the status of an inStore point order by simulating an event.
 *
 * @see OrderClient::simulateEvents()
 */
class OrderSimulateEventRequest
{
    /** Event to simulate on the order (e.g., "payment_approved"). */
    public string $event;
}