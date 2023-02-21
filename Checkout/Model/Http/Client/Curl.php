<?php

namespace Bold\Checkout\Model\Http\Client;

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
     * @param LoggerInterface $logger
     * @param int|null $sslVersion
     */
    public function __construct(LoggerInterface $logger, int $sslVersion = null)
    {
        parent::__construct($sslVersion);
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(string $method, string $url, array $headers, array $data = null): string
    {
        $this->logger->debug('Outgoing Call: ' . $method . ' ' . $url);
        $this->logger->debug('Outgoing Call Headers: ' . json_encode($headers));
        $this->logger->debug('Outgoing Call Data: ' . $data);
        $this->setHeaders($headers);
        $url = $this->prepareRequest($method, $url, $data);
        $this->makeRequest($method, $url, $data);
        $this->logger->debug('Outgoing call code: ' . $this->_responseStatus);
        $this->logger->debug('Outgoing call result: ' . $this->_responseBody);

        return $this->_responseBody;
    }

    /**
     * Build request for given data and type.
     *
     * @param string $method
     * @param string $url
     * @param array|null $data
     * @return string
     */
    private function prepareRequest(string $method, string $url, array $data = null): string
    {
        switch ($method) {
            case "PUT":
                $this->curlOption(CURLOPT_PUT, 1);
                break;
            case 'PATCH':
                $this->curlOption(CURLOPT_CUSTOMREQUEST, 'PATCH');
                if ($data) {
                    $this->curlOption(CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'DELETE' :
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
                $this->curlOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        return $url;
    }
}
