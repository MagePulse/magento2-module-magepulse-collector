<?php
/*
 * MagePulse
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MagePulse Proprietary EULA
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * https://magepulse.com/legal/magento-license/
 *
 * @category    MagePulse
 * @package     MagePulse_Collector
 * @copyright   Copyright (c) MagePulse (https://magepulse.com)
 * @license     https://magepulse.com/legal/magento-license/  MagePulse Proprietary EULA
 *
 */

declare(strict_types=1);

namespace MagePulse\Collector\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\Serializer\Json;

class ModuleMetaInfo
{
    private array $moduleMeta = [];
    private Reader $reader;
    private File $fileSystem;
    private Json $serializer;

    public function __construct(Reader $reader, File $fileSystem, Json $jsonSerializer)
    {
        $this->reader = $reader;
        $this->fileSystem = $fileSystem;
        $this->serializer = $jsonSerializer;
    }

    /**
     * Retrieve the module meta info
     *
     * @param string $moduleCode
     * @return mixed
     */
    public function getModuleMeta(string $moduleCode): mixed
    {
        if (!isset($this->moduleMeta[$moduleCode])) {
            $this->moduleMeta[$moduleCode] = '';
        }

        try {
            $moduleDir = $this->reader->getModuleDir('', $moduleCode);
            $composerFile = $moduleDir . '/composer.json';
            $fileData = $this->fileSystem->fileGetContents($composerFile);
            $this->moduleMeta[$moduleCode] = $this->serializer->unserialize($fileData);
        } catch (FileSystemException $e) {
            $this->moduleMeta[$moduleCode] = '';
        }

        return $this->moduleMeta[$moduleCode];
    }
}
