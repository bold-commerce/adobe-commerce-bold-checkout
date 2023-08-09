<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\ResourceModel\Quote\ProgressResource;

/**
 * Create order progress service.
 */
class Progress
{
    /**
     * @var ProgressResource
     */
    private $progressResource;

    /**
     * @param ProgressResource $inProgressResource
     */
    public function __construct(
        ProgressResource $inProgressResource
    ) {
        $this->progressResource = $inProgressResource;
    }

    /**
     * Check if create order already in progress.
     *
     * @param OrderDataInterface $orderData
     * @return bool
     */
    public function isInProgress(OrderDataInterface $orderData): bool
    {
        return $this->progressResource->getIsInProgress($orderData->getQuoteId());
    }

    /**
     * Save is in progress data.
     *
     * @param OrderDataInterface $orderData
     * @return void
     */
    public function start(OrderDataInterface $orderData): void
    {
        $this->progressResource->create($orderData->getQuoteId());
    }

    /**
     * Delete is in progress data.
     *
     * @param OrderDataInterface $orderData
     * @return void
     */
    public function stop(OrderDataInterface $orderData): void
    {
        $this->progressResource->delete($orderData->getQuoteId());
    }
}
