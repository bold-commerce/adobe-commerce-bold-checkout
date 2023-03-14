<?php

declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Checkout\Model\Config;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Platform\Model\CreateIntegration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Observe 'admin_system_config_changed_section_checkout' event and register an Integration if needed.
 */
class CheckoutSectionSave implements ObserverInterface
{
    private const INTEGRATION_NAME_TEMPLATE = 'Bold Checkout Website \'%s\' Integration';
    private const OBSERVED_PATHS = [Config::PATH_TOKEN, Config::PATH_SECRET];

    /**
     * @var \Bold\Checkout\Model\ConfigInterface
     */
    private $config;

    /**
     * @var \Bold\Platform\Model\CreateIntegration
     */
    private $createIntegration;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @param \Bold\Checkout\Model\ConfigInterface $config
     * @param \Bold\Platform\Model\CreateIntegration $createIntegration
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        ConfigInterface       $config,
        CreateIntegration     $createIntegration,
        StoreManagerInterface $storeManager,
        ManagerInterface      $messageManager
    ) {
        $this->config = $config;
        $this->createIntegration = $createIntegration;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
    }

    /**
     * Observe 'admin_system_config_changed_section_checkout' event and register an Integration if needed.
     *
     * @param Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        //todo: revisit.
        return;
        $event = $observer->getEvent();
        $changedPaths = (array)$event->getChangedPaths();
        if (!array_intersect(self::OBSERVED_PATHS, $changedPaths)) {
            return;
        }

        $websiteId = (int)$event->getWebsite();
        $website = $this->storeManager->getWebsite($websiteId);
        $name = sprintf(self::INTEGRATION_NAME_TEMPLATE, $website->getCode());
        $token = $this->config->getApiToken($websiteId);
        $secret = $this->config->getSharedSecret($websiteId);
        try {
            $this->createIntegration->create($name, $token, $secret);
            $this->messageManager->addSuccessMessage(__('"%1" successfully created (or updated).', $name));
        } catch (IntegrationException $exception) {
            $this->messageManager->addErrorMessage(__('Unable to create (or update) "%1".', $name));
        }
    }
}
