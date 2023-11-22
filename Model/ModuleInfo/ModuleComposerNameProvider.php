<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\ModuleInfo;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Get composer module name.
 */
class ModuleComposerNameProvider
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var Reader
     */
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
     * Get composer module name.
     *
     * @param string $module
     * @return string
     */
    public function getName(string $module): string
    {
        if (!isset($this->cache[$module])) {
            $this->cache[$module] = $this->parseName($module);
        }

        return $this->cache[$module];
    }

    /**
     * Parse composer module name.
     *
     * @param string $module
     * @return string
     */
    private function parseName(string $module): string
    {
        try {
            $directoryPath = $this->reader->getModuleDir('', $module);
            $dataPath = $directoryPath . '/composer.json';
            $data = $this->filesystem->fileGetContents($dataPath);
            $name = $this->serializer->unserialize($data)['name'];
        } catch (\Exception $e) {
            $name = (string)__('Error reading module name.');
        }

        return $name;
    }
}
