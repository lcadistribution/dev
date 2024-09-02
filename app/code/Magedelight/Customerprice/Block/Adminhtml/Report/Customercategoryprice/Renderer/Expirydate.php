<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Report\Customercategoryprice\Renderer;

use Magento\Framework\DataObject;

class Expirydate extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if ($row->getExpiryDate()) {
            return $row->getExpiryDate();
        }else{
            return "Lifetime";
        }
    }
}