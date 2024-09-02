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

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->query('DROP table opensi_comments');
        $setup->getConnection()->query('DROP table opensi_documents');
        $setup->getConnection()->query('DROP table opensi_shipping_methods');

        $setup->getConnection()->query('ALTER TABLE "sales_order" DROP "opensi_sync"');
        $setup->getConnection()->query('ALTER TABLE "sales_order" DROP "opensi_sync_at"');
        $setup->getConnection()->query('ALTER TABLE "sales_order" DROP "opensi_after"');
        $setup->getConnection()->query('ALTER TABLE "sales_order" DROP "opensi_date"');

        $setup->getConnection()->query('ALTER TABLE "sales_order_grid" DROP "opensi_sync"');
        $setup->getConnection()->query('ALTER TABLE "sales_order_grid" DROP "opensi_sync_at"');
        $setup->getConnection()->query('ALTER TABLE "sales_order_grid" DROP "opensi_after"');
        $setup->getConnection()->query('ALTER TABLE "sales_order_grid" DROP "opensi_date"');

        $setup->getConnection()->query('ALTER TABLE "sales_shipment" DROP "opensi_delivery_note"');

        $setup->endSetup();
    }
}
