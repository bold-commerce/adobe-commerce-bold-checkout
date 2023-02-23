<?php

declare(strict_types=1);

namespace Bold\Platform\Plugin\Product;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Add Product id to Bold Products synchronization queue.
 */
class SavePlugin
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
     * Add Product id to Bold Products synchronization queue.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(ProductInterface $subject, $result)
    {
        if ($this->config->isCheckoutEnabled()) {
            $productId = $subject->getId();
            $this->publisher->publish(self::TOPIC_NAME, [(int)$productId]);
        }

        return $result;
    }
}
