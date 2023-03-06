<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Order service request interface.
 */
interface RequestInterface extends ExtensibleDataInterface
{
    /**
     * @return \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface
     */
    public function getOrder(): OrderDataInterface;
}
