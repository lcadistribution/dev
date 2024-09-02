<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\Category\Price;
use Magento\Backend\Block\Template;
/**
 * Adminhtml customer price category search block.
 *
 */
class Search extends Template
{
    /**
     * Constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_category_search');
    }

    /**
     * Get header text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please Select Category to Add');
    }

    /**
     * Get buttons html.
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        $addButtonData = [
            'label' => __('Add Selected Category(s) to Customer'),
            'class' => 'action-add action-secondary',
            'id' => 'add_selected_Category',
        ];

        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            $addButtonData
        )->toHtml();
    }

    /**
     * Get header css class.
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-catalog-product';
    }
}
