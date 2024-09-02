<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Config\Form\Field;

/**
 * Custom import CSV file field for customer price.
 *
 */
class Import extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setType('file');
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';

        $html .= '<input id="time_condition" type="hidden" name="'.$this->getName().'" value="'.time().'" />';

        //$html .= parent::getElementHtml();
        $html .= '<input id="customerprice_sample_import"
                  name="customerpriceimport" data-ui-id="file-groups-sample-fields-import-value"
                  value="" class="" type="file" />';

        return $html;
    }
}
