<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Bold\Checkout\Block\Onepage\Button;
use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Check if the redirect to Bold checkout is allowed by parallel checkout.
 */
class IsRedirectToBoldCheckoutAllowedByParallel implements IsRedirectToBoldCheckoutAllowedInterface
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
    ) {
        $this->config = $config;
    }

    /**
     * Check if the redirect to Bold checkout is allowed by parallel checkout.
     *
     * @param CartInterface $quote
     * @param RequestInterface $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, RequestInterface $request): bool
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        if ($this->config->isCheckoutTypeParallel($websiteId)) {
            return (bool)$request->getParam(Button::KEY_PARALLEL);
        }

        return true;
    }
}
