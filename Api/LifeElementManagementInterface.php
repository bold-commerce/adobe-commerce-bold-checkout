<?php
declare(strict_types=1);

namespace Bold\Checkout\Api;

/**
 * (LiFE) Elements management.
 */
interface LifeElementManagementInterface
{
    const LIFE_ELEMENTS_API_URI = 'checkout/shop/{shopId}/life_elements';

    /**
     * Create (LiFE) Element on Bold Platform.
     *
     * @param int $websiteId
     * @param array $elementData
     * @return string
     * @throws \Exception
     */
    public function create(int $websiteId, array $elementData): string;

    /**
     * Retrieve list fo (LiFE) Elements from Bold Platform.
     *
     * @param int $websiteId
     * @return array
     * @throws \Exception
     */
    public function getList(int $websiteId): array;

    /**
     * Update (LiFE) Element on Bold Platform.
     *
     * @param int $websiteId
     * @param string $publicElementId
     * @param array $elementData
     * @return string
     */
    public function update(int $websiteId, string $publicElementId, array $elementData): string;

    /**
     * Delete (LiFE) Element from Bold Platform.
     *
     * @param int $websiteId
     * @param string $publicElementId
     * @return void
     * @throws \Exception
     */
    public function delete(int $websiteId, string $publicElementId): void;
}
