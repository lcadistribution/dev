<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Report;

class Customercategoryprice extends \Magento\Backend\Block\Widget\Grid\Container
{
    
    /**
     * Template file
     *
     * @var string
     */
    protected $_template = 'Magedelight_Customerprice::report/grid/container.phtml';
    
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magedelight_Customerprice';
        $this->_controller = 'adminhtml_report_customercategoryprice';
        $this->_headerText = __('Customer Special Price - Category Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }
}
