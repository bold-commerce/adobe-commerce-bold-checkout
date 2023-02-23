<?php

declare(strict_types=1);

namespace Bold\Platform\Model\Synchronizer;

/**
 * Synchronize entities by provided ids.
 */
interface EntitiesSynchronizerInterface
{
    /**
     * Synchronize entities by provided ids.
     *
     * @param array $ids
     * @return void
     */
    public function synchronize(array $ids): void;
}
