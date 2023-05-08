<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteShippingMethodInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Set quote shipping method service.
 */
class SetQuoteShippingMethod implements SetQuoteShippingMethodInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $quoteResultBuilder;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param Builder $quoteResultBuilder
     */
    public function __construct(
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        Builder $quoteResultBuilder
    ) {
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->cartRepository = $cartRepository;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->shopIdValidator = $shopIdValidator;
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
            $quote = $this->cartRepository->getActive($cartId);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        $quote = $this->cartRepository->get($cartId);
        $shippingInformation = $this->shippingInformationFactory->create()
            ->setShippingAddress($quote->getShippingAddress())
            ->setBillingAddress($quote->getBillingAddress())
            ->setShippingCarrierCode($shippingCarrierCode)
            ->setShippingMethodCode($shippingMethodCode);
        $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
