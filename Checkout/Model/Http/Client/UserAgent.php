<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client;

use Magento\Framework\Module\ModuleListInterface;

/**
 * User agent header builder.
 */
class UserAgent
{
    private const HEADER_PREFIX = 'Bold-Platform-Connector-M2';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(ModuleListInterface $moduleList)
    {
        $this->moduleList = $moduleList;
    }

    /**
     * Build user-agent header.
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        $moduleConfig = $this->moduleList->getOne('Bold_Checkout');
        return isset($moduleConfig['setup_version'])
            ? self::HEADER_PREFIX . ':' . $moduleConfig['version']
            : self::HEADER_PREFIX;
    }
}
