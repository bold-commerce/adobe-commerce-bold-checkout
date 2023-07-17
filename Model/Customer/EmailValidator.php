<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Customer;

use Bold\Checkout\Api\CustomerEmailValidatorInterface;
use Bold\Checkout\Api\Data\CustomerEmailValidator\ResultInterface;
use Bold\Checkout\Api\Data\CustomerEmailValidator\ResultInterfaceFactory;
use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Model\ResourceModel\GetWebsiteIdByShopId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customer email validation service.
 */
class EmailValidator implements CustomerEmailValidatorInterface
{
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
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory,
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        StoreManagerInterface $storeManager
    ) {
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $shopId, string $email): ResultInterface
    {
        try {
            $websiteId = $this->getWebsiteIdByShopId->getWebsiteId($shopId);
            $website = $this->storeManager->getWebsite($websiteId);
            if ($website->getId() === null) {
                return $this->getErrorResult(__('Incorrect "%1" Shop Id is provided.'));
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
        if (!$email) {
            return $this->getErrorResult(__('Empty email provided.'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->getErrorResult(__('Incorrect email format provided.'));
        }
        $domain = explode('@', $email)[1] ?? '';
        if (!checkdnsrr($domain)) {
            return $this->getErrorResult(__('Incorrect email domain provided.'));
        }
        return $this->resultFactory->create(
            [
                'errors' => [],
            ]
        );
    }

    /**
     * Build error result data model.
     *
     * @param Phrase $message
     * @return ResultInterface
     */
    public function getErrorResult(Phrase $message): ResultInterface
    {
        return $this->resultFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
                            'message' => $message,
                            'code' => 422,
                            'type' => 'server.validation_error',
                        ]
                    ),
                ],
            ]
        );
    }
}
