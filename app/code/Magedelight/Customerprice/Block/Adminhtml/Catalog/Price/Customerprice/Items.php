<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Catalog\Price\Customerprice;

use \Magento\Store\Model\StoreManagerInterface;
use Magedelight\Customerprice\Model\CustomerpriceDiscountFactory;

/**
 * Adminhtml customer price block.
 *
 */
class Items extends \Magento\Backend\Block\Template
{
    /**
     * Contains button descriptions to be shown at the top of accordion.
     *
     * @var array
     */
    protected $_buttons = [];


    /**
     * @var array
     */
    protected $websites;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var DiscountFactory
     */
    protected $discount;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param DiscountFactory $discount
     * @param \Magento\Directory\Helper\Data $_directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param array                                   $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CustomerpriceDiscountFactory $discount,
        \Magento\Directory\Helper\Data $_directoryHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        
        $this->discount = $discount;
        $this->_directoryHelper = $_directoryHelper;
        $this->_storeManager = $storeManager;
        
        parent::__construct($context,$data);
    }

    /**
     * Define block ID.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_customer_items_items');
    }

    /**
     * Accordion header text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Price per customer');
    }

    public function getReadOnly()
    {
        return false;
    }

    /**
     * Return HTML code of the block.
     *
     * @return string
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    public function getWebsites()
    {
        if ($this->websites !== null) {
            return $this->websites;
        }

        $this->websites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->_directoryHelper->getBaseCurrencyCode()]
        ];

        /** @var $website \Magento\Store\Model\Website */
        $allWebsites = $this->_storeManager->getWebsites();
        foreach ($allWebsites as $website) {
            $this->websites[$website->getId()] = [
                'name' => $website->getName(),
                'currency' => $website->getBaseCurrencyCode()
            ];
        }
        return $this->websites;
    }

    public function getWebsiteHtml()
    {
        $html = '';
        $allWebsites = $this->getWebsites();
        foreach ($allWebsites as $key => $value) {
            $html .= '<option value="'.$key.'">'.$value['name'].' '.$value['currency'].'</option>';
        }
        return $html;
    }
}
