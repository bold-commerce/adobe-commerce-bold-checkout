<?php

declare(strict_types=1);

namespace Bold\Checkout\Controller\Adminhtml\Clear;

use Bold\Checkout\Model\ClearModuleConfiguration;
use Bold\Checkout\Model\ClearModuleIntegration;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Controller, responsible for Bold configuration cleaning.
 */
class Index extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bold_Checkout::integration';

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var ClearModuleConfiguration
     */
    private $clearModuleConfiguration;

    /**
     * @var ClearModuleIntegration
     */
    private $clearModuleIntegration;

    /**
     * @param Context $context
     * @param ClearModuleConfiguration $clearModuleConfiguration
     * @param ClearModuleIntegration $clearModuleIntegration
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        Action\Context           $context,
        ClearModuleConfiguration $clearModuleConfiguration,
        ClearModuleIntegration $clearModuleIntegration,
        JsonFactory              $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->clearModuleConfiguration = $clearModuleConfiguration;
        $this->clearModuleIntegration = $clearModuleIntegration;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Clear Bold configuration and created integrations for specific scope.
     *
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $websiteId = (int)$this->getRequest()->getParam('website');
        $this->clearModuleConfiguration->clear($websiteId);
        $this->clearModuleIntegration->clear($websiteId);
        $data = ['success' => true];
        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }
}
