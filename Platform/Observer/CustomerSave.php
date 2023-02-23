<?php

namespace Bold\Platform\Observer;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Observes the `customer_save_after_data_object` event.
 */
class CustomerSave implements ObserverInterface
{
    public const TOPIC_NAME = 'bold.checkout.sync.customers';

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
     * Observer for customer_save_after_data_object.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isCheckoutEnabled()) {
            $customer = $observer->getEvent()->getCustomerDataObject();
            $customerId = $customer->getId();
            $this->publisher->publish(self::TOPIC_NAME, [(int)$customerId]);
        }
    }
}
