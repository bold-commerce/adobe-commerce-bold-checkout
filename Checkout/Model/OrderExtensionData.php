<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Model\ResourceModel\OrderExtensionData as OrderExtensionDataResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Bold order data entity.
 */
class OrderExtensionData extends AbstractModel
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(OrderExtensionDataResource::class);
    }

    /**
     * Set order entity id.
     *
     * @param int $orderId
     * @return void
     */
    public function setOrderId(int $orderId): void
    {
        $this->setData(OrderExtensionDataResource::ORDER_ID, $orderId);
    }

    /**
     * Retrieve order id.
     *
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->getData(OrderExtensionDataResource::ORDER_ID)
            ? (int)$this->getData(OrderExtensionDataResource::ORDER_ID)
            : null;
    }

    /**
     * Set order public id.
     *
     * @param string $publicId
     * @return void
     */
    public function setPublicId(string $publicId): void
    {
        $this->setData(OrderExtensionDataResource::PUBLIC_ID, $publicId);
    }

    /**
     * Retrieve public order id.
     *
     * @return string|null
     */
    public function getPublicId(): ?string
    {
        return $this->getData(OrderExtensionDataResource::PUBLIC_ID);
    }

    /**
     * Set order financial status.
     *
     * @param string|null $financialStatus
     * @return void
     */
    public function setFinancialStatus(?string $financialStatus): void
    {
        $this->setData(OrderExtensionDataResource::FINANCIAL_STATUS, $financialStatus);
    }

    /**
     * Retrieve financial order status.
     *
     * @return string|null
     */
    public function getFinancialStatus(): ?string
    {
        return $this->getData(OrderExtensionDataResource::FINANCIAL_STATUS);
    }

    /**
     * Set order fulfillment status.
     *
     * @param string|null $fulfillmentStatus
     * @return void
     */
    public function setFulfillmentStatus(?string $fulfillmentStatus): void
    {
        $this->setData(OrderExtensionDataResource::FULFILLMENT_STATUS, $fulfillmentStatus);
    }

    /**
     * Retrieve fulfillment order status.
     *
     * @return string|null
     */
    public function getFulfillmentStatus(): ?string
    {
        return $this->getData(OrderExtensionDataResource::FULFILLMENT_STATUS);
    }
}
