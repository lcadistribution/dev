<?php

/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Customerprice\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Customerprice extends AbstractReport implements HttpGetActionInterface
{
    const MENU_ID = 'Magedelight_Customerprice::customerPriceProductReport';

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magedelight_Customerprice::customerPriceProductReport';
    
    public function execute()
    {
        $this->_view->loadLayout();

        $this->_setActiveMenu(self::MENU_ID);
       
        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_report_customerprice.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);
        $this->_view->renderLayout();
    }
}
