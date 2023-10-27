<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteShippingMethodInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Set quote shipping method service.
 */
class SetQuoteShippingMethod implements SetQuoteShippingMethodInterface
{
    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * @var LoadAndValidate
     */
    private $loadAndValidate;

    /**
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param Builder $quoteResultBuilder
     * @param LoadAndValidate $loadAndValidate
     */
    public function __construct(
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        Builder $quoteResultBuilder,
        LoadAndValidate $loadAndValidate
    ) {
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->loadAndValidate = $loadAndValidate;
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethod(
        string $shopId,
        int $cartId,
        string $shippingMethodCode,
        string $shippingCarrierCode
    ): ResultInterface {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        $shippingInformation = $this->shippingInformationFactory->create()
            ->setShippingAddress($quote->getShippingAddress())
            ->setBillingAddress($quote->getBillingAddress())
            ->setShippingCarrierCode($shippingCarrierCode)
            ->setShippingMethodCode($shippingMethodCode);
        $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
