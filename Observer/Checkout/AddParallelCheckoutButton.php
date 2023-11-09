<?php

declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Block\Onepage\Button;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add parallel checkout button to shortcut buttons container.
 */
class AddParallelCheckoutButton implements ObserverInterface
{
    private const PARALLEL_CHECKOUT_TEMPLATE = 'Bold_Checkout::cart/checkout_button.phtml';

    /**
     * Add parallel checkout button to shortcut buttons container.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }
        $params = [
            'data' => [
                'template' => self::PARALLEL_CHECKOUT_TEMPLATE,
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
