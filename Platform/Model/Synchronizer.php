<?php

declare(strict_types=1);

namespace Bold\Platform\Model;

use \Bold\Platform\Model\Service\Synchronizer\EntitySynchronizerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Synchronize entities by provided type and ids.
 */
class Synchronizer
{
    /**
     * @var EntitySynchronizerInterface[]
     */
    private $synchronizers;

    /**
     * @param EntitySynchronizerInterface[] $synchronizers
     */
    public function __construct(
        array $synchronizers = []
    )
    {
        $this->synchronizers = $synchronizers;
    }


    /**
     * Synchronize entities by provided type and ids.
     *
     * @param string $entityType
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function synchronize(string $entityType, array $ids): void {

        $synchronizer = $this->synchronizers[$entityType] ?? null;
        if (!($synchronizer instanceof Synchronizer\EntitiesSynchronizerInterface)) {
            throw new LocalizedException(
                __(
                    'Synchronization error: entity type \'%s\' not expected.',
                    $entityType
                )
            );
        }
        $synchronizer->synchronize($ids);
    }
}
