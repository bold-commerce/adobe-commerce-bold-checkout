<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Config;

use Magento\Checkout\Model\Session;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * Is Bold Checkout payment is applicable for current quote.
 */
class CanUseCheckoutValueHandler implements ValueHandlerInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $subject, $storeId = null): bool
    {
        return $this->checkoutSession->getBoldCheckoutData() !== null
            && !$this->checkoutSession->getQuote()->getIsMultiShipping();
    }
}
