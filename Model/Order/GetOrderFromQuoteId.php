<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Order\GetOrderFromQuoteIdInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GetOrderFromQuoteId implements GetOrderFromQuoteIdInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param int $quoteId
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function getOrder(int $quoteId): OrderInterface
    {
        $searchCriteria =  $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::QUOTE_ID, $quoteId)
            ->create();

        $ordersList = $this->orderRepository->getList($searchCriteria);

        if ($ordersList->getTotalCount() === 0) {
            throw new LocalizedException(__('No order found with quote id="%1"', $quoteId));
        }

        $orders = $ordersList->getItems();
        return reset($orders);
    }
}
