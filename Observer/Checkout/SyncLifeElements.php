<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Api\LifeElementManagementInterface;
use Bold\Checkout\Model\ConfigInterface;
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
     * @var LifeElementManagementInterface
     */
    private $lifeElementManagement;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @param ConfigInterface $config
     * @param LifeElementManagementInterface $lifeElementManagement
     * @param MessageManager $messageManager
     */
    public function __construct(
        ConfigInterface $config,
        LifeElementManagementInterface $lifeElementManagement,
        MessageManager $messageManager
    ) {
        $this->config = $config;
        $this->lifeElementManagement = $lifeElementManagement;
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
        $boldLifeElements = $this->lifeElementManagement->getList($websiteId);

        if (empty($magentoLifeElements) && empty($boldLifeElements)) {
            return;
        }

        $this->sync($websiteId, $magentoLifeElements, $boldLifeElements);
    }

    /**
     * Synchronize (LiFE) Elements between Magento and Bold Platform.
     *
     * @param $websiteId
     * @param $magentoLifeElements
     * @param $boldLifeElements
     * @return void
     */
    private function sync($websiteId, $magentoLifeElements, $boldLifeElements): void
    {
        $magentoLifeMetaFields = array_values(
            array_map(function ($element) {
                return $element['meta_data_field'] ?? null;
            }, $magentoLifeElements)
        );

        $boldLifeMetaFields = array_map(function ($element) {
            return $element['meta_data_field'] ?? null;
        }, $boldLifeElements);

        $metaFieldsToAdd = array_diff($magentoLifeMetaFields, $boldLifeMetaFields);
        $metaFieldsToUpdate = array_diff($magentoLifeMetaFields, $metaFieldsToAdd);
        $metaFieldsToDelete = array_diff($boldLifeMetaFields, $magentoLifeMetaFields);

        // Create (LiFE) Elements on Bold Platform
        if (!empty($metaFieldsToAdd)) {
            $lifeElementsToAdd = [];
            foreach ($magentoLifeElements as $magentoLifeElement) {
                if (in_array($magentoLifeElement["meta_data_field"], $metaFieldsToAdd)) {
                    $magentoLifeElement['input_required'] = (bool)$magentoLifeElement['input_required'];
                    $lifeElementsToAdd[] = $magentoLifeElement;
                }
            }
            $this->createBoldLifeElements($websiteId, $lifeElementsToAdd);
        }

        // Update (LiFE) Elements on Bold Platform
        if (!empty($metaFieldsToUpdate)) {
            $lifeElementsToUpdate = [];
            foreach ($magentoLifeElements as $magentoLifeElement) {
                if (in_array($magentoLifeElement["meta_data_field"], $metaFieldsToUpdate)) {
                    foreach ($boldLifeElements as $boldLifeElement) {
                        if ($boldLifeElement['meta_data_field'] === $magentoLifeElement['meta_data_field']) {
                            $magentoLifeElement['input_required'] = (bool)$magentoLifeElement['input_required'];
                            $lifeElementsToUpdate[$boldLifeElement['public_id']] = $magentoLifeElement;
                        }
                    }
                }
            }
            $this->updateBoldLifeElements($websiteId, $lifeElementsToUpdate);
        }

        // Delete (LiFE) Elements from Bold Platform
        if (!empty($metaFieldsToDelete)) {
            $lifeElementsToDelete = [];
            foreach ($boldLifeElements as $boldLifeElement) {
                if (in_array($boldLifeElement["meta_data_field"], $metaFieldsToDelete)) {
                    $lifeElementsToDelete[] = $boldLifeElement['public_id'];
                }
            }
            $this->deleteBoldLifeElements($websiteId, $lifeElementsToDelete);
        }
    }

    /**
     * Create (LiFE) Elements on Bold Platform.
     *
     * @param $websiteId
     * @param array $elements
     * @return void
     */
    private function createBoldLifeElements($websiteId, array $elements)
    {
        try {
            foreach ($elements as $element) {
                $this->lifeElementManagement->create($websiteId, $element);
            }
            $this->messageManager->addSuccessMessage(
                __("A total of %1 (LiFE) Element(s) have been added.", count($elements))
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('There is an error while creating (LiFE) Elements: ') . ' ' . $exception->getMessage()
            );
        }
    }

    /**
     * Update (LiFE) Elements on Bold Platform.
     *
     * @param $websiteId
     * @param array $elements
     * @return void
     */
    private function updateBoldLifeElements($websiteId, array $elements)
    {
        try {
            foreach ($elements as $publicElementId => $elementData) {
                $this->lifeElementManagement->update($websiteId, $publicElementId, $elementData);
            }
            $this->messageManager->addSuccessMessage(
                __("A total of %1 (LiFE) Element(s) have been updated.", count($elements))
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('There is an error while updating (LiFE) Elements: ') . ' ' . $exception->getMessage()
            );
        }
    }

    /**
     * Delete (LiFE) Elements from Bold Platform.
     *
     * @param $websiteId
     * @param array $elements
     * @return void
     */
    private function deleteBoldLifeElements($websiteId, array $elements)
    {
        try {
            foreach ($elements as $element) {
                $this->lifeElementManagement->delete($websiteId, $element);
            }
            $this->messageManager->addSuccessMessage(
                __("A total of %1 (LiFE) Element(s) have been deleted.", count($elements))
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('There is an error while deleting (LiFE) Elements: ') . ' ' . $exception->getMessage()
            );
        }
    }
}
