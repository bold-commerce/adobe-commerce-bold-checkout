<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteCouponCodeInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;

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
     * @param ShopIdValidator $shopIdValidator
     * @param CouponManagementInterface $couponService
     * @param CartRepositoryInterface $cartRepository
     * @param Builder $quoteResultBuilder
     * @param ConfigInterface $config
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        CouponManagementInterface $couponService,
        CartRepositoryInterface $cartRepository,
        Builder $quoteResultBuilder,
        ConfigInterface $config
    ) {
        $this->couponService = $couponService;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function setCoupon(string $shopId, int $cartId, string $couponCode): ResultInterface
    {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            if ($this->config->isSelfHostedCheckoutEnabled((int)$quote->getStore()->getWebsite())) {
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
