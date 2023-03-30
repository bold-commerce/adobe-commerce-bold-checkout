<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client;

use Bold\Checkout\Api\Data\Http\Client\ResponseInterface;
use Bold\Checkout\Api\Data\Http\Client\ResponseInterfaceFactory;
use Exception;
use Magento\Framework\HTTP\Client\Curl as FrameworkCurl;
use Psr\Log\LoggerInterface;

/**
 * Bold checkout curl.
 */
class Curl extends FrameworkCurl
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResponseInterfaceFactory
     */
    private $responseFactory;

    /**
     * @param LoggerInterface $logger
     * @param ResponseInterfaceFactory $responseFactory
     * @param int|null $sslVersion
     */
    public function __construct(
        LoggerInterface $logger,
        ResponseInterfaceFactory $responseFactory,
        int $sslVersion = null
    ) {
        parent::__construct($sslVersion);
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Perform https request
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param array|null $data
     * @return ResponseInterface
     */
    public function sendRequest(string $method, string $url, array $headers, array $data = null): ResponseInterface
    {
        $this->logger->debug('Outgoing Call: ' . $method . ' ' . $url);
        $this->logger->debug('Outgoing Call Headers: ' . json_encode($headers));
        $this->logger->debug('Outgoing Call Data: ' . json_encode($data));
        $this->setHeaders($headers);
        $this->makeRequest($method, $url, json_encode($data));
        $this->logger->debug('Outgoing call code: ' . $this->_responseStatus);
        $this->logger->debug('Outgoing call result: ' . $this->_responseBody);
        try {
            $body = json_decode($this->_responseBody, true);
        } catch (Exception $e) {
            $body = [];
        }
        $errors = $this->getErrors($body);
        return $this->responseFactory->create(
            [
                'status' => (int)$this->_responseStatus,
                'body' => $errors ? [] : $body,
                'errors' => $errors,
            ]
        );
    }

    /**
     * Retrieve errors from response body.
     *
     * @param array $body
     * @return array
     */
    private function getErrors(array $body): array
    {
        $errors = $body['errors'] ?? [];
        if (isset($body['error'])) {
            $errors = [
                $body['error_description'] ?? $body['error'],
            ];
        }
        return $errors;
    }
}
