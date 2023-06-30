<?php
declare(strict_types=1);

namespace Bold\CheckoutVatExempt\Observer;

use Bold\CheckoutVatExempt\Model\ResourceModel\LoadVatExemptData;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Load vat exempt information from database and set to checkout session.
 */
class LoadVatExemptInformation implements ObserverInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var LoadVatExemptData
     */
    private $loadVatExemptData;

    /**
     * @param Session $checkoutSession
     * @param LoadVatExemptData $loadVatExemptData
     */
    public function __construct(Session $checkoutSession, LoadVatExemptData $loadVatExemptData)
    {
        $this->checkoutSession = $checkoutSession;
        $this->loadVatExemptData = $loadVatExemptData;
    }

    /**
     * Load vat exempt information from database and set to checkout session.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $data = $this->loadVatExemptData->load((int)$observer->getEvent()->getQuote()->getId());
        if (!$data) {
            return;
        }
        $this->checkoutSession->setVatStatus($data['vat_exempt_status']);
        $this->checkoutSession->setVatApplientName($data['vat_applient_name']);
        $this->checkoutSession->setVatSelectedReason($data['vat_selected_reason']);
        $this->checkoutSession->setVatAgreeTermsandconditions($data['vat_agree_terms_and_conditions']);
    }
}
