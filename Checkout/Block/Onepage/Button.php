<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\Onepage;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block for parallel checkout handling.
 */
class Button extends Template
{
    public const KEY_PARALLEL = 'parallel';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session          $checkoutSession,
        ConfigInterface  $config,
        array            $data = []
    )
    {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
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

        return $this->config->isCheckoutEnabled($websiteId) && $this->config->isCheckoutTypeParallel($websiteId);
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
        return $this->getUrl('checkout', [self::KEY_PARALLEL => true]);
    }
}
