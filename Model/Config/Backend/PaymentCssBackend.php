<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Config\Backend;

use Bold\Checkout\Model\GetDefaultPaymentCss;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Backend model for Payment CSS field.
 */
class PaymentCssBackend extends Value
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var GetDefaultPaymentCss
     */
    private $getDefaultPaymentCss;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param SerializerInterface $serializer
     * @param GetDefaultPaymentCss $getDefaultPaymentCss
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        SerializerInterface  $serializer,
        GetDefaultPaymentCss $getDefaultPaymentCss,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        array                $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
        $this->getDefaultPaymentCss = $getDefaultPaymentCss;
    }

    /**
     * @inheritDoc
     */
    public function afterLoad()
    {
        $serialized = (string)$this->getValue();
        if (!empty($serialized)) {
            try {
                $value = $this->serializer->unserialize($serialized);
            } catch (\Exception $exception) {
                $value = $this->getDefaultPaymentCss->getCss();
            }
        } else {
            $value = $this->getDefaultPaymentCss->getCss();
        }
        $this->setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $value = (string)$this->getValue() ?: $this->getDefaultPaymentCss->getCss();
        $serialized = $this->serializer->serialize($value);
        $this->setValue($serialized);
    }
}
