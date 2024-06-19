<?php

declare(strict_types=1);

namespace Bold\Checkout\Api\Data;

/**
 * Discount Data Interface
 * @api
 */
interface DiscountDataInterface
{
    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Get Base Amount
     *
     * @return float
     */
    public function getBaseAmount(): float;

    /**
     * Get Original Amount
     *
     * @return float
     */
    public function getOriginalAmount(): float;

    /**
     * Get Base Original Amount
     *
     * @return float
     */
    public function getBaseOriginalAmount(): float;
}
