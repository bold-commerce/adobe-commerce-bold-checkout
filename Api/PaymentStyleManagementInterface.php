<?php

declare(strict_types=1);

namespace Bold\Checkout\Api;

use Exception;

/**
 * Payment iframe styles management.
 */
interface PaymentStyleManagementInterface
{
    public const PAYMENT_CSS_API_URI = 'checkout/shop/{shopId}/payment_css';

    /**
     * Retrieve payment iframe styles.
     *
     * @param int $websiteId
     * @return array
     * @throws Exception
     */
    public function get(int $websiteId): array;

    /**
     * Update payment iframe styles.
     *
     * @param int $websiteId
     * @param array $data
     * @return void
     */
    public function update(int $websiteId,  array $data): void;

    /**
     * Delete payment iframe styles
     *
     * @param int $websiteId
     * @return void
     * @throws Exception
     */
    public function delete(int $websiteId): void;
}
