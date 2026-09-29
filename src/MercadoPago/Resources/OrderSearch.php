<?php

namespace MercadoPago\Resources;

use MercadoPago\Net\MPResource;
use MercadoPago\Serialization\Mapper;

/**
 * Represents a paginated search result for MercadoPago Orders.
 *
 * Returned by the Orders search endpoint, this resource wraps paging metadata
 * and the list of matching {@see Order} resources.
 *
 * @see \MercadoPago\Client\Order\OrderClient
 */
class OrderSearch extends MPResource
{
    /** Class mapper. */
    use Mapper;

    /** Pagination metadata containing the OpenAPI total, limit, and offset fields. */
    public array|object|null $paging;

    /** Ordered list of {@see Order} resources returned in the `data` field. */
    public ?array $data;

    private $map = [
        "paging" => "MercadoPago\Resources\Common\Paging",
        "data" => "MercadoPago\Resources\Order",
    ];

    /**
     * Method responsible for getting map of entities.
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
