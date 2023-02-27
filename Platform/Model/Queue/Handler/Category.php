<?php

namespace Bold\Platform\Model\Queue\Handler;

use \Bold\Platform\Model\Synchronizer;
use Bold\Checkout\Model\ConfigInterface;
use Magento\Catalog\Model\Category as CategoryModel;

/**
 * Bold Products synchronization queue handler.
 */
class Category
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
     * Handle Bold Category synchronization queue message.
     *
     * @param int[] $categoryIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $categoryIds): void
    {
        if (!$this->config->isCheckoutEnabled()) {
            return;
        }

        $this->synchronizer->synchronize(CategoryModel::ENTITY, $categoryIds);
    }
}
