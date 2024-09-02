<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Block\Adminhtml\Customer\Customerprice\Grid\Renderer;

use Magento\Framework\DataObject;

class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $helperdata;
    
    /**
     * @param \Magento\Framework\Pricing\Helper\Data $helperdata
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $helperdata
    ) {
        $this->helperdata = $helperdata;
    }

    /**
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        /*$mageCateId = $row->getMageCatId();
        $storeCat = $this->categoryFactory->create()->load($mageCateId);*/
        return $this->helperdata->currency($row->getPrice(), true, true);
    }
}