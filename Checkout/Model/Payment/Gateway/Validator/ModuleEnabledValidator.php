<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Validator;

use Bold\Checkout\Api\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Verify is bold checkout module is enabled.
 */
class ModuleEnabledValidator extends AbstractValidator
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface $config
     */
    public function __construct(ResultInterfaceFactory $resultFactory, ConfigInterface $config)
    {
        parent::__construct($resultFactory);
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        return $this->createResult(
            $this->config->isCheckoutEnabled(),
            [
                __('Please make sure Bold Checkout module output is enabled and Bold Checkout Integration is on.'),
            ]
        );
    }
}
