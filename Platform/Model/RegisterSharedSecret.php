<?php
declare(strict_types=1);

namespace Bold\Platform\Model;

use Bold\Checkout\Api\Data\Response\ErrorInterfaceFactory;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Platform\Api\RegisterSharedSecretInterface;
use Bold\Platform\Api\Data\RegisterSharedSecret\ResultInterface;
use Bold\Platform\Api\Data\RegisterSharedSecret\ResultInterfaceFactory;
use Bold\Platform\Model\Resource\GetWebsiteIdByShopId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Register shared secret for outgoing calls to bold m2 integration service.
 */
class RegisterSharedSecret implements RegisterSharedSecretInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var GetWebsiteIdByShopId
     */
    private $getWebsiteIdByShopId;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param ConfigInterface $config
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        ConfigInterface $config,
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
    }

    /**
     * @inheritDoc
     */
    public function register(string $shopId, string $sharedSecret): ResultInterface
    {
        try {
            $websiteId = $this->getWebsiteIdByShopId->getWebsiteId($shopId);
            $website = $this->storeManager->getWebsite($websiteId);
        } catch (LocalizedException $e) {
            return $this->resultFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                            ]
                        ),
                    ],
                ]
            );
        }
        $this->config->setSharedSecret($websiteId, $sharedSecret);
        return $this->resultFactory->create(
            [
                'shopId' => $shopId,
                'websiteCode' => $website->getCode(),
                'websiteId' => $website->getId(),
            ]
        );
    }
}
