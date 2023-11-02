<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Config;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * Is Bold Payment active value handler.
 */
class IsActiveValueHandler implements ValueHandlerInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param State $state
     * @param Session $checkoutSession
     */
    public function __construct(State $state, Session $checkoutSession)
    {
        $this->state = $state;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheirtDoc
     */
    public function handle(array $subject, $storeId = null)
    {
        try {
            if ($this->state->getAreaCode() === Area::AREA_FRONTEND) {
                return $this->checkoutSession->getBoldCheckoutData() !== null;
            }
        } catch (LocalizedException $e) {
            return false;
        }
        return true;
    }
}
