<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Config;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\SyncLifeElementsProcessorInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

/**
 * Sync (LiFE) Elements processor.
 */
class SyncLifeElementsProcessor implements SyncLifeElementsProcessorInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @param ClientInterface $client
     * @param MessageManager $messageManager
     */
    public function __construct(
        ClientInterface $client,
        MessageManager  $messageManager
    ) {
        $this->client = $client;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheirtDoc
     */
    public function process(int $websiteId, array $magentoLifeElements, array $boldLifeElements): void
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
            foreach ($magentoLifeElements as $lifeElement) {
                if (in_array($lifeElement["meta_data_field"], $metaFieldsToAdd)) {
                    $lifeElement['input_required'] = (bool)$lifeElement['input_required'];
                    $lifeElementsToAdd[] = $lifeElement;
                }
            }
            $this->createBoldLifeElements($websiteId, $lifeElementsToAdd);
        }

        // Delete (LiFE) Elements from Bold Platform
        if (!empty($metaFieldsToDelete)) {
            $lifeElementsToDelete = [];
            foreach ($boldLifeElements as $lifeElement) {
                if (in_array($lifeElement["meta_data_field"], $metaFieldsToDelete)) {
                    $lifeElementsToDelete[] = $lifeElement['public_id'];
                }
            }
            $this->deleteBoldLifeElements($websiteId, $lifeElementsToDelete);
        }
    }

    /**
     * Create (LiFE) Elements on Bold Platform.
     *
     * @param int $websiteId
     * @param array $data
     * @return void
     */
    private function createBoldLifeElements(int $websiteId, array $data)
    {
        foreach ($data as $item) {
            $result = $this->client->post($websiteId, self::LIFE_ELEMENTS_API_URI, $item);
            if ($result->getErrors()) {
                $error = current($result->getErrors());
                $this->messageManager->addErrorMessage(
                    __('There is an error while creating (LiFE) Elements: ') . ' ' . $error
                );
            }
        }
    }

    /**
     * Delete (LiFE) Elements from Bold Platform.
     *
     * @param int $websiteId
     * @param array $data
     * @return void
     */
    private function deleteBoldLifeElements(int $websiteId, array $data)
    {
        foreach ($data as $item) {
            $result = $this->client->delete($websiteId, self::LIFE_ELEMENTS_API_URI . '/' . $item, []);
            if ($result->getErrors()) {
                $error = current($result->getErrors());
                $this->messageManager->addErrorMessage(
                    __('There is an error while deleting (LiFE) Elements: ') . ' ' . $error
                );
            }
        }
    }
}
