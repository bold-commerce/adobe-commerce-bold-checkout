<?php
declare(strict_types=1);

namespace Bold\Platform\Model;

use Bold\Checkout\Api\Data\Response\ErrorInterfaceFactory;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Platform\Api\AddSharedSecretInterface;
use Bold\Platform\Api\Data\AddSharedSecret\ResultInterface;
use Bold\Platform\Api\Data\AddSharedSecret\ResultInterfaceFactory;
use Bold\Platform\Model\Resource\GetWebsiteIdByShopId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Register shared secret for outgoing calls to bold m2 integration service.
 */
class AddSharedSecret implements AddSharedSecretInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @param LoggerInterface $logger
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param ConfigInterface $config
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        ConfigInterface $config,
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->config = $config;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
    }

    /**
     * @inheritDoc
     */
    public function addSecret(string $shopId, string $sharedSecret): ResultInterface
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
            ]
        );
    }
}