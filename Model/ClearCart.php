<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\ClearCartInterface;
use Magento\Checkout\Model\Session;

/**
 * @inheritDoc
 */
class ClearCart implements ClearCartInterface
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function clear(string $shopId): array
    {
        return [];
    }
}
