<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteCouponCodeInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Set quote coupon code service.
 */
class SetQuoteCouponCode implements SetQuoteCouponCodeInterface
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
     * @var Quote
     */
    private $quoteResource;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Builder $quoteResultBuilder
     * @param LoadAndValidate $loadAndValidate
     * @param Quote $quoteResource
     * @param ConfigInterface $config
     */
    public function __construct(
        Builder $quoteResultBuilder,
        LoadAndValidate $loadAndValidate,
        Quote $quoteResource,
        ConfigInterface $config
    ) {
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->loadAndValidate = $loadAndValidate;
        $this->quoteResource = $quoteResource;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function setCoupon(string $shopId, int $cartId, string $couponCode): ResultInterface
    {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode($couponCode);
            $quote->collectTotals();
            $this->quoteResource->save($quote);
            $validateCouponCodes = $this->config->getIsValidateCouponCodes((int)$quote->getStore()->getWebsiteId());
            if ($validateCouponCodes && $quote->getCouponCode() !== $couponCode) {
                throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
            }
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
