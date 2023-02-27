<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Tax\Model\Config;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ConfigInterface $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ConfigInterface $config, ScopeConfigInterface $scopeConfig)
    {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Checks if Bold functionality is enabled for specific Quote.
     *
     * @param CartInterface $quote
     * @return bool
     */
    public function isAllowed(CartInterface $quote): bool
    {
        if (!$this->config->isCheckoutEnabled()) {
            return false;
        }
        if (!$this->isEnabledFor($quote)) {
            return false;
        }

        $cartItems = $quote->getAllItems();
        if (!$cartItems) {
            return false;
        }
        foreach ($cartItems as $item) {
            if ($item->getIsQtyDecimal()) {
                return false;
            }
            if ($item->getProductType() === Type::TYPE_BUNDLE) {
                return false;
            }
        }
        return $this->scopeConfig->getValue(Config::CONFIG_XML_PATH_BASED_ON) === 'shipping'
        && !$this->scopeConfig->isSetFlag(Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX)
        && $this->scopeConfig->isSetFlag(Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT);
    }

    /**
     * Verify quote against "Enabled For" bold checkout config.
     *
     * @param CartInterface $quote
     * @return bool
     */
    private function isEnabledFor(CartInterface $quote): bool
    {
        switch ($this->config->getEnabledFor()) {
            case ConfigInterface::VALUE_ENABLED_FOR_ALL:
                return true;
            case ConfigInterface::VALUE_ENABLED_FOR_IP:
                return in_array($quote->getRemoteIp(), $this->config->getIpWhitelist());
            case ConfigInterface::VALUE_ENABLED_FOR_CUSTOMER:
                return in_array($quote->getCustomerEmail(), $this->config->getCustomerWhitelist());
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
        return ($quote->getId() % 10) < ($this->config->getOrdersPercentage() / 10);
    }
}
