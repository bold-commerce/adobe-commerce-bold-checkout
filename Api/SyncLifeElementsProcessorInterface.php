<?php
declare(strict_types=1);

namespace Bold\Checkout\Api;

/**
 * Sync (LiFE) Elements processor.
 */
interface SyncLifeElementsProcessorInterface
{
    public const LIFE_ELEMENTS_API_URI = 'checkout/shop/{shopId}/life_elements';

    /**
     * Perform (LiFE) Elements synchronization between Magento and Bold Platform.
     *
     * @param int $websiteId
     * @param array $magentoLifeElements
     * @param array $boldLifeElements
     * @return void
     */
    public function process(int $websiteId, array $magentoLifeElements, array $boldLifeElements): void;
}
