<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GENERAL_ENABLED = 'customerprice/general/enable';
    const XML_PATH_ADVANCED_ENABLED = 'customerprice/general/advanceprice';

    const ACTION_NAME_ARRAY = ["catalog_category_view", "catalogsearch_result_index","md_customerprice_index_offers"];

    const XML_PATH_SPECIAL_PRICE_BUTTON = 'customerprice/general/ask_specialprice';

    const XML_PATH_SPECIAL_PRICE_BUTTON_LABEL = 'customerprice/general/display_ask_specialprice_button_label';

    const XML_PATH_EXPORT_CRON_ENABLED_CATEGORY = 'customerprice/cron/export_category';
    const XML_PATH_EXPORT_CRON_ENABLED_PRODUCT = 'customerprice/cron/export_product';
    const XML_PATH_IMPORT_CRON_ENABLED_CATEGORY = 'customerprice/cron/import_category';
    const XML_PATH_IMPORT_CRON_ENABLED_PRODUCT = 'customerprice/cron/import_product';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Customer Group factory.
     *
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_customerGroupFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;
    
    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $_optionHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $orderCreate;

    const EMAIL_TEMPLATE = 'customerprice/general/approve_email_template';

    const DISAPPROVE_EMAIL_TEMPLATE = 'customerprice/general/disapprove_email_template';
    
    const SENDER_EMAIL = 'trans_email/ident_general/email';

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

     /**
      * @var \Magento\Framework\Pricing\Helper\Data
      */
    private $pricingHelper;


    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\GroupFactory $customerGroupFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Helper\Product\Configuration $optionHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Session $customerSessionFactory
     * @param \Magento\Customer\Model\Session $state
     * @param \Magento\Customer\Model\Session $orderCreate
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Helper\Product\Configuration $optionHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\App\State $state,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        LoggerInterface $logger,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_customerGroupFactory = $customerGroupFactory;
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_catalogData = $catalogData;
        $this->_optionHelper = $optionHelper;
        $this->_customerSession = $customerSession;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->state = $state;
        $this->orderCreate = $orderCreate;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logger = $logger;
        $this->httpContext = $httpContext;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context);
    }

    public function isEnabled()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_ENABLED, $storeScope);
    }

    public function isAdvanced()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_ADVANCED_ENABLED, $storeScope);
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function isCustomerPriceAllow()
    {
        $isEnabled = $this->isEnabled();
        $isLoggedIn = 0;
        if ($this->state->getAreaCode() == 'adminhtml') {
            $isLoggedIn = $this->orderCreate->getQuote()->getCustomerIsGuest() ? false : true;
        } else {
            $isLoggedIn = $this->customerSessionFactory->create()->isLoggedIn();
        }
        if ($isEnabled && $isLoggedIn) {
            return true;
        }
        return false;
    }

    /**
     * @param float $price
     * @param bool  $format
     *
     * @return float
     */
    public function convertPrice($price, $format = true)
    {
        return $format ? $this->priceCurrency->convertAndFormat($price) : $this->priceCurrency->convert($price);
    }

    /**
     * @param float $price
     *
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->_storeManager->getStore()
        );
    }

    /**
     *
     * @return boolean
     */
    public function displayButton()
    {
        if ($this->getConfig('customerprice/general/hide_price')) {
            return $this->getConfig('customerprice/general/display_button');
        } else {
            return false;
        }
    }

    /**
     *
     * @return array
     */
    public function getActionArray()
    {
        return self::ACTION_NAME_ARRAY;
    }

    public function specialPriceButton()
    {

        return $this->getConfig(self::XML_PATH_SPECIAL_PRICE_BUTTON);
    }

    public function specialPriceButtonLabel()
    {

        return $this->getConfig(self::XML_PATH_SPECIAL_PRICE_BUTTON_LABEL);
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerData();

        return $customer->getFirstName()." ".$customer->getLastName();
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerData();

        return $customer->getEmail();
    }

    /**
     * Send Mail
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail($customerpriceData, $date)
    {
        $email =  $customerpriceData['customer_email'];

        $this->inlineTranslation->suspend();
        

        /* email template */
        $template = $this->scopeConfig->getValue(
            self::EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getStoreId()
        );

        $vars = [
            'name' => $customerpriceData['customer_name'],
            'pname' => $customerpriceData['product_name'],
            'price' => $this->pricingHelper->currency($customerpriceData['price'], true, false),
            'date' =>  date("Y-m-d", strtotime($date ?? '')),
            'special_price' => $this->pricingHelper->currency($customerpriceData['new_price'], true, false),
        ];

        // set from email
        $sender = $this->senderEmail();

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getStoreId()
            ]
        )->setTemplateVars(
            $vars
        )->setFromByScope(
            $sender
        )->addTo(
            $email
        )->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        $this->inlineTranslation->resume();

        return $this;
    }

    public function senderEmail()
    {
        $sender ['email'] = $this->scopeConfig->getValue(self::SENDER_EMAIL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getStoreId());
        $sender['name'] = 'admin';
        return $sender;
    }

    /**
     * Send Mail
     *
     * @return $this
     *
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendDisapproveMail($item)
    {
        $email =  $item->getEmail();

        $this->inlineTranslation->suspend();
        

        /* email template */
        $template = $this->scopeConfig->getValue(
            self::DISAPPROVE_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getStoreId()
        );

        $vars = [
            'name' => $item->getName(),
            'pname' => $item->getPname(),
            'price' => $this->pricingHelper->currency($item->getActualPrice(), true, false),
            'date' =>  date("Y-m-d", strtotime($item->getCreatedAt() ?? '')),
            'special_price' => $this->pricingHelper->currency($item->getSpecialPrice(), true, false),
        ];

        // set from email
        $sender = $this->senderEmail();

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getStoreId()
            ]
        )->setTemplateVars(
            $vars
        )->setFromByScope(
            $sender
        )->addTo(
            $email
        )->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        $this->inlineTranslation->resume();

        return $this;
    }

    public function isCustomerLoggedIn()
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    public function isExportCronEnabledCategory()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_EXPORT_CRON_ENABLED_CATEGORY, $storeScope);
    }

    public function isExportCronEnabledProduct()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_EXPORT_CRON_ENABLED_PRODUCT, $storeScope);
    }

    public function isImportCronEnabledCategory()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_IMPORT_CRON_ENABLED_CATEGORY, $storeScope);
    }

    public function isImportCronEnabledProduct()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_IMPORT_CRON_ENABLED_PRODUCT, $storeScope);
    }

    /**
     * Get user id
     *
     * @return string
     */
    public function getUserId()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerData();

        return $customer->getId();
    }

    /**
     * @return int
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }
}
