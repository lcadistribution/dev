<?php
/**
 * 2003-2017 OpenSi Connect
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Speedinfo
 * @package     Speedinfo_Opensi
 * @copyright   Copyright (c) 2017 Speedinfo SARL (http://www.speedinfo.fr)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Speedinfo\Opensi\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallData implements InstallDataInterface
{
  public function install( ModuleDataSetupInterface $setup, ModuleContextInterface $context )
  {
    /**
     * Update tables after InstallSchema
     */
    $setup->getConnection()->query('UPDATE '.$setup->getTable('sales_order'). ' SET opensi_after = 0, opensi_date = "0000-00-00 00:00:00"');
    $setup->getConnection()->query('UPDATE '.$setup->getTable('sales_order_grid'). ' SET opensi_after = 0, opensi_date = "0000-00-00 00:00:00"');


    /**
     * New order state/status - Validated
     */
    try {
      $status = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\Order\Status');
      $status->setData('status', 'validated')->setData('label', 'Validated')->save();
      $status->assignState(\Magento\Sales\Model\Order::STATE_NEW, false, true);
    }
    catch (Exception $e)
    {

    }


    /**
     * New order state/status - Partially Shipped
     */
    try {
      $status = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Model\Order\Status');
      $status->setData('status', 'partially_shipped')->setData('label', 'Partially shipped')->save();
      $status->assignState(\Magento\Sales\Model\Order::STATE_PROCESSING, false, true);
    }
    catch (Exception $e)
    {

    }
  }
}
