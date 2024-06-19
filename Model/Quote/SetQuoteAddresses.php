<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteAddressesInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Set quote addresses service.
 */
class SetQuoteAddresses implements SetQuoteAddressesInterface
{
    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var LoadAndValidate
     */
    private $loadAndValidate;

    /**
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param Builder $quoteResultBuilder
     * @param QuoteResource $quoteResource
     * @param LoadAndValidate $loadAndValidate
     */
    public function __construct(
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        Builder $quoteResultBuilder,
        QuoteResource $quoteResource,
        LoadAndValidate $loadAndValidate
    ) {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->quoteResource = $quoteResource;
        $this->loadAndValidate = $loadAndValidate;
    }

    /**
     * @inheritDoc
     */
    public function setAddresses(
        string $shopId,
        int $cartId,
        AddressInterface $billingAddress = null,
        AddressInterface $shippingAddress = null
    ): ResultInterface {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        if ($billingAddress === null) {
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->removeAddress($quote->getShippingAddress()->getId());
            $quote->setDataChanges(true);
            $quote->collectTotals();
            $this->quoteResource->save($quote);
            $quote->getExtensionAttributes()->setShippingAssignments([]);
            return $this->quoteResultBuilder->createSuccessResult($quote);
        }
        $shippingAddress = $shippingAddress === null || $shippingAddress->getSameAsBilling()
            ? $billingAddress
            : $shippingAddress;
        if ($this->addressDataChanged($quote->getBillingAddress(), $billingAddress)) {
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
        }
        if (!$quote->isVirtual()) {
            if ($this->addressDataChanged($quote->getShippingAddress(), $shippingAddress)) {
                $quote->removeAddress($quote->getShippingAddress()->getId());
                $quote->setShippingAddress($shippingAddress);
            }
            $shippingAssignment = $this->shippingAssignmentProcessor->create($quote);
            $quote->getExtensionAttributes()->setShippingAssignments([$shippingAssignment]);
            $quote->setExtensionAttributes($quote->getExtensionAttributes());
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        if (!$quote->getCustomerEmail()) {
            $quote->setCustomerEmail($billingAddress->getEmail());
        }

        $quote->setDataChanges(true);
        $quote->collectTotals();
        $this->quoteResource->save($quote);
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }

    /**
     * Check if address data has changed.
     *
     * @param AddressInterface $originalAddress
     * @param AddressInterface $newAddress
     * @return bool
     */
    private function addressDataChanged(AddressInterface $originalAddress, AddressInterface $newAddress): bool
    {
        return $originalAddress->getFirstname() !== $newAddress->getFirstname()
            || $originalAddress->getLastname() !== $newAddress->getLastname()
            || $originalAddress->getStreet() !== $newAddress->getStreet()
            || $originalAddress->getCity() !== $newAddress->getCity()
            || $originalAddress->getCompany() !== $newAddress->getCompany()
            || $originalAddress->getRegion() !== $newAddress->getRegion()
            || $originalAddress->getRegionCode() !== $newAddress->getRegionCode()
            || (int)$originalAddress->getRegionId() !== (int)$newAddress->getRegionId()
            || $originalAddress->getPostcode() !== $newAddress->getPostcode()
            || $originalAddress->getCountryId() !== $newAddress->getCountryId()
            || $originalAddress->getTelephone() !== $newAddress->getTelephone();
    }
}
