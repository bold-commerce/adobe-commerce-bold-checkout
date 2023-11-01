<?php
declare(strict_types=1);

namespace Bold\Checkout\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Config management.
 */
interface ConfigManagementInterface
{
    /**
     * Retrieve config value.
     *
     * @param string $path
     * @param int $websiteId
     * @return mixed
     * @throws LocalizedException
     */
    public function getValue(string $path, int $websiteId);

    /**
     * Retrieve config flag.
     *
     * @param string $path
     * @param int $websiteId
     * @return bool
     * @throws LocalizedException
     */
    public function isSetFlag(string $path, int $websiteId): bool;
}
