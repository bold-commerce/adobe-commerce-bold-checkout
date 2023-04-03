<?php
declare(strict_types=1);

namespace Bold\Platform\Plugin\Quote\Item\Collection;

use Exception;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Tax\Api\Data\AppliedTaxInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * Populate quote item extension attributes with applied tax details data plugin.
 */
class ProcessTaxDetailsDataPlugin
{
    /**
     * @var AppliedTaxInterfaceFactory
     */
    private $appliedTaxFactory;

    /**
     * @var SimpleDataObjectConverter
     */
    private $objectHelper;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AppliedTaxInterfaceFactory $appliedTaxFactory
     * @param DataObjectHelper $objectHelper
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        AppliedTaxInterfaceFactory $appliedTaxFactory,
        DataObjectHelper $objectHelper,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->objectHelper = $objectHelper;
        $this->appliedTaxFactory = $appliedTaxFactory;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Un-serialize applied tax details and add to extension attributes.
     *
     * @param Collection $subject
     * @param DataObject $item
     * @return void
     */
    public function beforeAddItem(Collection $subject, DataObject $item): void
    {
        $taxDetails = $item->getTaxDetails();
        if (!$taxDetails) {
            return;
        }
        try {
            $this->processTaxDetailsData($this->json->unserialize($taxDetails), $item);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Convert applied taxes data into extension attributes.
     *
     * @param array $taxDetailsData
     * @param CartItemInterface $item
     * @return void
     */
    private function processTaxDetailsData(array $taxDetailsData, CartItemInterface $item): void
    {
        $taxDetails = [];
        foreach ($taxDetailsData as $appliedTaxData) {
            $appliedTax = $this->appliedTaxFactory->create();
            $this->objectHelper->populateWithArray(
                $appliedTax,
                $appliedTaxData,
                AppliedTaxInterface::class
            );
            $taxDetails[] = $appliedTax;
        }
        $item->getExtensionAttributes()->setTaxDetails($taxDetails);
    }
}
