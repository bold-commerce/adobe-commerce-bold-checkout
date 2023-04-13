<?php
declare(strict_types=1);

namespace Bold\Platform\Test\Api;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Covers bold customer address validation endpoint.
 */
class ValidateCustomerAddressTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/shops/{{shopId}}/customer/address/validate';

    /**
     * Test validation endpoint is hit with wrong shop id.
     *
     * @return void
     */
    public function testInvalidShopId(): void
    {
        self::_markTestAsRestOnly();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace('{{shopId}}', 'invalid_shop_id', self::RESOURCE_PATH),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $result = $this->_webApiCall(
            $serviceInfo,
            [
                'address' => [],
            ]
        );
        self::assertFalse($result['valid']);
        self::assertEquals('No website found for "invalid_shop_id" shop Id.', $result['errors'][0]['message']);
        self::assertEquals(500, $result['errors'][0]['code']);
    }

    /**
     * Verify customer address validation endpoint.
     *
     * @param array $address
     * @param array $validationResult
     * @return void
     * @throws LocalizedException
     * @dataProvider addressValidationDataProvider
     */
    public function testAddressValidation(array $address, array $validationResult): void
    {
        self::_markTestAsRestOnly();
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsite('base');
        $config = Bootstrap::getObjectManager()->get(ConfigInterface::class);
        $shopId = $config->getShopId((int)$website->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace('{{shopId}}', $shopId, self::RESOURCE_PATH),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $result = $this->_webApiCall(
            $serviceInfo,
            [
                'address' => $address,
            ]
        );
        if ($validationResult['is_valid']) {
            self::assertTrue($result['valid']);
            self::assertEmpty($result['errors']);
            return;
        }
        self::assertFalse($result['valid']);
        self::assertEquals($validationResult['message'], $result['errors'][0]['message']);
        self::assertEquals($validationResult['code'], $result['errors'][0]['code']);
    }

    /**
     * Provide data for custom address validation test.
     *
     * @return array
     */
    public function addressValidationDataProvider(): array
    {
        return [
            'valid_address' => [
                'address' => [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'street' => ['West Centinela Avenue'],
                    'city' => 'Culver City',
                    'region' => 'CA',
                    'postcode' => '90230',
                    'country_id' => 'US',
                    'telephone' => '555-55-555',
                ],
                'validation_result' => [
                    'is_valid' => true,
                ],
            ],
            'missing_postcode_address' => [
                'address' => [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'street' => ['West Centinela Avenue'],
                    'city' => 'Culver City',
                    'region' => 'CA',
                    'country_id' => 'US',
                    'telephone' => '555-55-555',
                ],
                'validation_result' => [
                    'is_valid' => false,
                    'code' => 422,
                    'message' => '"postcode" is required. Enter and try again.',
                ],
            ],

        ];
    }
}
