<?php

declare(strict_types=1);

namespace Bold\Platform\Plugin\Category;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Add Category id to Bold Category synchronization queue.
 */
class SavePlugin
{
    public const TOPIC_NAME = 'bold.checkout.sync.categories';

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
     * Add Category id to Bold Category synchronization queue.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(CategoryInterface $subject, $result)
    {
        if ($this->config->isCheckoutEnabled()) {
            $categoryId = $subject->getId();
            $this->publisher->publish(self::TOPIC_NAME, [(int)$categoryId]);
        }

        return $result;
    }
}
