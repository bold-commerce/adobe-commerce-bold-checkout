<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\GetVersionInterface;
use Magento\Framework\Module\Dir;

class GetVersion implements GetVersionInterface
{

    /** @var Dir */
    protected $moduleDir;

    public function __construct(Dir $moduleDir) {
        $this->moduleDir = $moduleDir;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(string $shopId): string {
        $dir = $this->moduleDir->getDir('Bold_Checkout');
        $contents = file_get_contents("$dir/composer.json");
        $data = json_decode($contents, true);

        return $data['version'];
    }
}
