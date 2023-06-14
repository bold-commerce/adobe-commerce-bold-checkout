<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

/**
 * Add bold order payment data to magento order payment.
 */
class ProcessOrderPayment
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Populate magento order payment with bold order payment data.
     *
     * @param OrderInterface $order
     * @param OrderDataInterface $orderData
     * @return void
     * @throws LocalizedException|\Exception
     */
    public function process(OrderInterface $order, OrderDataInterface $orderData): void
    {
        $orderPayment = $order->getPayment();
        if ($orderPayment->getBaseAmountAuthorized() || $orderPayment->getBaseAmountPaid()) {
            return;
        }
        $baseAmountOrdered = $orderData->getPayment()->getBaseAmountOrdered()
            ?: $order->getOrderCurrency()->convert(
                $orderData->getPayment()->getAmountOrdered(),
                $order->getBaseCurrency()
            );
        $amountOrdered = $orderData->getPayment()->getAmountOrdered()
            ?: $order->getBaseCurrency()->convert(
                $baseAmountOrdered,
                $order->getOrderCurrency()
            );
        $baseAmountAuthorized = $orderData->getPayment()->getBaseAmountAuthorized()
            ?: $order->getOrderCurrency()->convert(
                $orderData->getPayment()->getAmountAuthorized(),
                $order->getBaseCurrency()
            );
        $amountAuthorized = $orderData->getPayment()->getAmountAuthorized()
            ?: $order->getBaseCurrency()->convert(
                $baseAmountAuthorized,
                $order->getOrderCurrency()
            );
        $baseAmountPaid = $orderData->getPayment()->getBaseAmountPaid()
            ?: $order->getOrderCurrency()->convert(
                $orderData->getPayment()->getAmountPaid(),
                $order->getBaseCurrency()
            );
        $amountPaid = $orderData->getPayment()->getAmountPaid()
            ?: $order->getBaseCurrency()->convert(
                $baseAmountPaid,
                $order->getOrderCurrency()
            );
        $orderPayment->setBaseAmountOrdered($baseAmountOrdered);
        $orderPayment->setAmountOrdered($amountOrdered);
        $orderPayment->setBaseAmountAuthorized($baseAmountAuthorized);
        $orderPayment->setAmountAuthorized($amountAuthorized ?: $amountOrdered);
        $orderPayment->setBaseAmountPaid($baseAmountPaid);
        $orderPayment->setAmountPaid($amountPaid);
        $orderPayment->setTransactionId($orderData->getTransaction()->getTxnId());
        $transaction = $orderPayment->addTransaction($orderData->getTransaction()->getTxnType());
        if (!$orderPayment->getIsTransactionClosed()) {
            $transaction->setIsClosed(0);
        }
        $orderPayment->setAdditionalInformation(
            array_merge(
                $orderPayment->getAdditionalInformation() ?: [],
                $orderData->getPayment()->getExtensionAttributes()->getAdditionalInformation() ?: []
            )
        );
        $this->transactionRepository->save($transaction);
        $this->orderPaymentRepository->save($orderPayment);
    }
}
