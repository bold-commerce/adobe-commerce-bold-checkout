<?php

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Model\Http\Client\RequestsLogger;
use Bold\Checkout\Model\Http\PlatformClient;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;

class OnboardBanner extends Field
{
    private const ONBOARD_DATA_PATH = '/{{shopId}}/onboard_banner_data';

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var PlatformClient */
    private $platformClient;

    /** @var RequestsLogger */
    private $logger;

    /** @var string */
    protected $_template = 'Bold_Checkout::system/config/form/field/onboard_banner.phtml';

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        PlatformClient $platformClient,
        RequestsLogger $logger,
    ) {
        parent::__construct($context, []);
        $this->storeManager = $storeManager;
        $this->platformClient = $platformClient;
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
        $bannerData = $this->platformClient->get($websiteId, self::ONBOARD_DATA_PATH);

        if ($bannerData->getStatus() !== 200) {
            $this->logger->logRequest($websiteId, self::ONBOARD_DATA_PATH, 'GET');
            return null;
        }

        return $bannerData->getBody();
    }
}
