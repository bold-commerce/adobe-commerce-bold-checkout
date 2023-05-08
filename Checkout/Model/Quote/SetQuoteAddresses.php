<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultInterface;
use Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultInterfaceFactory;
use Bold\Checkout\Api\Quote\SetQuoteAddressesInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\SetQuoteAddresses\ExtractCartTotals;
use Bold\Checkout\Model\Quote\SetQuoteAddresses\ExtractShippingMethods;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;

/**
 * Set quote addresses service.
 */
class SetQuoteAddresses implements SetQuoteAddressesInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

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
     * @var ExtractShippingMethods
     */
    private $extractShippingMethods;

    /**
     * @var ExtractCartTotals
     */
    private $extractCartTotals;

    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param ExtractShippingMethods $extractShippingMethods
     * @param ExtractCartTotals $extractCartTotals
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory,
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        ExtractShippingMethods $extractShippingMethods,
        ExtractCartTotals $extractCartTotals
    ) {
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->extractShippingMethods = $extractShippingMethods;
        $this->extractCartTotals = $extractCartTotals;
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
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
            return $this->resultFactory->create(
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
        $shippingAddress = $shippingAddress === null || $shippingAddress->getSameAsBilling()
            ? $billingAddress
            : $shippingAddress;
        $this->setBillingAddress($quote, $billingAddress);
        $this->setShippingAddress($quote, $shippingAddress);
        $quote->collectTotals();
        $this->cartRepository->save($quote);
        $this->processQuoteItems($quote);
        return $this->resultFactory->create(
            [
                'quote' => $quote,
                'totals' => $this->extractCartTotals->extract($quote),
                'shippingMethods' => $this->extractShippingMethods->extract($quote),
            ]
        );
    }

    /**
     * Set billing address to cart.
     *
     * @param CartInterface $quote
     * @param AddressInterface $billingAddress
     * @return void
     */
    private function setBillingAddress(
        CartInterface $quote,
        AddressInterface $billingAddress
    ) {
        $billingAddress->setCustomerId($quote->getCustomerId());
        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($billingAddress);
    }

    /**
     * Set shipping address to cart.
     *
     * @param CartInterface $quote
     * @param AddressInterface $shippingAddress
     * @return void
     */
    private function setShippingAddress(
        CartInterface $quote,
        AddressInterface $shippingAddress
    ) {
        $shippingAddress->setCustomerId($quote->getCustomerId());
        $quote->removeAddress($quote->getShippingAddress()->getId());
        $quote->setShippingAddress($shippingAddress);
        $cartExtension = $quote->getExtensionAttributes();
        $shippingAssignment = $this->shippingAssignmentProcessor->create($quote);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        $quote->setExtensionAttributes($cartExtension);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->setDataChanges(true);
    }

    /**
     * Add product to quote items extension attributes.
     *
     * This is needed for Bold Checkout to be able to display product information in the cart.
     *
     * @param CartInterface $quote
     * @return void
     */
    private function processQuoteItems(CartInterface $quote): void
    {
        $quoteItems = $quote->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            $quoteItem->getExtensionAttributes()->setProduct($quoteItem->getProduct());
        }
    }
}
