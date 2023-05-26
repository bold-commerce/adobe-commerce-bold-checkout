<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Block\Onepage\Button;
use Magento\Framework\App\Request\Http as Request;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Checks if Bold functionality is enabled for specific request.
 */
class IsBoldCheckoutAllowedForRequest
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    )
    {
        $this->config = $config;
    }

    /**
     * Checks if Bold functionality is enabled for specific  request.
     *
     * @param CartInterface $quote
     * @param Request $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, Request $request): bool
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();

        if (!$this->config->isCheckoutEnabled($websiteId)) {
            return false;
        }

        if ($this->config->isCheckoutTypeParallel($websiteId)
            && !$request->getParam(Button::KEY_PARALLEL)) {
            return false;
        }

        return true;
    }
}
