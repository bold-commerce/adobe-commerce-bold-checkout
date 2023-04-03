<?php
declare(strict_types=1);

namespace Bold\Platform\Plugin\Quote\Item\Collection;

use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;

/**
 * Join applied taxes to quote item collection plugin.
 */
class LoadTaxDetailsPlugin
{
    /**
     * Join applied taxes data to given quote item collection.
     *
     * @param Collection $subject
     * @return void
     */
    public function beforeLoad(Collection $subject): void
    {
        if ($subject->isLoaded()) {
            return;
        }
        $table = $subject->getTable('bold_checkout_quote_item_tax');
        $subject->getSelect()->joinLeft(
            ['bold_quote_item_tax' => $table],
            'bold_quote_item_tax.item_id = main_table.item_id',
            ['tax_details']
        );
    }
}
