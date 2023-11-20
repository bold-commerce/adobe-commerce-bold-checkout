<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\GetVersionInterface;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerVersionProvider;

/**
 * Get module version.
 */
class GetVersion implements GetVersionInterface
{
    /**
     * @var ModuleComposerVersionProvider
     */
    private $moduleVersionProvider;

    /**
     * @param ModuleComposerVersionProvider $moduleVersionProvider
     */
    public function __construct(ModuleComposerVersionProvider $moduleVersionProvider) {
        $this->moduleVersionProvider = $moduleVersionProvider;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(string $shopId): string {
       return $this->moduleVersionProvider->getVersion('Bold_Checkout');
    }
}
