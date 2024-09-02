<?php

namespace Nwdthemes\Revslider\Model\Gallery;

use Nwdthemes\Revslider\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Storage extends \Magento\Cms\Model\Wysiwyg\Images\Storage {

    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem\Io\File|null
     */
    private $ioFile;

    /**
     * @var \Magento\Framework\File\Mime|null
     */
    private $mime;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @var string
     */
    private $allowedPathPattern;

    /**
     * @var array
     */
    private $allowedDirs;

    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Nwdthemes\Revslider\Helper\Gallery\Images $cmsWysiwygImages,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Cms\Model\Wysiwyg\Images\Storage\CollectionFactory $storageCollectionFactory,
        \Magento\MediaStorage\Model\File\Storage\FileFactory $storageFileFactory,
        \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory $directoryDatabaseFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        array $resizeParameters = [],
        array $extensions = [],
        array $dirs = [],
        array $data = []
    ) {
        $extendedExtensions = array_merge($extensions, ['video_allowed' => [
            'mp4'   => 1,
            'mp3'   => 1,
            'webm'  => 1,
            'ogv'   => 1,
            'avi'   => 1
        ]]);

        parent::__construct(
            $session,
            $backendUrl,
            $cmsWysiwygImages,
            $coreFileStorageDb,
            $filesystem,
            $imageFactory,
            $assetRepo,
            $storageCollectionFactory,
            $storageFileFactory,
            $storageDatabaseFactory,
            $directoryDatabaseFactory,
            $uploaderFactory,
            $resizeParameters,
            $extendedExtensions,
            $dirs,
            $data
        );

        $this->coreConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->logger = ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $this->file = ObjectManager::getInstance()->get(\Magento\Framework\Filesystem\Driver\File::class);
        $this->ioFile = ObjectManager::getInstance()->get(\Magento\Framework\Filesystem\Io\File::class);
        $this->mime = ObjectManager::getInstance()->get(\Magento\Framework\File\Mime::class);
        $this->coreConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);

    }

    public function getFilesCollection($path, $type = null)
    {
        $collectFiles = $this->isDirectoryAllowed($path);

        if ($this->_coreFileStorageDb->checkDbUsage()) {
            $files = $this->_storageDatabaseFactory->create()->getDirectoryFiles($path);

            /** @var \Magento\MediaStorage\Model\File\Storage\File $fileStorageModel */
            $fileStorageModel = $this->_storageFileFactory->create();
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }

        $collection = $this->getCollection(
            $path
        )->setCollectDirs(
            false
        )->setCollectFiles(
            $collectFiles
        )->setCollectRecursively(
            false
        )->setOrder(
            'mtime',
            \Magento\Framework\Data\Collection::SORT_ORDER_ASC
        );

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed) . ')$/i');
        }

        // prepare items
        foreach ($collection as $item) {
            $item->setId($this->_cmsWysiwygImages->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($this->_cmsWysiwygImages->getShortFilename($item->getBasename()));
            $item->setUrl($this->_cmsWysiwygImages->getCurrentUrl() . $item->getBasename());
            $driver = $this->_directory->getDriver();
            $itemStats = $driver->stat($item->getFilename());
            $item->setSize($itemStats['size']);
            $mimeType = $itemStats['mimetype'] ?? $this->mime->getMimeType($item->getFilename());
            $item->setMimeType($mimeType);

            if ($this->isImage($item->getBasename())) {
                $thumbUrl = $this->getThumbnailUrl($item->getFilename(), true);
                // generate thumbnail "on the fly" if it does not exists
                if (!$thumbUrl) {
                    $thumbUrl = $this->_backendUrl->getUrl('nwdthemes_revslider/*/thumbnail', ['file' => $item->getId()]);
                }

                $size = @getimagesize($item->getFilename());

                if (is_array($size)) {
                    $item->setWidth($size[0]);
                    $item->setHeight($size[1]);
                }
            } else {
                $thumbUrl = $this->_assetRepo->getUrl(self::THUMB_PLACEHOLDER_PATH_SUFFIX);
            }

            $item->setThumbUrl($thumbUrl);
        }

        return $collection;
    }

    /**
     * Upload and resize new file
     *
     * @param string $targetPath Target directory
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array File info Array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadFile($targetPath, $type = null)
    {
        if (!($this->isDirectoryAllowed($targetPath))) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t upload the file to the current folder right now. Please try another folder.')
            );
        }
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->_uploaderFactory->create(['fileId' => 'image']);
        $allowed = $this->getAllowedExtensions($type);
        if ($allowed) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        if (!$uploader->checkMimeType($this->getAllowedMimeTypes($type))) {
            throw new \Magento\Framework\Exception\LocalizedException(__('File validation failed.'));
        }
        $result = $uploader->save($targetPath);

        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t upload the file right now.'));
        }

        // create thumbnail
        if ($type != 'video') {
            $this->resizeFile(rtrim($targetPath, '/') . '/' . ltrim($uploader->getUploadedFileName(), '/'), true);
        }

        return $result;
    }

    /**
     * Prepare mime types config settings.
     *
     * @param string|null $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    private function getAllowedMimeTypes($type = null): array
    {
        $allowed = $this->getExtensionsList($type);
        if ($type == 'video') {
            $allowed = array_keys(array_filter($allowed));
            foreach ($allowed as $key => $item) {
                $allowed[$key] = $type . '/' . $item;
            }
        } else {
            $allowed = array_values(array_filter($allowed));
        }
        return $allowed;
    }


    /**
     * Get list of allowed file extensions with mime type in values.
     *
     * @param string|null $type
     * @return array
     */
    private function getExtensionsList($type = null): array
    {
        if (is_string($type) && array_key_exists("{$type}_allowed", $this->_extensions)) {
            $allowed = $this->_extensions["{$type}_allowed"];
        } else {
            $allowed = $this->_extensions['allowed'];
        }

        return $allowed;
    }

    /**
     * Check if directory is allowed
     *
     * @param string $directoryPath Absolute path to a directory
     * @return bool
     */
    private function isDirectoryAllowed($directoryPath): bool
    {
        $storageRoot = $this->_cmsWysiwygImages->getStorageRoot();
        $storageRootLength = strlen($storageRoot) - strlen(\Nwdthemes\Revslider\Helper\Images::IMAGE_DIR);
        $mediaSubPathname = $directoryPath ? substr($directoryPath, $storageRootLength) : false;
        if (!$mediaSubPathname) {
            return false;
        }
        $mediaSubPathname = ltrim($mediaSubPathname, '/');
        return strpos($mediaSubPathname, \Nwdthemes\Revslider\Helper\Images::IMAGE_DIR) === 0;
    }

}
