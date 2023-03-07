<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Payment\Gateway\Service;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

/**
 * Place order for given cart.
 */
class CreateOrderFromQuote
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param CartManagementInterface $cartManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $eventManager
     * @param OrderSender $orderSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $eventManager,
        OrderSender $orderSender,
        LoggerInterface $logger
    ) {
        $this->cartManagement = $cartManagement;
        $this->eventManager = $eventManager;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Create and place bold order from quote.
     *
     * @param CartInterface $cart
     * @param OrderDataInterface $orderPayload
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function create(CartInterface $cart, OrderDataInterface $orderPayload): OrderInterface
    {
        if (!$cart->getIsActive()) {
            throw new LocalizedException(__('Cannot create order from inactive cart.'));
        }
        $cart->getPayment()->setMethod(Service::CODE);
        $cart->getPayment()->setStoreId($cart->getStoreId());
        $cart->getPayment()->setCustomerPaymentId($cart->getCustomerId());
        $cart->setBillingAddress($orderPayload->getBillingAddress());
        $cart->getBillingAddress()->setShouldIgnoreValidation(true);
        $this->prepareCartForCustomer($cart);
        $orderData = [
            'ext_order_id' => $orderPayload->getOrderNumber(),
            'remote_ip' => $orderPayload->getBrowserIp(),
            'extension_attribute_public_id_public_id' => $orderPayload->getPublicId(),
            'extension_attribute_financial_status_financial_status' => $orderPayload->getFinancialStatus(),
            'extension_attribute_fulfillment_status_fulfillment_status' => $orderPayload->getFulfillmentStatus(),
        ];
        $order = $this->cartManagement->submit($cart, $orderData);
        $this->eventManager->dispatch(
            'checkout_type_onepage_save_order_after',
            ['order' => $order, 'quote' => $cart]
        );
        if ($order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
        $this->eventManager->dispatch(
            'checkout_submit_all_after',
            [
                'order' => $order,
                'quote' => $cart,
            ]
        );

        return $order;
    }

    /**
     * Add customer and customer address data to cart.
     *
     * @param CartInterface $cart
     * @return void
     */
    private function prepareCartForCustomer(CartInterface $cart): void
    {
        if (!$cart->getCustomerId()) {
            $cart->setCustomerEmail($cart->getBillingAddress()->getEmail());
            $cart->setCustomerIsGuest(true);
            $cart->setCustomerGroupId(GroupManagement::NOT_LOGGED_IN_ID);
            return;
        }
        $billing = $cart->getBillingAddress();
        $billing->setCustomerId($cart->getCustomerId());
        $shipping = $cart->isVirtual() ? null : $cart->getShippingAddress();
        if ($shipping) {
            $shipping->setCustomerId($cart->getCustomerId());
        }
        $customer = $this->customerRepository->getById($cart->getCustomerId());
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();
        if ($shipping && !$shipping->getSameAsBilling() &&
            (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                //Make provided address as default shipping address
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
            }
            $cart->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
        }
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                if (!$hasDefaultShipping) {
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $cart->addCustomerAddress($billingAddress);
            $billing->setCustomerAddressData($billingAddress);
        }
    }
}
