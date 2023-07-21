<?php
declare(strict_types=1);

namespace Bold\Checkout\Api;

interface GetVersionInterface
{
    /**
     * Gets the version of the module
     *
     * @param string $shopId
     * @return string
     */
    public function getVersion(string $shopId): string;
}
