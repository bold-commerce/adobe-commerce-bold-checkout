<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Order\PlaceOrder\Request;

use Bold\Platform\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface;
use Bold\Platform\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Magento\Quote\Api\Data\AddressInterface;
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
     * @var AddressInterface
     */
    private $billingAddress;

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
     * @param int $quoteId
     * @param string $browserIp
     * @param string $publicId
     * @param string $financialStatus
     * @param string $fulfillmentStatus
     * @param string $orderStatus
     * @param string $orderNumber
     * @param float $total
     * @param AddressInterface $billingAddress
     * @param OrderPaymentInterface $payment
     * @param TransactionInterface $transaction
     * @param OrderDataExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        int $quoteId,
        string $browserIp,
        string $publicId,
        string $financialStatus,
        string $fulfillmentStatus,
        string $orderStatus,
        string $orderNumber,
        float $total,
        AddressInterface $billingAddress,
        OrderPaymentInterface $payment,
        TransactionInterface $transaction,
        OrderDataExtensionInterface $extensionAttributes = null
    ) {
        $this->quoteId = $quoteId;
        $this->browserIp = $browserIp;
        $this->publicId = $publicId;
        $this->financialStatus = $financialStatus;
        $this->fulfillmentStatus = $fulfillmentStatus;
        $this->orderStatus = $orderStatus;
        $this->orderNumber = $orderNumber;
        $this->total = $total;
        $this->payment = $payment;
        $this->billingAddress = $billingAddress;
        $this->extensionAttributes = $extensionAttributes;
        $this->transaction = $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId(): int
    {
        return $this->quoteId;
    }

    /**
     * @inheritDoc
     */
    public function getBrowserIp(): string
    {
        return $this->browserIp;
    }

    /**
     * @inheritDoc
     */
    public function getPublicId(): string
    {
        return $this->publicId;
    }

    /**
     * @inheritDoc
     */
    public function getFinancialStatus(): string
    {
        return $this->financialStatus;
    }

    /**
     * @inheritDoc
     */
    public function getFulfillmentStatus(): string
    {
        return $this->fulfillmentStatus;
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }

    /**
     * @inheritDoc
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @inheritDoc
     */
    public function getBillingAddress(): AddressInterface
    {
        return $this->billingAddress;
    }

    /**
     * @inheritDoc
     */
    public function getPayment(): OrderPaymentInterface
    {
        return $this->payment;
    }

    /**
     * @inheritDoc
     */
    public function getTransaction(): TransactionInterface
    {
        return $this->transaction;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?OrderDataExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
