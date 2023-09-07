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
    public function getDiscountData();

    /**
     * Get Rule Label
     *
     * @return string
     */
    public function getRuleLabel();

    /**
     * Get Rule ID
     *
     * @return int
     */
    public function getRuleID();
}
