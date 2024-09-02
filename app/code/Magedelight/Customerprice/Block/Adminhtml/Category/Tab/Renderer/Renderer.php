<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Category\Tab\Renderer;

class Renderer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render action
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<input class="checkbox customercustom"
         type="checkbox" relcust="'.$row->getEmail().'"
         rel="'.$row->getName().'" value = '.$row->getId().'>';
    }
}
