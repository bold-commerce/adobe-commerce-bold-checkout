<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\Data\RegisterSharedSecret\ResultInterface;
use Bold\Checkout\Api\Data\RegisterSharedSecret\ResultInterfaceFactory;
use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\RegisterSharedSecretInterface;
use Bold\Checkout\Model\ResourceModel\GetWebsiteIdByShopId;
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
            if ($website->getId() === null) {
                return $this->resultFactory->create(
                    [
                        'errors' => [
                            $this->errorFactory->create(
                                [
                                    'message' => __('Incorrect "%1" Shop Id is provided.'),
                                    'code' => 422,
                                    'type' => 'server.validation_error',
                                ]
                            ),
                        ],
                    ]
                );
            }
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
