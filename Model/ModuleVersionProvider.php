<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Get composer module version.
 */
class ModuleVersionProvider
{
    /** @var Reader */
    private $reader;

    /**
     * @var File
     */
    private $filesystem;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Reader $reader
     * @param File $filesystem
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Reader              $reader,
        File                $filesystem,
        SerializerInterface $serializer
    ) {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
    }

    /**
     * Get composer module version.
     *
     * @param string $module
     * @return string
     */
    public function getVersion(string $module): string
    {
        try {
            $directoryPath = $this->reader->getModuleDir('', $module);
            $dataPath = $directoryPath . '/composer.json';
            $data = $this->filesystem->fileGetContents($dataPath);
            $version = $this->serializer->unserialize($data)['version'];
        } catch (\Exception $e) {
            $version = __('Error reading module version.');
        }

        return $version;
    }
}
