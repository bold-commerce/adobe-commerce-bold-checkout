<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\IntegrationInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Delete Magento integrations.
 */
class ClearModuleIntegration
{
    /**
     * @var IntegrationInterface[]
     */
    private $integrations;

    /**
     * @param IntegrationInterface[] $integrations
     */
    public function __construct(
        array $integrations = []
    ) {
        $this->integrations = $integrations;
    }

    /**
     * Mass delete Magento integrations.
     *
     * Removes integrations using IntegrationInterface instances, provided through dependency injection.
     */
    public function clear(int $websiteId): void
    {
        foreach ($this->integrations as $integration) {
            if ($integration instanceof IntegrationInterface) {
                $integration->delete($websiteId);
            }
        }
    }
}
