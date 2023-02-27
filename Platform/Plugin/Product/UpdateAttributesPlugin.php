<?php

declare(strict_types=1);

namespace Bold\Platform\Plugin\Product;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Add updated Products ids to Bold Products synchronization queue.
 */
class UpdateAttributesPlugin
{

    public const TOPIC_NAME = 'bold.checkout.sync.products';

    /**
     * @var \Bold\Checkout\Model\ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private $publisher;

    /**
     * @param \Bold\Checkout\Model\ConfigInterface $config
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     */
    public function __construct(
        ConfigInterface    $config,
        PublisherInterface $publisher
    ) {
        $this->config = $config;
        $this->publisher = $publisher;
    }

    /**
     * Add updated Products ids to Bold Products synchronization queue.
     *
     * @param Action $subject
     * @param $result
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return mixed
     */
    public function afterUpdateAttributes(Action $subject, $result, $productIds, $attrData, $storeId)
    {
        if ($this->config->isCheckoutEnabled()) {
            $intIds = array_map(
                'intval', $productIds
            );
            $this->publisher->publish(self::TOPIC_NAME, $intIds);
        }

        return $result;
    }
}
