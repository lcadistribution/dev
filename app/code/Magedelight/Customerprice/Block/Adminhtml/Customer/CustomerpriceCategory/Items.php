<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\CustomerpriceCategory;

/**
 * Adminhtml customer price category items block.
 *
 */
class Items extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $customerpriceCategoryModel;
        
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategoryModel
     * @param array                                   $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magedelight\Customerprice\Api\Data\CustomerpriceCategoryInterface $customerpriceCategoryModel,
        array $data = []
    ) {
        
        $this->customerpriceCategoryModel = $customerpriceCategoryModel;
        
        parent::__construct($context,$data);
    }

    /**
     * Define block ID.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_category_items_items');
    }

    /**
     * Accordion header text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Price per customer by category');
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
    
    public function getExistsCategory()
    {
        $customerId = $this->getRequest()->getParam('id');
        
        $optionCollection = $this->customerpriceCategoryModel
                ->getCollection()
                ->addFieldToSelect('*')->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->setOrder('category_id');
        $exists= [];
        foreach ($optionCollection as $option) {
            $exists[] = $option['category_id'];
        }
        return json_encode($exists);
    }
}
