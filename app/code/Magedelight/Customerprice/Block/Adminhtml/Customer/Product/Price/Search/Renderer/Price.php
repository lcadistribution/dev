<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\Product\Price\Search\Renderer;

/**
 * Adminhtml customer price product search grid price column renderer.
 *
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Price
{
    /**
     * Render minimal price for downloadable products.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getTypeId() == 'downloadable') {
            $row->setPrice($row->getPrice());
        }

        return parent::render($row);
    }
}
