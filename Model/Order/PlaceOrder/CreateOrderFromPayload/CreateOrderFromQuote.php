<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder\CreateOrderFromPayload;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Payment\Gateway\Service;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\ShippingAssignmentBuilder;
use Magento\Tax\Api\OrderTaxManagementInterface;

/**
 * Place order for given cart.
 */
class CreateOrderFromQuote
{
    private const BROWSER_IP = 'remote_ip';
    private const ORDER_NUMBER = 'ext_order_id';

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var OrderTaxManagementInterface
     */
    private $orderTaxManagement;

    /**
     * @var ShippingAssignmentBuilder
     */
    private $shippingAssignmentBuilder;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param CartManagementInterface $cartManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderTaxManagementInterface $orderTaxManagement
     * @param ShippingAssignmentBuilder $shippingAssignmentBuilder
     * @param ManagerInterface $eventManager
     * @param Cart $cart // used for the backward compatibility with earlier versions of Magento.
     * @param Session $session
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        CustomerRepositoryInterface $customerRepository,
        OrderTaxManagementInterface $orderTaxManagement,
        ShippingAssignmentBuilder $shippingAssignmentBuilder,
        ManagerInterface $eventManager,
        Cart $cart,
        Session $session
    ) {
        $this->cartManagement = $cartManagement;
        $this->eventManager = $eventManager;
        $this->customerRepository = $customerRepository;
        $this->orderTaxManagement = $orderTaxManagement;
        $this->shippingAssignmentBuilder = $shippingAssignmentBuilder;
        $this->cart = $cart;
        $this->session = $session;
    }

    /**
     * Create and place bold order from quote.
     *
     * @param CartInterface $quote
     * @param OrderDataInterface $orderPayload
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function create(CartInterface $quote, OrderDataInterface $orderPayload): OrderInterface
    {
        if (!$quote->getIsActive()) {
            throw new LocalizedException(__('Cannot create order from inactive cart.'));
        }
        $quote->getPayment()->setMethod(Service::CODE);
        $quote->getPayment()->setStoreId($quote->getStoreId());
        $quote->getPayment()->setCustomerPaymentId($quote->getCustomerId());
        $this->prepareCartForCustomer($quote);
        $orderData = [
            self::ORDER_NUMBER => $orderPayload->getOrderNumber(),
            self::BROWSER_IP => $orderPayload->getBrowserIp(),
        ];
        $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$quote->isVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
        $orderData = new DataObject($orderData);
        $this->eventManager->dispatch(
            'bold_checkout_create_order_from_quote_submit_before',
            [
                'quote' => $quote,
                'orderPayload' => $orderPayload,
                'orderData' => $orderData,
            ]
        );
        $this->cart->setQuote($quote);
        $this->session->setQuoteId($quote->getId());
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $order = $this->cartManagement->submit($quote, $orderData->getData());
        $this->setOrderTaxDetails($order);
        if (!$quote->getIsVirtual()) {
            $this->setShippingAssignments($order);
        }
        $this->eventManager->dispatch(
            'checkout_type_onepage_save_order_after',
            ['order' => $order, 'quote' => $quote]
        );
        $this->eventManager->dispatch(
            'checkout_submit_all_after',
            [
                'order' => $order,
                'quote' => $quote,
                'order_payload' => $orderPayload,
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
            $cart->setCustomerPrefix($cart->getBillingAddress()->getPrefix());
            $cart->setCustomerFirstname($cart->getBillingAddress()->getFirstname());
            $cart->setCustomerMiddlename($cart->getBillingAddress()->getMiddlename());
            $cart->setCustomerLastname($cart->getBillingAddress()->getLastname());
            $cart->setCustomerSuffix($cart->getBillingAddress()->getSuffix());
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

    /**
     * Set order tax details to extension attributes.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function setOrderTaxDetails(OrderInterface $order): void
    {
        $extensionAttributes = $order->getExtensionAttributes();
        $orderTaxDetails = $this->orderTaxManagement->getOrderTaxDetails($order->getEntityId());
        $appliedTaxes = $orderTaxDetails->getAppliedTaxes();
        $extensionAttributes->setAppliedTaxes($appliedTaxes);
        if (!empty($appliedTaxes)) {
            $extensionAttributes->setConvertingFromQuote(true);
        }
        $items = $orderTaxDetails->getItems();
        $extensionAttributes->setItemAppliedTaxes($items);
        $order->setExtensionAttributes($extensionAttributes);
    }

    /**
     * Set shipping assignments to extension attributes.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function setShippingAssignments(OrderInterface $order): void
    {
        if ($order->getExtensionAttributes()->getShippingAssignments()) {
            return;
        }
        $this->shippingAssignmentBuilder->setOrderId($order->getEntityId());
        $order->getExtensionAttributes()->setShippingAssignments($this->shippingAssignmentBuilder->create());
    }
}
