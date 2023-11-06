<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

/**
 * Get template for the parallel checkout button.
 */
class GetParallelCheckoutTemplate
{
    private const PARALLEL_CHECKOUT_TEMPLATE = 'Bold_Checkout::cart/checkout_button.phtml';

    /**
     * Get template for the parallel checkout button.
     *
     * @return string
     */
    public function getTemplate(): string {
       return self::PARALLEL_CHECKOUT_TEMPLATE;
    }
}
