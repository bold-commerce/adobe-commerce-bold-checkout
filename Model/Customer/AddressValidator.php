<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Customer;

use Bold\Checkout\Api\CustomerAddressValidatorInterface;
use Bold\Checkout\Api\Data\CustomerAddressValidator\ResultInterface;
use Bold\Checkout\Api\Data\CustomerAddressValidator\ResultInterfaceFactory;
use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Model\ResourceModel\GetWebsiteIdByShopId;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\CompositeValidator;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer address validator.
 */
class AddressValidator implements CustomerAddressValidatorInterface
{
    /**
     * @var CompositeValidator
     */
    private $compositeValidator;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var AddressFactory
     */
    private $customerAddressFactory;

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
     * @param CompositeValidator $compositeValidator
     * @param AddressFactory $customerAddressFactory
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     */
    public function __construct(
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        StoreManagerInterface $storeManager,
        CompositeValidator $compositeValidator,
        AddressFactory $customerAddressFactory,
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory
    ) {
        $this->compositeValidator = $compositeValidator;
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->customerAddressFactory = $customerAddressFactory;
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
        $customerAddress = $this->customerAddressFactory->create();
        $customerAddress->updateData($address);
        $errors = $this->compositeValidator->validate($customerAddress);
        $responseErrors = [];
        foreach ($errors as $error) {
            $responseErrors[] = $this->errorFactory->create(
                [
                    'message' => $error,
                    'code' => 422,
                    'type' => 'server.validation_error',
                ]
            );
        }
        return $this->resultFactory->create(
            [
                'errors' => $responseErrors
            ]
        );
    }
}
