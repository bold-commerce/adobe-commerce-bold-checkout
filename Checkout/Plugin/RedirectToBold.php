<?php

declare(strict_types=1);

namespace Bold\Checkout\Plugin;

use Bold\Checkout\Model\Config;
use Magento\Checkout\Controller\Onepage;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;

class RedirectToBold
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var \Bold\Checkout\Model\Config
     */
    private Config $config;

    /**
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Bold\Checkout\Model\Config $config
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Config          $config
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->config = $config;
    }

    /**
     * @param \Magento\Checkout\Controller\Onepage $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundDispatch(Onepage $subject, callable $proceed, RequestInterface $request): ResultInterface
    {
        if (!$this->config->isCheckoutEnabled()) {
            return $proceed($request);
        }

        $checkoutUrl = $this->config->getCheckoutUrl();
        $redirect = $this->redirectFactory->create();
        $redirect->setPath($checkoutUrl);

        return $redirect;
    }
}
