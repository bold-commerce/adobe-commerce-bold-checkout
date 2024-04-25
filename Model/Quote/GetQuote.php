<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\GetQuoteInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Bold\Checkout\Model\Quote\Result\Builder\ExtractShippingMethods;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Set quote addresses service.
 */
class GetQuote implements GetQuoteInterface
{
    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @var LoadAndValidate
     */
    private $loadAndValidate;

    /**
     * @var ExtractShippingMethods 
     */
    private $extractShippingMethods;

    /**
     * @var ShippingInformationInterfaceFactory 
     */
    private $shippingInformationFactory;

    /**
     * @var ShippingInformationManagementInterface 
     */
    private $shippingInformationManagement;

    /**
     * @var QuoteResource 
     */
    private $quoteResource;

    /**
     * @param Builder $quoteResultBuilder
     * @param ExtractShippingMethods $extractShippingMethods
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param QuoteResource $quoteResource
     * @param LoadAndValidate $loadAndValidate
     */
    public function __construct(
        Builder $quoteResultBuilder,
        ExtractShippingMethods $extractShippingMethods,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        ShippingInformationManagementInterface $shippingInformationManagement,
        QuoteResource $quoteResource,
        LoadAndValidate $loadAndValidate
    ) {
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->extractShippingMethods = $extractShippingMethods;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->quoteResource = $quoteResource;
        $this->loadAndValidate = $loadAndValidate;
    }

    /**
     * @inheritDoc
     */
    public function getQuote(
        string $shopId,
        int $cartId
    ): ResultInterface {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        $shipping = $quote->getExtensionAttributes()?->getShippingAssignments();
        if(count($shipping) > 0) {
            $shipping = $shipping[0]->getShipping();
            $shippingAddress = $shipping->getAddress()?->getCountryId();
            $selectedShippingMethod = $shipping->getMethod();
            $methods = $this->extractShippingMethods->extract($quote);
            if(!is_null($shippingAddress) && is_null($selectedShippingMethod) && count($methods) > 0) {
                $shippingInformation = $this->shippingInformationFactory->create()
                    ->setShippingAddress($quote->getShippingAddress())
                    ->setBillingAddress($quote->getBillingAddress())
                    ->setShippingCarrierCode($methods[0]->getCarrierCode())
                    ->setShippingMethodCode($methods[0]->getMethodCode());
                $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);

                try {
                    $quote = $this->loadAndValidate->load($shopId, $cartId);
                    $quote->collectTotals();
                    return $this->quoteResultBuilder->createSuccessResult($quote);
                } catch (LocalizedException $e) {
                    return $this->quoteResultBuilder->createErrorResult($e->getMessage());
                }
            }
        }

        $quote->collectTotals();
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
