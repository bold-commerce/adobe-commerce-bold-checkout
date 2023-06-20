<?php
declare(strict_types=1);

namespace Bold\CheckoutSelfHosted\Controller\Index;

use Magento\Csp\Api\CspAwareActionInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Render Bold Checkout Self-Hosted page.
 */
class Index implements ActionInterface, CspAwareActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function modifyCsp(array $appliedPolicies): array
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $reactAppUrl = $this->config->getValue(
            'checkout/bold_checkout_base/template_url',
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
        $appliedPolicies[] = new FetchPolicy(
            'script-src',
            false,
            [$reactAppUrl],
            ['https']
        );

        return $appliedPolicies;
    }
}
