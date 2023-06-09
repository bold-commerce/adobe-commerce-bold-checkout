<?php
declare(strict_types=1);

namespace Bold\Checkout\UI\Payment;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\BoldStorefrontClient;
use Bold\Checkout\Model\Payment\Gateway\Service;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Config provider for Bold Checkout.
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountries;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $countries;

    /**
     * @param Session $checkoutSession
     * @param ConfigInterface $config
     * @param ClientInterface $client
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param ComponentRegistrar $componentRegistrar
     * @param Resolver $fileResolver
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param Json $json
     */
    public function __construct(
        Session $checkoutSession,
        ConfigInterface $config,
        ClientInterface $client,
        StoreManagerInterface $storeManager,
        AllowedCountries $allowedCountries,
        CollectionFactory $collectionFactory,
        Json $json,
        Reader $moduleReader,
        ReadFactory $readFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->client = $client;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->json = $json;
        $this->moduleReader = $moduleReader;
        $this->readFactory = $readFactory;
        $this->allowedCountries = $allowedCountries;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return [];
        }
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        $shopId = $this->config->getShopId($websiteId);
        $orderId = $boldCheckoutData['data']['public_order_id'] ?? null;
        $jwtToken = $boldCheckoutData['data']['jwt_token'] ?? null;
        return [
            'bold' => [
                'payment' => [
                    'iframeSrc' => $this->getIframeSrc(),
                    'title' => __('Pay Pal'),
                    'method' => Service::CODE,
                ],
                'shopId' => $shopId,
                'customerIsGuest' => $this->checkoutSession->getQuote()->getCustomerIsGuest(),
                'publicOrderId' => $orderId,
                'jwtToken' => $jwtToken,
                'countries' => $this->getAllowedCountries(),
                'url' => BoldStorefrontClient::URL . $shopId . '/' . $orderId . '/',
            ],
        ];
    }

    /**
     * Get iframe src.
     *
     * @return string|null
     */
    private function getIframeSrc(): ?string
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return null;
        }
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        $shopId = $this->config->getShopId($websiteId);
        try {
            $styles = $this->getStyles();
            if ($styles) {
                $this->client->post($websiteId, 'payments/styles', $styles);
            }
        } catch (\Exception $e) {
            return null;
        }
        $orderId = $boldCheckoutData['data']['public_order_id'] ?? null;
        $jwtToken = $boldCheckoutData['data']['jwt_token'] ?? null;
        if (!$orderId || !$jwtToken) {
            return null;
        }
        return BoldStorefrontClient::URL . $shopId . '/' . $orderId . '/payments/iframe?token=' . $jwtToken;
    }

    /**
     * Get iframe styles.
     *
     * @return array
     * @throws FileSystemException
     * @throws ValidatorException
     */
    private function getStyles(): array
    {
        $dir = $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Bold_Checkout');
        $read = $this->readFactory->create($dir . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'web');
        if (!$read->isFile('iframe-styles.json')) {
            return [];
        }
        return $this->json->unserialize($read->readFile('iframe-styles.json'));
    }

    /**
     * Get allowed countries.
     *
     * @return Country[]
     */
    public function getAllowedCountries(): array
    {
        if ($this->countries) {
            return $this->countries;
        }
        $allowedCountries = $this->allowedCountries->getAllowedCountries();
        $countriesCollection = $this->collectionFactory->create()->addFieldToFilter(
            'country_id',
            ['in' => $allowedCountries]
        );
        $this->countries = $countriesCollection->toOptionArray(false);
        return $this->countries;
    }
}
