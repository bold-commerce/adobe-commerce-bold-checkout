<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

/**
 * Get default payment CSS from file.
 */
class GetDefaultPaymentCss
{
    private const STYLES_FILE_NAME = 'iframe-styles.css';

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @param Reader $moduleReader
     * @param ReadFactory $readFactory
     */
    public function __construct(
        Reader      $moduleReader,
        ReadFactory $readFactory
    ) {
        $this->moduleReader = $moduleReader;
        $this->readFactory = $readFactory;
    }

    /**
     * Get default payment CSS from file.
     *
     * @return string
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function getCss(): string
    {
        $dir = $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Bold_Checkout');
        $read = $this->readFactory->create($dir . DIRECTORY_SEPARATOR . 'adminhtml' . DIRECTORY_SEPARATOR . 'web/css');
        if (!$read->isFile(self::STYLES_FILE_NAME)) {
            return '';
        }

        return $read->readFile(self::STYLES_FILE_NAME);
    }
}
