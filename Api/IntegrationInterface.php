<?php

declare(strict_types=1);

namespace Bold\Checkout\Api;

/**
 * Bold Integration model interface.
 */
interface IntegrationInterface
{
    /**
     * Get integration name.
     *
     * @param int $websiteId
     * @return string
     */
    public function getName(int $websiteId): string;

    /**
     * Get integration status.
     *
     * @param int $websiteId
     * @return int|null
     */
    public function getStatus(int $websiteId): ?int;

    /**
     * Update Integration (if required).
     *
     * @param int $websiteId
     * @return void
     */
    public function update(int $websiteId): void;
}
