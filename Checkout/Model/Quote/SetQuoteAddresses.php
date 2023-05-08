<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteAddressesInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ShippingAddressAssignment;

/**
 * Set quote addresses service.
 */
class SetQuoteAddresses implements SetQuoteAddressesInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $responseFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var ShippingAddressAssignment
     */
    private $shippingAddressAssignment;

    /**
     * @param ResultInterfaceFactory $responseFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param ShippingAddressAssignment $shippingAddressAssignment
     */
    public function __construct(
        ResultInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory,
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        ShippingAddressAssignment $shippingAddressAssignment
    ) {
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->shippingAddressAssignment = $shippingAddressAssignment;
    }

    /**
     * @inheritDoc
     */
    public function setAddresses(
        string $shopId,
        int $cartId,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress = null
    ): ResultInterface {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
        } catch (LocalizedException $e) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                                'code' => 422,
                                'type' => 'server.validation_error',
                            ]
                        ),
                    ],
                ]
            );
        }
        $useForShipping = $shippingAddress ? $shippingAddress->getSameAsBilling() : true;
        $this->setBillingAddress($quote, $billingAddress, $useForShipping);
        if ($shippingAddress && !$useForShipping) {
            $this->shippingAddressAssignment->setAddress($quote, $shippingAddress);
        }
        $quote->collectTotals();
        $this->cartRepository->save($quote);
        return $this->responseFactory->create(
            [
                'quote' => $quote,
            ]
        );
    }

    /**
     * Set billing address to cart.
     *
     * @param CartInterface $quote
     * @param AddressInterface $billingAddress
     * @param bool $useForShipping
     * @return void
     */
    private function setBillingAddress(
        CartInterface $quote,
        AddressInterface $billingAddress,
        bool $useForShipping = false
    ) {
        $billingAddress->setCustomerId($quote->getCustomerId());
        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($billingAddress);
        $this->shippingAddressAssignment->setAddress($quote, $billingAddress, $useForShipping);
        $quote->setDataChanges(true);
    }
}
