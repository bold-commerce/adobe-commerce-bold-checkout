<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Quote;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Tax\Api\Data\AppliedTaxInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;

/**
 * Add applied taxes to cart items and quote.
 */
class AddAppliedTaxToQuoteObserver implements ObserverInterface
{
    /**
     * @var AppliedTaxInterfaceFactory
     */
    private $appliedTaxFactory;

    /**
     * @var SimpleDataObjectConverter
     */
    private $objectHelper;

    /**
     * @param AppliedTaxInterfaceFactory $appliedTaxFactory
     * @param DataObjectHelper $objectHelper
     */
    public function __construct(
        AppliedTaxInterfaceFactory $appliedTaxFactory,
        DataObjectHelper $objectHelper
    ) {
        $this->objectHelper = $objectHelper;
        $this->appliedTaxFactory = $appliedTaxFactory;
    }

    /**
     * Add applied taxes to quote items and quote.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $this->addAppliedTaxesToItem($item);
        }
        $shippingAddress = $quote->getShippingAddress();
        $quote->getExtensionAttributes()->setShippingTaxAmount($shippingAddress->getShippingTaxAmount());
        $quote->getExtensionAttributes()->setBaseShippingTaxAmount($shippingAddress->getBaseShippingTaxAmount());
    }

    /**
     * Populate cart item with applied taxes.
     *
     * @param CartItemInterface $item
     * @return void
     */
    private function addAppliedTaxesToItem(CartItemInterface $item): void
    {
        if ($item->getChildren()) {
            return;
        }
        $origItem = $item;
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }
        if (!$item->getAppliedTaxes()) {
            return;
        }
        $itemTaxDetails = [];
        foreach ($item->getAppliedTaxes() as $tax) {
            $itemAppliedTax = $this->appliedTaxFactory->create();
            $this->objectHelper->populateWithArray(
                $itemAppliedTax,
                $tax,
                AppliedTaxInterface::class
            );
            $itemTaxDetails[] = $itemAppliedTax;
        }
        $origItem->getExtensionAttributes()->setTaxDetails($itemTaxDetails);
    }
}
