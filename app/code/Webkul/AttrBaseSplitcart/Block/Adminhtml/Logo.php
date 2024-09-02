<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AttrBaseSplitcart\Block\Adminhtml;

use \Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\PackageInfoFactory;

class Logo extends \Magento\Backend\Block\Template
{
    public const API_REQUEST_URI = 'https://wkm2repo.webkul.in/webkul-logo.png';

    public const MODULE_NAME = 'Webkul_AttrBaseSplitcart';

    public const EXPIRE_CONFIG_PATH = 'attrbasesplitcart/webkul/logo_expire_time';

    public const LOGO_CONFIG_PATH = 'attrbasesplitcart/webkul/logo';

    public const COUNTRY_CODE_PATH = 'general/country/default';

    public const CONTACT_EMAIL_PATH = 'trans_email/ident_general/email';
    
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var PackageInfoFactory
     */
    protected $_packageInfoFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param StoreManagerInterface $storeManager
     * @param WriterInterface $configWriter
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem\Driver\File $file,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        $this->_curl = $curl;
        $this->_storeManager = $storeManager;
        $this->configWriter = $configWriter;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->file = $file;
        $this->_packageInfoFactory = $packageInfoFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Webkul Logo
     *
     * @return string
     */
    public function getLogoUrl()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('path', self::EXPIRE_CONFIG_PATH);
        $expireTime = $collection->getFirstItem()->getValue();

        $dateTime = date('m/d/Y h:i:s a');
        
        if ((int) $expireTime < strtotime($dateTime)) {

            $endDate  = strtotime(date('m/d/Y h:i:s a', strtotime($dateTime . ' + 7 days')));

            $domain = $this->_storeManager->getStore()->getBaseUrl();
            $packageInfo = $this->_packageInfoFactory->create();
            $version = $packageInfo->getVersion(self::MODULE_NAME);

            $this->_curl->addHeader('domain', $domain);
            $this->_curl->addHeader('module', self::MODULE_NAME);
            $this->_curl->addHeader('version', $version);
            $this->_curl->addHeader('email', $this->scopeConfig->getValue(
                self::CONTACT_EMAIL_PATH
            ));
            $this->_curl->addHeader('country', $this->scopeConfig->getValue(
                self::COUNTRY_CODE_PATH
            ));

            try {
                $this->_curl->get(self::API_REQUEST_URI);
                $response = $this->_curl->getBody();
                $this->file->filePutContents('media/webkul-logo.png', $response);
                $mediaUrl = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $url = $mediaUrl.'webkul-logo.png';
                $this->configWriter->save(self::EXPIRE_CONFIG_PATH, $endDate);
                $this->configWriter->save(self::LOGO_CONFIG_PATH, $url);
            } catch (\Exception $e) {
                $url = '';
            }
        } else {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('path', self::LOGO_CONFIG_PATH);
            $url = $collection->getFirstItem()->getValue();
        }
        
        return $url;
    }
}
