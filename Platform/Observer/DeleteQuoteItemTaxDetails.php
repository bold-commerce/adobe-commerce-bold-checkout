<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Platform\Model\ResourceModel\Quote\Item\DeleteTaxDetails;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Remove applied taxes from quote item observer.
 */
class DeleteQuoteItemTaxDetails implements ObserverInterface
{
    /**
     * @var DeleteTaxDetails
     */
    private $deleteTaxDetails;

    /**
     * @param DeleteTaxDetails $deleteTaxDetails
     */
    public function __construct(DeleteTaxDetails $deleteTaxDetails)
    {
        $this->deleteTaxDetails = $deleteTaxDetails;
    }

    /**
     * Remove applied taxes from quote item after item has been removed from cart.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getItem();
        $this->deleteTaxDetails->delete((int)$item->getId());
    }
}
