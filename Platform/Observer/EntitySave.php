<?php

declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Observer for entity \Magento\Framework\Model\AbstractModel '_save_after' event.
 */
class EntitySave implements ObserverInterface
{
    /**
     * @var \Bold\Checkout\Model\ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private $publisher;

    /**
     * @var string
     */
    private $topicName;

    /**
     * @var string
     */
    private $entityIdField;

    /**
     * @param \Bold\Checkout\Model\ConfigInterface $config
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     * @param string $topicName
     * @param string $entityIdField
     */
    public function __construct(
        ConfigInterface    $config,
        PublisherInterface $publisher,
        string             $topicName,
        string             $entityIdField = 'entity_id'
    ) {
        $this->config = $config;
        $this->publisher = $publisher;
        $this->topicName = $topicName;
        $this->entityIdField = $entityIdField;
    }

    /**
     * Observer for entity \Magento\Framework\Model\AbstractModel '_save_after' event.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isCheckoutEnabled()) {
            /** @var \Magento\Framework\Model\AbstractModel $entity */
            $entity = $observer->getEvent()->getDataObject();
            $entityId = $entity->getData($this->entityIdField);
            $this->publisher->publish($this->topicName, [(int)$entityId]);
        }
    }
}
