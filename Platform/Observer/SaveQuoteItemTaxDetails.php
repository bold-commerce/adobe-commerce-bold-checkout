<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Platform\Model\ResourceModel\Quote\Item\DeleteTaxDetails;
use Bold\Platform\Model\ResourceModel\Quote\Item\SaveTaxDetails;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Tax\Api\Data\AppliedTaxInterface;

/**
 * Save applied tax details data for quote item observer.
 */
class SaveQuoteItemTaxDetails implements ObserverInterface
{
    /**
     * @var SaveTaxDetails
     */
    private $saveTaxDetails;

    /**
     * @var DeleteTaxDetails
     */
    private $deleteTaxDetails;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @param SaveTaxDetails $saveTaxDetails
     * @param DeleteTaxDetails $deleteTaxDetails
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        SaveTaxDetails $saveTaxDetails,
        DeleteTaxDetails $deleteTaxDetails,
        ExtensibleDataObjectConverter $dataObjectConverter
    ) {
        $this->saveTaxDetails = $saveTaxDetails;
        $this->deleteTaxDetails = $deleteTaxDetails;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Save applied tax details for given quote item.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        $taxDetails = $item->getExtensionAttributes()->getTaxDetails();
        if (!$taxDetails) {
            $this->deleteTaxDetails->delete((int)$item->getId());
        }
        $appliedTaxDetails = [];
        foreach ($taxDetails as $appliedTax) {
            $appliedTaxDetails[] = $this->dataObjectConverter->toNestedArray(
                $appliedTax,
                [],
                AppliedTaxInterface::class
            );
        }
        $this->saveTaxDetails->save((int)$item->getId(), $appliedTaxDetails);
    }
}
