<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder\Request;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface;
use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Place order request data model.
 */
class OrderData implements OrderDataInterface
{
    /**
     * @var int
     */
    private $quoteId;

    /**
     * @var string
     */
    private $browserIp;

    /**
     * @var string
     */
    private $publicId;

    /**
     * @var string
     */
    private $financialStatus;

    /**
     * @var string
     */
    private $fulfillmentStatus;

    /**
     * @var string
     */
    private $orderStatus;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var float
     */
    private $total;

    /**
     * @var PaymentInterface
     */
    private $payment;

    /**
     * @var TransactionInterface
     */
    private $transaction;

    /**
     * @var OrderDataExtensionInterface|null
     */
    private $extensionAttributes;


    /**
     * @inheritDoc
     */
    public function getQuoteId(): ?int
    {
        return $this->quoteId;
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId(int $quoteId): void
    {
        $this->quoteId = $quoteId;
    }

    /**
     * @inheritDoc
     */
    public function getBrowserIp(): ?string
    {
        return $this->browserIp;
    }

    /**
     * @inheritDoc
     */
    public function setBrowserIp(string $browserIp): void
    {
        $this->browserIp = $browserIp;
    }

    /**
     * @inheritDoc
     */
    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    /**
     * @inheritDoc
     */
    public function setPublicId(string $publicId): void
    {
        $this->publicId = $publicId;
    }

    /**
     * @inheritDoc
     */
    public function getFinancialStatus(): ?string
    {
        return $this->financialStatus;
    }

    /**
     * @inheritDoc
     */
    public function setFinancialStatus(string $financialStatus): void
    {
        $this->financialStatus = $financialStatus;
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentStatus(): ?string
    {
        return $this->fulfillmentStatus;
    }

    /**
     * @inheritDoc
     */
    public function setFulfillmentStatus(string $fulfillmentStatus): void
    {
        $this->fulfillmentStatus = $fulfillmentStatus;
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatus(): ?string
    {
        return $this->orderStatus;
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatus(string $orderStatus): void
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @inheritDoc
     */
    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    /**
     * @inheritDoc
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * @inheritDoc
     */
    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    /**
     * @inheritDoc
     */
    public function getPayment(): ?OrderPaymentInterface
    {
        return $this->payment;
    }

    /**
     * @inheritDoc
     */
    public function setPayment(\Magento\Sales\Api\Data\OrderPaymentInterface $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @inheritDoc
     */
    public function getTransaction(): ?TransactionInterface
    {
        return $this->transaction;
    }

    /**
     * @inheritDoc
     */
    public function setTransaction(TransactionInterface $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?OrderDataExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(?OrderDataExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
