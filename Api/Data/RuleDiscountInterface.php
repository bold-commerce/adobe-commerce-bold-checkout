<?php

declare(strict_types=1);

namespace Bold\Checkout\Api\Data;

/**
 * Rule discount Interface
 * @api
 */
interface RuleDiscountInterface
{
    /**
     * Get Discount Data
     *
     * @return \Bold\Checkout\Api\Data\DiscountDataInterface
     */
    public function getDiscountData(): DiscountDataInterface;

    /**
     * Get Rule Label
     *
     * @return string
     */
    public function getRuleLabel(): string;

    /**
     * Get Rule ID
     *
     * @return int
     */
    public function getRuleID(): int;
}
