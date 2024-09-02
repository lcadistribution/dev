<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Simpledetailconfigurable\Plugin;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class DownloadSampleFilePlugin extends \Magento\ImportExport\Controller\Adminhtml\Import\Download
{
    /**
     * Module dir
     */
    const PRODUCT_ATTRIBUTES_SAMPLE_FILE = 'Bss_Simpledetailconfigurable';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        ReadFactory $readFactory,
        ComponentRegistrar $componentRegistrar
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct(
            $context,
            $fileFactory,
            $resultRawFactory,
            $readFactory,
            $componentRegistrar
        );
    }

    /**
     * @param \Magento\ImportExport\Controller\Adminhtml\Import\Download $subject
     * @param callable $proceed
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\ImportExport\Controller\Adminhtml\Import\Download $subject,
                                                                   $proceed
    ) {
        $fileName = $this->getRequest()->getParam('filename') . '.csv';
        if ($this->getRequest()->getParam('filename')=='sdcp_preselect') {
            try {
                $moduleDir = $this->componentRegistrar->getPath(
                    ComponentRegistrar::MODULE,
                    self::PRODUCT_ATTRIBUTES_SAMPLE_FILE
                );
                $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
                $directoryRead = $this->readFactory->create($moduleDir);
                $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

                if (!$directoryRead->isFile($filePath)) {
                    $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('*/import');
                    return $resultRedirect;
                }

                $fileSize = isset($directoryRead->stat($filePath)['size'])
                    ? $directoryRead->stat($filePath)['size'] : null;
                $magentoVersion = $this->productMetadata->getVersion();
                if (version_compare($magentoVersion, '2.4.7', '>=')) {
                    return $this->fileFactory->create(
                        $fileName,
                        $directoryRead->readFile($filePath),
                        DirectoryList::VAR_IMPORT_EXPORT,
                        'application/octet-stream',
                        $fileSize
                    );
                } else {
                    $this->fileFactory->create(
                        $fileName,
                        null,
                        DirectoryList::VAR_DIR,
                        'application/octet-stream',
                        $fileSize
                    );
                    $resultRaw = $this->resultRawFactory->create();
                    $resultRaw->setContents($directoryRead->readFile($filePath));
                    return $resultRaw;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $proceed();
    }
}
