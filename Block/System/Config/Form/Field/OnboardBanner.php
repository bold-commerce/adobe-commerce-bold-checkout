<?php

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\RequestsLogger;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Store\Model\StoreManagerInterface;

class OnboardBanner extends Field
{
    const ONBOARD_IN_PROGRESS_DATA_PATH = '/onboard_banner_data/in_progress';
    const ONBOARD_COMPLETED_DATA_PATH = '/onboard_banner_data/complete';

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ConfigInterface */
    private $config;

    /** @var ClientInterface */
    private $client;

    /** @var RequestsLogger */
    private $logger;

    /** @var string */
    protected $_template = 'Bold_Checkout::system/config/form/field/onboard_banner.phtml';

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        ClientInterface $client,
        RequestsLogger $logger
    ) {
        parent::__construct($context, []);
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Render element HTML
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBannerData()
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $platformConnectorUrl = $this->config->getPlatformConnectorUrl($websiteId);
        $bannerDataUrl = parse_url($platformConnectorUrl, PHP_URL_SCHEME) . '://' . parse_url($platformConnectorUrl, PHP_URL_HOST);

        if ($this->isOnboardComplete()) {
            $bannerDataUrl .= self::ONBOARD_COMPLETED_DATA_PATH;
        } else {
            $bannerDataUrl .= self::ONBOARD_IN_PROGRESS_DATA_PATH;
        }

        $this->client->get($bannerDataUrl);

        if ($this->client->getStatus() !== 200) {
            $this->logger->logRequest($websiteId, $bannerDataUrl, 'GET');
            return null;
        }

        return json_decode($this->client->getBody());
    }

    public function isOnboardComplete()
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        return $this->config->isCheckoutEnabled($websiteId);
    }
}
