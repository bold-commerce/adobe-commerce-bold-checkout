<?php

declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Block\Onepage\Button;
use Bold\Checkout\Model\Config;
use Bold\Checkout\Model\GetParallelCheckoutTemplate;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add parallel checkout button to shortcut buttons container.
 */
class AddParallelCheckoutButton implements ObserverInterface
{
    /**
     * @var GetParallelCheckoutTemplate
     */
    private $getParallelCheckoutTemplate;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param GetParallelCheckoutTemplate $getParallelCheckoutTemplate
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GetParallelCheckoutTemplate $getParallelCheckoutTemplate,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->getParallelCheckoutTemplate = $getParallelCheckoutTemplate;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * Add parallel checkout button to shortcut buttons container.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        if (!$this->config->isCheckoutEnabled($websiteId)
            || !$this->config->isCheckoutTypeParallel($websiteId)) {
            return;
        }
        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }
        $params = [
            'data' => [
                'template' => $this->getParallelCheckoutTemplate->getTemplate(),
            ],
        ];
        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        if ($shortcutButtons->getLayout()->getBlock(Button::ELEMENT_ALIAS)) {
            return;
        }
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            Button::class,
            Button::ELEMENT_ALIAS,
            $params
        );
        $shortcutButtons->addShortcut($shortcut);
    }
}
