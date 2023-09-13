<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
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
     * @param OrderPaymentInterface $payment
     * @param TransactionInterface|null $transaction
     * @return void
     * @throws Exception
     */
    public function process(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        ?TransactionInterface $transaction = null
    ): void {
        $orderPayment = $order->getPayment();
        $orderPayment->addData($payment->getData());
        $baseAmountOrdered = !is_null($payment->getBaseAmountOrdered())
            ? $payment->getBaseAmountOrdered()
            : $order->getOrderCurrency()->convert(
                $payment->getAmountOrdered(),
                $order->getBaseCurrency()
            );
        $amountOrdered = !is_null($payment->getAmountOrdered())
            ? $payment->getAmountOrdered()
            : $order->getBaseCurrency()->convert(
                $baseAmountOrdered,
                $order->getOrderCurrency()
            );
        $baseAmountAuthorized = !is_null($payment->getBaseAmountAuthorized())
            ? $payment->getBaseAmountAuthorized()
            : $order->getOrderCurrency()->convert(
                $payment->getAmountAuthorized(),
                $order->getBaseCurrency()
            );
        $amountAuthorized = !is_null($payment->getAmountAuthorized())
            ? $payment->getAmountAuthorized()
            : $order->getBaseCurrency()->convert(
                $baseAmountAuthorized,
                $order->getOrderCurrency()
            );
        $baseAmountPaid = !is_null($payment->getBaseAmountPaid())
            ? $payment->getBaseAmountPaid()
            : $order->getOrderCurrency()->convert(
                $payment->getAmountPaid(),
                $order->getBaseCurrency()
            );
        $amountPaid = !is_null($payment->getAmountPaid())
            ? $payment->getAmountPaid()
            : $order->getBaseCurrency()->convert(
                $baseAmountPaid,
                $order->getOrderCurrency()
            );
        $orderPayment->setBaseAmountOrdered($baseAmountOrdered);
        $orderPayment->setAmountOrdered($amountOrdered);
        $orderPayment->setBaseAmountAuthorized($baseAmountAuthorized);
        $orderPayment->setAmountAuthorized($amountAuthorized ?: $amountOrdered);
        $orderPayment->setBaseAmountPaid($baseAmountPaid);
        $orderPayment->setAmountPaid($amountPaid);
        $orderPayment->setAdditionalInformation(
            array_merge(
                $orderPayment->getAdditionalInformation() ?: [],
                $payment->getExtensionAttributes()->getAdditionalInformation() ?: []
            )
        );
        if ($transaction) {
            $orderPayment->setTransactionId($transaction->getTxnId());
            $transaction = $orderPayment->addTransaction($transaction->getTxnType());
            if (!$orderPayment->getIsTransactionClosed()) {
                $transaction->setIsClosed(0);
            }
            
            $this->transactionRepository->save($transaction);
        }
        
        $this->orderPaymentRepository->save($orderPayment);
    }
}
