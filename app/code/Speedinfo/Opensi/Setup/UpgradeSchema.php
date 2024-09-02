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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
  /**
   * @param SchemaSetupInterface $setup
   * @param ModuleContextInterface $context
   * @throws \Zend_Db_Exception
   */
  public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
  {
    $installer = $setup;
    $installer->startSetup();

    /**
     * ALTER TABLES SALES_ORDER & SALES_ORDER_GRID
     * ---------------------------------------------------------------------------
     */
    if ($context->getVersion() < '2.0.1.3')
    {
      // sales_order table
      $tableName = $installer->getTable('sales_order');

      if ($installer->getConnection()->isTableExists($tableName) == true) {
        $columns = [
          'opensi_reference' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'    => 20,
            'unsigned' => true,
            'nullable' => true,
            'default' => null,
            'comment' => 'OpenSi order reference',
            'after' => 'opensi_date',
          ],
        ];

        $connection = $installer->getConnection();

        foreach ($columns as $name => $definition) {
          $connection->addColumn($tableName, $name, $definition);
        }
      }

      // sales_order_grid table
      $tableName = $installer->getTable('sales_order_grid');

      if ($installer->getConnection()->isTableExists($tableName) == true) {
        $columns = [
          'opensi_reference' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'    => 20,
            'unsigned' => true,
            'nullable' => true,
            'default' => null,
            'comment' => 'OpenSi order reference',
            'after' => 'opensi_date',
          ],
        ];

        $connection = $installer->getConnection();

        foreach ($columns as $name => $definition) {
          $connection->addColumn($tableName, $name, $definition);
        }
      }
    }


    /**
     * BANK TRANSACTIONS - STANDARD & ADVANCED PARAMETERS
     * ---------------------------------------------------------------------------
     */
    if ($context->getVersion() < '2.0.3.7')
    {
      // Change the paths of the bank transactions in `core_config_data`
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/bank_transactions/payment_methods_type', 'opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods_type') WHERE `path` = 'opensi_configuration/bank_transactions/payment_methods_type'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/bank_transactions/payment_methods_active', 'opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods_active') WHERE `path` = 'opensi_configuration/bank_transactions/payment_methods_active'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/bank_transactions/payment_methods', 'opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods') WHERE `path` = 'opensi_configuration/bank_transactions/payment_methods'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/bank_transactions/payment_methods_used', 'opensi_configuration/bank_transactions/bank_transactions_standard/payment_methods_used') WHERE `path` = 'opensi_configuration/bank_transactions/payment_methods_used'");

      // Set defaults
      $data = [
        'scope' => 'default',
        'scope_id' => 0,
        'path' => 'opensi_configuration/bank_transactions/bank_transactions_advanced/payment_txn_types',
        'value' => 'capture',
      ];
      $setup->getConnection()->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);

      $data = [
        'scope' => 'default',
        'scope_id' => 0,
        'path' => 'opensi_preferences/manage_orders/comments_titles',
        'value' => '1',
      ];
      $setup->getConnection()->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
    }


    /**
     * STANDARDS & OPTIONNALS ORDER STATUSES
     * ---------------------------------------------------------------------------
     */
    if ($context->getVersion() < '2.0.5.4')
    {
      // Change the paths of the order statuses in `core_config_data`
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/validated', 'opensi_configuration/order_statuses/order_statuses_standard/validated') WHERE `path` = 'opensi_configuration/order_statuses/validated'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/processing', 'opensi_configuration/order_statuses/order_statuses_standard/processing') WHERE `path` = 'opensi_configuration/order_statuses/processing'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/partially_shipped', 'opensi_configuration/order_statuses/order_statuses_standard/partially_shipped') WHERE `path` = 'opensi_configuration/order_statuses/partially_shipped'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/complete', 'opensi_configuration/order_statuses/order_statuses_standard/complete') WHERE `path` = 'opensi_configuration/order_statuses/complete'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/cancel', 'opensi_configuration/order_statuses/order_statuses_standard/cancel') WHERE `path` = 'opensi_configuration/order_statuses/cancel'");

      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/validated_active', 'opensi_configuration/order_statuses/order_statuses_standard/validated_active') WHERE `path` = 'opensi_configuration/order_statuses/validated_active'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/processing_active', 'opensi_configuration/order_statuses/order_statuses_standard/processing_active') WHERE `path` = 'opensi_configuration/order_statuses/processing_active'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/partially_shipped_active', 'opensi_configuration/order_statuses/order_statuses_standard/partially_shipped_active') WHERE `path` = 'opensi_configuration/order_statuses/partially_shipped_active'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/complete_active', 'opensi_configuration/order_statuses/order_statuses_standard/complete_active') WHERE `path` = 'opensi_configuration/order_statuses/complete_active'");
      $installer->run(
      	"UPDATE IGNORE `{$setup->getTable('core_config_data')}` SET `path` = REPLACE(`path`, 'opensi_configuration/order_statuses/cancel_active', 'opensi_configuration/order_statuses/order_statuses_standard/cancel_active') WHERE `path` = 'opensi_configuration/order_statuses/cancel_active'");

      // Set defaults
      $data = [
        'scope' => 'default',
        'scope_id' => 0,
        'path' => 'opensi_configuration/order_statuses/order_statuses_optionnal/partially_paid',
        'value' => '',
      ];
      $setup->getConnection()->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);

      $data = [
        'scope' => 'default',
        'scope_id' => 0,
        'path' => 'opensi_configuration/order_statuses/order_statuses_optionnal/partially_paid_active',
        'value' => '0',
      ];
      $setup->getConnection()->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);

      $data = [
        'scope' => 'default',
        'scope_id' => 0,
        'path' => 'opensi_configuration/order_statuses/order_statuses_optionnal/totally_paid',
        'value' => '',
      ];
      $setup->getConnection()->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);

      $data = [
        'scope' => 'default',
        'scope_id' => 0,
        'path' => 'opensi_configuration/order_statuses/order_statuses_optionnal/totally_paid_active',
        'value' => '0',
      ];
      $setup->getConnection()->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
    }


    /**
     * END SETUP
     * ---------------------------------------------------------------------------
     */
    $installer->endSetup();
  }
}
