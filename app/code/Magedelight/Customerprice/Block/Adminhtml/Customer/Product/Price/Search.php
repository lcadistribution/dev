<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\Product\Price;
use Magento\Backend\Block\Template;

/**
 * Adminhtml customer price product search block.
 *
 */
class Search extends Template
{
   

    /**
     * Get header text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please Select Products to Add');
    }

    /**
     * Get buttons html.
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        $addButtonData = [
            'label' => __('Add Selected Product(s) to Customer'),
            'onclick' => 'addSelectedProduct()',
            'class' => 'action-add action-secondary',
            'id' => 'add_selected_products',
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
