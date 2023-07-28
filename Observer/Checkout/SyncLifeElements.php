<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\SyncLifeElementsProcessorInterface;
use Bold\Checkout\Model\ConfigInterface;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

/**
 * Observe 'admin_system_config_changed_section_checkout' event and sync (LiFE) elements.
 */
class SyncLifeElements implements ObserverInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var SyncLifeElementsProcessorInterface
     */
    private $syncLifeElementsProcessor;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @param ConfigInterface $config
     * @param ClientInterface $client
     * @param SyncLifeElementsProcessorInterface $syncLifeElementsProcessor
     * @param MessageManager $messageManager
     */
    public function __construct(
        ConfigInterface $config,
        ClientInterface $client,
        SyncLifeElementsProcessorInterface $syncLifeElementsProcessor,
        MessageManager $messageManager
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->syncLifeElementsProcessor = $syncLifeElementsProcessor;
        $this->messageManager = $messageManager;
    }

    /**
     * Sync (LiFE) Elements.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $websiteId = (int)$event->getWebsite();

        if (!$this->config->isCheckoutEnabled($websiteId)
            && (!$this->config->isCheckoutTypeStandard($websiteId)
                || !$this->config->isCheckoutTypeParallel($websiteId))
        ) {
            return;
        }

        $magentoLifeElements = $this->config->getLifeElements($websiteId);
        if (empty($magentoLifeElements)) {
            return;
        }
        $boldLifeElements = $this->getBoldListElements($websiteId);
        $this->syncLifeElementsProcessor->process($websiteId, $magentoLifeElements, $boldLifeElements);
    }


    /**
     * Retrieve list fo elements from Bold Platform.
     *
     * @param int $websiteId
     * @return array
     */
    private function getBoldListElements(int $websiteId): array
    {
        $result = $this->client->get($websiteId, SyncLifeElementsProcessorInterface::LIFE_ELEMENTS_API_URI);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            $this->messageManager->addErrorMessage(
                __('There is an error while getting (LiFE) Elements: ') . ' ' . $error
            );
        }

        return $result->getBody()['data']['life_elements'];
    }
}
