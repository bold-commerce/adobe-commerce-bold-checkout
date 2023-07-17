<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Catalog;

use Bold\Checkout\Api\Catalog\GetProductsInterface;
use Bold\Checkout\Api\Data\Catalog\GetProductsResultInterface;
use Bold\Checkout\Api\Data\Catalog\GetProductsResultInterfaceFactory;
use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Exception;
use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Get products list with media gallery fallback for the complex products.
 */
class GetProducts implements GetProductsInterface
{
    /**
     * @var int[]
     */
    private $websiteIds = [];

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var GetProductsResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var ProductAttributeMediaGalleryManagementInterface
     */
    private $productAttributeMediaGalleryManagement;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @param ShopIdValidator $shopIdValidator
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ErrorInterfaceFactory $errorFactory
     * @param GetProductsResultInterfaceFactory $resultFactory
     * @param ProductAttributeMediaGalleryManagementInterface $productAttributeMediaGalleryManagement
     * @param Configurable $configurable
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ErrorInterfaceFactory $errorFactory,
        GetProductsResultInterfaceFactory $resultFactory,
        ProductAttributeMediaGalleryManagementInterface $productAttributeMediaGalleryManagement,
        Configurable $configurable
    ) {
        $this->shopIdValidator = $shopIdValidator;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->errorFactory = $errorFactory;
        $this->resultFactory = $resultFactory;
        $this->productAttributeMediaGalleryManagement = $productAttributeMediaGalleryManagement;
        $this->configurable = $configurable;
    }

    /**
     * @inheritDoc
     */
    public function getList(string $shopId, SearchCriteriaInterface $searchCriteria): GetProductsResultInterface
    {
        try {
            $this->shopIdValidator->validate($shopId, (int)$this->storeManager->getStore()->getId());
        } catch (LocalizedException $e) {
            return $this->resultFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'type' => 'server.validation_error',
                                'code' => 422,
                                'message' => $e->getMessage(),
                            ]
                        ),
                    ],
                ]
            );
        }
        try {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if ($filter->getField() === 'website_id' && $filter->getValue() === '0') {
                        $filter->setValue(
                            implode(',', $this->getWebsiteIds())
                        );
                    }
                }
            }
            $productSearchResults = $this->productRepository->getList($searchCriteria);
            foreach ($productSearchResults->getItems() as $product) {
                if (!$product->getMediaGalleryEntries()) {
                    $parentIds = $this->configurable->getParentIdsByChild($product->getId());
                    if ($parentIds) {
                        $parentProduct = $this->productRepository->getById($parentIds[0]);
                        $product->setMediaGalleryEntries($parentProduct->getMediaGalleryEntries());
                    }
                }
            }
            return $this->resultFactory->create(
                [
                    'products' => $productSearchResults->getItems(),
                    'totalCount' => $productSearchResults->getTotalCount(),
                ]
            );
        } catch (Exception $e) {
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
    }

    /**
     * Get all website ids.
     *
     * @return int[]
     */
    public function getWebsiteIds(): array
    {
        if (!$this->websiteIds) {
            $this->websiteIds = array_map(
                function (WebsiteInterface $website) {
                    return (int)$website->getId();
                },
                $this->storeManager->getWebsites()
            );
        }

        return $this->websiteIds;
    }
}
