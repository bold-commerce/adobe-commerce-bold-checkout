<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\ConfigManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Config management.
 */
class ConfigManagement implements ConfigManagementInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @ingeritDoc
     */
    public function getValue(string $path, int $websiteId)
    {
        if (!$websiteId) {
            throw new LocalizedException(__('Website cannot be equal to "%1".', $websiteId));
        }

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * @ingeritDoc
     */
    public function isSetFlag(string $path, int $websiteId): bool
    {
        if (!$websiteId) {
            throw new LocalizedException(__('Website cannot be equal to "%1".', $websiteId));
        }

        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }
}
