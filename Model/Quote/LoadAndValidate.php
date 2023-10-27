<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
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
     * @param ShopIdValidator $shopIdValidator
     * @param Cart $cart
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     * @param QuoteResource $quoteResource
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        Cart $cart,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        QuoteResource $quoteResource,
        QuoteFactory $quoteFactory
    ) {
        $this->shopIdValidator = $shopIdValidator;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
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
    public function load(string $shopId , int $cartId): CartInterface
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $cartId);
        $this->storeManager->setCurrentStore($quote->getStoreId());
        $this->storeManager->getStore()->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        $this->checkoutSession->replaceQuote($quote);
        $this->cart->setQuote($quote);
        $this->shopIdValidator->validate($shopId, $quote->getStoreId());
        return $quote;
    }
}
