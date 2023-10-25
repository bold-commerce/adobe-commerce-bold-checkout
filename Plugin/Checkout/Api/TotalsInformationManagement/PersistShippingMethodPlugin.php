<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Checkout\Api\TotalsInformationManagement;

use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;

/**
 * Persist shipping method to quote if Bold Checkout is allowed.
 */
class PersistShippingMethodPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var IsBoldCheckoutAllowedForCart
     */
    private $isBoldCheckoutAllowedForCart;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param IsBoldCheckoutAllowedForCart $isBoldCheckoutAllowedForCart
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        IsBoldCheckoutAllowedForCart $isBoldCheckoutAllowedForCart
    ) {
        $this->cartRepository = $cartRepository;
        $this->isBoldCheckoutAllowedForCart = $isBoldCheckoutAllowedForCart;
    }

    /**
     * Persist pre-selected shipping method before navigating to Bold Checkout.
     *
     * @param TotalsInformationManagementInterface $subject
     * @param TotalsInterface $result
     * @param int $cartId
     * @return TotalsInterface
     */
    public function afterCalculate(
        TotalsInformationManagementInterface $subject,
        TotalsInterface $result,
        $cartId
    ): TotalsInterface {
        $cart = $this->cartRepository->get($cartId);
        if (!$this->isBoldCheckoutAllowedForCart->isAllowed($cart)) {
            return $result;
        }
        try {
            $cart->getExtensionAttributes()->setShippingAssignments(null);
        } catch (\Throwable $e) {
            //No shipping assignments extension attributes.
        }
        $this->cartRepository->save($cart);
        return $result;
    }
}
