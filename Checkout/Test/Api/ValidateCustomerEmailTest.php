<?php
declare(strict_types=1);

namespace Bold\Checkout\Test\Api;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Covers bold customer email validation endpoint.
 */
class ValidateCustomerEmailTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/shops/{{shopId}}/customer/email/validate';

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
                'email' => 'john.doe@example.com',
            ]
        );
        self::assertFalse($result['valid']);
        self::assertEquals('No website found for "invalid_shop_id" shop Id.', $result['errors'][0]['message']);
        self::assertEquals(500, $result['errors'][0]['code']);
    }

    /**
     * Verify customer email validation endpoint.
     *
     * @param string $email
     * @param array $validationResult
     * @return void
     * @dataProvider emailValidationDataProvider
     */
    public function testEmailValidation(string $email, array $validationResult): void
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
                'email' => $email,
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
     * Provide data for email validation test.
     *
     * @return array[]
     */
    public function emailValidationDataProvider(): array
    {
        return [
            'empty_email' => [
                'email' => '',
                'validation_result' => [
                    'is_valid' => false,
                    'code' => 422,
                    'message' => 'Empty email provided.',
                ],
            ],
            'incorrect_email_domain' => [
                'email' => 'john.doe@test.com',
                'validation_result' => [
                    'is_valid' => false,
                    'code' => 422,
                    'message' => 'Incorrect email domain provided.',
                ],
            ],
            'valid_email' => [
                'email' => 'john.doe@example.com',
                'validation_result' => [
                    'is_valid' => true,
                ],
            ],
        ];
    }
}
