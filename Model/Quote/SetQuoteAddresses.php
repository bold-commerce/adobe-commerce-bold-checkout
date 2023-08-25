<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteAddressesInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Checkout\Model\Cart;
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param Builder $quoteResultBuilder
     * @param Cart $cart used for the backward compatibility with earlier versions of Magento.
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        Builder $quoteResultBuilder,
        Cart $cart
    ) {
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->cart = $cart;
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
            $quote = $this->cartRepository->getActive($cartId);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        if ($billingAddress === null) {
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->removeAddress($quote->getShippingAddress()->getId());
            $quote->collectTotals();
            $this->cartRepository->save($quote);
            $quote->getExtensionAttributes()->setShippingAssignments([]);
            return $this->quoteResultBuilder->createSuccessResult($quote);
        }
        $shippingAddress = $shippingAddress === null || $shippingAddress->getSameAsBilling()
            ? $billingAddress
            : $shippingAddress;
        $this->setBillingAddress($quote, $billingAddress);
        if (!$quote->isVirtual()) {
            $this->setShippingAddress($quote, $shippingAddress);
        }
        $this->cart->setQuote($quote);
        $quote->collectTotals();
        $this->cartRepository->save($quote);
        return $this->quoteResultBuilder->createSuccessResult($quote);
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
        if ($this->isAddressTheSame($billingAddress, $quote->getBillingAddress())) {
            return;
        }
        $billingAddress->setCustomerId($quote->getCustomerId());
        $quote->removeAddress($quote->getBillingAddress()->getId());
        $quote->setBillingAddress($billingAddress);
        $quote->setDataChanges(true);
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
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        if (!$shippingAddress->getShippingMethod() && $shippingMethod) {
            $shippingAddress->setShippingMethod($shippingMethod);
        }
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
     * Check if new address should be set to quote. Required by self-hosted checkout.
     *
     * @param AddressInterface $newAddress
     * @param AddressInterface|null $oldAddress
     * @return bool
     */
    private function isAddressTheSame(AddressInterface $newAddress, AddressInterface $oldAddress = null): bool
    {
        if ($oldAddress === null) {
            return false;
        }
        $newAddressData = $newAddress->getData();
        unset($newAddressData['region_code']);
        foreach ($newAddressData as $key => $newValue) {
            $oldValue = $oldAddress->getData($key);
            if (!\is_scalar($newValue)) {
                continue;
            }
            if ((string)$newValue !== (string)$oldValue) {
                return false;
            }
        }
        return true;
    }
}
