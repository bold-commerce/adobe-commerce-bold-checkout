<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Order\PlaceOrder;

use Bold\Platform\Api\Data\PlaceOrder\Request\OrderDataInterface;
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
     * @throws LocalizedException
     */
    public function process(OrderInterface $order, OrderDataInterface $orderData): void
    {
        $orderPayment = $order->getPayment();
        $orderPayment->setBaseAmountOrdered($orderData->getPayment()->getBaseAmountOrdered());
        $orderPayment->setAmountOrdered(
            $order->getBaseCurrency()->convert(
                $orderData->getPayment()->getBaseAmountOrdered(),
                $order->getOrderCurrency()
            )
        );
        $orderPayment->setBaseAmountAuthorized($orderData->getPayment()->getBaseAmountOrdered());
        $orderPayment->setAmountAuthorized(
            $order->getBaseCurrency()->convert($orderData->getPayment()->getBaseAmountOrdered())
        );
        $orderPayment->setBaseAmountPaid($orderData->getPayment()->getBaseAmountPaid());
        $orderPayment->setAmountPaid(
            $order->getBaseCurrency()->convert(
                $orderData->getPayment()->getBaseAmountPaid(),
                $order->getOrderCurrency()
            )
        );
        $orderPayment->setTransactionId($orderData->getTransaction()->getTxnId());
        $transaction = $orderPayment->addTransaction($orderData->getTransaction()->getTxnType());
        if (!$orderPayment->getIsTransactionClosed()) {
            $transaction->setIsClosed(0);
        }
        $this->transactionRepository->save($transaction);
        $this->orderPaymentRepository->save($orderPayment);
    }
}
