<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\Onepage;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block for parallel checkout handling.
 */
class Button extends Template implements ShortcutInterface
{
    public const KEY_PARALLEL = 'parallel';

    public const ELEMENT_ALIAS = 'bold.checkout.parallel.button';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var IsBoldCheckoutAllowedForCart
     */
    private $allowedForCart;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param ConfigInterface $config
     * @param IsBoldCheckoutAllowedForCart $allowedForCart
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $checkoutSession,
        ConfigInterface $config,
        IsBoldCheckoutAllowedForCart $allowedForCart,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->allowedForCart = $allowedForCart;
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ELEMENT_ALIAS);
    }

    /**
     * Check if parallel checkout is enabled.
     *
     * @return bool
     */
    public function isParallelCheckoutEnabled(): bool
    {
        $quote = $this->checkoutSession->getQuote();
        $websiteId = (int)$quote->getStore()->getWebsiteId();

        return $this->allowedForCart->isAllowed($quote) && $this->config->isCheckoutTypeParallel($websiteId);
    }

    /**
     * Get button title.
     *
     * @return string
     */
    public function getButtonTitle(): string
    {
        $quote = $this->checkoutSession->getQuote();
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        return $this->config->getParallelCheckoutButtonTitle($websiteId);
    }

    /**
     * Check if checkout is disabled by quote.
     *
     * @return bool
     */
    public function isDisabledByQuote(): bool
    {
        return !$this->checkoutSession->getQuote()->validateMinimumAmount();
    }

    /**
     * Get parallel checkout url.
     *
     * @return string
     */
    public function getCheckoutUrl(): string
    {
        return $this->getUrl(
            'checkout',
            [
                '_secure' => $this->getRequest()->isSecure(),
                self::KEY_PARALLEL => true,
            ]
        );
    }
}
