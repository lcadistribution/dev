<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_TableCategoryView
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TableCategoryView\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\RequestForQuote\Model\Config\Source\AllowCategory;
use Mageplaza\TableCategoryView\Model\Config\Source\TableOptions;

/**
 * Class Data
 * @package Mageplaza\TableCategoryView\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mptablecategory';

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param HttpContext $httpContext
     * @param Registry $registry
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        HttpContext $httpContext,
        Registry $registry,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository  = $productRepository;
        $this->httpContext        = $httpContext;
        $this->_registry          = $registry;
        $this->customerSession    = $customerSession;
        $this->customerRepository = $customerRepository;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getDefaultImage()
    {
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return $mediaBaseUrl . 'catalog/product/placeholder/' .
            $this->scopeConfig->getValue('catalog/placeholder/small_image_placeholder');
    }

    /**
     * Get Store ID
     * @return int/null
     */
    public function getStoreId()
    {
        try {
            return $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->critical($exception);

            return null;
        }
    }

    /**
     * @param null $storeId
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isEnabled($storeId = null)
    {
        $currentCustomerGroup = (string) $this->getCurrentCustomerGroup();
        $customerGroups       = $this->getCustomerGroup();

        return parent::isEnabled($storeId) && in_array($currentCustomerGroup, $customerGroups, true);
    }

    /**
     * Get current theme id
     * @return mixed
     */
    public function getCurrentThemeId()
    {
        return $this->getConfigValue(DesignInterface::XML_PATH_THEME_ID);
    }

    /**
     * @return int|null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCurrentCustomerGroup()
    {
        $customerGroup = 0;

        if ($this->customerSession->isLoggedIn()) {
            $customerId    = $this->customerSession->getCustomerId();
            $customer      = $this->customerRepository->getById($customerId);
            $customerGroup = $customer->getGroupId();
        }

        return $customerGroup;
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getCustomerGroup($storeId = null)
    {
        $customerGroup = explode(',', $this->getConfigGeneral('customer_group', $storeId));

        return $customerGroup;
    }

    /**
     * @return bool|mixed
     */
    public function isDefault()
    {
        $config   = $this->getConfigGeneral('is_default');
        $category = $this->_registry->registry('current_category');
        if ($category) {
            $isDefault = $category->getData('mp_table_view_default');
            $config    = $category->getData('mp_table_view');

            if ($isDefault === null || $isDefault === '1') {
                return $this->getConfigGeneral('is_default');
            }
        }

        return !empty($config);
    }

    /**
     * @param string $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDisplay($field = '', $storeId = null)
    {
        return $this->getModuleConfig('display/' . $field, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAddButtonMode($storeId = null)
    {
        return $this->getDisplay('add_button', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAddButtonText($storeId = null)
    {
        return $this->getDisplay('button_text', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAddButtonColor($storeId = null)
    {
        return $this->getDisplay('button_color', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAddButtonBackground($storeId = null)
    {
        return $this->getDisplay('button_background', $storeId);
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array(TableOptions::IMAGE, $this->getTableOptions(), true);
    }

    /**
     * @return bool
     */
    public function isShortDesc()
    {
        return in_array(TableOptions::DESC, $this->getTableOptions(), true);
    }

    /**
     * @return bool
     */
    public function isReview()
    {
        return in_array(TableOptions::REVIEW, $this->getTableOptions(), true);
    }

    /**
     * @return bool
     */
    public function isStock()
    {
        return in_array(TableOptions::STOCK, $this->getTableOptions(), true);
    }

    /**
     * @return array
     */
    public function getTableOptions()
    {
        if (!empty($this->getDisplay('table_options'))) {
            return explode(',', $this->getDisplay('table_options'));
        }

        return [];
    }

    /**
     * @return string
     */
    public function getReviewText()
    {
        return $this->getDisplay('review_label');
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isMpcpgv($storeId = null)
    {
        if ($this->getConfigValue('mpcpgv/general/enabled') === null) {
            return '2';
        }
        if (empty($this->getConfigValue('mpcpgv/general/enabled'))) {
            return '1';
        }

        return $this->getDisplay('is_mp_cpgv', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getPopupOption($storeId = null)
    {
        return explode(',', $this->getDisplay('popup', $storeId) ?? '');
    }

    /**
     * @param null $productId
     *
     * @return array|bool
     */
    public function getOptionsProduct($productId = null)
    {
        $optionValue = [];
        $productId   = $productId ?: (int) $this->_request->getParam('id');

        try {
            $product = $this->productRepository->getById(
                $productId,
                false,
                $this->storeManager->getStore()->getId()
            );
            foreach ($product->getOptions() as $key1 => $option) {
                if (!empty($option->getValues())) {
                    $subValue = [];
                    foreach ($option->getValues() as $key2 => $value) {
                        $subValue[$key2] = $value->getData();
                    }
                    $optionValue[$option->getOptionId()]           = $option->getData();
                    $optionValue[$option->getOptionId()]['values'] = $subValue;
                } else {
                    $optionValue[$option->getOptionId()] = $option->getData();
                }
            }

            return $optionValue;
        } catch (NoSuchEntityException $exception) {
            $this->_logger->critical($exception);

            return false;
        }
    }

    /**
     * Check enable module
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function moduleIsEnable($moduleName)
    {
        $result = false;
        if ($this->_moduleManager->isEnabled($moduleName)) {
            switch ($moduleName) {
                case 'Mageplaza_CallForPrice':
                    $cfpHelper = $this->objectManager->create(\Mageplaza\CallForPrice\Helper\Data::class);
                    $result    = $cfpHelper->isEnabled() ? true : false;
                    break;
                case 'Mageplaza_RequestForQuote':
                    $rfqHelper = $this->objectManager->create(\Mageplaza\RequestForQuote\Helper\Data::class);
                    $result    = (!$rfqHelper->isShowButton() || !$this->checkCategory($rfqHelper));
                    break;
            }
        }

        return $result;
    }

    /**
     * @param \Mageplaza\RequestForQuote\Helper\Data $rfqHelper
     *
     * @return bool
     */
    public function checkCategory($rfqHelper)
    {
        $currentCat = $this->_registry->registry('current_category');

        if ($currentCat) {
            if ($rfqHelper->getConfigGeneral('allow_category') === AllowCategory::ALL_CATEGORIES) {
                return true;
            }

            return in_array($currentCat->getId(), $rfqHelper->getCategory(), true);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isLogin()
    {
        return $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
