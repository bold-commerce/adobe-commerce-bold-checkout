<?php

declare(strict_types=1);

namespace Bold\Platform\Model\Synchronizer;

/**
 * Get prepared entities by entity ids.
 */
interface GetPreparedEntities
{
    /**
     * Get prepared entities by entity ids.
     *
     * @param array $entityIds
     * @return \Magento\Framework\Model\AbstractExtensibleModel[]
     */
    public function getItems(array $entityIds): array;
}
