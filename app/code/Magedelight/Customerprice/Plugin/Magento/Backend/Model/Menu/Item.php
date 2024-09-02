<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Plugin\Magento\Backend\Model\Menu;

class Item
{
    public function afterGetUrl($subject, $result)
    {
        $menuId = $subject->getId();

        if ($menuId == 'Magedelight_Customerprice::documentation') {
            $result = 'http://docs.magedelight.com/display/MAG/Price+Per+Customer+-+Magento+2';
        }

        return $result;
    }
}
