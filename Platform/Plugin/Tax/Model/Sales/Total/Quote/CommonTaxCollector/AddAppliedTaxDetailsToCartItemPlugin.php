<?php
declare(strict_types=1);

namespace Bold\Platform\Plugin\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

/**
 * Save applied tax details in quote item extension attributes plugin.
 */
class AddAppliedTaxDetailsToCartItemPlugin
{
    /**
     * Save applied taxes in quote item extension attributes.
     *
     * @param CommonTaxCollector $subject
     * @param CommonTaxCollector $result
     * @param AbstractItem $quoteItem
     * @param TaxDetailsItemInterface $itemTaxDetails
     * @return CommonTaxCollector
     */
    public function afterUpdateItemTaxInfo(
        CommonTaxCollector $subject,
        CommonTaxCollector $result,
        AbstractItem $quoteItem,
        TaxDetailsItemInterface $itemTaxDetails
    ): CommonTaxCollector {
        $quoteItem->getExtensionAttributes()->setTaxDetails($itemTaxDetails->getAppliedTaxes());
        return $result;
    }
}
