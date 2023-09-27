<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\GetVersionInterface;

class GetVersion implements GetVersionInterface
{
    /**
     * @var ModuleVersionProvider
     */
    private $moduleVersionProvider;

    /**
     * @param ModuleVersionProvider $moduleVersionProvider
     */
    public function __construct(ModuleVersionProvider $moduleVersionProvider) {
        $this->moduleVersionProvider = $moduleVersionProvider;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(string $shopId): string {
       return $this->moduleVersionProvider->getVersion('Bold_Checkout');
    }
}
