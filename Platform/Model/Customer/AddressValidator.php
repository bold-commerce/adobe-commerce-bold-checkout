<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Customer;

use Bold\Platform\Api\CustomerAddressValidatorInterface;
use Bold\Platform\Api\Data\CustomerAddressValidator\ResultInterface;
use Bold\Platform\Api\Data\CustomerAddressValidator\ResultInterfaceFactory;
use Bold\Platform\Api\Data\Response\ErrorInterfaceFactory;
use Bold\Platform\Model\ResourceModel\GetWebsiteIdByShopId;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\ValidateAddress;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer address validator.
 */
class AddressValidator implements CustomerAddressValidatorInterface
{
    /**
     * @var ValidateAddress
     */
    private $validateAddress;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var GetWebsiteIdByShopId
     */
    private $getWebsiteIdByShopId;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param StoreManagerInterface $storeManager
     * @param ValidateAddress $validateAddress
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     */
    public function __construct(
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        StoreManagerInterface $storeManager,
        ValidateAddress $validateAddress,
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory
    ) {
        $this->validateAddress = $validateAddress;
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $shopId, AddressInterface $address): ResultInterface
    {
        try {
            $websiteId = $this->getWebsiteIdByShopId->getWebsiteId($shopId);
            $website = $this->storeManager->getWebsite($websiteId);
            if (!$website->getId()) {
                return $this->resultFactory->create(
                    [
                        'errors' => [
                            $this->errorFactory->create(
                                [
                                    'message' => __('Incorrect "%1" Shop Id is provided.'),
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
        try {
            $responseErrors = [];
            $this->validateAddress->execute($address);
        } catch (GraphQlInputException $e) {
            $responseErrors[] = $this->errorFactory->create(
                [
                    'message' => $e->getMessage(),
                    'code' => 422,
                    'type' => 'server.validation_error',
                ]
            );
        }
        return $this->resultFactory->create(
            [
                'errors' => $responseErrors,
            ]
        );
    }
}
