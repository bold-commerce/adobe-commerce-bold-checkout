<?php

declare(strict_types=1);

namespace Bold\Platform\Plugin\Customer\Address;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Add Customer id to Bold Customers synchronization queue.
 */
class SavePlugin
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
     * Add Customer id to Bold Customers synchronization queue.
     *
     * @param \Magento\Customer\Model\Address\AddressModelInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(AddressModelInterface $subject, $result)
    {
        if ($this->config->isCheckoutEnabled()) {
            $customerId = $subject->getCustomerId();
            $this->publisher->publish(self::TOPIC_NAME, [(int)$customerId]);
        }

        return $result;
    }
}
