<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client\Command\DeleteCommand;

use Magento\Framework\HTTP\Client\Curl as CurlCore;

/**
 * Bold Checkout Curl client.
 */
class Curl extends CurlCore
{
    /**
     * Perform delete request.
     *
     * @param string $uri
     * @param string $params
     */
    public function delete(string $uri, string $params): void
    {
        $this->curlOption(CURLOPT_POSTFIELDS, $params);
        $this->makeRequest('DELETE', $uri, $params);
    }

    /**
     * Perform put request.
     *
     * @param string $uri
     * @param string $params
     */
    public function put(string $uri, string $params): void
    {
        $this->curlOption(CURLOPT_POSTFIELDS, $params);
        $this->makeRequest('PUT', $uri, $params);
    }
}
