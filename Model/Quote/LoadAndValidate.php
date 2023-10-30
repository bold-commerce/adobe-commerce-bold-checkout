<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\LoadAndValidate\StoreIdResolver;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Load quote by id and validate shop id.
 */
class LoadAndValidate
{
    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var StoreIdResolver $cartStoreIdResolver
     */
    private $cartStoreIdResolver;

    /**
     * @param ShopIdValidator $shopIdValidator
     * @param Cart $cart
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     * @param QuoteResource $quoteResource
     * @param QuoteFactory $quoteFactory
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param CartExtensionFactory $cartExtensionFactory
     * @param StoreIdResolver $cartStoreIdResolver
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        Cart $cart,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        QuoteResource $quoteResource,
        QuoteFactory $quoteFactory,
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        CartExtensionFactory $cartExtensionFactory,
        StoreIdResolver $cartStoreIdResolver
    ) {
        $this->shopIdValidator = $shopIdValidator;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->cartStoreIdResolver = $cartStoreIdResolver;
    }

    /**
     * Load quote by id and validate shop id.
     *
     * @param string $shopId
     * @param int $cartId
     * @return CartInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function load(string $shopId, int $cartId): CartInterface
    {
        $quote = $this->quoteFactory->create();
        $storeId = $this->cartStoreIdResolver->resolve($cartId);
        $quote->setStoreId($storeId);
        $this->quoteResource->load($quote, $cartId);
        $this->storeManager->getStore()->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        $this->checkoutSession->replaceQuote($quote);
        $this->cart->setQuote($quote);
        $this->shopIdValidator->validate($shopId, (int)$quote->getStoreId());
        $quote->setItems($quote->getAllVisibleItems());
        $shippingAssignments = [];
        if (!$quote->isVirtual() && $quote->getItemsQty() > 0) {
            $shippingAssignments[] = $this->shippingAssignmentProcessor->create($quote);
        }
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        $cartExtension->setShippingAssignments($shippingAssignments);
        $quote->setExtensionAttributes($cartExtension);
        return $quote;
    }
}
