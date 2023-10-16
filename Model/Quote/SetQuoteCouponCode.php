<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteCouponCodeInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Set quote coupon code service.
 */
class SetQuoteCouponCode implements SetQuoteCouponCodeInterface
{
    /**
     * @var CouponManagementInterface
     */
    private $couponService;

    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @param ShopIdValidator $shopIdValidator
     * @param CouponManagementInterface $couponService
     * @param CartRepositoryInterface $cartRepository
     * @param Builder $quoteResultBuilder
     * @param ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     * @param Cart $cart used for the backward compatibility with earlier versions of Magento.
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        CouponManagementInterface $couponService,
        CartRepositoryInterface $cartRepository,
        Builder $quoteResultBuilder,
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        Cart $cart
    ) {
        $this->couponService = $couponService;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
    }

    /**
     * @inheritdoc
     */
    public function setCoupon(string $shopId, int $cartId, string $couponCode): ResultInterface
    {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->checkoutSession->replaceQuote($quote);
            $this->cart->setQuote($quote);
            $this->storeManager->setCurrentStore($quote->getStoreId());
            $this->storeManager->getStore()->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
            if ($this->config->isCheckoutTypeSelfHosted((int)$quote->getStore()->getWebsiteId())) {
                return $this->quoteResultBuilder->createSuccessResult($quote);
            }
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
            $this->couponService->set($cartId, $couponCode);
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
