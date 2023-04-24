<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

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
        $shippingAddress = $quote->getShippingAddress();
        foreach ($quote->getAllItems() as $item) {
            $this->addAppliedTaxesToItem($shippingAddress->getAppliedTaxes(), $item);
        }
        $quote->getExtensionAttributes()->setShippingTaxAmount($shippingAddress->getShippingTaxAmount());
        $quote->getExtensionAttributes()->setBaseShippingTaxAmount($shippingAddress->getBaseShippingTaxAmount());
    }

    /**
     * Populate cart item with applied taxes.
     *
     * @param array $appliedTaxes
     * @param CartItemInterface $item
     * @return void
     */
    private function addAppliedTaxesToItem(array $appliedTaxes, CartItemInterface $item): void
    {
        $taxDetails = [];
        foreach ($appliedTaxes as $appliedTaxData) {
            if ((int)$item->getItemId() !== (int)$appliedTaxData['item_id']) {
                continue;
            }
            $appliedTax = $this->appliedTaxFactory->create();
            $this->objectHelper->populateWithArray(
                $appliedTax,
                $appliedTaxData,
                AppliedTaxInterface::class
            );
            $taxDetails[] = $appliedTax;
        }
        $item->getExtensionAttributes()->setTaxDetails($taxDetails);
    }
}
