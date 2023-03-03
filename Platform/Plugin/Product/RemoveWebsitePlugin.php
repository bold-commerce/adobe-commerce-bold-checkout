<?php

declare(strict_types=1);

namespace Bold\Platform\Plugin\Product;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Magento\Catalog\Model\Product\Website;
use Psr\Log\LoggerInterface;

class RemoveWebsitePlugin
{
    public const TOPIC_NAME = 'bold.checkout.delete.products';

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntitySyncPublisher   $publisher,
        LoggerInterface       $logger
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
    }
    /**
     * @param Website $subject
     * @param $result
     * @param array $websiteIds
     * @param array $productIds
     */
    public function afterRemoveProducts(Website $subject, $result, $websiteIds, $productIds)
    {
        $websiteIds = array_map('intval', $websiteIds);
        $productIds = array_map('intval', $productIds);
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, $productIds);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }
}
