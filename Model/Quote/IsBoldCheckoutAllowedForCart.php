<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Checks if Bold functionality is enabled for specific cart.
 */
class IsBoldCheckoutAllowedForCart
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Checks if Bold functionality is enabled for specific Quote.
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function isAllowed(CartInterface $quote): bool
    {
        if (!$this->config->isCheckoutEnabled((int)$quote->getStore()->getWebsiteId())) {
            file_put_contents('/home/s3jamaligarden/public_html/var/log/eee.log', '_1_');
            return false;
        }
        if (!$this->isEnabledFor($quote)) {
            file_put_contents('/home/s3jamaligarden/public_html/var/log/eee.log', '_2_');
            return false;
        }

        $cartItems = $quote->getAllItems();
        if (!$cartItems) {
            file_put_contents('/home/s3jamaligarden/public_html/var/log/eee.log', '_3_');
            return false;
        }
        foreach ($cartItems as $item) {
            if ($item->getIsQtyDecimal()) {
                file_put_contents('/home/s3jamaligarden/public_html/var/log/eee.log', '_4_');
                return false;
            }
        }
        file_put_contents('/home/s3jamaligarden/public_html/var/log/eee.log', '_1_');
        return true;
    }

    /**
     * Verify quote against "Enabled For" bold checkout config.
     *
     * @param CartInterface $quote
     * @return bool
     */
    private function isEnabledFor(CartInterface $quote): bool
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        switch ($this->config->getEnabledFor($websiteId)) {
            case ConfigInterface::VALUE_ENABLED_FOR_ALL:
                return true;
            case ConfigInterface::VALUE_ENABLED_FOR_IP:
                return in_array($quote->getRemoteIp(), $this->config->getIpWhitelist($websiteId));
            case ConfigInterface::VALUE_ENABLED_FOR_CUSTOMER:
                return in_array($quote->getCustomerEmail(), $this->config->getCustomerWhitelist($websiteId));
            case ConfigInterface::VALUE_ENABLED_FOR_PERCENTAGE:
                return $this->resolveByPercentage($quote);
            default:
                return false;
        }
    }

    /**
     * Resolve if Bold functionality is enabled for specific Quote by Orders Percentage.
     *
     * @param CartInterface $quote
     * @return bool
     */
    private function resolveByPercentage(CartInterface $quote): bool
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        return ($quote->getId() % 10) < ($this->config->getOrdersPercentage($websiteId) / 10);
    }
}
