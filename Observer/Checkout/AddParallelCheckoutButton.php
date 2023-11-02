<?php

declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Block\Onepage\Button;
use Bold\Checkout\Model\GetParallelCheckoutTemplate;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * @param GetParallelCheckoutTemplate $getParallelCheckoutTemplate
     */
    public function __construct(GetParallelCheckoutTemplate $getParallelCheckoutTemplate)
    {
        $this->getParallelCheckoutTemplate = $getParallelCheckoutTemplate;
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
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            Button::class,
            Button::ELEMENT_ALIAS,
            $params
        );
        $shortcutButtons->addShortcut($shortcut);
    }
}
