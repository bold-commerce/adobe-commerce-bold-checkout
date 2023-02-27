<?php

namespace Bold\Platform\Model\Queue\Handler;

use \Bold\Platform\Model\Synchronizer;
use Bold\Checkout\Model\ConfigInterface;
use Magento\Catalog\Model\Product as ProductModel;

/**
 * Bold Products synchronization queue handler.
 */
class Product
{
    /**
     * @var \Bold\Checkout\Model\ConfigInterface
     */
    private $config;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @param \Bold\Checkout\Model\ConfigInterface $config
     * @param \Bold\Platform\Model\Synchronizer $synchronizer
     */
    public function __construct(
        ConfigInterface $config,
        Synchronizer    $synchronizer
    ) {
        $this->config = $config;
        $this->synchronizer = $synchronizer;
    }

    /**
     * Handle Bold Products synchronization queue message.
     *
     * @param int[] $productIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $productIds): void
    {
        if (!$this->config->isCheckoutEnabled()) {
            return;
        }

        $this->synchronizer->synchronize(ProductModel::ENTITY, $productIds);
    }
}
