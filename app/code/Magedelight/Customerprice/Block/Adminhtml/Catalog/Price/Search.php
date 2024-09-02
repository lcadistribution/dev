<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Catalog\Price;
use Magento\Backend\Block\Template;
/**
 * Adminhtml customer price block.
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
        $this->setId('product_customer_search');
    }

    /**
     * Get header text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please Select Customers to Add');
    }

    /**
     * Get buttons html.
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        $addButtonData = [
            'label' => __('Add Selected Customer(s) to Product'),
            'onclick' => 'addSelectedProduct()',
            'class' => 'action-default scalable add action-default primary',
            'id' => 'add_selected_customers',
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
