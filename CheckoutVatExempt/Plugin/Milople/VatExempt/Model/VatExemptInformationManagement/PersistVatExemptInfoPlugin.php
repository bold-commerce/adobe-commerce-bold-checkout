<?php
declare(strict_types=1);

namespace Bold\CheckoutVatExempt\Plugin\Milople\VatExempt\Model\VatExemptInformationManagement;

use Bold\CheckoutVatExempt\Model\ResourceModel\SaveVatExemptData;
use Magento\Checkout\Model\Session;
use Milople\Vatexempt\Api\VatexemptInformationManagementInterface;

/**
 * Persist vat exempt data to database plugin.
 */
class PersistVatExemptInfoPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var SaveVatExemptData
     */
    private $saveVatExemptData;

    /**
     * @param Session $checkoutSession
     * @param SaveVatExemptData $saveVatExemptData
     */
    public function __construct(Session $checkoutSession, SaveVatExemptData $saveVatExemptData)
    {
        $this->checkoutSession = $checkoutSession;
        $this->saveVatExemptData = $saveVatExemptData;
    }

    /**
     * @param VatexemptInformationManagementInterface $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveVatexemptInformation(VatexemptInformationManagementInterface $subject, bool $result): bool
    {
        $vatExemptData = [
            'quote_id' => (int)$this->checkoutSession->getQuoteId(),
            'vat_exempt_status' => (int)$this->checkoutSession->getVatStatus(),
            'vat_applient_name' => (string)$this->checkoutSession->getVatApplientName(),
            'vat_selected_reason' => (string)$this->checkoutSession->getVatSelectedReason(),
            'vat_agree_terms_and_conditions' => (int)$this->checkoutSession->getVatAgreeTermsandconditions(),
        ];
        $this->saveVatExemptData->save($vatExemptData);
        return $result;
    }
}
