<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteAddressesInterface;
use Bold\Checkout\Model\ConfigInterface;
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
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param Builder $quoteResultBuilder
     * @param QuoteResource $quoteResource
     * @param LoadAndValidate $loadAndValidate
     * @param ConfigInterface $config
     */
    public function __construct(
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        Builder $quoteResultBuilder,
        QuoteResource $quoteResource,
        LoadAndValidate $loadAndValidate,
        ConfigInterface $config
    ) {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->quoteResource = $quoteResource;
        $this->loadAndValidate = $loadAndValidate;
        $this->config = $config;
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
        $quote->getBillingAddress()->addData($billingAddress->getData());
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->addData($shippingAddress->getData());
            $shippingAssignment = $this->shippingAssignmentProcessor->create($quote);
            $quote->getExtensionAttributes()->setShippingAssignments([$shippingAssignment]);
            $quote->setExtensionAttributes($quote->getExtensionAttributes());
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
        $quote->setDataChanges(true);
        $quote->collectTotals();
        $this->quoteResource->save($quote);
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
