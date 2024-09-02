<?php
/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
namespace Magedelight\Customerprice\Controller\Adminhtml\Report;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportReportExcel extends AbstractReport
{
    public function execute()
    {
        $fileName = 'customerpricereport.xml';
        $grid = $this->_view->getLayout()->createBlock(\Magedelight\Customerprice\Block\Adminhtml\Report\Customerprice\Grid::class);
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName), DirectoryList::VAR_DIR);
    }
}
