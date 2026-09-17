<?php
namespace Bold\Checkout\Controller\Index;

use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class InitHeadlessOrder extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var InitOrderFromQuote
     */
    protected $initOrderFromQuote;

    /**
     * @var Session
     */
    protected $checkoutSession;

    public function __construct(
        Context $context, 
        JsonFactory $resultJsonFactory,
        InitOrderFromQuote $initOrderFromQuote,
        Session $checkoutSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $result = $this->resultJsonFactory->create();
        $data = $this->initOrderFromQuote->init($quote);
        return $result->setData($data);
    }
} 